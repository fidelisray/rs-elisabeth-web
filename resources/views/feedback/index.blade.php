<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('components.seo-meta', ['title' => __('Kritik & Saran') . ' - RS St. Elisabeth Semarang'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    @vite([
        'resources/css/footer.css',
        'resources/js/components/back-to-top.js',
        'resources/css/style.css',
        'resources/css/btn-accent.css',
        'resources/css/navbar-dropdown.css',
        'resources/css/top-bar.css',
        'resources/css/search-and-quick-access.css',
        'resources/css/feedback.css'
    ])
</head>
<body>

    @include('components.navbar')

    <main class="feedback-page-wrapper">

        <!-- 1. Hero Header Section -->
        <section class="feedback-hero text-center">
            <div class="container position-relative">
                <!-- Breadcrumb -->
                <nav class="feedback-breadcrumb mb-3" aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('Beranda') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('Kritik & Saran') }}</li>
                    </ol>
                </nav>

                <div class="feedback-eyebrow">
                    <i class="fa-solid fa-comment-medical"></i> {{ __('Suara Pelanggan') }}
                </div>
                <h1 class="feedback-title">{{ __('Formulir Keluhan & Masukan Pasien') }}</h1>
                <p class="feedback-subtitle">
                    {{ __('Kritik, saran, dan masukan Anda sangat berharga bagi kami dalam menjaga komitmen mutu pelayanan medis dan kenyamanan pasien di RS St. Elisabeth Semarang.') }}
                </p>
            </div>
        </section>

        <!-- 2. Form Container Section -->
        <section class="feedback-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-xl-9">

                        {{-- TAMPILAN SUKSES SETELAH SUBMIT --}}
                        @if(session('feedback_success'))
                        <div class="feedback-main-card feedback-success-card">
                            <div class="success-icon-badge">
                                <i class="fa-solid fa-check"></i>
                            </div>
                            <h2 class="fw-bold mb-2 text-dark">{{ __('Terima Kasih Atas Masukan Anda') }}</h2>
                            <p class="text-muted mb-3 mx-auto" style="max-width: 580px;">
                                {{ session('feedback_message') ?? __('Keluhan dan masukan Anda telah berhasil kami terima. Tim Customer Care kami akan segera menindaklanjuti informasi ini dengan penuh perhatian.') }}
                            </p>

                            @if(session('ticket_number'))
                            <div class="success-ticket-box">
                                <div class="ticket-number-label">{{ __('Nomor Tiket Pengaduan') }}</div>
                                <div class="ticket-number-code">{{ session('ticket_number') }}</div>
                                <small class="text-muted d-block mt-1">
                                    {{ __('Simpan nomor tiket ini untuk keperluan pengecekan status atau konfirmasi lebih lanjut.') }}
                                </small>
                            </div>
                            @endif

                            <div class="mt-4 d-flex flex-wrap justify-content-center gap-3">
                                <a href="{{ route('feedback.index') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-pen me-2"></i>{{ __('Kirim Masukan Lainnya') }}
                                </a>
                                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-brands fa-whatsapp me-2"></i>{{ __('Chat Customer Care') }}
                                </a>
                                <a href="{{ url('/') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                    <i class="fa-solid fa-house me-2"></i>{{ __('Kembali ke Beranda') }}
                                </a>
                            </div>
                        </div>

                        {{-- FORMULIR PENGISIAN --}}
                        @else
                        <div class="feedback-main-card">

                            {{-- Pesan Error Global / Flash --}}
                            @if(session('feedback_error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                                <i class="fa-solid fa-circle-exclamation me-2"></i>
                                {{ session('feedback_error') }}
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

                            <form action="{{ route('feedback.store') }}" method="POST" id="patientFeedbackForm" novalidate>
                                @csrf

                                {{-- Honeypot anti-spam (tidak terlihat oleh manusia) --}}
                                <div style="display: none !important;" aria-hidden="true">
                                    <label for="hp_website">Leave this empty</label>
                                    <input type="text" name="hp_website" id="hp_website" tabindex="-1" autocomplete="off">
                                </div>

                                <!-- BAGIAN 1: IDENTITAS PELAPOR & PASIEN -->
                                <div class="feedback-step-header">
                                    <span class="step-number">1</span>
                                    <h2 class="step-title">{{ __('Identitas Pelapor & Pasien') }}</h2>
                                </div>

                                <div class="row g-3 mb-4">
                                    <!-- Nama Pelapor -->
                                    <div class="col-md-6">
                                        <label for="reporter_name" class="feedback-label">
                                            {{ __('Nama Pelapor') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="reporter_name" 
                                               id="reporter_name" 
                                               class="form-control feedback-input @error('reporter_name') is-invalid @enderror" 
                                               placeholder="{{ __('Contoh: Maria Kristina') }}"
                                               value="{{ old('reporter_name') }}" 
                                               required>
                                        @error('reporter_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- No Telepon Pelapor -->
                                    <div class="col-md-6">
                                        <label for="phone_number" class="feedback-label">
                                            {{ __('No Telepon / WhatsApp') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="tel" 
                                               name="phone_number" 
                                               id="phone_number" 
                                               class="form-control feedback-input @error('phone_number') is-invalid @enderror" 
                                               placeholder="{{ __('Contoh: 081234567890') }}"
                                               value="{{ old('phone_number') }}" 
                                               required>
                                        <div class="feedback-helper-text">{{ __('Nomor aktif untuk konfirmasi & tindak lanjut pihak rumah sakit.') }}</div>
                                        @error('phone_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Hubungan dengan Pasien -->
                                    <div class="col-12 mt-3">
                                        <label class="feedback-label">
                                            {{ __('Hubungan dengan Pasien') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="relation-pills-group">
                                            @foreach($relations as $rel)
                                            <label class="relation-pill-label">
                                                <input type="radio" 
                                                       name="relation_to_patient" 
                                                       value="{{ $rel }}" 
                                                       class="relation-pill-input"
                                                       {{ old('relation_to_patient') === $rel ? 'checked' : '' }}
                                                       required>
                                                <span class="relation-pill-btn">
                                                    <i class="fa-regular fa-circle-dot"></i> {{ __($rel) }}
                                                </span>
                                            </label>
                                            @endforeach
                                        </div>
                                        @error('relation_to_patient')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror

                                        <!-- Input dinamis jika pilih "Lainnya" -->
                                        <div class="mt-2 {{ old('relation_to_patient') === 'Lainnya' ? '' : 'd-none' }}" id="relationOtherContainer">
                                            <input type="text" 
                                                   name="relation_other" 
                                                   id="relation_other" 
                                                   class="form-control feedback-input @error('relation_other') is-invalid @enderror" 
                                                   placeholder="{{ __('Sebutkan hubungan lainnya (misal: Kerabat, Tetangga, dll)') }}"
                                                   value="{{ old('relation_other') }}">
                                            @error('relation_other')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Nama Pasien -->
                                    <div class="col-12 mt-3">
                                        <label for="patient_name" class="feedback-label">
                                            {{ __('Nama Pasien') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               name="patient_name" 
                                               id="patient_name" 
                                               class="form-control feedback-input @error('patient_name') is-invalid @enderror" 
                                               placeholder="{{ __('Nama lengkap pasien yang menerima pelayanan/dirawat') }}"
                                               value="{{ old('patient_name') }}" 
                                               required>
                                        <div class="feedback-helper-text">{{ __('Tuliskan nama pasien yang bersangkutan untuk memudahkan penelusuran data.') }}</div>
                                        @error('patient_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- BAGIAN 2: KATEGORI KELUHAN -->
                                <div class="feedback-step-header mt-5">
                                    <span class="step-number">2</span>
                                    <h2 class="step-title">{{ __('Kategori Keluhan') }}</h2>
                                </div>

                                <div class="mb-4">
                                    <p class="text-muted small mb-3">
                                        <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                        {{ __('Mohon pilih kategori sesuai keluhan Anda (bisa lebih dari satu):') }}
                                    </p>

                                    <div class="category-checkbox-list">
                                        @foreach($categories as $idx => $cat)
                                        <label class="category-card-label">
                                            <input type="checkbox" 
                                                   name="complaint_categories[]" 
                                                   value="{{ $cat }}" 
                                                   class="category-card-input"
                                                   {{ (is_array(old('complaint_categories')) && in_array($cat, old('complaint_categories'))) ? 'checked' : '' }}>
                                            <div class="category-card-content">
                                                <span class="category-card-icon">
                                                    <i class="fa-solid fa-check"></i>
                                                </span>
                                                <div>
                                                    <div class="category-text-title">{{ __($cat) }}</div>
                                                </div>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>

                                    @error('complaint_categories')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror

                                    <!-- Input dinamis jika checkbox 'Lainnya' dicentang -->
                                    <div class="mt-3 {{ (is_array(old('complaint_categories')) && in_array('Lainnya', old('complaint_categories'))) ? '' : 'd-none' }}" id="categoryOtherContainer">
                                        <label for="category_other" class="feedback-label">{{ __('Sebutkan Kategori Lainnya') }}</label>
                                        <input type="text" 
                                               name="category_other" 
                                               id="category_other" 
                                               class="form-control feedback-input @error('category_other') is-invalid @enderror" 
                                               placeholder="{{ __('Tuliskan kategori keluhan lainnya') }}"
                                               value="{{ old('category_other') }}">
                                        @error('category_other')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- BAGIAN 3: RINCIAN KELUHAN & SOLUSI -->
                                <div class="feedback-step-header mt-5">
                                    <span class="step-number">3</span>
                                    <h2 class="step-title">{{ __('Uraian Keluhan & Harapan Solusi') }}</h2>
                                </div>

                                <div class="mb-4">
                                    <!-- Rincian Keluhan -->
                                    <div class="mb-4">
                                        <label for="complaint_detail" class="feedback-label">
                                            {{ __('Mohon jelaskan keluhan Anda secara rinci') }} <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="complaint_detail" 
                                                  id="complaint_detail" 
                                                  rows="5" 
                                                  class="form-control feedback-textarea @error('complaint_detail') is-invalid @enderror" 
                                                  placeholder="{{ __('Tuliskan secara detail mengenai kejadian, tanggal/waktu, lokasi/ruangan/poli, petugas terkait (bila ada), serta kronologi kejadian yang Anda alami...') }}"
                                                  required>{{ old('complaint_detail') }}</textarea>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="feedback-helper-text">{{ __('Minimal 10 karakter.') }}</div>
                                            <div class="char-counter" id="complaintDetailCounter">0 / 3000</div>
                                        </div>
                                        @error('complaint_detail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Solusi yang Diharapkan -->
                                    <div class="mb-4">
                                        <label for="expected_solution" class="feedback-label">
                                            {{ __('Apa solusi yang Anda harapkan dari Rumah Sakit?') }} <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="expected_solution" 
                                                  id="expected_solution" 
                                                  rows="3" 
                                                  class="form-control feedback-textarea @error('expected_solution') is-invalid @enderror" 
                                                  placeholder="{{ __('Contoh: Mohon evaluasi pelayanan di bagian pendaftaran, perbaikan fasilitas ruang tunggu, klarifikasi dari pihak dokter/petugas, dll.') }}"
                                                  required>{{ old('expected_solution') }}</textarea>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="feedback-helper-text">{{ __('Harapan Anda akan menjadi bahan pertimbangan utama dalam evaluasi.') }}</div>
                                            <div class="char-counter" id="expectedSolutionCounter">0 / 1500</div>
                                        </div>
                                        @error('expected_solution')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Catatan Privasi & Keamanan Data -->
                                    <div class="privacy-notice-box mb-4">
                                        <div class="d-flex gap-2">
                                            <i class="fa-solid fa-shield-halved text-primary fs-5 mt-1 flex-shrink-0"></i>
                                            <div>
                                                <strong>{{ __('Jaminan Kerahasiaan Data:') }}</strong>
                                                {{ __('Seluruh data dan identitas yang Anda cantumkan dalam formulir ini dijaga kerahasiaannya dan hanya dipergunakan untuk keperluan investigasi serta perbaikan mutu pelayanan RS St. Elisabeth Semarang.') }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Kirim Form -->
                                    <div class="text-center mt-4">
                                        <button type="submit" id="btnSubmitFeedback" class="btn btn-submit-feedback">
                                            <span class="btn-text">
                                                <i class="fa-solid fa-paper-plane me-2"></i>{{ __('Kirim Keluhan & Masukan') }}
                                            </span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                {{ __('Sedang Mengirim...') }}
                                            </span>
                                        </button>
                                    </div>
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

    @include('components.floating-buttons')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/726e331ad1.js" crossorigin="anonymous"></script>
    @vite([
        'resources/js/navbar/navbar.js',
        'resources/js/navbar/navbar-dropdown.js'
    ])
    @include('components.json-ld')

    {{-- Interactive Script for Feedback Form --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('patientFeedbackForm');
            if (!form) return;

            const reporterNameInput = document.getElementById('reporter_name');
            const patientNameInput = document.getElementById('patient_name');
            const relationInputs = document.querySelectorAll('input[name="relation_to_patient"]');
            const relationOtherContainer = document.getElementById('relationOtherContainer');
            const relationOtherInput = document.getElementById('relation_other');

            const categoryInputs = document.querySelectorAll('input[name="complaint_categories[]"]');
            const categoryOtherContainer = document.getElementById('categoryOtherContainer');
            const categoryOtherInput = document.getElementById('category_other');

            const complaintDetailTextarea = document.getElementById('complaint_detail');
            const complaintDetailCounter = document.getElementById('complaintDetailCounter');
            const expectedSolutionTextarea = document.getElementById('expected_solution');
            const expectedSolutionCounter = document.getElementById('expectedSolutionCounter');

            const submitBtn = document.getElementById('btnSubmitFeedback');
            const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
            const btnSpinner = submitBtn ? submitBtn.querySelector('.btn-spinner') : null;

            // 1. Hubungan dengan Pasien: Diri Sendiri auto-fill & Lainnya toggle
            relationInputs.forEach(input => {
                input.addEventListener('change', function () {
                    if (this.checked && this.value === 'Diri Sendiri') {
                        if (reporterNameInput && reporterNameInput.value.trim() !== '') {
                            patientNameInput.value = reporterNameInput.value.trim();
                        }
                    }

                    if (this.checked && this.value === 'Lainnya') {
                        relationOtherContainer.classList.remove('d-none');
                        if (relationOtherInput) relationOtherInput.focus();
                    } else {
                        relationOtherContainer.classList.add('d-none');
                    }
                });
            });

            // Sinkronisasi otomatis saat nama pelapor diketik jika 'Diri Sendiri' aktif
            if (reporterNameInput) {
                reporterNameInput.addEventListener('input', function () {
                    const checkedRelation = document.querySelector('input[name="relation_to_patient"]:checked');
                    if (checkedRelation && checkedRelation.value === 'Diri Sendiri') {
                        patientNameInput.value = this.value;
                    }
                });
            }

            // 2. Kategori Keluhan: Toggle input teks 'Lainnya'
            categoryInputs.forEach(input => {
                input.addEventListener('change', function () {
                    if (this.value === 'Lainnya') {
                        if (this.checked) {
                            categoryOtherContainer.classList.remove('d-none');
                            if (categoryOtherInput) categoryOtherInput.focus();
                        } else {
                            categoryOtherContainer.classList.add('d-none');
                        }
                    }
                });
            });

            // 3. Counter Karakter Real-time
            function updateCounter(textarea, counterEl, max) {
                if (!textarea || !counterEl) return;
                const len = textarea.value.length;
                counterEl.textContent = `${len} / ${max}`;
                if (len > max) {
                    counterEl.classList.add('text-danger');
                } else {
                    counterEl.classList.remove('text-danger');
                }
            }

            if (complaintDetailTextarea) {
                updateCounter(complaintDetailTextarea, complaintDetailCounter, 3000);
                complaintDetailTextarea.addEventListener('input', function () {
                    updateCounter(this, complaintDetailCounter, 3000);
                });
            }

            if (expectedSolutionTextarea) {
                updateCounter(expectedSolutionTextarea, expectedSolutionCounter, 1500);
                expectedSolutionTextarea.addEventListener('input', function () {
                    updateCounter(this, expectedSolutionCounter, 1500);
                });
            }

            // 4. Loading State saat Form Dikirim
            form.addEventListener('submit', function () {
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
