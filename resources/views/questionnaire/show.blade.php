<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    @include('components.seo-meta', ['title' => __($title) . ' - RS St. Elisabeth Semarang'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/top-bar.css',
        'resources/css/questionnaire.css'
    ])
</head>
<body>

    @include('components.navbar')

    <main class="questionnaire-page-wrapper">

        @include('components.hero-section', [
            'badgeIcon' => $badgeIcon,
            'badgeText' => __($badge),
            'title' => __($title),
            'subtitle' => __('Pelanggan yang terhormat, demi perbaikan mutu pelayanan kami, mohon Bapak/Ibu/Sdr dapat meluangkan waktu untuk mengisi kuesioner kepuasan pasien kami berikut ini. Terima kasih atas partisipasinya.'),
            'breadcrumbs' => [
                ['label' => __('Beranda'), 'url' => url('/')],
                ['label' => __('Kuesioner'), 'url' => route('questionnaire.index')],
                ['label' => __($badge)],
            ],
        ])

        <!-- 2. Form Container Section -->
        <section class="questionnaire-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-9">

                        {{-- TAMPILAN SUKSES SETELAH SUBMIT --}}
                        @if(session('questionnaire_success'))
                        <div class="questionnaire-main-card questionnaire-success-card">
                            <div class="success-heart-badge">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>
                            <h2 class="fw-bold mb-2 text-dark">{{ __('Terima Kasih Atas Partisipasi Anda') }}</h2>
                            <p class="text-muted mb-4 mx-auto" style="max-width: 600px; line-height: 1.7;">
                                {{ session('questionnaire_message') ?? __('Penilaian dan masukan Anda telah berhasil kami terima. Setiap evaluasi sangat berharga bagi manajemen dalam terus meningkatkan mutu pelayanan medis dan kenyamanan pasien di RS St. Elisabeth Semarang.') }}
                            </p>

                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="{{ route('questionnaire.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 questionnaire-success-btn">
                                    <i class="fa-solid fa-list-check" aria-hidden="true"></i>
                                    <span>{{ __('Isi Kuesioner Lainnya') }}</span>
                                </a>
                                <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 questionnaire-success-btn">
                                    <img
                                        src="{{ Vite::asset('resources/images/web-icon.webp') }}"
                                        alt=""
                                        width="22"
                                        height="22"
                                        aria-hidden="true"
                                        class="questionnaire-hospital-icon"
                                    >
                                    <span>{{ __('Website RS St. Elisabeth Semarang') }}</span>
                                </a>
                            </div>
                        </div>

                        {{-- FORMULIR PENGISIAN --}}
                        @else
                        @php
                            $fieldMeta = \App\Support\QuestionnaireDefinition::fieldMetadata($type, $suggestionPrompt ?? null);
                        @endphp
                        <div class="questionnaire-main-card">

                            {{-- Pesan Error Global / Flash --}}
                            @if(session('questionnaire_error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-2"></i>
                                {{ session('questionnaire_error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif

                            {{-- Ringkasan Error Spesifik per Butir Pertanyaan (Server-Side & Client-Side Reaktif) --}}
                            <div
                                id="questionnaireErrorSummary"
                                class="questionnaire-error-summary mb-4 {{ $errors->any() ? '' : 'd-none' }}"
                                role="alert"
                                tabindex="-1"
                                data-label-question-prefix="{{ __('Pertanyaan') }}"
                                data-label-default-required="{{ __('Mohon pilih salah satu penilaian pada pertanyaan ini.') }}"
                                data-label-suggestions-required="{{ __('Mohon tuliskan saran atau masukan Anda untuk peningkatan layanan rawat jalan kami.') }}"
                                data-label-suggestions-min="{{ __('Saran atau masukan minimal memuat :min karakter.', ['min' => 3]) }}"
                            >
                                <div class="questionnaire-error-summary-header">
                                    <div class="questionnaire-error-summary-icon" aria-hidden="true">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                    </div>
                                    <div>
                                        <div class="questionnaire-error-summary-title">
                                            {{ __('Mohon periksa kembali isian formulir Anda:') }}
                                        </div>
                                        <div class="questionnaire-error-summary-subtitle">
                                            {{ __('Klik pada daftar di bawah untuk langsung menuju ke pertanyaan yang belum lengkap:') }}
                                        </div>
                                    </div>
                                </div>
                                <ul class="questionnaire-error-list mb-0" id="questionnaireErrorList">
                                    @if($errors->any())
                                        @foreach($errors->messages() as $errField => $errMessages)
                                            @php
                                                $firstMsg = $errMessages[0] ?? '';
                                                $metaItem = $fieldMeta[$errField] ?? null;
                                                $targetCardId = $metaItem['card_id'] ?? null;
                                                $badgeLabel = $metaItem['badge'] ?? null;
                                            @endphp
                                            <li class="questionnaire-error-list-item" data-error-field="{{ $errField }}">
                                                @if($targetCardId)
                                                    <a href="#{{ $targetCardId }}" class="questionnaire-error-link" data-target-card="{{ $targetCardId }}" data-error-field="{{ $errField }}">
                                                        @if($badgeLabel)
                                                            <span class="questionnaire-error-badge">{{ $badgeLabel }}</span>
                                                        @endif
                                                        <span class="questionnaire-error-text">{{ $firstMsg }}</span>
                                                        <i class="fa-solid fa-arrow-turn-down questionnaire-error-jump-icon" aria-hidden="true"></i>
                                                    </a>
                                                @else
                                                    <div class="questionnaire-error-link is-static">
                                                        <span class="questionnaire-error-text">{{ $firstMsg }}</span>
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>

                            <!-- Indikator Progres Pengisian Interaktif -->
                            <div class="questionnaire-progress-tracker">
                                <div class="progress-tracker-text">
                                    <i class="fa-regular fa-circle-check text-primary"></i>
                                    <span>{{ __('Pertanyaan Terjawab:') }}</span>
                                    <span class="progress-tracker-badge" id="answeredBadge">0 / {{ count($questions) + 3 }}</span>
                                </div>
                                <div class="progress-tracker-bar-wrap">
                                    <div class="progress-tracker-bar-fill" id="progressBarFill" style="width: 0%;"></div>
                                </div>
                            </div>

                            <form action="{{ $actionRoute }}" method="POST" id="patientQuestionnaireForm" novalidate>
                                @csrf

                                {{-- Honeypot anti-spam (tersembunyi dari manusia) --}}
                                <div style="display: none !important;" aria-hidden="true">
                                    <label for="hp_website">Leave this empty</label>
                                    <input type="text" name="hp_website" id="hp_website" tabindex="-1" autocomplete="off">
                                    <input type="hidden" name="questionnaire_type" value="{{ $type }}">
                                </div>

                                <!-- BAGIAN 1: PROFIL RESPONDEN -->
                                <div class="questionnaire-step-header">
                                    <span class="step-number">1</span>
                                    <h2 class="step-title">{{ __('Profil Responden') }}</h2>
                                </div>

                                <div class="mb-4">
                                    <!-- Pertanyaan Usia -->
                                    <div
                                        class="question-item-card mb-3 @error('age_group') has-error @enderror"
                                        id="card_age_group"
                                        data-rating-card="1"
                                        data-field-name="age_group"
                                        data-question-badge="{{ __('Pertanyaan :number', ['number' => 'A']) }}"
                                        data-question-title="{{ __('Berapakah usia anda?') }}"
                                        data-required-reason="{{ __('Mohon pilih kelompok usia Anda.') }}"
                                    >
                                        <div class="question-text">
                                            <span class="question-number-badge">A</span>
                                            {{ __('Berapakah usia anda?') }} <span class="text-danger">*</span>
                                        </div>
                                        <div class="demography-pill-group">
                                            @foreach($demographics['age_groups'] as $age)
                                            <label class="demography-pill-label">
                                                <input type="radio" 
                                                       name="age_group" 
                                                       value="{{ $age }}" 
                                                       class="demography-pill-input"
                                                       {{ old('age_group') === $age ? 'checked' : '' }} 
                                                       required>
                                                <span class="demography-pill-btn">
                                                    <i class="fa-regular fa-circle-dot"></i> {{ __($age) }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                        <div class="questionnaire-inline-error text-danger small mt-2 {{ $errors->has('age_group') ? '' : 'd-none' }}" data-inline-error="age_group">
                                            <i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>
                                            <span class="inline-error-text">{{ $errors->first('age_group') }}</span>
                                        </div>
                                    </div>

                                    <!-- Pertanyaan Jenis Kelamin -->
                                    <div
                                        class="question-item-card mb-3 @error('gender') has-error @enderror"
                                        id="card_gender"
                                        data-rating-card="1"
                                        data-field-name="gender"
                                        data-question-badge="{{ __('Pertanyaan :number', ['number' => 'B']) }}"
                                        data-question-title="{{ __('Apakah jenis kelamin anda?') }}"
                                        data-required-reason="{{ __('Mohon pilih jenis kelamin Anda.') }}"
                                    >
                                        <div class="question-text">
                                            <span class="question-number-badge">B</span>
                                            {{ __('Apakah jenis kelamin anda?') }} <span class="text-danger">*</span>
                                        </div>
                                        <div class="demography-pill-group">
                                            @foreach($demographics['genders'] as $gen)
                                            <label class="demography-pill-label">
                                                <input type="radio" 
                                                       name="gender" 
                                                       value="{{ $gen }}" 
                                                       class="demography-pill-input"
                                                       {{ old('gender') === $gen ? 'checked' : '' }} 
                                                       required>
                                                <span class="demography-pill-btn">
                                                    <i class="fa-regular fa-circle-dot"></i> {{ __($gen) }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                        <div class="questionnaire-inline-error text-danger small mt-2 {{ $errors->has('gender') ? '' : 'd-none' }}" data-inline-error="gender">
                                            <i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>
                                            <span class="inline-error-text">{{ $errors->first('gender') }}</span>
                                        </div>
                                    </div>

                                    <!-- Pertanyaan Cara Bayar -->
                                    <div
                                        class="question-item-card mb-3 @error('payment_method') has-error @enderror"
                                        id="card_payment_method"
                                        data-rating-card="1"
                                        data-field-name="payment_method"
                                        data-question-badge="{{ __('Pertanyaan :number', ['number' => 'C']) }}"
                                        data-question-title="{{ __('Apakah cara bayar yang anda gunakan untuk melakukan pemeriksaan di Rumah Sakit ST. Elisabeth Semarang saat ini?') }}"
                                        data-required-reason="{{ __('Mohon pilih cara bayar yang Anda gunakan.') }}"
                                    >
                                        <div class="question-text">
                                            <span class="question-number-badge">C</span>
                                            {{ __('Apakah cara bayar yang anda gunakan untuk melakukan pemeriksaan di Rumah Sakit ST. Elisabeth Semarang saat ini?') }} <span class="text-danger">*</span>
                                        </div>
                                        <div class="demography-pill-group">
                                            @foreach($demographics['payments'] as $pay)
                                            <label class="demography-pill-label">
                                                <input type="radio" 
                                                       name="payment_method" 
                                                       value="{{ $pay }}" 
                                                       class="demography-pill-input"
                                                       {{ old('payment_method') === $pay ? 'checked' : '' }} 
                                                       required>
                                                <span class="demography-pill-btn">
                                                    <i class="fa-regular fa-circle-dot"></i> {{ __($pay) }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                        <div class="questionnaire-inline-error text-danger small mt-2 {{ $errors->has('payment_method') ? '' : 'd-none' }}" data-inline-error="payment_method">
                                            <i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>
                                            <span class="inline-error-text">{{ $errors->first('payment_method') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- BAGIAN 2: KUALITAS PELAYANAN RS ST. ELISABETH -->
                                <div class="questionnaire-step-header mt-5">
                                    <span class="step-number">2</span>
                                    <h2 class="step-title">{{ __('Kualitas Pelayanan Rumah Sakit ST. Elisabeth Semarang') }}</h2>
                                </div>

                                <div class="mb-4">
                                    @foreach($questions as $idx => $q)
                                    @php
                                        $qName = $q['name'];
                                        $qNumber = $idx + 1;
                                        $qTranslated = __($q['text']);
                                    @endphp
                                    <div
                                        class="question-item-card @error($qName) has-error @enderror"
                                        id="card_{{ $qName }}"
                                        data-rating-card="1"
                                        data-field-name="{{ $qName }}"
                                        data-question-badge="{{ __('Pertanyaan :number', ['number' => $qNumber]) }}"
                                        data-question-title="{{ $qTranslated }}"
                                        data-required-reason="{{ __('Mohon pilih salah satu penilaian pada pertanyaan ini.') }}"
                                    >
                                        <div class="question-text">
                                            <span class="question-number-badge">{{ $qNumber }}</span>
                                            {{ $qTranslated }} <span class="text-danger">*</span>
                                        </div>
                                        <div class="rating-pill-group">
                                            @foreach($scales[$q['scale']] as $scaleOpt)
                                            <label class="rating-pill-label">
                                                <input type="radio" 
                                                       name="{{ $qName }}" 
                                                       value="{{ $scaleOpt }}" 
                                                       class="rating-pill-input"
                                                       {{ old($qName) === $scaleOpt ? 'checked' : '' }} 
                                                       required>
                                                <span class="rating-pill-btn">
                                                    {{ __($scaleOpt) }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                        <div class="questionnaire-inline-error text-danger small mt-2 {{ $errors->has($qName) ? '' : 'd-none' }}" data-inline-error="{{ $qName }}">
                                            <i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>
                                            <span class="inline-error-text">{{ $errors->first($qName) }}</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- BAGIAN 3: SARAN & MASUKAN -->
                                <div class="questionnaire-step-header mt-5">
                                    <span class="step-number">3</span>
                                    <h2 class="step-title">{{ __('Saran & Masukan') }}</h2>
                                </div>

                                <div
                                    class="question-item-card question-item-card--textarea mb-4 @error('suggestions') has-error @enderror"
                                    id="card_suggestions"
                                    data-field-name="suggestions"
                                    data-is-required="{{ $isSuggestionRequired ? '1' : '0' }}"
                                    data-question-badge="{{ __('Saran & Masukan') }}"
                                    data-question-title="{{ __($suggestionPrompt) }}"
                                    data-required-reason="{{ __('Mohon tuliskan saran atau masukan Anda untuk peningkatan layanan rawat jalan kami.') }}"
                                >
                                    <label for="suggestions" class="question-text d-block">
                                        {{ __($suggestionPrompt) }}
                                        @if($isSuggestionRequired)
                                        <span class="text-danger">*</span>
                                        @else
                                        <span class="text-muted fw-normal small">({{ __('Opsional') }})</span>
                                        @endif
                                    </label>
                                    <textarea name="suggestions" 
                                              id="suggestions" 
                                              rows="4" 
                                              class="form-control questionnaire-textarea @error('suggestions') is-invalid @enderror" 
                                              placeholder="{{ __('Tuliskan saran atau masukan Anda di sini untuk peningkatan kualitas pelayanan kami...') }}"
                                              maxlength="3000"
                                              {{ $isSuggestionRequired ? 'required' : '' }}>{{ old('suggestions') }}</textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        @if($isSuggestionRequired)
                                        <small class="text-muted">{{ __('Wajib diisi untuk membantu kami mengevaluasi layanan rawat jalan.') }}</small>
                                        @else
                                        <small class="text-muted">{{ __('Masukan Anda akan menjadi bahan pertimbangan utama kami.') }}</small>
                                        @endif
                                        <div class="char-counter"><span id="charCount">0</span>/3000</div>
                                    </div>
                                    <div class="questionnaire-inline-error text-danger small mt-2 {{ $errors->has('suggestions') ? '' : 'd-none' }}" data-inline-error="suggestions">
                                        <i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>
                                        <span class="inline-error-text">{{ $errors->first('suggestions') }}</span>
                                    </div>
                                </div>

                                <!-- Pernyataan Privasi -->
                                <div class="alert alert-light border rounded-3 p-3 text-muted small mb-4">
                                    <i class="fa-solid fa-lock text-primary me-2"></i>
                                    {{ __('Data dan penilaian yang Anda sampaikan dilindungi kerahasiaannya dan hanya dipergunakan untuk kepentingan evaluasi peningkatan mutu pelayanan RS St. Elisabeth Semarang.') }}
                                </div>

                                <!-- Tombol Kirim -->
                                <div class="text-center pt-2">
                                    <button type="submit" class="btn-submit-questionnaire" id="submitBtn">
                                        <span class="btn-text">
                                            <i class="fa-solid fa-paper-plane me-2"></i>{{ __('Kirim Penilaian Kuesioner') }}
                                        </span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            {{ __('Sedang Mengirim...') }}
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </section>

    </main>

    @include('components.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9HjB+Zp6Gvpepeco+dpeP4bF+rPz5mJ5+F5u5b5Fk5b5Fk5b5" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('patientQuestionnaireForm');
            if (!form) return;

            const submitBtn = document.getElementById('submitBtn');
            const btnText = submitBtn.querySelector('.btn-text');
            const btnSpinner = submitBtn.querySelector('.btn-spinner');
            const suggestionsTextarea = document.getElementById('suggestions');
            const charCount = document.getElementById('charCount');
            const answeredBadge = document.getElementById('answeredBadge');
            const progressBarFill = document.getElementById('progressBarFill');
            const errorSummary = document.getElementById('questionnaireErrorSummary');
            const errorList = document.getElementById('questionnaireErrorList');

            // Hitung total butir pertanyaan wajib (Demografi 3 + Pertanyaan Rating N)
            const ratingCards = document.querySelectorAll('.question-item-card[data-rating-card="1"]');
            const allFieldCards = document.querySelectorAll('.question-item-card[data-field-name]');
            const totalRequired = ratingCards.length;

            function focusAndScrollCard(cardId) {
                const targetCard = document.getElementById(cardId);
                if (!targetCard) return;

                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetCard.classList.add('is-focused-target');
                setTimeout(() => targetCard.classList.remove('is-focused-target'), 1400);

                const firstControl = targetCard.querySelector('input[type="radio"], textarea, input');
                if (firstControl) {
                    firstControl.focus({ preventScroll: true });
                }
            }

            if (errorSummary) {
                errorSummary.addEventListener('click', function (e) {
                    const link = e.target.closest('[data-target-card]');
                    if (!link) return;
                    e.preventDefault();
                    focusAndScrollCard(link.getAttribute('data-target-card'));
                });
            }

            function clearCardError(fieldName) {
                const card = document.getElementById('card_' + fieldName);
                if (card) {
                    card.classList.remove('has-error');
                    const inlineErr = card.querySelector('[data-inline-error="' + fieldName + '"]');
                    if (inlineErr) {
                        inlineErr.classList.add('d-none');
                    }
                    const textarea = card.querySelector('textarea');
                    if (textarea) {
                        textarea.classList.remove('is-invalid');
                    }
                }

                if (errorList) {
                    const item = errorList.querySelector('[data-error-field="' + fieldName + '"]');
                    if (item) {
                        item.remove();
                    }
                    if (errorList.children.length === 0 && errorSummary) {
                        errorSummary.classList.add('d-none');
                    }
                }
            }

            function setCardError(card, fullMessage, inlineReason) {
                const fieldName = card.getAttribute('data-field-name');
                card.classList.add('has-error');

                const inlineErr = card.querySelector('[data-inline-error="' + fieldName + '"]');
                if (inlineErr) {
                    const textSpan = inlineErr.querySelector('.inline-error-text');
                    if (textSpan) {
                        textSpan.textContent = inlineReason;
                    }
                    inlineErr.classList.remove('d-none');
                }

                const textarea = card.querySelector('textarea');
                if (textarea) {
                    textarea.classList.add('is-invalid');
                }
            }

            function validateAllFieldsClientSide() {
                const missing = [];
                const minMsg = errorSummary
                    ? (errorSummary.getAttribute('data-label-suggestions-min') || 'Minimal 3 karakter.')
                    : 'Minimal 3 karakter.';

                allFieldCards.forEach(card => {
                    const fieldName = card.getAttribute('data-field-name');
                    const badge = card.getAttribute('data-question-badge') || '';
                    const title = card.getAttribute('data-question-title') || '';
                    const reason = card.getAttribute('data-required-reason') || '';

                    if (fieldName === 'suggestions') {
                        const isReq = card.getAttribute('data-is-required') === '1';
                        const val = suggestionsTextarea ? suggestionsTextarea.value.trim() : '';
                        if (isReq && val === '') {
                            const fullMsg = `${badge} (${title}) - ${reason}`;
                            setCardError(card, fullMsg, reason);
                            missing.push({ fieldName, cardId: card.id, badge, fullMsg });
                        } else if (isReq && val.length < 3) {
                            const fullMsg = `${badge} - ${minMsg}`;
                            setCardError(card, fullMsg, minMsg);
                            missing.push({ fieldName, cardId: card.id, badge, fullMsg });
                        } else {
                            clearCardError(fieldName);
                        }
                        return;
                    }

                    const checked = card.querySelector('input[type="radio"]:checked');
                    if (!checked) {
                        const fullMsg = `${badge} (${title}) - ${reason}`;
                        setCardError(card, fullMsg, reason);
                        missing.push({ fieldName, cardId: card.id, badge, fullMsg });
                    } else {
                        clearCardError(fieldName);
                    }
                });

                if (missing.length > 0 && errorSummary && errorList) {
                    errorList.innerHTML = '';
                    missing.forEach(item => {
                        const li = document.createElement('li');
                        li.className = 'questionnaire-error-list-item';
                        li.setAttribute('data-error-field', item.fieldName);

                        const a = document.createElement('a');
                        a.href = '#' + item.cardId;
                        a.className = 'questionnaire-error-link';
                        a.setAttribute('data-target-card', item.cardId);
                        a.setAttribute('data-error-field', item.fieldName);

                        if (item.badge) {
                            const badgeSpan = document.createElement('span');
                            badgeSpan.className = 'questionnaire-error-badge';
                            badgeSpan.textContent = item.badge;
                            a.appendChild(badgeSpan);
                        }

                        const textSpan = document.createElement('span');
                        textSpan.className = 'questionnaire-error-text';
                        textSpan.textContent = item.fullMsg;
                        a.appendChild(textSpan);

                        const icon = document.createElement('i');
                        icon.className = 'fa-solid fa-arrow-turn-down questionnaire-error-jump-icon';
                        icon.setAttribute('aria-hidden', 'true');
                        a.appendChild(icon);

                        li.appendChild(a);
                        errorList.appendChild(li);
                    });

                    errorSummary.classList.remove('d-none');
                    errorSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    errorSummary.focus({ preventScroll: true });
                    return false;
                }

                return true;
            }

            function updateProgress() {
                let answered = 0;
                ratingCards.forEach(card => {
                    const checked = card.querySelector('input[type="radio"]:checked');
                    if (checked) {
                        answered++;
                        card.classList.add('is-answered');
                    } else {
                        card.classList.remove('is-answered');
                    }
                });

                if (answeredBadge && progressBarFill) {
                    answeredBadge.textContent = `${answered} / ${totalRequired}`;
                    const percent = Math.min(100, Math.round((answered / totalRequired) * 100));
                    progressBarFill.style.width = `${percent}%`;
                }
            }

            // Pasang event listener pada setiap radio button (update progress + auto-clear error)
            form.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    updateProgress();
                    if (this.name) {
                        clearCardError(this.name);
                    }
                });
            });

            // Hitung karakter saran & auto-clear error saran saat valid
            if (suggestionsTextarea && charCount) {
                charCount.textContent = suggestionsTextarea.value.length;
                suggestionsTextarea.addEventListener('input', function () {
                    charCount.textContent = this.value.length;
                    const card = document.getElementById('card_suggestions');
                    const isReq = card && card.getAttribute('data-is-required') === '1';
                    const trimmed = this.value.trim();
                    if (!isReq || trimmed.length >= 3) {
                        clearCardError('suggestions');
                    }
                });
            }

            // Jalankan initial progress check saat pertama render
            updateProgress();

            // Validasi instan + cegah double submit & tampilkan spinner
            form.addEventListener('submit', function (e) {
                if (!validateAllFieldsClientSide()) {
                    e.preventDefault();
                    return;
                }
                if (submitBtn) {
                    submitBtn.disabled = true;
                    if (btnText) btnText.classList.add('d-none');
                    if (btnSpinner) btnSpinner.classList.remove('d-none');
                }
            });
        });
    </script>
</body>
</html>
