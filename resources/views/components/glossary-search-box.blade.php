@props([
    'keyword' => '',
    'variant' => 'hero',
    'idPrefix' => 'glossarySearch',
    'label' => null,
])

@php
    $normalizedVariant = in_array($variant, ['hero', 'detail'], true) ? $variant : 'hero';
    $resolvedLabel = $label ?? ($normalizedVariant === 'detail'
        ? __('Cari Istilah Lain')
        : __('Pencarian Istilah Medis'));
    $keywordValue = is_string($keyword) ? $keyword : '';
    $hasKeyword = trim($keywordValue) !== '';

    $formId = $idPrefix . 'Form';
    $inputId = $idPrefix . 'Input';
    $clearId = $idPrefix . 'Clear';
    $spinnerId = $idPrefix . 'Spinner';
    $panelId = $idPrefix . 'Panel';
    $listboxId = $idPrefix . 'Listbox';
    $statusId = $idPrefix . 'Status';
@endphp

<div
    class="glossary-search-widget glossary-search-widget--{{ $normalizedVariant }}"
    data-glossary-search-widget
    data-endpoint="{{ route('glossary.search') }}"
    data-index-url="{{ route('glossary.index') }}"
    data-label-popular="{{ __('Istilah Populer') }}"
    data-label-results="{{ __('Saran Istilah Medis') }}"
    data-label-view-all="{{ __('Lihat semua :count hasil untuk ":query"') }}"
    data-label-view-all-one="{{ __('Lihat 1 hasil untuk ":query"') }}"
    data-label-empty="{{ __('Tidak ada nama istilah medis yang cocok') }}"
    data-label-empty-hint="{{ __('Coba gunakan nama istilah medis seperti Diabetes, Asthma, atau Stroke.') }}"
    data-label-loading="{{ __('Mencari istilah medis...') }}"
    data-label-error="{{ __('Gagal memuat saran pencarian. Tekan Enter untuk mencari.') }}"
    data-label-clear="{{ __('Hapus kata pencarian') }}"
    data-label-live-results="{{ __('Menampilkan :count saran istilah medis') }}"
    data-label-live-popular="{{ __('Menampilkan :count istilah populer') }}"
>
    <label for="{{ $inputId }}" class="glossary-search-label">
        {{ $resolvedLabel }}
    </label>

    <form
        action="{{ route('glossary.index') }}"
        method="GET"
        id="{{ $formId }}"
        class="glossary-search-form"
        role="search"
        autocomplete="off"
    >
        <div class="glossary-search-shell">
            <span class="glossary-search-icon" aria-hidden="true">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>

            <input
                type="search"
                id="{{ $inputId }}"
                name="q"
                value="{{ $keywordValue }}"
                class="glossary-search-input"
                placeholder="{{ __('Cari nama istilah medis (mis. Diabetes, Asthma, Stroke)...') }}"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="off"
                spellcheck="false"
                enterkeyhint="search"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="{{ $listboxId }}"
                aria-haspopup="listbox"
                data-search-input
            >

            <span
                id="{{ $spinnerId }}"
                class="glossary-search-spinner"
                data-search-spinner
                aria-hidden="true"
                hidden
            ></span>

            <button
                type="button"
                id="{{ $clearId }}"
                class="glossary-search-clear"
                data-search-clear
                aria-label="{{ __('Hapus kata pencarian') }}"
                @if(!$hasKeyword) hidden @endif
            >
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>

            <button type="submit" class="glossary-search-submit">
                <span>{{ __('Cari') }}</span>
            </button>
        </div>
    </form>

    <div
        id="{{ $panelId }}"
        class="glossary-suggest-panel"
        data-suggest-panel
        hidden
    >
        <div class="glossary-suggest-header" data-suggest-header hidden></div>
        <div class="glossary-suggest-state" data-suggest-state hidden></div>
        <ul
            id="{{ $listboxId }}"
            class="glossary-suggest-list"
            role="listbox"
            tabindex="-1"
            aria-label="{{ __('Daftar saran istilah medis') }}"
            data-suggest-listbox
        ></ul>
        <div class="glossary-suggest-footer" data-suggest-footer hidden></div>
    </div>

    <div
        id="{{ $statusId }}"
        class="visually-hidden"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        data-suggest-status
    ></div>
</div>
