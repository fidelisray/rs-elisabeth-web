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
                                <a href="{{ route('questionnaire.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-list-check me-2"></i>{{ __('Pilih Kuesioner Lainnya') }}
                                </a>
                                <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-house me-2"></i>{{ __('Kembali ke Beranda') }}
                                </a>
                            </div>
                        </div>

                        {{-- FORMULIR PENGISIAN --}}
                        @else
                        <div class="questionnaire-main-card">

                            {{-- Pesan Error Global / Flash --}}
                            @if(session('questionnaire_error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-2"></i>
                                {{ session('questionnaire_error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            @endif

                            @if($errors->any())
                            <div class="alert alert-danger rounded-3 mb-4">
                                <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ __('Mohon periksa kembali isian formulir Anda:') }}</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

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
                                    <div class="question-item-card mb-3" id="card_age_group">
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
                                        @error('age_group')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Pertanyaan Jenis Kelamin -->
                                    <div class="question-item-card mb-3" id="card_gender">
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
                                        @error('gender')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Pertanyaan Cara Bayar -->
                                    <div class="question-item-card mb-3" id="card_payment_method">
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
                                        @error('payment_method')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- BAGIAN 2: KUALITAS PELAYANAN RS ST. ELISABETH -->
                                <div class="questionnaire-step-header mt-5">
                                    <span class="step-number">2</span>
                                    <h2 class="step-title">{{ __('Kualitas Pelayanan Rumah Sakit ST. Elisabeth Semarang') }}</h2>
                                </div>

                                <div class="mb-4">
                                    @foreach($questions as $idx => $q)
                                    <div class="question-item-card" id="card_{{ $q['name'] }}">
                                        <div class="question-text">
                                            <span class="question-number-badge">{{ $idx + 1 }}</span>
                                            {{ __($q['text']) }} <span class="text-danger">*</span>
                                        </div>
                                        <div class="rating-pill-group">
                                            @foreach($scales[$q['scale']] as $scaleOpt)
                                            <label class="rating-pill-label">
                                                <input type="radio" 
                                                       name="{{ $q['name'] }}" 
                                                       value="{{ $scaleOpt }}" 
                                                       class="rating-pill-input"
                                                       {{ old($q['name']) === $scaleOpt ? 'checked' : '' }} 
                                                       required>
                                                <span class="rating-pill-btn">
                                                    {{ __($scaleOpt) }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                        @error($q['name'])
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endforeach
                                </div>

                                <!-- BAGIAN 3: SARAN & MASUKAN -->
                                <div class="questionnaire-step-header mt-5">
                                    <span class="step-number">3</span>
                                    <h2 class="step-title">{{ __('Saran & Masukan') }}</h2>
                                </div>

                                <div class="mb-4">
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
                                    <div class="d-flex justify-content-between align-items-center">
                                        @if($isSuggestionRequired)
                                        <small class="text-muted">{{ __('Wajib diisi untuk membantu kami mengevaluasi layanan rawat jalan.') }}</small>
                                        @else
                                        <small class="text-muted">{{ __('Masukan Anda akan menjadi bahan pertimbangan utama kami.') }}</small>
                                        @endif
                                        <div class="char-counter"><span id="charCount">0</span>/3000</div>
                                    </div>
                                    @error('suggestions')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
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

            // Hitung total butir pertanyaan wajib (Demografi 3 + Pertanyaan Rating N)
            const ratingCards = document.querySelectorAll('.question-item-card');
            const totalRequired = ratingCards.length;

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

            // Pasang event listener pada setiap radio button
            form.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', updateProgress);
            });

            // Hitung karakter saran
            if (suggestionsTextarea && charCount) {
                charCount.textContent = suggestionsTextarea.value.length;
                suggestionsTextarea.addEventListener('input', function () {
                    charCount.textContent = this.value.length;
                });
            }

            // Jalankan initial progress check saat pertama render
            updateProgress();

            // Cegah double submit & tampilkan spinner
            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) {
                    return; // Biarkan HTML5 validation jalan
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
