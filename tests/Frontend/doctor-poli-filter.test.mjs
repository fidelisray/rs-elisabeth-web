import test from 'node:test';
import assert from 'node:assert/strict';
import {
    buildFilteredSchedule,
    filterDoctors,
    formatPoliName,
    getAvailablePolis,
    getPoliCounts,
    getSpecialtyCounts,
    matchesPoliOptionQuery,
    preprocessingApiData,
    resetReferenceCaches,
    setDoctorContext,
    state,
} from '../../resources/js/dokter/dokterService.js';
import { createScheduleGrid, renderModal, t } from '../../resources/js/dokter/dokterUI.js';

const SAMPLE_POLI_REFERENCES = [
    { code: 'PL-02', name: 'KLINIK BEDAH', is_active: true },
    { code: 'PL-04', name: 'KLINIK LANJUT USIA (GERIATRI)', is_active: true },
    { code: 'PL-05', name: 'KLINIK GIGI DAN MULUT', is_active: true },
    { code: 'PL-09', name: 'KLINIK KULIT DAN KELAMIN', is_active: true },
    { code: 'PL-11', name: 'KLINIK NYERI', is_active: false },
    { code: 'PL-14', name: 'KLINIK PENYAKIT DALAM', is_active: true },
    { code: 'PL-18', name: 'KLINIK THT-KL', is_active: true },
    { code: 'PL-21', name: 'KLINIK RAFAEL', is_active: false },
    { code: 'PL-22', name: 'KLINIK TELEKONSULTASI', is_active: true },
    { code: 'PL-26', name: 'KLINIK REHABILITASI MEDIK', is_active: true },
    { code: 'PL-27', name: 'KLINIK UMUM', is_active: false },
    { code: 'PL-31', name: 'KLINIK ELISABETH BEDAH', is_active: true },
    { code: 'PL-43', name: 'KLINIK ELISABETH PENYAKIT DALAM', is_active: true },
    { code: 'PL-52', name: 'KLINIK ELISABETH REHABILITASI MEDIK', is_active: true },
    { code: 'PL-53', name: 'KLINIK ELISABETH LANJUT USIA (GERIATRI)', is_active: true },
    { code: 'PL-99', name: 'KLINIK MEDICAL CHECK UP (MCU)', is_active: false },
    { code: 'PM-03', name: 'INSTALASI RADIOLOGI', is_active: false },
    { code: 'PM-04', name: 'INSTALASI REHABILITASI MEDIK', is_active: false },
    { code: 'PM-09', name: 'UNIT PEMULASARAN JENAZAH', is_active: false },
    { code: 'PM-11', name: 'UNIT ESWL', is_active: false },
    { code: 'PM-16', name: 'KLINIK NYERI', is_active: false },
];

const SAMPLE_SPECIALTY_MAP = new Map([
    ['KSM-04', 'BEDAH DIGESTIF'],
    ['KSM-08', 'BEDAH ONKOLOGI'],
    ['KSM-12', 'KEDOKTERAN FISIK DAN REHABILITASI'],
    ['KSM-14', 'KEDOKTERAN GIGI DAN MULUT'],
    ['KSM-20', 'KESEHATAN KULIT & KELAMIN'],
    ['KSM-24', 'ORTHOPAEDI DAN TRAUMATOLOGI'],
    ['KSM-28', 'PENYAKIT DALAM'],
    ['KSM-36', 'UMUM'],
]);

const ACTIVE_POLI_CODES = SAMPLE_POLI_REFERENCES.filter((p) => p.is_active).map((p) => p.code);

const SAMPLE_RAW_SCHEDULES = [
    // dr. C. Prabani: PL-02 (Mon 2 sessions -> Sesi 1 non-bookable because PL-31 Elisabeth on Mon, Sesi 2 bookable) + PL-31 (Mon, Tue)
    {
        ParamedicCode: '0033011295',
        ParamedicName: 'dr. C. Prabani, Sp.B, KBD',
        SpecialityCode: 'KSM-04',
        SpecialtyName: 'BEDAH DIGESTIF',
        ServiceUnitCode: 'PL-02',
        ServiceUnitName: 'KLINIK BEDAH',
        DayNumber: 1,
        ListTimeSchedule: [
            { StartTime: '08:00', EndTime: '10:00', Session: 1 },
            { StartTime: '10:00', EndTime: '12:00', Session: 2 },
        ],
    },
    {
        ParamedicCode: '0033011295',
        ParamedicName: 'dr. C. Prabani, Sp.B, KBD',
        SpecialityCode: 'KSM-04',
        SpecialtyName: 'BEDAH DIGESTIF',
        ServiceUnitCode: 'PL-31',
        ServiceUnitName: 'KLINIK ELISABETH BEDAH',
        DayNumber: 1,
        ListTimeSchedule: [{ StartTime: '09:00', EndTime: '11:00', Session: 1 }],
    },
    {
        ParamedicCode: '0033011295',
        ParamedicName: 'dr. C. Prabani, Sp.B, KBD',
        SpecialityCode: 'KSM-04',
        SpecialtyName: 'BEDAH DIGESTIF',
        ServiceUnitCode: 'PL-31',
        ServiceUnitName: 'KLINIK ELISABETH BEDAH',
        DayNumber: 2,
        ListTimeSchedule: [{ StartTime: '09:00', EndTime: '11:00', Session: 1 }],
    },
    // dr. Edmond: BEDAH ONKOLOGI with PL-02, PL-22, PL-31
    {
        ParamedicCode: '5604261225',
        ParamedicName: 'dr. Edmond Rukmana Wikanta, Sp.B.Subsp.Onk(K)',
        SpecialityCode: 'KSM-08',
        SpecialtyName: 'BEDAH ONKOLOGI',
        ServiceUnitCode: 'PL-02',
        ServiceUnitName: 'KLINIK BEDAH',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 11:00',
    },
    {
        ParamedicCode: '5604261225',
        ParamedicName: 'dr. Edmond Rukmana Wikanta, Sp.B.Subsp.Onk(K)',
        SpecialityCode: 'KSM-08',
        SpecialtyName: 'BEDAH ONKOLOGI',
        ServiceUnitCode: 'PL-22',
        ServiceUnitName: 'KLINIK TELEKONSULTASI',
        DayNumber: 3,
        OperationalTimeName: '13:00 - 14:00',
    },
    {
        ParamedicCode: '5604261225',
        ParamedicName: 'dr. Edmond Rukmana Wikanta, Sp.B.Subsp.Onk(K)',
        SpecialityCode: 'KSM-08',
        SpecialtyName: 'BEDAH ONKOLOGI',
        ServiceUnitCode: 'PL-31',
        ServiceUnitName: 'KLINIK ELISABETH BEDAH',
        DayNumber: 4,
        OperationalTimeName: '10:00 - 12:00',
    },
    // dr. Lindawati: KEDOKTERAN FISIK DAN REHABILITASI with PM-04, PL-26, PL-52, PM-16 (KLINIK NYERI)
    {
        ParamedicCode: '4731011215',
        ParamedicName: 'dr. Lindawati, Sp.KFR',
        SpecialityCode: 'KSM-12',
        SpecialtyName: 'KEDOKTERAN FISIK DAN REHABILITASI',
        ServiceUnitCode: 'PM-04',
        ServiceUnitName: 'INSTALASI REHABILITASI MEDIK',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 12:00',
    },
    {
        ParamedicCode: '4731011215',
        ParamedicName: 'dr. Lindawati, Sp.KFR',
        SpecialityCode: 'KSM-12',
        SpecialtyName: 'KEDOKTERAN FISIK DAN REHABILITASI',
        ServiceUnitCode: 'PL-26',
        ServiceUnitName: 'KLINIK REHABILITASI MEDIK',
        DayNumber: 2,
        OperationalTimeName: '08:00 - 12:00',
    },
    {
        ParamedicCode: '4731011215',
        ParamedicName: 'dr. Lindawati, Sp.KFR',
        SpecialityCode: 'KSM-12',
        SpecialtyName: 'KEDOKTERAN FISIK DAN REHABILITASI',
        ServiceUnitCode: 'PL-52',
        ServiceUnitName: 'KLINIK ELISABETH REHABILITASI MEDIK',
        DayNumber: 3,
        OperationalTimeName: '09:00 - 11:00',
    },
    {
        ParamedicCode: '4731011215',
        ParamedicName: 'dr. Lindawati, Sp.KFR',
        SpecialityCode: 'KSM-12',
        SpecialtyName: 'KEDOKTERAN FISIK DAN REHABILITASI',
        ServiceUnitCode: 'PM-16',
        ServiceUnitName: 'KLINIK NYERI',
        DayNumber: 4,
        OperationalTimeName: '08:00 - 12:00',
    },
    // dr. Dian Kalista: ORTHOPAEDI with PL-11 & PM-16 (duplicate KLINIK NYERI names) + PL-02
    {
        ParamedicCode: '0194010321',
        ParamedicName: 'dr. Dian Kalista, Sp. OT',
        SpecialityCode: 'KSM-24',
        SpecialtyName: 'ORTHOPAEDI DAN TRAUMATOLOGI',
        ServiceUnitCode: 'PL-11',
        ServiceUnitName: 'KLINIK NYERI',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 11:00',
    },
    {
        ParamedicCode: '0194010321',
        ParamedicName: 'dr. Dian Kalista, Sp. OT',
        SpecialityCode: 'KSM-24',
        SpecialtyName: 'ORTHOPAEDI DAN TRAUMATOLOGI',
        ServiceUnitCode: 'PM-16',
        ServiceUnitName: 'KLINIK NYERI',
        DayNumber: 2,
        OperationalTimeName: '08:00 - 12:00',
    },
    {
        ParamedicCode: '0194010321',
        ParamedicName: 'dr. Dian Kalista, Sp. OT',
        SpecialityCode: 'KSM-24',
        SpecialtyName: 'ORTHOPAEDI DAN TRAUMATOLOGI',
        ServiceUnitCode: 'PL-02',
        ServiceUnitName: 'KLINIK BEDAH',
        DayNumber: 3,
        OperationalTimeName: '08:00 - 11:00',
    },
    // dr. Yosef Purwoko: PENYAKIT DALAM with PL-04 (Geriatri) + PL-14 (Penyakit Dalam) + PL-43 (Elisabeth Penyakit Dalam)
    {
        ParamedicCode: '0003010700',
        ParamedicName: 'dr. Yosef Purwoko, Sp.PD, KGER',
        SpecialityCode: 'KSM-28',
        SpecialtyName: 'PENYAKIT DALAM',
        ServiceUnitCode: 'PL-04',
        ServiceUnitName: 'KLINIK LANJUT USIA (GERIATRI)',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 10:00',
    },
    {
        ParamedicCode: '0003010700',
        ParamedicName: 'dr. Yosef Purwoko, Sp.PD, KGER',
        SpecialityCode: 'KSM-28',
        SpecialtyName: 'PENYAKIT DALAM',
        ServiceUnitCode: 'PL-14',
        ServiceUnitName: 'KLINIK PENYAKIT DALAM',
        DayNumber: 2,
        OperationalTimeName: '08:00 - 12:00',
    },
    {
        ParamedicCode: '0003010700',
        ParamedicName: 'dr. Yosef Purwoko, Sp.PD, KGER',
        SpecialityCode: 'KSM-28',
        SpecialtyName: 'PENYAKIT DALAM',
        ServiceUnitCode: 'PL-43',
        ServiceUnitName: 'KLINIK ELISABETH PENYAKIT DALAM',
        DayNumber: 5,
        OperationalTimeName: '10:00 - 12:00',
    },
    // dr. B. Kris Pranarka: PENYAKIT DALAM specialty, ONLY practices in PL-04 (Geriatri) + PL-22 (Telekonsultasi)
    {
        ParamedicCode: '4124010112',
        ParamedicName: 'dr. B. Kris Pranarka, Sp.PD, KGer',
        SpecialityCode: 'KSM-28',
        SpecialtyName: 'PENYAKIT DALAM',
        ServiceUnitCode: 'PL-04',
        ServiceUnitName: 'KLINIK LANJUT USIA (GERIATRI)',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 10:00',
    },
    {
        ParamedicCode: '4124010112',
        ParamedicName: 'dr. B. Kris Pranarka, Sp.PD, KGer',
        SpecialityCode: 'KSM-28',
        SpecialtyName: 'PENYAKIT DALAM',
        ServiceUnitCode: 'PL-22',
        ServiceUnitName: 'KLINIK TELEKONSULTASI',
        DayNumber: 3,
        OperationalTimeName: '10:00 - 11:00',
    },
    // dr. Elisabeth S. Indrayanti: doctor whose first name is "Elisabeth", only practices in PL-09
    {
        ParamedicCode: '0065280209',
        ParamedicName: 'dr. Elisabeth S. Indrayanti, Sp.KK(K)',
        SpecialityCode: 'KSM-20',
        SpecialtyName: 'KESEHATAN KULIT & KELAMIN',
        ServiceUnitCode: 'PL-09',
        ServiceUnitName: 'KLINIK KULIT DAN KELAMIN',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 12:00',
    },
    // DD00004: DOKTER KLINIK (specialty UMUM) practicing in PL-21 (KLINIK RAFAEL) and PL-27 (KLINIK UMUM, inactive in regonline)
    {
        ParamedicCode: 'DD00004',
        ParamedicName: 'DOKTER KLINIK',
        SpecialityCode: 'KSM-36',
        SpecialtyName: 'UMUM',
        ServiceUnitCode: 'PL-21',
        ServiceUnitName: 'KLINIK RAFAEL',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 14:00',
    },
    {
        ParamedicCode: 'DD00004',
        ParamedicName: 'DOKTER KLINIK',
        SpecialityCode: 'KSM-36',
        SpecialtyName: 'UMUM',
        ServiceUnitCode: 'PL-27',
        ServiceUnitName: 'KLINIK UMUM',
        DayNumber: 2,
        OperationalTimeName: '08:00 - 20:00',
    },
    // drg. Budi: PL-05 KLINIK GIGI DAN MULUT
    {
        ParamedicCode: '9999000001',
        ParamedicName: 'drg. Budi Santoso',
        SpecialityCode: 'KSM-14',
        SpecialtyName: 'KEDOKTERAN GIGI DAN MULUT',
        ServiceUnitCode: 'PL-05',
        ServiceUnitName: 'KLINIK GIGI DAN MULUT',
        DayNumber: 1,
        OperationalTimeName: '09:00 - 12:00',
    },
];

function setupDoctors() {
    resetReferenceCaches();
    const doctors = preprocessingApiData(SAMPLE_RAW_SCHEDULES, null, ACTIVE_POLI_CODES);
    setDoctorContext(doctors);
    return doctors;
}

function getAllVisibleUnitCodes(dokter) {
    const codes = [];
    for (const day of state.days) {
        for (const item of dokter.schedule[day] || []) {
            codes.push(item.serviceUnitCode);
        }
    }
    return [...new Set(codes)].sort();
}

function getActiveDays(dokter) {
    return state.days.filter((day) => (dokter.schedule[day] || []).length > 0);
}

const FILTER_OPTS = {
    specialtyMap: SAMPLE_SPECIALTY_MAP,
    poliReferences: SAMPLE_POLI_REFERENCES,
};

test('formatPoliName preserves medical acronyms, parentheses, and Indonesian conjunctions', () => {
    assert.equal(formatPoliName('KLINIK THT-KL'), 'Klinik THT-KL');
    assert.equal(formatPoliName('KLINIK LANJUT USIA (GERIATRI)'), 'Klinik Lanjut Usia (Geriatri)');
    assert.equal(formatPoliName('KLINIK MEDICAL CHECK UP (MCU)'), 'Klinik Medical Check Up (MCU)');
    assert.equal(formatPoliName('KLINIK GIGI DAN MULUT'), 'Klinik Gigi dan Mulut');
    assert.equal(formatPoliName('UNIT ESWL'), 'Unit ESWL');
});

test('getAvailablePolis includes both active and inactive ref_poli_online entries with schedules, excludes empty units, and disambiguates duplicates', () => {
    const doctors = setupDoctors();
    const polis = getAvailablePolis(doctors, SAMPLE_POLI_REFERENCES);
    const byCode = new Map(polis.map((p) => [p.code, p]));

    // Active and inactive units that have schedules are included
    assert.ok(byCode.has('PL-31'));
    assert.ok(byCode.has('PL-27'));
    assert.equal(byCode.get('PL-27').isActive, false);
    assert.ok(byCode.has('PM-04'));

    // Units with 0 doctors on page are excluded
    assert.equal(byCode.has('PM-09'), false);
    assert.equal(byCode.has('PM-03'), false);
    assert.equal(byCode.has('PL-18'), false);

    // Duplicate names PL-11 & PM-16 are disambiguated
    assert.equal(byCode.get('PL-11').name, 'Klinik Nyeri (PL-11)');
    assert.equal(byCode.get('PM-16').name, 'Klinik Nyeri (PM-16)');

    // Fail-safe fallback when poli reference list is null
    const fallbackPolis = getAvailablePolis(doctors, null);
    assert.ok(fallbackPolis.some((p) => p.code === 'PL-31'));
    assert.ok(fallbackPolis.some((p) => p.code === 'PL-27'));
});

test('Search Box poli queries filter doctors and narrow schedule chips to matching polis only', () => {
    setupDoctors();

    // 1. Exact poli name: Klinik Elisabeth Bedah
    const res1 = filterDoctors('Klinik Elisabeth Bedah', [], [], FILTER_OPTS);
    assert.deepEqual(res1.map((d) => d.paramedicCode).sort(), ['0033011295', '5604261225']);
    res1.forEach((doc) => {
        assert.deepEqual(getAllVisibleUnitCodes(doc), ['PL-31']);
    });
    const edmond1 = res1.find((d) => d.paramedicCode === '5604261225');
    assert.deepEqual(getActiveDays(edmond1), [4]);

    // 2. Partial poli name: Elisabeth Bedah
    const res2 = filterDoctors('Elisabeth Bedah', [], [], FILTER_OPTS);
    assert.deepEqual(res2.map((d) => d.paramedicCode).sort(), ['0033011295', '5604261225']);
    res2.forEach((doc) => assert.deepEqual(getAllVisibleUnitCodes(doc), ['PL-31']));

    // 3. Rehabilitasi Medik hides Klinik Nyeri (PM-16) on dr. Lindawati
    const res3 = filterDoctors('Rehabilitasi Medik', [], [], FILTER_OPTS);
    assert.deepEqual(res3.map((d) => d.paramedicCode), ['4731011215']);
    assert.deepEqual(getAllVisibleUnitCodes(res3[0]), ['PL-26', 'PL-52', 'PM-04']);
    assert.deepEqual(getActiveDays(res3[0]), [1, 2, 3]);

    // 4. Klinik Rehabilitasi Medik also excludes Instalasi Rehabilitasi Medik (PM-04)
    const res4 = filterDoctors('Klinik Rehabilitasi Medik', [], [], FILTER_OPTS);
    assert.deepEqual(getAllVisibleUnitCodes(res4[0]), ['PL-26', 'PL-52']);

    // 5. Telekonsultasi shows only PL-22 chips
    const res5 = filterDoctors('Telekonsultasi', [], [], FILTER_OPTS);
    assert.deepEqual(res5.map((d) => d.paramedicCode).sort(), ['4124010112', '5604261225']);
    res5.forEach((doc) => assert.deepEqual(getAllVisibleUnitCodes(doc), ['PL-22']));

    // 6. Indonesian synonym "Poli Gigi" matches KLINIK GIGI DAN MULUT
    const res6 = filterDoctors('Poli Gigi', [], [], FILTER_OPTS);
    assert.deepEqual(res6.map((d) => d.paramedicCode), ['9999000001']);
    assert.deepEqual(getAllVisibleUnitCodes(res6[0]), ['PL-05']);

    // 7. Klinik Penyakit Dalam matches dr. Yosef (only PL-14, PL-43) and excludes dr. Kris Pranarka
    const res7 = filterDoctors('Klinik Penyakit Dalam', [], [], FILTER_OPTS);
    assert.deepEqual(res7.map((d) => d.paramedicCode), ['0003010700']);
    assert.deepEqual(getAllVisibleUnitCodes(res7[0]), ['PL-14', 'PL-43']);

    // 8. Klinik Umum on DOKTER KLINIK (specialty UMUM) shows only PL-27 and hides PL-21
    const res8 = filterDoctors('Klinik Umum', [], [], FILTER_OPTS);
    assert.deepEqual(res8.map((d) => d.paramedicCode), ['DD00004']);
    assert.deepEqual(getAllVisibleUnitCodes(res8[0]), ['PL-27']);
});

test('Search Box doctor-only and specialty-only queries keep all schedule chips intact', () => {
    setupDoctors();

    // Doctor name only
    const resDoc = filterDoctors('Prabani', [], [], FILTER_OPTS);
    assert.equal(resDoc.length, 1);
    assert.deepEqual(getAllVisibleUnitCodes(resDoc[0]), ['PL-02', 'PL-31']);

    // Specialty name only: Bedah Onkologi keeps PL-02, PL-22, PL-31
    const resSpec1 = filterDoctors('Bedah Onkologi', [], [], FILTER_OPTS);
    assert.equal(resSpec1.length, 1);
    assert.deepEqual(getAllVisibleUnitCodes(resSpec1[0]), ['PL-02', 'PL-22', 'PL-31']);

    // Specialty name only: Penyakit Dalam keeps both Yosef and Kris Pranarka with all chips
    const resSpec2 = filterDoctors('Penyakit Dalam', [], [], FILTER_OPTS);
    assert.deepEqual(resSpec2.map((d) => d.paramedicCode).sort(), ['0003010700', '4124010112']);
    const yosef = resSpec2.find((d) => d.paramedicCode === '0003010700');
    assert.deepEqual(getAllVisibleUnitCodes(yosef), ['PL-04', 'PL-14', 'PL-43']);

    // Doctor whose first name is "Elisabeth" keeps PL-09 while poli matches keep only Elisabeth clinics
    const resEli = filterDoctors('Elisabeth', [], [], FILTER_OPTS);
    const drEli = resEli.find((d) => d.paramedicCode === '0065280209');
    const drPrabani = resEli.find((d) => d.paramedicCode === '0033011295');
    assert.ok(drEli);
    assert.deepEqual(getAllVisibleUnitCodes(drEli), ['PL-09']);
    assert.ok(drPrabani);
    assert.deepEqual(getAllVisibleUnitCodes(drPrabani), ['PL-31']);
});

test('Search Box combined doctor name + poli keyword filters to that doctor and poli chips only', () => {
    setupDoctors();

    const res = filterDoctors('Prabani Elisabeth', [], [], FILTER_OPTS);
    assert.equal(res.length, 1);
    assert.equal(res[0].paramedicCode, '0033011295');
    assert.deepEqual(getAllVisibleUnitCodes(res[0]), ['PL-31']);

    const resCombo = filterDoctors('Edmond Telekonsultasi', [], [], FILTER_OPTS);
    assert.equal(resCombo.length, 1);
    assert.deepEqual(getAllVisibleUnitCodes(resCombo[0]), ['PL-22']);
    assert.deepEqual(getActiveDays(resCombo[0]), [3]);

    // Specialty + unrelated poli combo ("Bedah Telekonsultasi")
    const resSpecPoli = filterDoctors('Bedah Telekonsultasi', [], [], FILTER_OPTS);
    assert.deepEqual(resSpecPoli.map((d) => d.paramedicCode), ['5604261225']);
    assert.deepEqual(getAllVisibleUnitCodes(resSpecPoli[0]), ['PL-22']);
});

test('Checkbox Poliklinik AND/OR combinations and cross-faceted counts are accurate', () => {
    setupDoctors();

    // OR within Poliklinik category
    const orPoli = filterDoctors('', [], ['PL-31', 'PL-22'], FILTER_OPTS);
    assert.deepEqual(orPoli.map((d) => d.paramedicCode).sort(), ['0033011295', '4124010112', '5604261225']);
    const edmond = orPoli.find((d) => d.paramedicCode === '5604261225');
    assert.deepEqual(getAllVisibleUnitCodes(edmond), ['PL-22', 'PL-31']);

    // AND across Specialty and Poliklinik
    const andSpecPoli = filterDoctors('', ['KSM-04'], ['PL-31'], FILTER_OPTS);
    assert.deepEqual(andSpecPoli.map((d) => d.paramedicCode), ['0033011295']);
    assert.deepEqual(getAllVisibleUnitCodes(andSpecPoli[0]), ['PL-31']);

    // Conflicting Poliklinik checkbox + Search Box poli yields 0 doctors (no empty schedule cards)
    const conflict = filterDoctors('Telekonsultasi', [], ['PL-31'], FILTER_OPTS);
    assert.equal(conflict.length, 0);

    // Duplicate name disambiguation by code: selecting only PL-11 hides PM-16
    const onlyPl11 = filterDoctors('', [], ['PL-11'], FILTER_OPTS);
    assert.deepEqual(onlyPl11.map((d) => d.paramedicCode), ['0194010321']);
    assert.deepEqual(getAllVisibleUnitCodes(onlyPl11[0]), ['PL-11']);
    assert.deepEqual(getActiveDays(onlyPl11[0]), [1]);

    // Cross-faceted specialty counts given selected poli PL-31
    const specCounts = getSpecialtyCounts('', ['PL-31'], FILTER_OPTS);
    assert.equal(specCounts.get('KSM-04'), 1);
    assert.equal(specCounts.get('KSM-08'), 1);
    assert.equal(specCounts.get('KSM-28') || 0, 0);

    // Cross-faceted poli counts given keyword "Elisabeth Bedah": PL-31 = 2, PL-02 = 0 (no phantom counts)
    const poliCountsKeyword = getPoliCounts('Elisabeth Bedah', [], FILTER_OPTS);
    assert.equal(poliCountsKeyword.get('PL-31'), 2);
    assert.equal(poliCountsKeyword.get('PL-02') || 0, 0);

    // Cross-faceted poli counts given specialty KSM-12 (dr. Lindawati)
    const poliCountsSpec = getPoliCounts('', ['KSM-12'], FILTER_OPTS);
    assert.equal(poliCountsSpec.get('PM-04'), 1);
    assert.equal(poliCountsSpec.get('PL-26'), 1);
    assert.equal(poliCountsSpec.get('PL-52'), 1);
    assert.equal(poliCountsSpec.get('PM-16'), 1);
    assert.equal(poliCountsSpec.get('PL-31') || 0, 0);
});

test('Filtered schedule chips preserve bookable flags in both doctor card grid and detail modal without mutating fullDoctorList', () => {
    setupDoctors();

    const modalCardsEl = { innerHTML: '' };
    const fotoEl = { innerHTML: '' };
    const namaEl = { textContent: '' };
    const specBadgeEl = { textContent: '' };
    globalThis.document = {
        documentElement: { lang: 'id' },
        getElementById: (id) => ({
            'modal-schedule-cards': modalCardsEl,
            'foto-dokter': fotoEl,
            namaDokter: namaEl,
        }[id] ?? null),
        querySelector: (selector) => (selector === '#detailDokter .speciality-title' ? specBadgeEl : null),
        querySelectorAll: () => [],
    };

    try {
        // Filter dr. Prabani to PL-02 only; Monday PL-02 Sesi 1 must stay non-bookable because PL-31 exists on Monday
        const filtered = filterDoctors('Prabani', [], ['PL-02'], FILTER_OPTS);
        assert.equal(filtered.length, 1);
        const prabani = filtered[0];

        assert.deepEqual(getActiveDays(prabani), [1]);
        assert.equal(prabani.schedule[1].length, 1);
        assert.equal(prabani.schedule[1][0].sessions[0].bookable, false);
        assert.equal(prabani.schedule[1][0].sessions[1].bookable, true);

        // Card schedule grid renders static div for Sesi 1 and interactive button for Sesi 2, and omits PL-31
        const { html: cardHtml } = createScheduleGrid(prabani);
        assert.match(cardHtml, /<div class="jadwal-slot">[\s\S]*08:00 - 10:00[\s\S]*KLINIK BEDAH/);
        assert.match(cardHtml, /<button type="button"[\s\S]*jadwal-slot-interactive[\s\S]*data-booking-poli="PL-02"[\s\S]*data-booking-sesi="2"/);
        assert.doesNotMatch(cardHtml, /KLINIK ELISABETH BEDAH/);
        assert.doesNotMatch(cardHtml, /Selasa/);

        // Modal schedule tab renders matching filtered chips with same bookable behavior
        renderModal(prabani);
        assert.match(modalCardsEl.innerHTML, /<div class="modal-time-slot">[\s\S]*08:00 - 10:00[\s\S]*KLINIK BEDAH/);
        assert.match(modalCardsEl.innerHTML, /<button type="button"[\s\S]*modal-time-slot jadwal-slot-interactive[\s\S]*data-booking-poli="PL-02"[\s\S]*data-booking-sesi="2"/);
        assert.doesNotMatch(modalCardsEl.innerHTML, /KLINIK ELISABETH BEDAH/);
        assert.doesNotMatch(modalCardsEl.innerHTML, /Selasa/);

        // Clearing filters restores all original chips (fullDoctorList never mutated)
        const restored = filterDoctors('Prabani', [], [], FILTER_OPTS);
        assert.deepEqual(getAllVisibleUnitCodes(restored[0]), ['PL-02', 'PL-31']);
        assert.deepEqual(getActiveDays(restored[0]), [1, 2]);

        // I18N check for ID and EN keys
        assert.equal(t('removePoliFilter'), 'Hapus filter poliklinik');
        globalThis.document.documentElement.lang = 'en';
        assert.equal(t('removePoliFilter'), 'Remove clinic filter');
        assert.equal(t('removeSpecialtyFilter'), 'Remove specialty filter');
        assert.equal(t('poliNotFound'), 'No clinic found.');
    } finally {
        delete globalThis.document;
    }
});

test('buildFilteredSchedule handles null or empty schedules safely', () => {
    assert.equal(buildFilteredSchedule(null, new Set(['PL-01'])), null);
    const empty = buildFilteredSchedule({ 1: [], 2: [], 3: [], 4: [], 5: [], 6: [], 7: [] }, new Set(['PL-01']));
    assert.deepEqual(empty[1], []);
});

const EDGE_POLI_REFERENCES = [
    ...SAMPLE_POLI_REFERENCES,
    { code: 'PL-01', name: 'KLINIK ANAK', is_active: true },
    { code: 'PL-06', name: 'KLINIK GIZI', is_active: true },
    { code: 'PL-07', name: 'KLINIK JANTUNG DAN PEMBULUH DARAH', is_active: true },
    { code: 'PL-17', name: 'KLINIK SARAF', is_active: true },
    { code: 'PL-25', name: 'HOME CARE RAWAT JALAN', is_active: false },
];

const EDGE_SPECIALTY_MAP = new Map([
    ...SAMPLE_SPECIALTY_MAP,
    ['KSM-03', 'BEDAH ANAK'],
    ['KSM-06', 'BEDAH SARAF'],
    ['KSM-09', 'BEDAH UMUM'],
    ['KSM-11', 'GIZI KLINIK'],
]);

const EDGE_RAW_SCHEDULES = [
    ...SAMPLE_RAW_SCHEDULES,
    // dr. ME Wati Windayani: specialty GIZI KLINIK, practices at PL-06 (KLINIK GIZI) + PL-22 (KLINIK TELEKONSULTASI)
    {
        ParamedicCode: '2844020104',
        ParamedicName: 'dr. ME Wati Windayani, Sp.GK',
        SpecialityCode: 'KSM-11',
        SpecialtyName: 'GIZI KLINIK',
        ServiceUnitCode: 'PL-06',
        ServiceUnitName: 'KLINIK GIZI',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 11:00',
    },
    {
        ParamedicCode: '2844020104',
        ParamedicName: 'dr. ME Wati Windayani, Sp.GK',
        SpecialityCode: 'KSM-11',
        SpecialtyName: 'GIZI KLINIK',
        ServiceUnitCode: 'PL-22',
        ServiceUnitName: 'KLINIK TELEKONSULTASI',
        DayNumber: 3,
        OperationalTimeName: '13:00 - 14:00',
    },
    // dr. Ari Setiawan: BEDAH UMUM, practices only at PL-02 & PL-31 (NOT Klinik Umum)
    {
        ParamedicCode: '0091000001',
        ParamedicName: 'dr. Ari Setiawan, Sp.B',
        SpecialityCode: 'KSM-09',
        SpecialtyName: 'BEDAH UMUM',
        ServiceUnitCode: 'PL-02',
        ServiceUnitName: 'KLINIK BEDAH',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 12:00',
    },
    {
        ParamedicCode: '0091000001',
        ParamedicName: 'dr. Ari Setiawan, Sp.B',
        SpecialityCode: 'KSM-09',
        SpecialtyName: 'BEDAH UMUM',
        ServiceUnitCode: 'PL-31',
        ServiceUnitName: 'KLINIK ELISABETH BEDAH',
        DayNumber: 2,
        OperationalTimeName: '09:00 - 11:00',
    },
    // dr. Christian Beta: BEDAH SARAF, practices only at PL-02 (NOT Klinik Saraf)
    {
        ParamedicCode: '0091000002',
        ParamedicName: 'dr. Christian Beta K, Sp.BS',
        SpecialityCode: 'KSM-06',
        SpecialtyName: 'BEDAH SARAF',
        ServiceUnitCode: 'PL-02',
        ServiceUnitName: 'KLINIK BEDAH',
        DayNumber: 1,
        OperationalTimeName: '08:00 - 10:00',
    },
    // dr. Elizabeth Margareth: BEDAH ANAK, practices only at PL-02 (NOT Klinik Anak)
    {
        ParamedicCode: '0091000003',
        ParamedicName: 'dr. Elizabeth Margareth, Sp.BA',
        SpecialityCode: 'KSM-03',
        SpecialtyName: 'BEDAH ANAK',
        ServiceUnitCode: 'PL-02',
        ServiceUnitName: 'KLINIK BEDAH',
        DayNumber: 2,
        OperationalTimeName: '10:00 - 12:00',
    },
    // dr. Dewi Kusuma Wati: MCU (MEDICAL CHECKUP) + PL-99 (KLINIK MEDICAL CHECK UP (MCU)) + PL-27 (KLINIK UMUM)
    {
        ParamedicCode: '4518010514',
        ParamedicName: 'dr. Dewi Kusuma Wati',
        SpecialityCode: 'KSM-36',
        SpecialtyName: 'UMUM',
        ServiceUnitCode: 'MCU',
        ServiceUnitName: 'MEDICAL CHECKUP',
        DayNumber: 1,
        OperationalTimeName: '07:00 - 12:00',
    },
    {
        ParamedicCode: '4518010514',
        ParamedicName: 'dr. Dewi Kusuma Wati',
        SpecialityCode: 'KSM-36',
        SpecialtyName: 'UMUM',
        ServiceUnitCode: 'PL-99',
        ServiceUnitName: 'KLINIK MEDICAL CHECK UP (MCU)',
        DayNumber: 2,
        OperationalTimeName: '08:00 - 12:00',
    },
    {
        ParamedicCode: '4518010514',
        ParamedicName: 'dr. Dewi Kusuma Wati',
        SpecialityCode: 'KSM-36',
        SpecialtyName: 'UMUM',
        ServiceUnitCode: 'PL-27',
        ServiceUnitName: 'KLINIK UMUM',
        DayNumber: 3,
        OperationalTimeName: '08:00 - 14:00',
    },
    // dr. Venny Christinna A: MCU (MEDICAL CHECKUP) + PL-27 (KLINIK UMUM)
    {
        ParamedicCode: '4883010217',
        ParamedicName: 'dr. Venny Christinna A',
        SpecialityCode: 'KSM-36',
        SpecialtyName: 'UMUM',
        ServiceUnitCode: 'MCU',
        ServiceUnitName: 'MEDICAL CHECKUP',
        DayNumber: 4,
        OperationalTimeName: '07:00 - 12:00',
    },
    {
        ParamedicCode: '4883010217',
        ParamedicName: 'dr. Venny Christinna A',
        SpecialityCode: 'KSM-36',
        SpecialtyName: 'UMUM',
        ServiceUnitCode: 'PL-27',
        ServiceUnitName: 'KLINIK UMUM',
        DayNumber: 5,
        OperationalTimeName: '08:00 - 14:00',
    },
];

const EDGE_OPTS = {
    specialtyMap: EDGE_SPECIALTY_MAP,
    poliReferences: EDGE_POLI_REFERENCES,
};

function setupEdgeDoctors() {
    resetReferenceCaches();
    const activeCodes = EDGE_POLI_REFERENCES.filter((p) => p.is_active).map((p) => p.code);
    const doctors = preprocessingApiData(EDGE_RAW_SCHEDULES, null, activeCodes);
    setDoctorContext(doctors);
    return doctors;
}

test('Explicit poli phrase "Klinik Gizi" filters chips unlike reversed specialty "Gizi Klinik"', () => {
    setupEdgeDoctors();

    const klinikGizi = filterDoctors('Klinik Gizi', [], [], EDGE_OPTS);
    assert.deepEqual(klinikGizi.map((d) => d.paramedicCode), ['2844020104']);
    assert.deepEqual(getAllVisibleUnitCodes(klinikGizi[0]), ['PL-06']);

    const watiKlinikGizi = filterDoctors('Wati Klinik Gizi', [], [], EDGE_OPTS);
    assert.deepEqual(watiKlinikGizi.map((d) => d.paramedicCode), ['2844020104']);
    assert.deepEqual(getAllVisibleUnitCodes(watiKlinikGizi[0]), ['PL-06']);

    // Conflicting poli checkbox PL-22 with "Klinik Gizi" returns 0 doctors and no phantom poli count
    assert.equal(filterDoctors('Klinik Gizi', [], ['PL-22'], EDGE_OPTS).length, 0);
    const counts = getPoliCounts('Klinik Gizi', [], EDGE_OPTS);
    assert.equal(counts.get('PL-06'), 1);
    assert.equal(counts.get('PL-22') || 0, 0);

    // Specialty name order "Gizi Klinik" and "Gizi Klinik Wati" keep all chips intact
    const giziKlinik = filterDoctors('Gizi Klinik', [], [], EDGE_OPTS);
    assert.deepEqual(giziKlinik.map((d) => d.paramedicCode), ['2844020104']);
    assert.deepEqual(getAllVisibleUnitCodes(giziKlinik[0]), ['PL-06', 'PL-22']);

    const giziKlinikWati = filterDoctors('Gizi Klinik Wati', [], [], EDGE_OPTS);
    assert.deepEqual(giziKlinikWati.map((d) => d.paramedicCode), ['2844020104']);
    assert.deepEqual(getAllVisibleUnitCodes(giziKlinikWati[0]), ['PL-06', 'PL-22']);
});

test('Overlapping specialty/doctor words with mismatched explicit poli phrases do not false-match generic "Klinik"', () => {
    setupEdgeDoctors();

    assert.equal(filterDoctors('Bedah Umum Klinik Umum', [], [], EDGE_OPTS).length, 0);
    assert.equal(filterDoctors('Bedah Saraf Klinik Saraf', [], [], EDGE_OPTS).length, 0);
    assert.equal(filterDoctors('Bedah Anak Klinik Anak', [], [], EDGE_OPTS).length, 0);
    assert.equal(filterDoctors('Bedah Klinik Umum', [], [], EDGE_OPTS).length, 0);
    assert.equal(filterDoctors('Klinik Umum Bedah', [], [], EDGE_OPTS).length, 0);
    assert.equal(filterDoctors('dr. Elisabeth Klinik Elisabeth', [], [], EDGE_OPTS).length, 0);

    // Valid reversed order ("Klinik Bedah Prabani") still matches and filters properly
    const reversed = filterDoctors('Klinik Bedah Prabani', [], [], EDGE_OPTS);
    assert.deepEqual(reversed.map((d) => d.paramedicCode), ['0033011295']);
    assert.deepEqual(getAllVisibleUnitCodes(reversed[0]), ['PL-02', 'PL-31']);
});

test('Conjunction "&", compound words ("Medical Checkup"), and exact unit codes ("MCU", "PL-31") match accurately', () => {
    setupEdgeDoctors();

    // "&" matches "DAN"
    const gigiAmp = filterDoctors('Klinik Gigi & Mulut', [], [], EDGE_OPTS);
    assert.deepEqual(gigiAmp.map((d) => d.paramedicCode), ['9999000001']);
    assert.deepEqual(getAllVisibleUnitCodes(gigiAmp[0]), ['PL-05']);

    // "MCU" matches both MCU (MEDICAL CHECKUP) and PL-99 (KLINIK MEDICAL CHECK UP (MCU)), hiding PL-27
    const mcu = filterDoctors('MCU', [], [], EDGE_OPTS);
    assert.deepEqual(mcu.map((d) => d.paramedicCode).sort(), ['4518010514', '4883010217']);
    const dewiMcu = mcu.find((d) => d.paramedicCode === '4518010514');
    const vennyMcu = mcu.find((d) => d.paramedicCode === '4883010217');
    assert.deepEqual(getAllVisibleUnitCodes(dewiMcu), ['MCU', 'PL-99']);
    assert.deepEqual(getAllVisibleUnitCodes(vennyMcu), ['MCU']);

    // "Medical Checkup" (no space) matches both MCU and PL-99 ("CHECK UP")
    const medCheckup = filterDoctors('Medical Checkup', [], [], EDGE_OPTS);
    assert.deepEqual(medCheckup.map((d) => d.paramedicCode).sort(), ['4518010514', '4883010217']);
    const dewiCheckup = medCheckup.find((d) => d.paramedicCode === '4518010514');
    assert.deepEqual(getAllVisibleUnitCodes(dewiCheckup), ['MCU', 'PL-99']);

    // Exact poli code matches only that poli without matching partial prefixes like "pl"
    const pl31 = filterDoctors('PL-31', [], [], EDGE_OPTS);
    assert.ok(pl31.length > 0);
    pl31.forEach((doc) => assert.deepEqual(getAllVisibleUnitCodes(doc), ['PL-31']));

    // Mini-search helper supports "&", compound words, and exact codes
    assert.equal(matchesPoliOptionQuery('gigi & mulut', 'Klinik Gigi dan Mulut', 'KLINIK GIGI DAN MULUT', 'PL-05'), true);
    assert.equal(matchesPoliOptionQuery('medical checkup', 'Klinik Medical Check Up (MCU)', 'KLINIK MEDICAL CHECK UP (MCU)', 'PL-99'), true);
    assert.equal(matchesPoliOptionQuery('homecare', 'Home Care Rawat Jalan', 'HOME CARE RAWAT JALAN', 'PL-25'), true);
    assert.equal(matchesPoliOptionQuery('PL-31', 'Klinik Elisabeth Bedah', 'KLINIK ELISABETH BEDAH', 'PL-31'), true);
    assert.equal(matchesPoliOptionQuery('ikan', 'Klinik Anak', 'KLINIK ANAK', 'PL-01'), false);
});
