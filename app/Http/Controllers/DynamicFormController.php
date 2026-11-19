<?php

namespace App\Http\Controllers;

use App\DTOs\Cms\DynamicFormSchemaDto;
use App\DTOs\Cms\DynamicFormSummaryDto;
use App\Exceptions\CmsApiException;
use App\Http\Requests\SubmitDynamicFormWebRequest;
use App\Http\Requests\UploadDynamicFormFileWebRequest;
use App\Services\CmsApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class DynamicFormController extends Controller
{
    public const RESPONDENT_COOKIE = 'rse_form_respondent';

    public function __construct(
        protected CmsApiService $cmsApiService
    ) {}

    public function index(Request $request): Response
    {
        $rawMode = $request->query('mode');
        $activeMode = in_array($rawMode, ['questionnaire', 'feedback'], true) ? $rawMode : 'all';
        $filterMode = $activeMode === 'all' ? null : $activeMode;

        $forms = [];
        $loadError = false;

        try {
            $rawForms = $this->cmsApiService->getDynamicForms($filterMode);
            $forms = array_values(array_filter(
                array_map(fn (array $item): DynamicFormSummaryDto => DynamicFormSummaryDto::fromArray($item), $rawForms),
                fn (DynamicFormSummaryDto $dto): bool => $dto->is_public_listed
            ));
        } catch (CmsApiException) {
            $loadError = true;
        }

        return response()->view('dynamic-forms.index', [
            'forms' => $forms,
            'activeMode' => $activeMode,
            'loadError' => $loadError,
        ], $loadError ? 503 : 200);
    }

    public function show(Request $request, string $slug): Response
    {
        $source = $request->query('source') === 'qr_barcode' ? 'qr_barcode' : 'web_public';

        return $this->renderSlug($request, $slug, source: $source);
    }

    /**
     * Render formulir dinamis berdasarkan slug (dipakai rute /formulir/{slug} dan delegasi opsional rute lama).
     *
     * @param  list<array{label: string, url?: string}>|null  $breadcrumbs
     */
    public function renderSlug(
        Request $request,
        string $slug,
        string $source = 'web_public',
        ?string $submitUrl = null,
        ?string $uploadUrl = null,
        ?array $breadcrumbs = null
    ): Response {
        $normalizedSource = in_array($source, ['web_public', 'qr_barcode'], true) ? $source : 'web_public';

        try {
            $rawForm = $this->cmsApiService->getDynamicFormBySlug($slug);
        } catch (CmsApiException) {
            return response()->view('dynamic-forms.show', [
                'form' => null,
                'availability' => null,
                'noindex' => true,
                'loadError' => true,
                'submitSource' => $normalizedSource,
                'rendererConfig' => null,
                'breadcrumbs' => $breadcrumbs,
            ], 503);
        }

        if ($rawForm === null) {
            abort(404, __('Formulir tidak ditemukan.'));
        }

        $form = DynamicFormSchemaDto::fromArray($rawForm);
        $availability = $form->availability();
        $noindex = ! $form->is_public_listed;

        if (! $form->allow_multiple_submissions) {
            $existingCookie = $request->cookie(self::RESPONDENT_COOKIE);
            if (! is_string($existingCookie) || ! Str::isUuid($existingCookie)) {
                Cookie::queue($this->makeRespondentCookie((string) Str::uuid(), $request));
            }
        }

        $resolvedUploadUrl = $uploadUrl ?? route('dynamic-forms.upload', ['slug' => $form->slug]);
        $resolvedSubmitUrl = $submitUrl ?? route('dynamic-forms.submit', ['slug' => $form->slug]);

        $rendererConfig = [
            'schema' => $form->toClientSchema(),
            'uploadUrl' => $resolvedUploadUrl,
            'submitUrl' => $resolvedSubmitUrl,
            'source' => $normalizedSource,
            'siteLocale' => app()->getLocale(),
            'catalogUrl' => route('dynamic-forms.index'),
            'homeUrl' => url('/'),
        ];

        return response()->view('dynamic-forms.show', [
            'form' => $form,
            'availability' => $availability,
            'noindex' => $noindex,
            'loadError' => false,
            'submitSource' => $normalizedSource,
            'rendererConfig' => $rendererConfig,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function upload(UploadDynamicFormFileWebRequest $request, string $slug): JsonResponse
    {
        try {
            $rawForm = $this->cmsApiService->getDynamicFormBySlug($slug);
        } catch (CmsApiException) {
            return response()->json([
                'success' => false,
                'message' => __('Layanan unggah berkas belum tersedia. Silakan coba lagi nanti.'),
                'errors' => [],
            ], 503);
        }

        if ($rawForm === null) {
            return response()->json([
                'success' => false,
                'message' => __('Formulir tidak ditemukan.'),
                'errors' => [],
            ], 404);
        }

        $form = DynamicFormSchemaDto::fromArray($rawForm);
        if (! $form->is_accepting_responses) {
            $closedMsg = $form->closed_message ?: __('Formulir ini sedang tidak menerima pengisian.');

            return response()->json([
                'success' => false,
                'message' => $closedMsg,
                'errors' => ['form' => [$closedMsg]],
            ], 422);
        }

        $result = $this->cmsApiService->uploadDynamicFormFile(
            $slug,
            (string) $request->validated('field_key'),
            $request->file('file')
        );

        if (($result['status'] ?? '') === 'success') {
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? __('Berkas berhasil diunggah.'),
                'data' => $result['data'] ?? [],
            ], 201);
        }

        $status = match ($result['http_code'] ?? 503) {
            404 => 404,
            422 => 422,
            429 => 429,
            default => 503,
        };
        $headers = isset($result['retry_after']) ? ['Retry-After' => (string) $result['retry_after']] : [];

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? __('Gagal mengunggah berkas.'),
            'errors' => $result['errors'] ?? [],
        ], $status, $headers);
    }

    public function submit(SubmitDynamicFormWebRequest $request, string $slug): JsonResponse
    {
        return $this->submitSlug($request, $slug);
    }

    public function submitSlug(
        SubmitDynamicFormWebRequest $request,
        string $slug,
        ?string $forcedSource = null
    ): JsonResponse {
        try {
            $rawForm = $this->cmsApiService->getDynamicFormBySlug($slug);
        } catch (CmsApiException) {
            return response()->json([
                'success' => false,
                'message' => __('Layanan formulir belum tersedia. Silakan coba lagi nanti.'),
                'errors' => [],
            ], 503);
        }

        if ($rawForm === null) {
            return response()->json([
                'success' => false,
                'message' => __('Formulir tidak ditemukan.'),
                'errors' => [],
            ], 404);
        }

        $form = DynamicFormSchemaDto::fromArray($rawForm);
        if (! $form->is_accepting_responses) {
            $closedMsg = $form->closed_message ?: __('Formulir ini sedang tidak menerima pengisian.');

            return response()->json([
                'success' => false,
                'message' => $closedMsg,
                'errors' => ['form' => [$closedMsg]],
            ], 422);
        }

        $validated = $request->validated();
        $source = $forcedSource ?? ($validated['source'] ?? 'web_public');
        if (! in_array($source, ['web_public', 'qr_barcode'], true)) {
            $source = 'web_public';
        }

        $locale = $validated['locale'] ?? (in_array(app()->getLocale(), ['id', 'en'], true) ? app()->getLocale() : 'id');

        $newCookieUuid = null;
        $respondentKey = null;

        if (! $form->allow_multiple_submissions) {
            $cookieVal = $request->cookie(self::RESPONDENT_COOKIE);
            if (is_string($cookieVal) && Str::isUuid($cookieVal)) {
                $respondentKey = $cookieVal;
            } else {
                $newCookieUuid = (string) Str::uuid();
                $respondentKey = $newCookieUuid;
            }
        }

        $payload = [
            'version_id' => $validated['version_id'] ?? $form->published_version_id,
            'answers' => $validated['answers'] ?? [],
            'hp_website' => $validated['hp_website'] ?? null,
            'locale' => $locale,
            'source' => $source,
        ];

        if ($respondentKey !== null) {
            $payload['respondent_key'] = $respondentKey;
        }

        $result = $this->cmsApiService->submitDynamicForm($slug, $payload);

        if (($result['status'] ?? '') === 'success') {
            $response = response()->json([
                'success' => true,
                'message' => $result['message'] ?? __('Jawaban Anda berhasil dikirim.'),
                'data' => $result['data'] ?? [],
            ], 201);

            if ($newCookieUuid !== null) {
                $response->withCookie($this->makeRespondentCookie($newCookieUuid, $request));
            }

            return $response;
        }

        $status = match ($result['http_code'] ?? 503) {
            404 => 404,
            422 => 422,
            429 => 429,
            default => 503,
        };
        $headers = isset($result['retry_after']) ? ['Retry-After' => (string) $result['retry_after']] : [];
        $errors = is_array($result['errors'] ?? null) ? $result['errors'] : [];
        $message = $result['message'] ?? __('Gagal memproses pengiriman formulir.');

        if ($status === 422) {
            if (isset($errors['respondent_key'])) {
                $translated = __('Anda sudah pernah mengisi formulir ini sebelumnya.');
                $errors['respondent_key'] = [$translated];
                $message = $translated;
            } elseif (isset($errors['version_id'])) {
                $translated = __('Versi formulir telah diperbarui. Muat ulang formulir sebelum mengirim.');
                $errors['version_id'] = [$translated];
                $message = $translated;
            } elseif (isset($errors['hp_website'])) {
                $translated = __('Pengisian formulir ditolak oleh filter keamanan.');
                $errors['hp_website'] = [$translated];
                $message = $translated;
            }
        }

        $response = response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status, $headers);

        if ($newCookieUuid !== null) {
            $response->withCookie($this->makeRespondentCookie($newCookieUuid, $request));
        }

        return $response;
    }

    private function makeRespondentCookie(string $uuid, Request $request): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            name: self::RESPONDENT_COOKIE,
            value: $uuid,
            minutes: 60 * 24 * 365,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        );
    }
}
