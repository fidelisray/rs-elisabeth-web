// Belum dieksekusi. Memakai node:test bawaan, tanpa API atau dependensi jaringan.
import test from 'node:test';
import assert from 'node:assert/strict';
import { renderProfile } from '../../resources/js/dokter/dokterUI.js';
import { preprocessingApiData } from '../../resources/js/dokter/dokterService.js';

test('CMS text is assigned as text and cleared when a profile is unavailable', () => {
    const elements = Object.fromEntries(['doctor-profile-status', 'doctor-profile-bio', 'doctor-profile-location', 'doctor-profile-experience', 'doctor-profile-education', 'namaDokter']
        .map((key) => [key, { textContent: '', hidden: false }]));
    globalThis.document = { documentElement: { lang: 'en' }, getElementById: (key) => elements[key], querySelector: () => null };
    try {
        renderProfile('ready', { name: 'Doctor', bio: '<img src=x onerror=alert(1)>', education: null });
        assert.equal(elements['doctor-profile-bio'].textContent, '<img src=x onerror=alert(1)>');
        assert.equal(elements['doctor-profile-education'].textContent, 'Information has not been provided.');
        renderProfile('unavailable');
        assert.equal(elements['doctor-profile-bio'].textContent, 'Information has not been provided.');
        assert.match(elements['doctor-profile-status'].textContent, /could not be loaded/);
    } finally { delete globalThis.document; }
});

test('Malformed schedule rows do not crash preprocessing', () => {
    assert.deepEqual(preprocessingApiData(null), []);
    assert.deepEqual(preprocessingApiData([{ ParamedicCode: 'D1', DayNumber: 99 }]), []);
    const result = preprocessingApiData([{ ParamedicCode: 'D1', DayNumber: 1, ParamedicName: 'Doctor' }]);
    assert.equal(result[0].paramedicCode, 'D1');
    assert.deepEqual(result[0].schedule[1][0].jam, []);
});
