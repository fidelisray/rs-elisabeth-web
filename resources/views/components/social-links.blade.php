@php
    $socialItems = [
        [
            'key' => 'instagram',
            'url' => config('services.social.instagram', '#'),
            'title' => 'Instagram RS St. Elisabeth',
            'label' => 'Instagram',
            'icon' => 'fa-brands fa-instagram',
        ],
        [
            'key' => 'facebook',
            'url' => config('services.social.facebook', '#'),
            'title' => 'Facebook RS St. Elisabeth',
            'label' => 'Facebook',
            'icon' => 'fa-brands fa-facebook-f',
        ],
        [
            'key' => 'youtube',
            'url' => config('services.social.youtube', '#'),
            'title' => 'YouTube RS St. Elisabeth',
            'label' => 'YouTube',
            'icon' => 'fa-brands fa-youtube',
        ],
        [
            'key' => 'threads',
            'url' => config('services.social.threads', '#'),
            'title' => 'Threads RS St. Elisabeth',
            'label' => 'Threads',
            'icon' => 'fa-brands fa-threads',
        ],
    ];
@endphp

@foreach ($socialItems as $social)
    @php
        $rawUrl = is_string($social['url']) ? trim($social['url']) : '';
        $isExternal = $rawUrl !== '' && preg_match('/^https?:\/\//i', $rawUrl) === 1;
    @endphp
    <a href="{{ $isExternal ? $rawUrl : '#' }}"
       class="btn-social {{ $social['key'] }}"
       title="{{ $social['title'] }}"
       aria-label="{{ $social['label'] }}"
       @if ($isExternal) target="_blank" rel="noopener noreferrer" @endif>
        <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
    </a>
@endforeach
