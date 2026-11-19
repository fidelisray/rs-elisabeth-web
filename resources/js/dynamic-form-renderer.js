const OTHER_CHOICE_KEY = '__other__';
const SUBMIT_SENTINEL = '__submit__';

const UI_STRINGS = {
    id: {
        stepProgress: 'Bagian {current} dari {total}',
        previous: 'Kembali',
        next: 'Lanjutkan',
        submit: 'Kirim Jawaban',
        submitting: 'Mengirim...',
        selectPlaceholder: 'Pilih salah satu...',
        otherInputLabel: 'Sebutkan jawaban lainnya',
        otherPlaceholder: 'Tuliskan detail jawaban Anda...',
        chooseFile: 'Pilih Berkas',
        chooseFiles: 'Pilih Berkas',
        maxFilesReached: 'Batas berkas tercapai',
        uploading: 'Mengunggah...',
        uploadDone: 'Siap dikirim',
        removeFile: 'Hapus',
        allowedMeta: 'Ekstensi: {exts} | Maksimal {mb} MB/berkas | Maksimal {max} berkas',
        openMedia: 'Buka lampiran informasi',
        copyTicket: 'Salin Nomor Tiket',
        ticketCopied: 'Nomor tiket disalin.',
        ticketLabel: 'Nomor Tiket Tindak Lanjut',
        ticketHint: 'Simpan nomor tiket ini sebagai referensi tindak lanjut.',
        successHeading: 'Terima Kasih, Jawaban Anda Terkirim',
        submitAnother: 'Isi Formulir Lagi',
        backToCatalog: 'Lihat Daftar Formulir',
        backToHome: 'Kembali ke Beranda',
        reloadPage: 'Muat Ulang Halaman',
        summaryErrorTitle: 'Periksa kembali isian formulir berikut:',
        uploadInProgress: 'Tunggu hingga seluruh proses unggah berkas selesai.',
        ratingSelected: 'Dipilih: {val} dari {max}',
        ratingAria: '{val} dari {max}',
        charCount: '{len} / {max}',
        errSessionExpired: 'Sesi halaman telah kedaluwarsa. Muat ulang halaman sebelum melanjutkan.',
        errInitFailed: 'Gagal memuat konfigurasi formulir. Silakan muat ulang halaman.',
        errRequired: 'Pertanyaan [{label}] wajib diisi.',
        errRequiredChoice: 'Pertanyaan [{label}] wajib dipilih.',
        errOtherRequired: 'Isian teks untuk opsi Lainnya pada [{label}] wajib diisi.',
        errOtherMax: 'Isian teks Lainnya pada [{label}] maksimal 500 karakter.',
        errEmail: 'Format email pada [{label}] tidak valid.',
        errUrl: 'Format URL pada [{label}] harus diawali http:// atau https://.',
        errPhone: 'Format nomor telepon pada [{label}] tidak valid.',
        errNumber: 'Pertanyaan [{label}] harus berupa angka.',
        errMinValue: 'Nilai pada [{label}] minimal {min}.',
        errMaxValue: 'Nilai pada [{label}] maksimal {max}.',
        errMinLength: 'Jawaban [{label}] minimal {min} karakter.',
        errMaxLength: 'Jawaban [{label}] maksimal {max} karakter.',
        errPattern: 'Format jawaban pada [{label}] tidak sesuai pola yang diminta.',
        errMinSelected: 'Pertanyaan [{label}] membutuhkan minimal {min} pilihan.',
        errMaxSelected: 'Pertanyaan [{label}] maksimal {max} pilihan.',
        errMatrixRequired: 'Kisi pertanyaan [{label}] wajib diisi.',
        errMatrixAllRows: 'Seluruh baris pada kisi pertanyaan [{label}] wajib diisi.',
        errDateInvalid: 'Format tanggal pada [{label}] harus YYYY-MM-DD.',
        errDateMin: 'Tanggal pada [{label}] tidak boleh sebelum {min}.',
        errDateMax: 'Tanggal pada [{label}] tidak boleh setelah {max}.',
        errTimeInvalid: 'Format waktu pada [{label}] harus HH:MM (24 jam).',
        errTimeMin: 'Waktu pada [{label}] tidak boleh sebelum {min}.',
        errTimeMax: 'Waktu pada [{label}] tidak boleh setelah {max}.',
        errFileRequired: 'Lampiran berkas pada [{label}] wajib diunggah.',
        errFileExt: 'Ekstensi berkas .{ext} tidak diizinkan. Gunakan: {exts}.',
        errFileSize: 'Ukuran berkas melebihi batas maksimal {mb} MB.',
        errFileEmpty: 'Berkas yang dipilih kosong atau tidak dapat dibaca.',
        errFileMaxCount: 'Maksimal {max} berkas untuk pertanyaan ini.',
        errUploadFailed: 'Gagal mengunggah berkas. Silakan coba lagi.',
        errNetwork: 'Terjadi gangguan jaringan saat mengirim formulir. Periksa koneksi Anda dan coba lagi.',
        errRateLimit: 'Terlalu banyak permintaan. Silakan tunggu {seconds} detik lalu coba lagi.',
        errNoSections: 'Formulir ini belum memiliki pertanyaan yang dapat ditampilkan.',
    },
    en: {
        stepProgress: 'Section {current} of {total}',
        previous: 'Back',
        next: 'Continue',
        submit: 'Submit Response',
        submitting: 'Submitting...',
        selectPlaceholder: 'Select an option...',
        otherInputLabel: 'Please specify other',
        otherPlaceholder: 'Enter your answer...',
        chooseFile: 'Choose File',
        chooseFiles: 'Choose Files',
        maxFilesReached: 'Maximum files reached',
        uploading: 'Uploading...',
        uploadDone: 'Ready to submit',
        removeFile: 'Remove',
        allowedMeta: 'Allowed: {exts} | Max {mb} MB/file | Up to {max} file(s)',
        openMedia: 'Open reference link',
        copyTicket: 'Copy Ticket Number',
        ticketCopied: 'Ticket number copied.',
        ticketLabel: 'Follow-up Ticket Number',
        ticketHint: 'Save this ticket number for follow-up reference.',
        successHeading: 'Thank You, Your Response Has Been Recorded',
        submitAnother: 'Submit Another Response',
        backToCatalog: 'Browse Public Forms',
        backToHome: 'Back to Home',
        reloadPage: 'Reload Page',
        summaryErrorTitle: 'Please check the following form fields:',
        uploadInProgress: 'Please wait until all file uploads finish.',
        ratingSelected: 'Selected: {val} out of {max}',
        ratingAria: '{val} out of {max}',
        charCount: '{len} / {max}',
        errSessionExpired: 'Your page session has expired. Please reload the page before continuing.',
        errInitFailed: 'Failed to load form configuration. Please reload the page.',
        errRequired: '[{label}] is required.',
        errRequiredChoice: 'Please select an option for [{label}].',
        errOtherRequired: 'Please enter details for the Other option in [{label}].',
        errOtherMax: 'Other option text for [{label}] must not exceed 500 characters.',
        errEmail: 'Please enter a valid email address for [{label}].',
        errUrl: 'URL for [{label}] must start with http:// or https://.',
        errPhone: 'Please enter a valid phone number for [{label}].',
        errNumber: '[{label}] must be a numeric value.',
        errMinValue: '[{label}] must be at least {min}.',
        errMaxValue: '[{label}] must not exceed {max}.',
        errMinLength: '[{label}] must be at least {min} characters.',
        errMaxLength: '[{label}] must not exceed {max} characters.',
        errPattern: '[{label}] does not match the required format.',
        errMinSelected: 'Please select at least {min} option(s) for [{label}].',
        errMaxSelected: 'Please select at most {max} option(s) for [{label}].',
        errMatrixRequired: 'Please complete [{label}].',
        errMatrixAllRows: 'Please answer every row in [{label}].',
        errDateInvalid: 'Date for [{label}] must be YYYY-MM-DD.',
        errDateMin: 'Date for [{label}] cannot be before {min}.',
        errDateMax: 'Date for [{label}] cannot be after {max}.',
        errTimeInvalid: 'Time for [{label}] must be HH:MM (24-hour).',
        errTimeMin: 'Time for [{label}] cannot be before {min}.',
        errTimeMax: 'Time for [{label}] cannot be after {max}.',
        errFileRequired: 'Please upload at least one file for [{label}].',
        errFileExt: 'File extension .{ext} is not allowed. Allowed: {exts}.',
        errFileSize: 'File size exceeds the {mb} MB limit.',
        errFileEmpty: 'The selected file is empty or unreadable.',
        errFileMaxCount: 'Maximum {max} file(s) allowed for this question.',
        errUploadFailed: 'File upload failed. Please try again.',
        errNetwork: 'Network error while submitting the form. Please check your connection and retry.',
        errRateLimit: 'Too many requests. Please wait {seconds} seconds and try again.',
        errNoSections: 'This form does not have any sections configured yet.',
    },
};

function formatMsg(template, vars = {}) {
    return String(template).replace(/\{(\w+)\}/g, (_, key) => (vars[key] !== undefined ? String(vars[key]) : ''));
}

function isSafeHttpUrl(rawUrl) {
    if (typeof rawUrl !== 'string' || rawUrl.trim() === '') return null;
    try {
        const parsed = new URL(rawUrl.trim());
        if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
            return parsed.toString();
        }
    } catch {
        return null;
    }
    return null;
}

function isImageUrl(safeUrl) {
    return /\.(png|jpe?g|webp|gif)(\?.*)?$/i.test(safeUrl);
}

function formatBytes(bytes) {
    const num = Number(bytes || 0);
    if (num >= 1048576) return `${(num / 1048576).toFixed(2)} MB`;
    if (num >= 1024) return `${Math.round(num / 1024)} KB`;
    return `${num} B`;
}

function localTodayIso() {
    const now = new Date();
    const y = now.getFullYear();
    const m = String(now.getMonth() + 1).padStart(2, '0');
    const d = String(now.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function resolveBoundDate(bound) {
    if (typeof bound !== 'string' || bound.trim() === '') return null;
    const trimmed = bound.trim().toLowerCase();
    if (trimmed === 'today') return localTodayIso();
    return /^\d{4}-\d{2}-\d{2}$/.test(trimmed) ? trimmed : null;
}

function parseOptionalRuleInt(val) {
    if (val === undefined || val === null || val === '' || typeof val === 'boolean') return null;
    const n = Number(val);
    return Number.isNaN(n) || !Number.isFinite(n) ? null : Math.trunc(n);
}

function charLength(str) {
    return Array.from(String(str)).length;
}

function compilePattern(pattern) {
    if (typeof pattern !== 'string' || pattern.trim() === '') return null;
    const trimmed = pattern.trim();
    try {
        if (trimmed.startsWith('/') && trimmed.lastIndexOf('/') > 0) {
            const lastSlash = trimmed.lastIndexOf('/');
            const body = trimmed.slice(1, lastSlash);
            const flags = trimmed.slice(lastSlash + 1).replace(/[^gimsuy]/g, '');
            return new RegExp(body, flags);
        }
        return new RegExp(trimmed);
    } catch {
        return null;
    }
}

// Parity with DynamicFormLogicEvaluator::normalizeComparableValue
function normalizeComparableValue(value) {
    if (value && typeof value === 'object' && !Array.isArray(value) && Object.prototype.hasOwnProperty.call(value, 'selected')) {
        return value.selected;
    }
    return value;
}

function isValueEmpty(value) {
    if (value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
        return true;
    }
    if (Array.isArray(value)) {
        return value.every((item) => item === null || item === undefined || item === '' || (Array.isArray(item) && item.length === 0));
    }
    if (typeof value === 'object') {
        return Object.values(value).every((item) => item === null || item === undefined || item === '' || (Array.isArray(item) && item.length === 0));
    }
    return false;
}

function isNumericValue(val) {
    if (val === null || val === undefined || val === '' || typeof val === 'boolean' || Array.isArray(val) || typeof val === 'object') {
        return false;
    }
    return !Number.isNaN(Number(val)) && Number.isFinite(Number(val));
}

function matchesEquals(actual, expected) {
    if (Array.isArray(actual)) {
        return actual.length === 1 && String(actual[0]) === String(expected ?? '');
    }
    if (isNumericValue(actual) && isNumericValue(expected)) {
        return Number(actual) === Number(expected);
    }
    return String(actual) === String(expected ?? '');
}

function matchesIn(actual, expected) {
    const list = Array.isArray(expected)
        ? expected.map((v) => String(v).trim()).filter(Boolean)
        : String(expected ?? '').split(',').map((v) => v.trim()).filter(Boolean);

    if (list.length === 0) return false;
    if (Array.isArray(actual)) {
        const actualStrings = actual.map((v) => String(v));
        return actualStrings.some((item) => list.includes(item));
    }
    return list.includes(String(actual));
}

function matchesContains(actual, expected) {
    const needle = String(expected ?? '');
    if (needle === '') return false;
    if (Array.isArray(actual)) {
        return actual.map((v) => String(v)).includes(needle);
    }
    return String(actual).toLowerCase().includes(needle.toLowerCase());
}

function evaluateOperator(actual, operator, expected) {
    const empty = isValueEmpty(actual);
    switch (operator) {
        case 'is_empty':
            return empty;
        case 'is_not_empty':
            return !empty;
        case 'equals':
            return !empty && matchesEquals(actual, expected);
        case 'not_equals':
            return !empty && !matchesEquals(actual, expected);
        case 'in':
            return !empty && matchesIn(actual, expected);
        case 'not_in':
            return !empty && !matchesIn(actual, expected);
        case 'contains':
            return !empty && matchesContains(actual, expected);
        case 'gt':
            return isNumericValue(actual) && isNumericValue(expected) && Number(actual) > Number(expected);
        case 'gte':
            return isNumericValue(actual) && isNumericValue(expected) && Number(actual) >= Number(expected);
        case 'lt':
            return isNumericValue(actual) && isNumericValue(expected) && Number(actual) < Number(expected);
        case 'lte':
            return isNumericValue(actual) && isNumericValue(expected) && Number(actual) <= Number(expected);
        default:
            return false;
    }
}

function evaluateVisibility(field, effectiveAnswers) {
    const vis = field?.conditional_logic?.visibility;
    if (!vis || !vis.enabled) return true;

    const rules = Array.isArray(vis.rules)
        ? vis.rules.filter((rule) => rule && typeof rule === 'object' && String(rule.source_field_key ?? '').trim() !== '')
        : [];

    if (rules.length === 0) return true;

    const matchMode = vis.match === 'any' ? 'any' : 'all';

    for (const rule of rules) {
        const sourceKey = String(rule.source_field_key ?? '');
        const operator = String(rule.operator ?? 'equals');
        const expected = rule.value ?? null;
        const actual = normalizeComparableValue(effectiveAnswers[sourceKey] ?? null);
        const matched = evaluateOperator(actual, operator, expected);

        if (matchMode === 'any' && matched) return true;
        if (matchMode === 'all' && !matched) return false;
    }

    return matchMode === 'all';
}

function resolveChoiceJumpTarget(field, answerValue) {
    if (field.type !== 'radio' && field.type !== 'dropdown') return null;

    let selectedKey = '';
    if (answerValue && typeof answerValue === 'object' && !Array.isArray(answerValue)) {
        selectedKey = String(answerValue.selected ?? '');
    } else if (answerValue !== null && answerValue !== undefined && typeof answerValue !== 'object') {
        selectedKey = String(answerValue);
    }

    if (!selectedKey) return null;

    const choices = Array.isArray(field?.options?.choices) ? field.options.choices : [];
    for (const choice of choices) {
        if (String(choice.key ?? '') === selectedKey) {
            const target = String(choice.go_to_section_key ?? '').trim();
            return target !== '' ? target : null;
        }
    }
    return null;
}

function resolveSectionNextTarget(section, sections, currentIndex) {
    const rule = section?.next_section_rule || {};
    const action = String(rule.action ?? 'next');
    const target = String(rule.target_section_key ?? '').trim();

    if (action === 'submit' || target === SUBMIT_SENTINEL) {
        return SUBMIT_SENTINEL;
    }
    if (action === 'jump' && target !== '') {
        return target;
    }
    return sections[currentIndex + 1]?.section_key || null;
}

function buildSvgIcon(kind) {
    const svgNS = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'currentColor');
    svg.setAttribute('aria-hidden', 'true');

    const path = document.createElementNS(svgNS, 'path');
    if (kind === 'heart') {
        path.setAttribute('d', 'M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z');
    } else if (kind === 'thumb') {
        path.setAttribute('d', 'M2 20h2c.55 0 1-.45 1-1v-9c0-.55-.45-1-1-1H2v11zm19.83-7.12c.11-.25.17-.52.17-.8V11c0-1.1-.9-2-2-2h-5.5l.92-4.65c.05-.22.02-.46-.08-.66a4.8 4.8 0 0 0-.88-1.22L14 2 7.59 8.41C7.21 8.79 7 9.3 7 9.83v7.84C7 18.95 8.05 20 9.34 20h8.11c.7 0 1.36-.37 1.72-.97l2.66-6.15z');
    } else {
        path.setAttribute('d', 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z');
    }
    svg.appendChild(path);
    return svg;
}

class DynamicFormWizard {
    constructor(root, config) {
        this.root = root;
        this.config = config;
        this.schema = config.schema || {};
        this.sections = Array.isArray(this.schema.sections) ? [...this.schema.sections] : [];
        this.sections.sort((a, b) => Number(a.sort_order ?? 0) - Number(b.sort_order ?? 0));
        this.sections.forEach((section) => {
            if (Array.isArray(section.fields)) {
                section.fields.sort((a, b) => Number(a.sort_order ?? 0) - Number(b.sort_order ?? 0));
            } else {
                section.fields = [];
            }
        });

        const lang = config.siteLocale === 'en' ? 'en' : 'id';
        this.t = UI_STRINGS[lang];
        this.csrfToken = (typeof document !== 'undefined'
            ? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            : '') || '';

        this.answers = {};
        this.uploads = {};
        this.fieldErrors = {};
        this.bannerError = null;
        this.bannerReload = false;
        this.isSubmitting = false;
        this.submissionResult = null;
        this.sectionHistory = this.sections.length > 0 ? [this.sections[0].section_key] : [];
    }

    init() {
        this.render();
    }

    currentSectionKey() {
        return this.sectionHistory[this.sectionHistory.length - 1] || null;
    }

    currentSection() {
        const key = this.currentSectionKey();
        return this.sections.find((s) => s.section_key === key) || null;
    }

    findSectionIndex(sectionKey) {
        return this.sections.findIndex((s) => s.section_key === sectionKey);
    }

    // Evaluates full path from section 0 so visibility rules referencing earlier sections stay consistent
    evaluateEffectiveState(stopAtSectionKey = null) {
        const sectionsByKey = new Map();
        const indexByKey = new Map();
        this.sections.forEach((section, index) => {
            sectionsByKey.set(section.section_key, section);
            indexByKey.set(section.section_key, index);
        });

        const effectiveAnswers = {};
        const visibleBySection = new Map();
        const activeSectionKeys = [];
        const visited = new Set();

        let currentKey = this.sections[0]?.section_key || '';
        for (let step = 0; step < this.sections.length && currentKey !== ''; step += 1) {
            if (visited.has(currentKey) || !sectionsByKey.has(currentKey)) break;
            visited.add(currentKey);
            activeSectionKeys.push(currentKey);

            const section = sectionsByKey.get(currentKey);
            const visibleFields = [];
            let jumpTarget = null;

            for (const field of section.fields) {
                if (!evaluateVisibility(field, effectiveAnswers)) {
                    continue;
                }
                visibleFields.push(field);

                if (field.type === 'info_block') continue;

                const val = this.getFieldPayloadValue(field);
                if (val !== undefined && val !== null) {
                    effectiveAnswers[field.field_key] = val;
                    const choiceJump = resolveChoiceJumpTarget(field, val);
                    if (choiceJump !== null) {
                        jumpTarget = choiceJump;
                    }
                }
            }

            visibleBySection.set(currentKey, visibleFields);

            if (stopAtSectionKey && currentKey === stopAtSectionKey) {
                break;
            }

            const nextKey = jumpTarget ?? resolveSectionNextTarget(section, this.sections, indexByKey.get(currentKey));
            if (!nextKey || nextKey === SUBMIT_SENTINEL || visited.has(nextKey)) {
                break;
            }
            currentKey = nextKey;
        }

        return {
            activeSectionKeys,
            visibleBySection,
            sanitizedAnswers: effectiveAnswers,
        };
    }

    getFieldPayloadValue(field) {
        const key = field.field_key;
        if (field.type === 'file_upload') {
            const list = (this.uploads[key] || []).filter((item) => item.status === 'done' && item.token);
            return list.length > 0 ? list.map((item) => item.token) : undefined;
        }
        const val = this.answers[key];
        if (val === undefined || val === null) return undefined;
        if (typeof val === 'string') {
            return val.trim() === '' ? undefined : val;
        }
        return val;
    }

    hasActiveUploads(visibleFields) {
        return visibleFields.some((field) => {
            if (field.type !== 'file_upload') return false;
            return (this.uploads[field.field_key] || []).some((item) => item.status === 'uploading');
        });
    }

    validateVisibleFields(visibleFields) {
        const errors = {};

        for (const field of visibleFields) {
            if (field.type === 'info_block') continue;

            const errs = this.validateSingleField(field);
            if (errs.length > 0) {
                errors[field.field_key] = errs;
            }
        }

        return errors;
    }

    validateSingleField(field) {
        const key = field.field_key;
        const label = field.label || key;
        const isRequired = Boolean(field.is_required);
        const options = field.options || {};
        const rules = field.validation_rules || {};
        const errs = [];

        if (field.type === 'short_text') {
            const raw = this.answers[key];
            const trimmed = raw === undefined || raw === null ? '' : String(raw).trim();
            if (trimmed === '') {
                if (isRequired) errs.push(formatMsg(this.t.errRequired, { label }));
                return errs;
            }

            const subtype = options.subtype || 'text';
            if (subtype === 'number') {
                if (!isNumericValue(trimmed)) {
                    errs.push(formatMsg(this.t.errNumber, { label }));
                    return errs;
                }
                const num = Number(trimmed);
                if (rules.min_value !== undefined && rules.min_value !== null && rules.min_value !== '' && num < Number(rules.min_value)) {
                    errs.push(formatMsg(this.t.errMinValue, { label, min: rules.min_value }));
                }
                if (rules.max_value !== undefined && rules.max_value !== null && rules.max_value !== '' && num > Number(rules.max_value)) {
                    errs.push(formatMsg(this.t.errMaxValue, { label, max: rules.max_value }));
                }
                return errs;
            }

            if (subtype === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
                errs.push(formatMsg(this.t.errEmail, { label }));
            }
            if (subtype === 'url') {
                if (!isSafeHttpUrl(trimmed)) {
                    errs.push(formatMsg(this.t.errUrl, { label }));
                }
            }
            if (subtype === 'phone') {
                const digits = trimmed.replace(/[^0-9]/g, '');
                if (!/^\+?[0-9\s\-().]{7,20}$/.test(trimmed) || digits.length < 7 || digits.length > 16) {
                    errs.push(formatMsg(this.t.errPhone, { label }));
                }
            }

            const charLen = charLength(trimmed);
            const minLen = parseOptionalRuleInt(rules.min_length);
            const rawMaxLen = parseOptionalRuleInt(rules.max_length);
            const maxLen = rawMaxLen !== null && rawMaxLen > 0 ? Math.min(500, rawMaxLen) : 500;
            if (minLen !== null && charLen < minLen) {
                errs.push(formatMsg(this.t.errMinLength, { label, min: minLen }));
            }
            if (charLen > maxLen) {
                errs.push(formatMsg(this.t.errMaxLength, { label, max: maxLen }));
            }

            if (rules.pattern) {
                const regex = compilePattern(rules.pattern);
                if (regex && !regex.test(trimmed)) {
                    errs.push(formatMsg(this.t.errPattern, { label }));
                }
            }
            return errs;
        }

        if (field.type === 'long_text') {
            const raw = this.answers[key];
            const trimmed = raw === undefined || raw === null ? '' : String(raw).trim();
            if (trimmed === '') {
                if (isRequired) errs.push(formatMsg(this.t.errRequired, { label }));
                return errs;
            }
            const charLen = charLength(trimmed);
            const minLen = parseOptionalRuleInt(rules.min_length);
            const rawMaxLen = parseOptionalRuleInt(rules.max_length);
            const maxLen = rawMaxLen !== null && rawMaxLen > 0 ? Math.min(5000, rawMaxLen) : 5000;
            if (minLen !== null && charLen < minLen) {
                errs.push(formatMsg(this.t.errMinLength, { label, min: minLen }));
            }
            if (charLen > maxLen) {
                errs.push(formatMsg(this.t.errMaxLength, { label, max: maxLen }));
            }
            return errs;
        }

        if (field.type === 'radio') {
            const val = this.answers[key];
            const selected = val && typeof val === 'object' ? String(val.selected ?? '').trim() : '';
            const otherText = val && typeof val === 'object' ? String(val.other_text ?? '').trim() : '';
            if (!selected) {
                if (isRequired) errs.push(formatMsg(this.t.errRequiredChoice, { label }));
                return errs;
            }
            if (selected === OTHER_CHOICE_KEY) {
                if (otherText === '') errs.push(formatMsg(this.t.errOtherRequired, { label }));
                else if (charLength(otherText) > 500) errs.push(formatMsg(this.t.errOtherMax, { label }));
            }
            return errs;
        }

        if (field.type === 'checkbox') {
            const val = this.answers[key];
            const selected = val && typeof val === 'object' && Array.isArray(val.selected) ? val.selected.filter(Boolean) : [];
            const otherText = val && typeof val === 'object' ? String(val.other_text ?? '').trim() : '';
            if (selected.length === 0) {
                if (isRequired) errs.push(formatMsg(this.t.errRequiredChoice, { label }));
                return errs;
            }
            if (selected.includes(OTHER_CHOICE_KEY)) {
                if (otherText === '') errs.push(formatMsg(this.t.errOtherRequired, { label }));
                else if (charLength(otherText) > 500) errs.push(formatMsg(this.t.errOtherMax, { label }));
            }
            const minSelected = parseOptionalRuleInt(rules.min_selected);
            const maxSelected = parseOptionalRuleInt(rules.max_selected);
            if (minSelected !== null && selected.length < minSelected) {
                errs.push(formatMsg(this.t.errMinSelected, { label, min: minSelected }));
            }
            if (maxSelected !== null && maxSelected > 0 && selected.length > maxSelected) {
                errs.push(formatMsg(this.t.errMaxSelected, { label, max: maxSelected }));
            }
            return errs;
        }

        if (field.type === 'dropdown') {
            const selected = String(this.answers[key] ?? '').trim();
            if (!selected && isRequired) {
                errs.push(formatMsg(this.t.errRequiredChoice, { label }));
            }
            return errs;
        }

        if (field.type === 'linear_scale' || field.type === 'rating') {
            const val = this.answers[key];
            if ((val === undefined || val === null || val === '') && isRequired) {
                errs.push(formatMsg(this.t.errRequiredChoice, { label }));
            }
            return errs;
        }

        if (field.type === 'matrix_grid') {
            const val = this.answers[key];
            const rows = Array.isArray(options.rows) ? options.rows : [];
            const requireAllRows = Boolean(options.require_all_rows);
            let answeredRows = 0;

            if (val && typeof val === 'object') {
                for (const row of rows) {
                    const cell = val[row.key];
                    if (Array.isArray(cell)) {
                        if (cell.length > 0) answeredRows += 1;
                    } else if (cell !== undefined && cell !== null && String(cell).trim() !== '') {
                        answeredRows += 1;
                    }
                }
            }

            if (answeredRows === 0) {
                if (isRequired) errs.push(formatMsg(this.t.errMatrixRequired, { label }));
            } else if ((isRequired || answeredRows > 0) && requireAllRows && answeredRows < rows.length) {
                errs.push(formatMsg(this.t.errMatrixAllRows, { label }));
            }
            return errs;
        }

        if (field.type === 'date') {
            const val = String(this.answers[key] ?? '').trim();
            if (val === '') {
                if (isRequired) errs.push(formatMsg(this.t.errRequired, { label }));
                return errs;
            }
            if (!/^\d{4}-\d{2}-\d{2}$/.test(val)) {
                errs.push(formatMsg(this.t.errDateInvalid, { label }));
                return errs;
            }
            const minDate = resolveBoundDate(rules.min_date);
            const maxDate = resolveBoundDate(rules.max_date);
            if (minDate && val < minDate) errs.push(formatMsg(this.t.errDateMin, { label, min: minDate }));
            if (maxDate && val > maxDate) errs.push(formatMsg(this.t.errDateMax, { label, max: maxDate }));
            return errs;
        }

        if (field.type === 'time') {
            const val = String(this.answers[key] ?? '').trim();
            if (val === '') {
                if (isRequired) errs.push(formatMsg(this.t.errRequired, { label }));
                return errs;
            }
            const hhmm = val.slice(0, 5);
            if (!/^([01]\d|2[0-3]):[0-5]\d$/.test(hhmm)) {
                errs.push(formatMsg(this.t.errTimeInvalid, { label }));
                return errs;
            }
            if (rules.min_time && hhmm < String(rules.min_time)) errs.push(formatMsg(this.t.errTimeMin, { label, min: rules.min_time }));
            if (rules.max_time && hhmm > String(rules.max_time)) errs.push(formatMsg(this.t.errTimeMax, { label, max: rules.max_time }));
            return errs;
        }

        if (field.type === 'file_upload') {
            const doneFiles = (this.uploads[key] || []).filter((item) => item.status === 'done' && item.token);
            if (doneFiles.length === 0 && isRequired) {
                errs.push(formatMsg(this.t.errFileRequired, { label }));
            }
            return errs;
        }

        return errs;
    }

    resolveCurrentNextTarget(currentSection, visibleFields) {
        let choiceJump = null;
        for (const field of visibleFields) {
            if (field.type !== 'radio' && field.type !== 'dropdown') continue;
            const val = this.getFieldPayloadValue(field);
            const target = resolveChoiceJumpTarget(field, val);
            if (target !== null) {
                choiceJump = target;
            }
        }
        if (choiceJump !== null) return choiceJump;

        const currentIndex = this.findSectionIndex(currentSection.section_key);
        return resolveSectionNextTarget(currentSection, this.sections, currentIndex);
    }

    isFinalStep(currentSection, visibleFields) {
        const nextTarget = this.resolveCurrentNextTarget(currentSection, visibleFields);
        return !nextTarget
            || nextTarget === SUBMIT_SENTINEL
            || this.findSectionIndex(nextTarget) === -1
            || this.sectionHistory.includes(nextTarget);
    }

    handleNextOrSubmit() {
        const current = this.currentSection();
        if (!current || this.isSubmitting) return;

        const state = this.evaluateEffectiveState(current.section_key);
        const visibleFields = state.visibleBySection.get(current.section_key) || [];

        if (this.hasActiveUploads(visibleFields)) {
            this.bannerError = this.t.uploadInProgress;
            this.bannerReload = false;
            this.render();
            this.focusFirstAlert();
            return;
        }

        const stepErrors = this.validateVisibleFields(visibleFields);
        if (Object.keys(stepErrors).length > 0) {
            this.fieldErrors = stepErrors;
            this.bannerError = null;
            this.render();
            this.focusFirstErrorField(visibleFields);
            return;
        }

        this.fieldErrors = {};
        this.bannerError = null;
        this.bannerReload = false;

        const nextTarget = this.resolveCurrentNextTarget(current, visibleFields);
        if (!nextTarget || nextTarget === SUBMIT_SENTINEL) {
            this.submitForm();
            return;
        }

        if (this.findSectionIndex(nextTarget) === -1 || this.sectionHistory.includes(nextTarget)) {
            this.submitForm();
            return;
        }

        this.sectionHistory.push(nextTarget);
        this.render();
        this.scrollToCardTop();
    }

    handlePrevious() {
        if (this.sectionHistory.length <= 1 || this.isSubmitting) return;
        this.fieldErrors = {};
        this.bannerError = null;
        this.bannerReload = false;
        this.sectionHistory.pop();
        this.render();
        this.scrollToCardTop();
    }

    async submitForm() {
        this.isSubmitting = true;
        this.bannerError = null;
        this.bannerReload = false;
        this.render();

        const fullEval = this.evaluateEffectiveState();
        const hpInput = this.root.querySelector('input[name="hp_website"]');
        const payload = {
            version_id: this.schema.version_id || null,
            answers: fullEval.sanitizedAnswers,
            hp_website: hpInput ? hpInput.value : '',
            locale: this.config.siteLocale || 'id',
            source: this.config.source || 'web_public',
        };

        try {
            const response = await fetch(this.config.submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const json = await response.json().catch(() => ({}));

            if (response.ok && json.success) {
                this.isSubmitting = false;
                this.submissionResult = json.data || {};
                this.render();
                const successHeading = this.root.querySelector('[data-success-focus]');
                if (successHeading) {
                    successHeading.focus();
                }
                return;
            }

            this.isSubmitting = false;

            if (response.status === 419) {
                this.bannerReload = true;
                this.bannerError = this.t.errSessionExpired;
                this.render();
                this.focusFirstAlert();
                return;
            }

            if (response.status === 429) {
                const retryAfter = response.headers.get('Retry-After') || '60';
                this.bannerError = json.message || formatMsg(this.t.errRateLimit, { seconds: retryAfter });
                this.render();
                this.focusFirstAlert();
                return;
            }

            const serverErrors = json.errors && typeof json.errors === 'object' ? json.errors : {};
            const mappedFieldErrors = {};
            let firstBadFieldKey = null;

            Object.entries(serverErrors).forEach(([errKey, messages]) => {
                if (errKey.startsWith('answers.') && Array.isArray(messages) && messages.length > 0) {
                    const fieldKey = errKey.slice('answers.'.length);
                    mappedFieldErrors[fieldKey] = messages.map(String);
                    if (!firstBadFieldKey) firstBadFieldKey = fieldKey;
                }
            });

            this.fieldErrors = mappedFieldErrors;

            const topMessages = [];
            ['form', 'version_id', 'respondent_key', 'hp_website', 'answers'].forEach((k) => {
                if (Array.isArray(serverErrors[k])) {
                    serverErrors[k].forEach((m) => topMessages.push(String(m)));
                }
            });

            this.bannerReload = Boolean(serverErrors.form || serverErrors.version_id);
            this.bannerError = topMessages[0] || json.message || this.t.errNetwork;

            if (firstBadFieldKey) {
                this.navigateToSectionOfField(firstBadFieldKey);
            }

            this.render();

            if (firstBadFieldKey) {
                this.focusFieldByKey(firstBadFieldKey);
            } else {
                this.focusFirstAlert();
            }
        } catch {
            this.isSubmitting = false;
            this.bannerError = this.t.errNetwork;
            this.render();
            this.focusFirstAlert();
        }
    }

    navigateToSectionOfField(fieldKey) {
        const targetSection = this.sections.find((s) => s.fields.some((f) => f.field_key === fieldKey));
        if (!targetSection) return;

        const existingPos = this.sectionHistory.indexOf(targetSection.section_key);
        if (existingPos !== -1) {
            this.sectionHistory = this.sectionHistory.slice(0, existingPos + 1);
            return;
        }

        const evalState = this.evaluateEffectiveState(targetSection.section_key);
        const targetPos = evalState.activeSectionKeys.indexOf(targetSection.section_key);
        if (targetPos !== -1) {
            this.sectionHistory = evalState.activeSectionKeys.slice(0, targetPos + 1);
        }
    }

    handleFileSelection(field, filesList) {
        const key = field.field_key;
        const options = field.options || {};
        const allowedExts = (Array.isArray(options.allowed_extensions) ? options.allowed_extensions : ['pdf', 'jpg', 'jpeg', 'png']).map((e) => String(e).toLowerCase());
        const maxMb = Math.max(1, Math.min(10, Number(options.max_file_size_mb ?? 5)));
        const maxBytes = maxMb * 1024 * 1024;
        const maxFiles = Math.max(1, Math.min(5, Number(options.max_files ?? 1)));

        if (!this.uploads[key]) this.uploads[key] = [];
        const currentList = this.uploads[key];

        const picked = Array.from(filesList || []);
        if (picked.length === 0) return;

        delete this.fieldErrors[key];
        const batchErrors = [];
        const queuedEntries = [];

        for (const file of picked) {
            const activeCount = currentList.filter((item) => item.status === 'uploading' || item.status === 'done').length;
            if (activeCount >= maxFiles) {
                batchErrors.push(formatMsg(this.t.errFileMaxCount, { max: maxFiles }));
                break;
            }

            const ext = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';
            if (!ext || !allowedExts.includes(ext)) {
                batchErrors.push(formatMsg(this.t.errFileExt, { ext: ext || '-', exts: allowedExts.join(', ') }));
                continue;
            }

            if (file.size <= 0) {
                batchErrors.push(this.t.errFileEmpty);
                continue;
            }

            if (file.size > maxBytes) {
                batchErrors.push(formatMsg(this.t.errFileSize, { mb: maxMb }));
                continue;
            }

            const entry = {
                localId: `${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
                name: file.name,
                size: file.size,
                progress: 0,
                status: 'uploading',
                token: null,
                error: null,
                xhr: null,
                preserveBatchError: false,
            };
            currentList.push(entry);
            queuedEntries.push({ entry, file });
        }

        if (batchErrors.length > 0) {
            this.fieldErrors[key] = batchErrors;
            queuedEntries.forEach(({ entry }) => {
                entry.preserveBatchError = true;
            });
        }

        this.refreshUploadFieldInPlace(field);
        queuedEntries.forEach(({ entry, file }) => {
            this.uploadSingleFile(field, entry, file);
        });
    }

    uploadSingleFile(field, entry, file) {
        const key = field.field_key;
        const formData = new FormData();
        formData.append('field_key', key);
        formData.append('file', file, file.name);

        const xhr = new XMLHttpRequest();
        entry.xhr = xhr;
        xhr.open('POST', this.config.uploadUrl, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        if (this.csrfToken) {
            xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
        }

        xhr.upload.onprogress = (event) => {
            if (!this.isUploadEntryActive(key, entry.localId)) return;
            if (event.lengthComputable && event.total > 0) {
                entry.progress = Math.min(99, Math.round((event.loaded / event.total) * 100));
                const bar = this.root.querySelector(`[data-upload-bar="${entry.localId}"]`);
                if (bar) bar.style.width = `${entry.progress}%`;
                const sub = this.root.querySelector(`[data-upload-sub="${entry.localId}"]`);
                if (sub) sub.textContent = `${formatBytes(entry.size)} | ${this.t.uploading} (${entry.progress}%)`;
            }
        };

        xhr.onload = () => {
            entry.xhr = null;
            if (!this.isUploadEntryActive(key, entry.localId)) return;

            let json = {};
            try {
                json = JSON.parse(xhr.responseText || '{}');
            } catch {
                json = {};
            }

            if (xhr.status === 419) {
                this.uploads[key] = (this.uploads[key] || []).filter((u) => u.localId !== entry.localId);
                this.bannerReload = true;
                this.bannerError = this.t.errSessionExpired;
                this.render();
                this.focusFirstAlert();
                return;
            }

            if (xhr.status >= 200 && xhr.status < 300 && json.success && json.data?.upload_token) {
                entry.status = 'done';
                entry.progress = 100;
                entry.token = json.data.upload_token;
                entry.name = json.data.original_name || entry.name;
                entry.size = json.data.size_bytes || entry.size;
                if (!entry.preserveBatchError) {
                    delete this.fieldErrors[key];
                }
            } else {
                entry.status = 'error';
                const fileErrs = json.errors?.file || json.errors?.form || json.errors?.field_key;
                const msg = Array.isArray(fileErrs) && fileErrs.length > 0 ? fileErrs[0] : (json.message || this.t.errUploadFailed);
                entry.error = msg;
                this.uploads[key] = (this.uploads[key] || []).filter((u) => u.localId !== entry.localId);
                this.fieldErrors[key] = [String(msg)];
            }
            this.refreshUploadFieldInPlace(field);
        };

        xhr.onerror = () => {
            entry.xhr = null;
            if (!this.isUploadEntryActive(key, entry.localId)) return;
            this.uploads[key] = (this.uploads[key] || []).filter((u) => u.localId !== entry.localId);
            this.fieldErrors[key] = [this.t.errUploadFailed];
            this.refreshUploadFieldInPlace(field);
        };

        xhr.send(formData);
    }

    isUploadEntryActive(fieldKey, localId) {
        return (this.uploads[fieldKey] || []).some((u) => u.localId === localId);
    }

    removeUpload(field, localId) {
        const fieldKey = typeof field === 'string' ? field : field.field_key;
        const target = (this.uploads[fieldKey] || []).find((u) => u.localId === localId);
        if (target?.xhr && target.status === 'uploading') {
            try {
                target.xhr.abort();
            } catch {
                // ignore abort errors
            }
            target.xhr = null;
        }
        this.uploads[fieldKey] = (this.uploads[fieldKey] || []).filter((u) => u.localId !== localId);
        delete this.fieldErrors[fieldKey];
        if (typeof field === 'object' && field !== null) {
            this.refreshUploadFieldInPlace(field);
        } else {
            this.render();
        }
    }

    refreshUploadFieldInPlace(field) {
        if (typeof document === 'undefined') return;
        const key = field.field_key;
        const card = this.root.querySelector(`[data-field-key="${key}"]`);
        if (!card) {
            this.render();
            return;
        }
        const box = card.querySelector(`[data-upload-box="${key}"]`);
        if (!box) {
            this.render();
            return;
        }

        const errors = this.fieldErrors[key] || [];
        const labelId = `df_lbl_${key}`;
        const helpId = field.help_text ? `df_help_${key}` : null;
        const errorId = errors.length > 0 ? `df_err_${key}` : null;
        const describedBy = [helpId, errorId].filter(Boolean).join(' ') || null;

        card.classList.toggle('has-error', errors.length > 0);
        const oldErr = card.querySelector('.df-field-error');
        if (oldErr) oldErr.remove();

        this.populateUploadBox(box, field, { labelId, describedBy, hasError: errors.length > 0 });

        if (errors.length > 0) {
            const errEl = document.createElement('div');
            errEl.className = 'df-field-error';
            errEl.id = errorId;
            errEl.setAttribute('role', 'alert');
            errEl.textContent = errors.join(' ');
            card.appendChild(errEl);
        }
    }

    scrollToCardTop() {
        this.root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    focusFirstAlert() {
        const alertEl = this.root.querySelector('[data-alert-focus]');
        if (alertEl) {
            alertEl.focus();
            alertEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    focusFirstErrorField(visibleFields) {
        const firstInvalid = visibleFields.find((f) => this.fieldErrors[f.field_key]?.length);
        if (firstInvalid) {
            this.focusFieldByKey(firstInvalid.field_key);
        }
    }

    focusFieldByKey(fieldKey) {
        const card = this.root.querySelector(`[data-field-key="${fieldKey}"]`);
        if (!card) return;
        const focusable = card.querySelector('input:not([type="hidden"]), select, textarea, button, [tabindex="0"]');
        if (focusable) {
            focusable.focus();
        }
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Re-evaluate conditional visibility in the current section without tearing down active input focus
    refreshVisibilityInPlace() {
        const current = this.currentSection();
        if (!current) return;

        const evalState = this.evaluateEffectiveState();
        const visibleSet = new Set((evalState.visibleBySection.get(current.section_key) || []).map((f) => f.field_key));

        current.fields.forEach((field) => {
            const card = this.root.querySelector(`[data-field-key="${field.field_key}"]`);
            if (!card) return;
            const shouldShow = visibleSet.has(field.field_key);
            card.classList.toggle('d-none', !shouldShow);
            if (!shouldShow) {
                delete this.fieldErrors[field.field_key];
                card.classList.remove('has-error');
                const errNode = card.querySelector('.df-field-error');
                if (errNode) errNode.remove();
                card.querySelectorAll('[aria-invalid="true"]').forEach((el) => el.removeAttribute('aria-invalid'));
            }
        });

        const currentPos = Math.max(1, this.sectionHistory.length);
        const estTotal = Math.max(currentPos, evalState.activeSectionKeys.length);
        const pct = Math.min(100, Math.round((currentPos / estTotal) * 100));
        const stepSpan = this.root.querySelector('.df-progress-step-text');
        if (stepSpan) {
            stepSpan.textContent = formatMsg(this.t.stepProgress, { current: currentPos, total: estTotal });
        }
        const track = this.root.querySelector('.df-progress-track');
        if (track) {
            track.setAttribute('aria-valuemax', String(estTotal));
            track.setAttribute('aria-valuenow', String(currentPos));
        }
        const fill = this.root.querySelector('.df-progress-fill');
        if (fill) {
            fill.style.width = `${pct}%`;
        }

        const nextBtn = this.root.querySelector('[data-action="next-submit"]');
        if (nextBtn) {
            const visibleFields = evalState.visibleBySection.get(current.section_key) || [];
            const finalStep = this.isFinalStep(current, visibleFields);
            nextBtn.textContent = this.isSubmitting
                ? this.t.submitting
                : (finalStep ? this.t.submit : this.t.next);
        }
    }

    render() {
        if (typeof document === 'undefined') return;
        this.root.innerHTML = '';

        if (this.submissionResult) {
            this.root.appendChild(this.renderSuccessView());
            return;
        }

        if (this.sections.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'df-state-card';
            const p = document.createElement('p');
            p.className = 'mb-0 text-muted';
            p.textContent = this.t.errNoSections;
            empty.appendChild(p);
            this.root.appendChild(empty);
            return;
        }

        const current = this.currentSection();
        if (!current) return;

        const evalState = this.evaluateEffectiveState();
        const visibleFields = evalState.visibleBySection.get(current.section_key) || [];
        const visibleSet = new Set(visibleFields.map((f) => f.field_key));

        const formEl = document.createElement('form');
        formEl.noValidate = true;
        formEl.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleNextOrSubmit();
        });

        // Honeypot trap matching CMS StoreDynamicFormSubmissionRequest
        const hpWrap = document.createElement('div');
        hpWrap.className = 'df-hp-trap';
        hpWrap.setAttribute('aria-hidden', 'true');
        const hpLabel = document.createElement('label');
        hpLabel.setAttribute('for', 'df_hp_website');
        hpLabel.textContent = 'Leave empty';
        const hpInput = document.createElement('input');
        hpInput.type = 'text';
        hpInput.id = 'df_hp_website';
        hpInput.name = 'hp_website';
        hpInput.tabIndex = -1;
        hpInput.autocomplete = 'off';
        hpWrap.append(hpLabel, hpInput);
        formEl.appendChild(hpWrap);

        // Step progress bar
        if (this.sections.length > 1) {
            const currentPos = Math.max(1, this.sectionHistory.length);
            const estTotal = Math.max(currentPos, evalState.activeSectionKeys.length);
            const pct = Math.min(100, Math.round((currentPos / estTotal) * 100));

            const progressBox = document.createElement('div');
            progressBox.className = 'df-progress-box';

            const progHeader = document.createElement('div');
            progHeader.className = 'df-progress-header';

            const stepSpan = document.createElement('span');
            stepSpan.className = 'df-progress-step-text';
            stepSpan.textContent = formatMsg(this.t.stepProgress, { current: currentPos, total: estTotal });

            const nameSpan = document.createElement('span');
            nameSpan.className = 'df-progress-section-name';
            nameSpan.textContent = current.title || '';

            progHeader.append(stepSpan, nameSpan);

            const track = document.createElement('div');
            track.className = 'df-progress-track';
            track.setAttribute('role', 'progressbar');
            track.setAttribute('aria-valuemin', '1');
            track.setAttribute('aria-valuemax', String(estTotal));
            track.setAttribute('aria-valuenow', String(currentPos));

            const fill = document.createElement('div');
            fill.className = 'df-progress-fill';
            fill.style.width = `${pct}%`;

            track.appendChild(fill);
            progressBox.append(progHeader, track);
            formEl.appendChild(progressBox);
        }

        // Top-level server/network error banner
        if (this.bannerError) {
            const alertBox = document.createElement('div');
            alertBox.className = 'df-alert-banner is-error';
            alertBox.setAttribute('role', 'alert');
            alertBox.setAttribute('tabindex', '-1');
            alertBox.setAttribute('data-alert-focus', '1');

            const msg = document.createElement('div');
            msg.className = 'fw-semibold';
            msg.textContent = this.bannerError;
            alertBox.appendChild(msg);

            if (this.bannerReload) {
                const reloadWrap = document.createElement('div');
                const reloadBtn = document.createElement('button');
                reloadBtn.type = 'button';
                reloadBtn.className = 'df-btn-secondary';
                reloadBtn.textContent = this.t.reloadPage;
                reloadBtn.addEventListener('click', () => window.location.reload());
                reloadWrap.appendChild(reloadBtn);
                alertBox.appendChild(reloadWrap);
            }

            formEl.appendChild(alertBox);
        }

        // Section heading
        const secHeader = document.createElement('div');
        secHeader.className = 'df-section-header';

        const secTitle = document.createElement('h2');
        secTitle.className = 'df-section-title';
        secTitle.textContent = current.title || '';
        secHeader.appendChild(secTitle);

        if (current.description) {
            const secDesc = document.createElement('p');
            secDesc.className = 'df-section-desc';
            secDesc.textContent = current.description;
            secHeader.appendChild(secDesc);
        }
        formEl.appendChild(secHeader);

        // Render fields in section (hiding non-visible ones in DOM so toggling keeps smooth layout)
        const stack = document.createElement('div');
        stack.className = 'df-fields-stack';

        current.fields.forEach((field) => {
            const node = this.renderField(field);
            if (!visibleSet.has(field.field_key)) {
                node.classList.add('d-none');
            }
            stack.appendChild(node);
        });
        formEl.appendChild(stack);

        // Actions bar
        const actions = document.createElement('div');
        actions.className = 'df-wizard-actions';

        if (this.sectionHistory.length > 1) {
            const backBtn = document.createElement('button');
            backBtn.type = 'button';
            backBtn.className = 'df-btn-secondary';
            backBtn.textContent = this.t.previous;
            backBtn.disabled = this.isSubmitting;
            backBtn.addEventListener('click', () => this.handlePrevious());
            actions.appendChild(backBtn);
        } else {
            const spacer = document.createElement('div');
            actions.appendChild(spacer);
        }

        const isFinal = this.isFinalStep(current, visibleFields);
        const nextBtn = document.createElement('button');
        nextBtn.type = 'submit';
        nextBtn.className = 'df-btn-primary';
        nextBtn.setAttribute('data-action', 'next-submit');
        nextBtn.disabled = this.isSubmitting;
        nextBtn.textContent = this.isSubmitting ? this.t.submitting : (isFinal ? this.t.submit : this.t.next);
        actions.appendChild(nextBtn);

        formEl.appendChild(actions);
        this.root.appendChild(formEl);
    }

    renderField(field) {
        if (field.type === 'info_block') {
            return this.renderInfoBlock(field);
        }

        const key = field.field_key;
        const errors = this.fieldErrors[key] || [];
        const card = document.createElement('div');
        card.className = errors.length > 0 ? 'df-field-card has-error' : 'df-field-card';
        card.setAttribute('data-field-key', key);

        const labelId = `df_lbl_${key}`;
        const helpId = field.help_text ? `df_help_${key}` : null;
        const errorId = errors.length > 0 ? `df_err_${key}` : null;
        const describedBy = [helpId, errorId].filter(Boolean).join(' ') || null;

        const isGroupControl = ['radio', 'checkbox', 'linear_scale', 'rating', 'matrix_grid', 'file_upload'].includes(field.type);
        const labelEl = document.createElement(isGroupControl ? 'div' : 'label');
        labelEl.className = 'df-field-label';
        labelEl.id = labelId;
        if (!isGroupControl) {
            labelEl.setAttribute('for', `df_input_${key}`);
        }
        labelEl.textContent = field.label || key;

        if (field.is_required) {
            const reqSpan = document.createElement('span');
            reqSpan.className = 'df-required-mark';
            reqSpan.setAttribute('aria-hidden', 'true');
            reqSpan.textContent = '*';
            labelEl.appendChild(reqSpan);
        }

        card.appendChild(labelEl);

        if (field.help_text) {
            const helpEl = document.createElement('div');
            helpEl.className = 'df-field-help';
            helpEl.id = helpId;
            helpEl.textContent = field.help_text;
            card.appendChild(helpEl);
        }

        const controlEl = this.renderFieldControl(field, { labelId, describedBy, hasError: errors.length > 0 });
        card.appendChild(controlEl);

        if (errors.length > 0) {
            const errEl = document.createElement('div');
            errEl.className = 'df-field-error';
            errEl.id = errorId;
            errEl.setAttribute('role', 'alert');
            errEl.textContent = errors.join(' ');
            card.appendChild(errEl);
        }

        return card;
    }

    renderInfoBlock(field) {
        const block = document.createElement('div');
        block.className = 'df-info-block';
        block.setAttribute('data-field-key', field.field_key);

        if (field.label) {
            const title = document.createElement('h3');
            title.className = 'df-info-block-title';
            title.textContent = field.label;
            block.appendChild(title);
        }

        const content = field.options?.content;
        if (typeof content === 'string' && content.trim() !== '') {
            const body = document.createElement('div');
            body.className = 'df-info-block-content';
            body.textContent = content;
            block.appendChild(body);
        }

        const safeMedia = isSafeHttpUrl(field.options?.media_url);
        if (safeMedia) {
            const mediaWrap = document.createElement('div');
            mediaWrap.className = 'df-info-block-media';
            if (isImageUrl(safeMedia)) {
                const img = document.createElement('img');
                img.src = safeMedia;
                img.alt = field.label || '';
                img.loading = 'lazy';
                mediaWrap.appendChild(img);
            } else {
                const link = document.createElement('a');
                link.href = safeMedia;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'df-btn-secondary';
                link.textContent = this.t.openMedia;
                mediaWrap.appendChild(link);
            }
            block.appendChild(mediaWrap);
        }

        return block;
    }

    renderFieldControl(field, meta) {
        const key = field.field_key;
        const options = field.options || {};
        const rules = field.validation_rules || {};

        if (field.type === 'short_text') {
            const subtype = options.subtype || 'text';
            const input = document.createElement('input');
            input.id = `df_input_${key}`;
            input.className = 'df-input';
            input.type = ({ email: 'email', phone: 'tel', number: 'number', url: 'url' })[subtype] || 'text';
            if (subtype === 'number') input.step = 'any';
            if (field.placeholder) input.placeholder = field.placeholder;
            if (field.is_required) input.required = true;
            if (meta.describedBy) input.setAttribute('aria-describedby', meta.describedBy);
            if (meta.hasError) input.setAttribute('aria-invalid', 'true');
            input.value = this.answers[key] ?? '';

            input.addEventListener('input', (e) => {
                this.answers[key] = e.target.value;
                this.refreshVisibilityInPlace();
            });
            return input;
        }

        if (field.type === 'long_text') {
            const wrap = document.createElement('div');
            const textarea = document.createElement('textarea');
            textarea.id = `df_input_${key}`;
            textarea.className = 'df-textarea';
            textarea.rows = 4;
            const maxLen = rules.max_length ? Math.min(5000, Number(rules.max_length)) : 5000;
            textarea.maxLength = maxLen;
            if (field.placeholder) textarea.placeholder = field.placeholder;
            if (field.is_required) textarea.required = true;
            if (meta.describedBy) textarea.setAttribute('aria-describedby', meta.describedBy);
            if (meta.hasError) textarea.setAttribute('aria-invalid', 'true');
            textarea.value = this.answers[key] ?? '';

            const counter = document.createElement('div');
            counter.className = 'df-char-counter';
            counter.textContent = formatMsg(this.t.charCount, { len: charLength(textarea.value), max: maxLen });

            textarea.addEventListener('input', (e) => {
                this.answers[key] = e.target.value;
                counter.textContent = formatMsg(this.t.charCount, { len: charLength(e.target.value), max: maxLen });
                this.refreshVisibilityInPlace();
            });

            wrap.append(textarea, counter);
            return wrap;
        }

        if (field.type === 'radio') {
            const group = document.createElement('div');
            group.className = 'df-choice-list';
            group.setAttribute('role', 'radiogroup');
            group.setAttribute('aria-labelledby', meta.labelId);
            if (field.is_required) group.setAttribute('aria-required', 'true');
            if (meta.describedBy) group.setAttribute('aria-describedby', meta.describedBy);

            const current = this.answers[key] && typeof this.answers[key] === 'object' ? this.answers[key] : { selected: '', other_text: '' };
            const choices = Array.isArray(options.choices) ? [...options.choices] : [];
            if (options.allow_other) {
                choices.push({ key: OTHER_CHOICE_KEY, label: options.other_label || 'Lainnya' });
            }

            let otherWrap = null;
            let otherInput = null;

            choices.forEach((choice) => {
                const optLabel = document.createElement('label');
                const isSelected = current.selected === choice.key;
                optLabel.className = isSelected ? 'df-choice-option is-selected' : 'df-choice-option';

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = `df_radio_${key}`;
                radio.value = choice.key;
                radio.className = 'df-choice-input';
                radio.checked = isSelected;

                const span = document.createElement('span');
                span.textContent = choice.label || choice.key;

                radio.addEventListener('change', () => {
                    const prevOther = this.answers[key]?.other_text || '';
                    this.answers[key] = choice.key === OTHER_CHOICE_KEY
                        ? { selected: OTHER_CHOICE_KEY, other_text: prevOther }
                        : { selected: choice.key };

                    group.querySelectorAll('.df-choice-option').forEach((el) => el.classList.remove('is-selected'));
                    optLabel.classList.add('is-selected');
                    if (otherWrap) {
                        otherWrap.classList.toggle('d-none', choice.key !== OTHER_CHOICE_KEY);
                        if (choice.key === OTHER_CHOICE_KEY && otherInput) otherInput.focus();
                    }
                    this.refreshVisibilityInPlace();
                });

                optLabel.append(radio, span);
                group.appendChild(optLabel);
            });

            if (options.allow_other) {
                otherWrap = document.createElement('div');
                otherWrap.className = current.selected === OTHER_CHOICE_KEY ? 'df-other-input-wrap' : 'df-other-input-wrap d-none';

                otherInput = document.createElement('input');
                otherInput.type = 'text';
                otherInput.className = 'df-input';
                otherInput.maxLength = 500;
                otherInput.placeholder = this.t.otherPlaceholder;
                otherInput.setAttribute('aria-label', this.t.otherInputLabel);
                otherInput.value = current.other_text || '';
                otherInput.addEventListener('input', (e) => {
                    this.answers[key] = { selected: OTHER_CHOICE_KEY, other_text: e.target.value };
                });

                otherWrap.appendChild(otherInput);
                group.appendChild(otherWrap);
            }

            return group;
        }

        if (field.type === 'checkbox') {
            const group = document.createElement('div');
            group.className = 'df-choice-list';
            group.setAttribute('role', 'group');
            group.setAttribute('aria-labelledby', meta.labelId);
            if (field.is_required) group.setAttribute('aria-required', 'true');
            if (meta.describedBy) group.setAttribute('aria-describedby', meta.describedBy);

            const current = this.answers[key] && typeof this.answers[key] === 'object'
                ? this.answers[key]
                : { selected: [], other_text: '' };
            const selectedSet = new Set(Array.isArray(current.selected) ? current.selected : []);

            const choices = Array.isArray(options.choices) ? [...options.choices] : [];
            if (options.allow_other) {
                choices.push({ key: OTHER_CHOICE_KEY, label: options.other_label || 'Lainnya' });
            }

            let otherWrap = null;
            let otherInput = null;

            choices.forEach((choice) => {
                const optLabel = document.createElement('label');
                const isSelected = selectedSet.has(choice.key);
                optLabel.className = isSelected ? 'df-choice-option is-selected' : 'df-choice-option';

                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.value = choice.key;
                cb.className = 'df-choice-input';
                cb.checked = isSelected;

                const span = document.createElement('span');
                span.textContent = choice.label || choice.key;

                cb.addEventListener('change', () => {
                    if (cb.checked) {
                        selectedSet.add(choice.key);
                    } else {
                        selectedSet.delete(choice.key);
                    }
                    optLabel.classList.toggle('is-selected', cb.checked);

                    const arr = Array.from(selectedSet);
                    const otherText = otherInput ? otherInput.value : (current.other_text || '');
                    this.answers[key] = arr.length > 0
                        ? (selectedSet.has(OTHER_CHOICE_KEY) ? { selected: arr, other_text: otherText } : { selected: arr })
                        : undefined;

                    if (otherWrap) {
                        otherWrap.classList.toggle('d-none', !selectedSet.has(OTHER_CHOICE_KEY));
                        if (cb.checked && choice.key === OTHER_CHOICE_KEY && otherInput) otherInput.focus();
                    }
                    this.refreshVisibilityInPlace();
                });

                optLabel.append(cb, span);
                group.appendChild(optLabel);
            });

            if (options.allow_other) {
                otherWrap = document.createElement('div');
                otherWrap.className = selectedSet.has(OTHER_CHOICE_KEY) ? 'df-other-input-wrap' : 'df-other-input-wrap d-none';

                otherInput = document.createElement('input');
                otherInput.type = 'text';
                otherInput.className = 'df-input';
                otherInput.maxLength = 500;
                otherInput.placeholder = this.t.otherPlaceholder;
                otherInput.setAttribute('aria-label', this.t.otherInputLabel);
                otherInput.value = current.other_text || '';
                otherInput.addEventListener('input', (e) => {
                    const arr = Array.from(selectedSet);
                    this.answers[key] = { selected: arr, other_text: e.target.value };
                });

                otherWrap.appendChild(otherInput);
                group.appendChild(otherWrap);
            }

            return group;
        }

        if (field.type === 'dropdown') {
            const select = document.createElement('select');
            select.id = `df_input_${key}`;
            select.className = 'df-select';
            if (field.is_required) select.required = true;
            if (meta.describedBy) select.setAttribute('aria-describedby', meta.describedBy);
            if (meta.hasError) select.setAttribute('aria-invalid', 'true');

            const blankOpt = document.createElement('option');
            blankOpt.value = '';
            blankOpt.textContent = field.placeholder || this.t.selectPlaceholder;
            select.appendChild(blankOpt);

            (Array.isArray(options.choices) ? options.choices : []).forEach((choice) => {
                const opt = document.createElement('option');
                opt.value = choice.key;
                opt.textContent = choice.label || choice.key;
                select.appendChild(opt);
            });

            select.value = this.answers[key] ?? '';
            select.addEventListener('change', (e) => {
                this.answers[key] = e.target.value;
                this.refreshVisibilityInPlace();
            });
            return select;
        }

        if (field.type === 'linear_scale') {
            const minVal = Number(options.min_value ?? 1);
            const maxVal = Number(options.max_value ?? 5);
            const wrap = document.createElement('div');
            wrap.className = 'df-scale-wrapper';

            if (options.min_label || options.max_label) {
                const endpoints = document.createElement('div');
                endpoints.className = 'df-scale-endpoints';
                const minLbl = document.createElement('span');
                minLbl.textContent = options.min_label || '';
                const maxLbl = document.createElement('span');
                maxLbl.textContent = options.max_label || '';
                endpoints.append(minLbl, maxLbl);
                wrap.appendChild(endpoints);
            }

            const grid = document.createElement('div');
            grid.className = 'df-scale-grid';
            grid.setAttribute('role', 'radiogroup');
            grid.setAttribute('aria-labelledby', meta.labelId);
            if (field.is_required) grid.setAttribute('aria-required', 'true');
            if (meta.describedBy) grid.setAttribute('aria-describedby', meta.describedBy);

            for (let v = minVal; v <= maxVal; v += 1) {
                const lbl = document.createElement('label');
                lbl.className = 'df-scale-label';

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = `df_scale_${key}`;
                radio.value = String(v);
                radio.className = 'df-scale-input';
                radio.checked = Number(this.answers[key]) === v && this.answers[key] !== undefined && this.answers[key] !== '';

                const pill = document.createElement('span');
                pill.className = 'df-scale-pill';
                pill.textContent = String(v);

                radio.addEventListener('change', () => {
                    this.answers[key] = v;
                    this.refreshVisibilityInPlace();
                });

                lbl.append(radio, pill);
                grid.appendChild(lbl);
            }

            wrap.appendChild(grid);
            return wrap;
        }

        if (field.type === 'rating') {
            const maxStars = Math.max(3, Math.min(10, Number(options.max_stars ?? 5)));
            const iconKind = ['star', 'heart', 'thumb'].includes(options.icon) ? options.icon : 'star';
            const wrap = document.createElement('div');

            const group = document.createElement('div');
            group.className = 'df-rating-group';
            group.setAttribute('role', 'radiogroup');
            group.setAttribute('aria-labelledby', meta.labelId);
            if (field.is_required) group.setAttribute('aria-required', 'true');
            if (meta.describedBy) group.setAttribute('aria-describedby', meta.describedBy);

            const currentVal = this.answers[key] ? Number(this.answers[key]) : 0;
            const summary = document.createElement('div');
            summary.className = 'df-rating-summary';
            summary.textContent = currentVal > 0 ? formatMsg(this.t.ratingSelected, { val: currentVal, max: maxStars }) : '';

            for (let v = 1; v <= maxStars; v += 1) {
                const lbl = document.createElement('label');
                lbl.className = 'df-rating-label';

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = `df_rating_${key}`;
                radio.value = String(v);
                radio.className = 'df-rating-input';
                radio.checked = currentVal === v;
                radio.setAttribute('aria-label', formatMsg(this.t.ratingAria, { val: v, max: maxStars }));

                const btn = document.createElement('span');
                btn.className = v <= currentVal ? 'df-rating-btn is-active' : 'df-rating-btn';
                btn.appendChild(buildSvgIcon(iconKind));

                const numSpan = document.createElement('span');
                numSpan.className = 'df-rating-num';
                numSpan.textContent = String(v);
                btn.appendChild(numSpan);

                radio.addEventListener('change', () => {
                    this.answers[key] = v;
                    group.querySelectorAll('.df-rating-btn').forEach((el, idx) => {
                        el.classList.toggle('is-active', idx + 1 <= v);
                    });
                    summary.textContent = formatMsg(this.t.ratingSelected, { val: v, max: maxStars });
                    this.refreshVisibilityInPlace();
                });

                lbl.append(radio, btn);
                group.appendChild(lbl);
            }

            wrap.append(group, summary);
            return wrap;
        }

        if (field.type === 'matrix_grid') {
            const selectionMode = options.selection_mode === 'multiple' ? 'multiple' : 'single';
            const rows = Array.isArray(options.rows) ? options.rows : [];
            const cols = Array.isArray(options.columns) ? options.columns : [];
            const currentVal = this.answers[key] && typeof this.answers[key] === 'object' ? { ...this.answers[key] } : {};

            const scrollBox = document.createElement('div');
            scrollBox.className = 'df-matrix-scroll table-responsive';
            scrollBox.setAttribute('role', 'group');
            scrollBox.setAttribute('aria-labelledby', meta.labelId);
            if (field.is_required) scrollBox.setAttribute('aria-required', 'true');
            if (meta.describedBy) scrollBox.setAttribute('aria-describedby', meta.describedBy);

            const table = document.createElement('table');
            table.className = 'df-matrix-table';

            const thead = document.createElement('thead');
            const headRow = document.createElement('tr');
            const cornerTh = document.createElement('th');
            cornerTh.scope = 'col';
            cornerTh.textContent = '';
            headRow.appendChild(cornerTh);

            cols.forEach((col) => {
                const th = document.createElement('th');
                th.scope = 'col';
                th.textContent = col.label || col.key;
                headRow.appendChild(th);
            });
            thead.appendChild(headRow);
            table.appendChild(thead);

            const tbody = document.createElement('tbody');
            rows.forEach((row) => {
                const tr = document.createElement('tr');
                const rowTh = document.createElement('th');
                rowTh.scope = 'row';
                rowTh.className = 'df-matrix-row-label';
                rowTh.textContent = row.label || row.key;
                tr.appendChild(rowTh);

                cols.forEach((col) => {
                    const td = document.createElement('td');
                    td.className = 'text-center';

                    const cellLbl = document.createElement('label');
                    cellLbl.className = 'df-matrix-cell-label';

                    const input = document.createElement('input');
                    input.type = selectionMode === 'multiple' ? 'checkbox' : 'radio';
                    if (selectionMode === 'single') {
                        input.name = `df_matrix_${key}_${row.key}`;
                        input.checked = currentVal[row.key] === col.key;
                    } else {
                        const rowArr = Array.isArray(currentVal[row.key]) ? currentVal[row.key] : [];
                        input.checked = rowArr.includes(col.key);
                    }
                    input.value = col.key;
                    input.setAttribute('aria-label', `${row.label || row.key}: ${col.label || col.key}`);

                    input.addEventListener('change', () => {
                        const map = this.answers[key] && typeof this.answers[key] === 'object' ? { ...this.answers[key] } : {};
                        if (selectionMode === 'single') {
                            map[row.key] = col.key;
                        } else {
                            const existing = new Set(Array.isArray(map[row.key]) ? map[row.key] : []);
                            if (input.checked) existing.add(col.key);
                            else existing.delete(col.key);
                            if (existing.size > 0) map[row.key] = Array.from(existing);
                            else delete map[row.key];
                        }
                        this.answers[key] = Object.keys(map).length > 0 ? map : undefined;
                        this.refreshVisibilityInPlace();
                    });

                    cellLbl.appendChild(input);
                    td.appendChild(cellLbl);
                    tr.appendChild(td);
                });

                tbody.appendChild(tr);
            });

            table.appendChild(tbody);
            scrollBox.appendChild(table);
            return scrollBox;
        }

        if (field.type === 'date') {
            const input = document.createElement('input');
            input.id = `df_input_${key}`;
            input.type = 'date';
            input.className = 'df-input';
            const minDate = resolveBoundDate(rules.min_date);
            const maxDate = resolveBoundDate(rules.max_date);
            if (minDate) input.min = minDate;
            if (maxDate) input.max = maxDate;
            if (field.is_required) input.required = true;
            if (meta.describedBy) input.setAttribute('aria-describedby', meta.describedBy);
            if (meta.hasError) input.setAttribute('aria-invalid', 'true');
            input.value = this.answers[key] ?? '';

            input.addEventListener('change', (e) => {
                this.answers[key] = e.target.value;
                this.refreshVisibilityInPlace();
            });
            return input;
        }

        if (field.type === 'time') {
            const input = document.createElement('input');
            input.id = `df_input_${key}`;
            input.type = 'time';
            input.className = 'df-input';
            if (rules.min_time) input.min = String(rules.min_time);
            if (rules.max_time) input.max = String(rules.max_time);
            if (field.is_required) input.required = true;
            if (meta.describedBy) input.setAttribute('aria-describedby', meta.describedBy);
            if (meta.hasError) input.setAttribute('aria-invalid', 'true');
            input.value = this.answers[key] ?? '';

            input.addEventListener('change', (e) => {
                this.answers[key] = e.target.value ? e.target.value.slice(0, 5) : '';
                this.refreshVisibilityInPlace();
            });
            return input;
        }

        if (field.type === 'file_upload') {
            const box = document.createElement('div');
            box.className = 'df-upload-box';
            box.setAttribute('data-upload-box', key);
            this.populateUploadBox(box, field, meta);
            return box;
        }

        const fallback = document.createElement('div');
        return fallback;
    }

    populateUploadBox(box, field, meta) {
        const key = field.field_key;
        const options = field.options || {};
        const allowedExts = (Array.isArray(options.allowed_extensions) ? options.allowed_extensions : ['pdf', 'jpg', 'jpeg', 'png']).map((e) => String(e).toLowerCase());
        const maxMb = Math.max(1, Math.min(10, Number(options.max_file_size_mb ?? 5)));
        const maxFiles = Math.max(1, Math.min(5, Number(options.max_files ?? 1)));
        const existingUploads = this.uploads[key] || [];
        const atLimit = existingUploads.length >= maxFiles;

        box.innerHTML = '';

        const triggerRow = document.createElement('div');
        triggerRow.className = 'df-upload-trigger-row';

        const nativeInput = document.createElement('input');
        nativeInput.type = 'file';
        nativeInput.id = `df_input_${key}`;
        nativeInput.className = 'df-upload-input-native';
        nativeInput.accept = allowedExts.map((ext) => `.${ext}`).join(',');
        nativeInput.multiple = maxFiles > 1;
        nativeInput.disabled = atLimit;
        if (field.is_required) nativeInput.setAttribute('aria-required', 'true');
        if (meta.hasError) nativeInput.setAttribute('aria-invalid', 'true');
        if (meta.describedBy) nativeInput.setAttribute('aria-describedby', meta.describedBy);

        const pickBtnId = `df_pick_${key}`;
        const pickBtnLabel = document.createElement('label');
        pickBtnLabel.id = pickBtnId;
        pickBtnLabel.className = 'df-btn-secondary';
        pickBtnLabel.setAttribute('for', nativeInput.id);
        if (atLimit) {
            pickBtnLabel.setAttribute('aria-disabled', 'true');
            pickBtnLabel.textContent = this.t.maxFilesReached;
        } else {
            pickBtnLabel.textContent = maxFiles > 1 ? this.t.chooseFiles : this.t.chooseFile;
        }
        nativeInput.setAttribute('aria-labelledby', `${meta.labelId} ${pickBtnId}`);

        nativeInput.addEventListener('change', (e) => {
            this.handleFileSelection(field, e.target.files);
            e.target.value = '';
        });

        triggerRow.append(nativeInput, pickBtnLabel);
        box.appendChild(triggerRow);

        const metaNote = document.createElement('p');
        metaNote.className = 'df-upload-meta';
        metaNote.textContent = formatMsg(this.t.allowedMeta, {
            exts: allowedExts.map((e) => `.${e}`).join(', '),
            mb: maxMb,
            max: maxFiles,
        });
        box.appendChild(metaNote);

        if (existingUploads.length > 0) {
            const ul = document.createElement('ul');
            ul.className = 'df-upload-list';

            existingUploads.forEach((item) => {
                const li = document.createElement('li');
                li.className = 'df-upload-item';

                const info = document.createElement('div');
                info.className = 'df-upload-item-info';

                const nameDiv = document.createElement('div');
                nameDiv.className = 'df-upload-item-name';
                nameDiv.textContent = item.name;

                const subDiv = document.createElement('div');
                subDiv.className = 'df-upload-item-sub';
                subDiv.setAttribute('data-upload-sub', item.localId);
                subDiv.textContent = item.status === 'uploading'
                    ? `${formatBytes(item.size)} | ${this.t.uploading} (${item.progress}%)`
                    : `${formatBytes(item.size)} | ${this.t.uploadDone}`;

                info.append(nameDiv, subDiv);

                if (item.status === 'uploading') {
                    const prog = document.createElement('div');
                    prog.className = 'df-upload-progress';
                    const bar = document.createElement('div');
                    bar.className = 'df-upload-progress-bar';
                    bar.setAttribute('data-upload-bar', item.localId);
                    bar.style.width = `${item.progress}%`;
                    prog.appendChild(bar);
                    info.appendChild(prog);
                }

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'df-upload-remove-btn';
                removeBtn.textContent = this.t.removeFile;
                removeBtn.setAttribute('aria-label', `${this.t.removeFile}: ${item.name}`);
                removeBtn.addEventListener('click', () => this.removeUpload(field, item.localId));

                li.append(info, removeBtn);
                ul.appendChild(li);
            });

            box.appendChild(ul);
        }
    }

    renderSuccessView() {
        const card = document.createElement('div');
        card.className = 'df-state-card';
        card.setAttribute('role', 'status');

        const badge = document.createElement('div');
        badge.className = 'df-state-icon is-success';
        badge.setAttribute('aria-hidden', 'true');
        const checkSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        checkSvg.setAttribute('viewBox', '0 0 24 24');
        checkSvg.setAttribute('width', '28');
        checkSvg.setAttribute('height', '28');
        checkSvg.setAttribute('fill', 'currentColor');
        const checkPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        checkPath.setAttribute('d', 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z');
        checkSvg.appendChild(checkPath);
        badge.appendChild(checkSvg);

        const heading = document.createElement('h2');
        heading.className = 'fw-bold mb-2';
        heading.style.color = 'var(--secondary-darker-color, #124f8c)';
        heading.setAttribute('tabindex', '-1');
        heading.setAttribute('data-success-focus', '1');
        heading.textContent = this.t.successHeading;

        const message = document.createElement('p');
        message.className = 'mb-3 mx-auto';
        message.style.maxWidth = '580px';
        message.style.color = '#475569';
        message.textContent = this.submissionResult.confirmation_message || this.schema.confirmation_message || '';

        card.append(badge, heading, message);

        const ticket = this.submissionResult.ticket_number;
        if (this.schema.mode === 'feedback' && ticket) {
            const ticketBox = document.createElement('div');
            ticketBox.className = 'df-ticket-box';

            const lbl = document.createElement('div');
            lbl.className = 'df-ticket-label';
            lbl.textContent = this.t.ticketLabel;

            const code = document.createElement('div');
            code.className = 'df-ticket-code';
            code.textContent = ticket;

            const hint = document.createElement('p');
            hint.className = 'small mb-2 mt-1';
            hint.style.color = '#475569';
            hint.textContent = this.t.ticketHint;

            const copyBtn = document.createElement('button');
            copyBtn.type = 'button';
            copyBtn.className = 'df-btn-secondary mt-2';
            copyBtn.textContent = this.t.copyTicket;
            copyBtn.addEventListener('click', () => {
                if (navigator.clipboard?.writeText) {
                    navigator.clipboard.writeText(ticket).then(() => {
                        copyBtn.textContent = this.t.ticketCopied;
                    }).catch(() => {});
                }
            });

            ticketBox.append(lbl, code, hint, copyBtn);
            card.appendChild(ticketBox);
        }

        const actions = document.createElement('div');
        actions.className = 'd-flex flex-wrap justify-content-center gap-3 mt-4';

        if (this.schema.allow_multiple_submissions) {
            const againBtn = document.createElement('button');
            againBtn.type = 'button';
            againBtn.className = 'df-btn-secondary';
            againBtn.textContent = this.t.submitAnother;
            againBtn.addEventListener('click', () => {
                this.answers = {};
                this.uploads = {};
                this.fieldErrors = {};
                this.bannerError = null;
                this.submissionResult = null;
                this.sectionHistory = this.sections.length > 0 ? [this.sections[0].section_key] : [];
                this.render();
                this.scrollToCardTop();
            });
            actions.appendChild(againBtn);
        }

        const catLink = document.createElement('a');
        catLink.href = this.config.catalogUrl;
        catLink.className = 'df-btn-secondary';
        catLink.textContent = this.t.backToCatalog;
        actions.appendChild(catLink);

        const homeLink = document.createElement('a');
        homeLink.href = this.config.homeUrl;
        homeLink.className = 'df-btn-primary';
        homeLink.textContent = this.t.backToHome;
        actions.appendChild(homeLink);

        card.appendChild(actions);
        return card;
    }
}

export {
    evaluateOperator,
    evaluateVisibility,
    isSafeHttpUrl,
    normalizeComparableValue,
    resolveChoiceJumpTarget,
    resolveSectionNextTarget,
    DynamicFormWizard,
};

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('dynamicFormApp');
        const configEl = document.getElementById('dynamicFormConfig');
        if (!root || !configEl) return;

        try {
            const config = JSON.parse(configEl.textContent || '{}');
            const wizard = new DynamicFormWizard(root, config);
            wizard.init();
        } catch {
            const lang = document.documentElement.lang === 'en' ? 'en' : 'id';
            root.innerHTML = '';
            const card = document.createElement('div');
            card.className = 'df-state-card';
            card.setAttribute('role', 'alert');
            const p = document.createElement('p');
            p.className = 'mb-0 fw-semibold';
            p.style.color = '#b91c1c';
            p.textContent = UI_STRINGS[lang].errInitFailed;
            card.appendChild(p);
            root.appendChild(card);
        }
    });
}
