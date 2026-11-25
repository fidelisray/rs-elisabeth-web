import test from 'node:test';
import assert from 'node:assert/strict';
import {
    DynamicFormWizard,
    evaluateOperator,
    evaluateVisibility,
    isNumericValue,
    isSafeHttpUrl,
    isValidIndonesianPhone,
    isValueEmpty,
    matchesTextFormatPreset,
    resolveChoiceJumpTarget,
    resolveSectionNextTarget,
} from '../../resources/js/dynamic-form-renderer.js';

test('evaluates all 11 visibility operators consistently with CMS logic', () => {
    assert.equal(evaluateOperator(null, 'is_empty', null), true);
    assert.equal(evaluateOperator([], 'is_empty', null), true);
    assert.equal(evaluateOperator('ok', 'is_not_empty', null), true);
    assert.equal(evaluateOperator('5', 'equals', 5), true);
    assert.equal(evaluateOperator('a', 'not_equals', 'b'), true);
    assert.equal(evaluateOperator('igd', 'in', 'igd,poli'), true);
    assert.equal(evaluateOperator('lab', 'not_in', 'igd,poli'), true);
    assert.equal(evaluateOperator('Pelayanan Bagus', 'contains', 'bagus'), true);
    assert.equal(evaluateOperator(['a', 'b'], 'contains', 'b'), true);
    assert.equal(evaluateOperator(4, 'gt', 3), true);
    assert.equal(evaluateOperator(3, 'gte', 3), true);
    assert.equal(evaluateOperator(2, 'lt', 3), true);
    assert.equal(evaluateOperator(3, 'lte', 3), true);
    assert.equal(evaluateOperator('', 'lt', 3), false);
});

test('evaluates radio object visibility and strips hidden or jumped section answers', () => {
    const wizard = new DynamicFormWizard({ querySelector: () => null }, {
        schema: {
            sections: [
                {
                    section_key: 'sec_1',
                    title: 'Step 1',
                    sort_order: 1,
                    next_section_rule: { action: 'next', target_section_key: null },
                    fields: [
                        {
                            field_key: 'unit',
                            type: 'radio',
                            label: 'Unit',
                            is_required: true,
                            sort_order: 1,
                            options: {
                                choices: [
                                    { key: 'igd', label: 'IGD', go_to_section_key: 'sec_3' },
                                    { key: 'poli', label: 'Poli', go_to_section_key: null },
                                ],
                            },
                        },
                        {
                            field_key: 'nama_poli',
                            type: 'short_text',
                            label: 'Nama Poli',
                            is_required: true,
                            sort_order: 2,
                            conditional_logic: {
                                visibility: {
                                    enabled: true,
                                    match: 'all',
                                    rules: [{ source_field_key: 'unit', operator: 'equals', value: 'poli' }],
                                },
                            },
                        },
                    ],
                },
                {
                    section_key: 'sec_2',
                    title: 'Poli Detail',
                    sort_order: 2,
                    fields: [{ field_key: 'antrean', type: 'rating', label: 'Antrean', sort_order: 1 }],
                },
                {
                    section_key: 'sec_3',
                    title: 'IGD Detail',
                    sort_order: 3,
                    fields: [{ field_key: 'triase', type: 'rating', label: 'Triase', sort_order: 1 }],
                },
            ],
        },
        siteLocale: 'id',
    });

    wizard.answers = {
        unit: { selected: 'igd' },
        nama_poli: 'Poli Gigi',
        antrean: 4,
        triase: 5,
    };

    const evaluated = wizard.evaluateEffectiveState();
    assert.deepEqual(evaluated.activeSectionKeys, ['sec_1', 'sec_3']);
    assert.deepEqual(evaluated.sanitizedAnswers, {
        unit: { selected: 'igd' },
        triase: 5,
    });
});

test('filters unsafe info_block URLs and validates choice jumps and fields', () => {
    assert.equal(isSafeHttpUrl('javascript:alert(1)'), null);
    assert.equal(isSafeHttpUrl('data:text/html,<script>alert(1)</script>'), null);
    assert.equal(isSafeHttpUrl('https://rs-elisabeth.com/panduan.pdf'), 'https://rs-elisabeth.com/panduan.pdf');

    assert.equal(resolveChoiceJumpTarget({
        type: 'radio',
        options: { choices: [{ key: 'k', go_to_section_key: '__submit__' }] },
    }, { selected: 'k' }), '__submit__');

    assert.equal(resolveSectionNextTarget(
        { next_section_rule: { action: 'submit', target_section_key: null } },
        [{ section_key: 'a' }, { section_key: 'b' }],
        0
    ), '__submit__');

    assert.equal(evaluateVisibility({
        conditional_logic: {
            visibility: {
                enabled: true,
                match: 'any',
                rules: [
                    { source_field_key: 'q1', operator: 'equals', value: 'yes' },
                    { source_field_key: 'q2', operator: 'gt', value: '10' },
                ],
            },
        },
    }, { q1: 'no', q2: 12 }), true);

    const wizard = new DynamicFormWizard({ querySelector: () => null }, {
        schema: {
            sections: [{
                section_key: 'sec_1',
                title: 'S1',
                fields: [
                    { field_key: 'email', type: 'short_text', label: 'Email', is_required: true, options: { subtype: 'email' } },
                    { field_key: 'phone', type: 'short_text', label: 'HP', is_required: true, options: { subtype: 'phone' } },
                    { field_key: 'pilihan', type: 'radio', label: 'Pilihan', is_required: true, options: { allow_other: true, choices: [{ key: 'a', label: 'A' }] } },
                    { field_key: 'matriks', type: 'matrix_grid', label: 'Matriks', is_required: true, options: { require_all_rows: true, rows: [{ key: 'r1' }, { key: 'r2' }], columns: [{ key: 'c1' }] } },
                ],
            }],
        },
        siteLocale: 'en',
    });

    wizard.answers = {
        email: 'bad-email',
        phone: '123',
        pilihan: { selected: '__other__', other_text: '' },
        matriks: { r1: 'c1' },
    };

    const errs = wizard.validateVisibleFields(wizard.sections[0].fields);
    assert.ok(errs.email?.length > 0);
    assert.ok(errs.phone?.length > 0);
    assert.ok(errs.pilihan?.length > 0);
    assert.ok(errs.matriks?.length > 0);
});

test('handles empty string length/selection rules, broken jump final step, and in-flight upload abort', () => {
    const wizard = new DynamicFormWizard({ querySelector: () => null }, {
        schema: {
            sections: [{
                section_key: 's1',
                title: 'S1',
                fields: [
                    {
                        field_key: 'nama',
                        type: 'short_text',
                        label: 'Nama',
                        is_required: true,
                        validation_rules: { min_length: '', max_length: '' },
                    },
                    {
                        field_key: 'opsi',
                        type: 'checkbox',
                        label: 'Opsi',
                        is_required: true,
                        options: { choices: [{ key: 'a', label: 'A' }] },
                        validation_rules: { min_selected: '', max_selected: '' },
                    },
                ],
            }],
        },
        siteLocale: 'id',
    });

    wizard.answers = { nama: 'Siti Aminah', opsi: { selected: ['a'] } };
    assert.deepEqual(wizard.validateVisibleFields(wizard.sections[0].fields), {});

    let aborted = false;
    wizard.uploads.lampiran = [{
        localId: 'up_1',
        status: 'uploading',
        xhr: { abort() { aborted = true; } },
    }];
    wizard.removeUpload('lampiran', 'up_1');
    assert.equal(aborted, true);
    assert.deepEqual(wizard.uploads.lampiran, []);

    assert.equal(
        wizard.isFinalStep({ section_key: 's1', next_section_rule: { action: 'jump', target_section_key: 'missing' } }, []),
        true
    );
});

test('maintains PHP parity for whitespace-only strings and matrix_grid object values', () => {
    assert.equal(isValueEmpty('   '), true);
    assert.equal(isValueEmpty('\t\n '), true);
    assert.equal(isValueEmpty(['   ', '']), true);
    assert.equal(isValueEmpty(0), false);
    assert.equal(isValueEmpty('0'), false);

    assert.equal(evaluateOperator('   ', 'is_empty', null), true);
    assert.equal(evaluateOperator('   ', 'is_not_empty', null), false);
    assert.equal(evaluateOperator('   ', 'equals', '   '), false);

    assert.equal(isNumericValue('   '), false);
    assert.equal(isNumericValue(''), false);
    assert.equal(isNumericValue(0), true);
    assert.equal(isNumericValue('0'), true);
    assert.equal(isNumericValue(' 42 '), true);
    assert.equal(evaluateOperator('   ', 'lt', 3), false);
    assert.equal(evaluateOperator('   ', 'gte', 0), false);
    assert.equal(evaluateOperator(5, 'gt', '   '), false);

    // matrix_grid object/map handling without [object Object] coercion
    assert.equal(isValueEmpty({}), true);
    assert.equal(isValueEmpty({ r1: '', r2: '   ' }), true);
    assert.equal(isValueEmpty({ r1: [], r2: ['   '] }), true);
    assert.equal(isValueEmpty({ r1: 'c1', r2: '' }), false);
    assert.equal(isValueEmpty({ r1: ['c1'] }), false);

    assert.equal(evaluateOperator({ r1: '   ' }, 'is_empty', null), true);
    assert.equal(evaluateOperator({ r1: ['   '] }, 'is_empty', null), true);
    assert.equal(evaluateOperator({ r1: 'c1' }, 'is_not_empty', null), true);
    assert.equal(evaluateOperator({ r1: ['c1'] }, 'is_not_empty', null), true);
    assert.equal(evaluateOperator({ r1: 'c1' }, 'equals', '[object Object]'), false);
    assert.equal(evaluateOperator({ r1: 'c1' }, 'equals', 'c1'), false);
    assert.equal(evaluateOperator({ r1: 'c1' }, 'not_equals', 'c1'), false);
    assert.equal(evaluateOperator({ r1: 'c1' }, 'in', ['[object Object]', 'c1']), false);
    assert.equal(evaluateOperator({ r1: 'c1' }, 'not_in', ['c1']), false);
    assert.equal(evaluateOperator({ r1: 'c1' }, 'contains', 'object'), false);
    assert.equal(evaluateOperator({ r1: ['c1'] }, 'contains', 'c1'), false);
    assert.equal(evaluateOperator({ r1: 'c1' }, 'gt', 0), false);
});

test('validates Indonesian phone numbers (10-13 local digits) and short_text format presets with CMS parity', () => {
    for (const valid of ['0812345678', '0812345678901', '+6281234567890', '6281234567890', '0812-3456-7890', '0812.3456.7890']) {
        assert.equal(isValidIndonesianPhone(valid), true, `Expected valid phone: ${valid}`);
    }
    for (const invalid of ['081234567', '08123456789012', '08012345678', '0248310076', '1234567890', '0812abcd7890', '']) {
        assert.equal(isValidIndonesianPhone(invalid), false, `Expected invalid phone: ${invalid}`);
    }

    assert.equal(matchesTextFormatPreset('letters_spaces', "dr. Budi Santoso, Sp.PD Ma'ruf"), true);
    assert.equal(matchesTextFormatPreset('letters_spaces', 'Budi 123'), false);
    assert.equal(matchesTextFormatPreset('digits_only', '0033740101900001'), true);
    assert.equal(matchesTextFormatPreset('digits_only', '3374-0101'), false);
    assert.equal(matchesTextFormatPreset('code_dash', 'RM-001234'), true);
    assert.equal(matchesTextFormatPreset('code_dash', 'RM 001!'), false);

    const wizard = new DynamicFormWizard({ querySelector: () => null }, {
        schema: {
            sections: [{
                section_key: 'sec_1',
                title: 'Identitas',
                fields: [
                    { field_key: 'nama', type: 'short_text', label: 'Nama', is_required: true, options: { subtype: 'text' }, validation_rules: { format_preset: 'letters_spaces' } },
                    { field_key: 'nik', type: 'short_text', label: 'NIK', is_required: true, options: { subtype: 'text' }, validation_rules: { format_preset: 'digits_only', min_length: 16, max_length: 16 } },
                    { field_key: 'kode', type: 'short_text', label: 'Kode', is_required: true, options: { subtype: 'text' }, validation_rules: { format_preset: 'code_dash' } },
                    { field_key: 'hp', type: 'short_text', label: 'HP', is_required: true, options: { subtype: 'phone' } },
                ],
            }],
        },
        siteLocale: 'id',
    });

    wizard.answers = {
        nama: "dr. Siti Ma'ruf",
        nik: '0033740101900001',
        kode: 'IGD-02',
        hp: '+62 812-3456-7890',
    };
    assert.deepEqual(wizard.validateVisibleFields(wizard.sections[0].fields), {});

    wizard.answers = {
        nama: 'Budi 99',
        nik: '12345',
        kode: 'IGD 02!',
        hp: '08012345678',
    };
    const errs = wizard.validateVisibleFields(wizard.sections[0].fields);
    assert.ok(errs.nama?.[0]?.includes('huruf, spasi'));
    assert.ok(errs.nik?.length > 0);
    assert.ok(errs.kode?.[0]?.includes('tanda hubung'));
    assert.ok(errs.hp?.[0]?.includes('nomor telepon Indonesia'));
});

test('resolves specific question summary metadata and clears field error reactively when valid answer is provided', () => {
    const wizard = new DynamicFormWizard({ querySelector: () => null }, {
        schema: {
            sections: [{
                section_key: 'sec_1',
                title: 'Bagian 1',
                fields: [
                    { field_key: 'intro', type: 'info_block', label: 'Pengantar', sort_order: 1 },
                    { field_key: 'nama', type: 'short_text', label: 'Nama Lengkap', is_required: true, sort_order: 2 },
                    { field_key: 'layanan', type: 'radio', label: 'Unit Layanan', is_required: true, sort_order: 3, options: { choices: [{ key: 'igd', label: 'IGD' }] } },
                ],
            }],
        },
        siteLocale: 'id',
    });

    const visibleFields = wizard.sections[0].fields;
    wizard.fieldErrors = wizard.validateVisibleFields(visibleFields);

    assert.equal(wizard.fieldErrors.nama?.[0], 'Pertanyaan [Nama Lengkap] wajib diisi.');
    assert.equal(wizard.fieldErrors.layanan?.[0], 'Pertanyaan [Unit Layanan] wajib dipilih.');

    // info_block tidak dihitung sebagai nomor pertanyaan
    const namaMeta = wizard.getFieldSummaryMeta('nama');
    const layananMeta = wizard.getFieldSummaryMeta('layanan');
    assert.equal(namaMeta.number, 1);
    assert.equal(namaMeta.badge, 'Pertanyaan 1');
    assert.equal(layananMeta.number, 2);
    assert.equal(layananMeta.badge, 'Pertanyaan 2');

    // Jika nilai masih kosong, error tetap dipertahankan
    wizard.answers.nama = '   ';
    wizard.clearFieldErrorIfValid(visibleFields[1]);
    assert.ok(wizard.fieldErrors.nama?.length > 0);

    // Begitu diisi jawaban valid, error untuk field tersebut langsung terhapus
    wizard.answers.nama = 'Maria Kristina';
    wizard.clearFieldErrorIfValid(visibleFields[1]);
    assert.equal(wizard.fieldErrors.nama, undefined);
    assert.ok(wizard.fieldErrors.layanan?.length > 0);

    wizard.answers.layanan = { selected: 'igd' };
    wizard.clearFieldErrorIfValid(visibleFields[2]);
    assert.deepEqual(wizard.fieldErrors, {});
});
