export const state = {
    itemsPerPage: 8,
    fullDoctorList: [],
    filteredDoctorList: [],
    currentPage: 1,
    days: [1, 2, 3, 4, 5, 6, 7],
};

export function preprocessingApiData(doctorData, knownSpecialtyCode = null, activePoliCodesOverride = undefined) {
    const doctorMap = new Map();

    if (!Array.isArray(doctorData)) return [];

    doctorData.forEach((item) => {
        if (!item || !item.ParamedicCode || !Number.isInteger(Number(item.DayNumber)) || Number(item.DayNumber) < 1 || Number(item.DayNumber) > 7) return;
        if (!doctorMap.has(item.ParamedicCode)) {
            const schedule = {};
            state.days.forEach((day) => {
                schedule[day] = [];
            });

            doctorMap.set(item.ParamedicCode, {
                entry_id: `${item.ServiceUnitCode}0823${item.ParamedicCode}9373`,
                paramedicCode: String(item.ParamedicCode),
                paramedicName: item.ParamedicName,
                specialtyCode: item.SpecialityCode || knownSpecialtyCode,
                specialtyName: item.SpecialtyName || "",
                serviceUnitName: item.ServiceUnitName || "",
                schedule,
            });
        }

        const doc = doctorMap.get(item.ParamedicCode);

        let sessions = [];
        if (Array.isArray(item.ListTimeSchedule) && item.ListTimeSchedule.length > 0) {
            sessions = item.ListTimeSchedule.map((ts, idx) => ({
                jam: `${ts.StartTime} - ${ts.EndTime}`,
                sesi: ts.Session ?? (idx + 1),
            }));
        } else if (typeof item.OperationalTimeName === 'string' && item.OperationalTimeName.trim() !== '') {
            sessions = item.OperationalTimeName.split("|").map((jamStr, idx) => ({
                jam: jamStr.trim(),
                sesi: idx + 1,
            }));
        }

        doc.schedule[item.DayNumber].push({
            jam: sessions.map((s) => s.jam),
            sessions,
            serviceUnitCode: item.ServiceUnitCode,
            serviceUnitName: item.ServiceUnitName,
        });
    });

    const activePoliSet = resolveActivePoliSet(activePoliCodesOverride);
    const doctors = [...doctorMap.values()];
    doctors.forEach((doc) => markRegOnlineBookableSlots(doc, activePoliSet));
    return doctors;
}

let activePoliCacheSet;

function getActivePoliCodeSet() {
    if (activePoliCacheSet !== undefined) return activePoliCacheSet;
    if (typeof document === "undefined") {
        activePoliCacheSet = null;
        return activePoliCacheSet;
    }

    try {
        const el = document.getElementById("regonline-active-poli-json");
        if (!el) {
            activePoliCacheSet = null;
            return activePoliCacheSet;
        }
        const parsed = JSON.parse(el.textContent || "null");
        if (Array.isArray(parsed)) {
            activePoliCacheSet = new Set(parsed.map((code) => String(code).trim()).filter(Boolean));
        } else {
            activePoliCacheSet = null;
        }
    } catch (e) {
        console.error("Error parsing active poli json:", e);
        activePoliCacheSet = null;
    }

    return activePoliCacheSet;
}

function resolveActivePoliSet(override) {
    if (override === undefined) return getActivePoliCodeSet();
    if (override === null) return null;
    if (override instanceof Set) return override;
    if (Array.isArray(override)) {
        return new Set(override.map((code) => String(code).trim()).filter(Boolean));
    }
    return null;
}

function getCommonClinicName(unitName = "") {
    return String(unitName).replace(/Klinik\s+(Elisabeth\s+)?/i, "").trim().toLowerCase();
}

function isElisabethClinic(unitName = "") {
    return String(unitName).toLowerCase().includes("elisabeth");
}

// Menentukan status bookable berdasarkan referensi aktif dbo.ref_poli_online dan aturan sesi Elisabeth vs Reguler
function markRegOnlineBookableSlots(doc, activePoliSet = null) {
    state.days.forEach((day) => {
        const dayItems = doc.schedule[day] || [];
        if (dayItems.length === 0) return;

        // Unit yang tidak aktif di ref_poli_online tidak ikut dihitung dalam rasio Elisabeth vs Reguler
        const clinicStats = new Map();
        dayItems.forEach((item) => {
            const unitCode = String(item.serviceUnitCode || "").trim();
            if (activePoliSet !== null && !activePoliSet.has(unitCode)) return;

            const key = getCommonClinicName(item.serviceUnitName);
            const stat = clinicStats.get(key) ?? { elisabeth: 0, biasa: 0 };
            if (isElisabethClinic(item.serviceUnitName)) {
                stat.elisabeth += item.sessions.length;
            } else {
                stat.biasa += item.sessions.length;
            }
            clinicStats.set(key, stat);
        });

        dayItems.forEach((item) => {
            const unitCode = String(item.serviceUnitCode || "").trim();
            const isPoliActive = activePoliSet === null || activePoliSet.has(unitCode);

            if (!isPoliActive) {
                item.sessions = item.sessions.map((slot) => ({ ...slot, bookable: false }));
                return;
            }

            const isElisabeth = isElisabethClinic(item.serviceUnitName);
            const stat = clinicStats.get(getCommonClinicName(item.serviceUnitName)) ?? { elisabeth: 0, biasa: 0 };
            const hasElisabeth = stat.elisabeth > 0;
            const hasMultipleRegularSessions = stat.biasa > 1;

            item.sessions = item.sessions.map((slot) => {
                if (!hasElisabeth || isElisabeth || !hasMultipleRegularSessions) {
                    return { ...slot, bookable: true };
                }

                // Ada Klinik Elisabeth & Klinik Reguler sesi > 1: Sesi 1 non-bookable, Sesi 2+ bookable
                return { ...slot, bookable: Number(slot.sesi) !== 1 };
            });
        });
    });
}

export function setDoctorContext(doctorList) {
    state.fullDoctorList = doctorList;
    state.filteredDoctorList = doctorList;
    state.currentPage = 1;
}

let specialtyCacheMap = null;

export function getSpecialtyMap() {
    if (!specialtyCacheMap) {
        specialtyCacheMap = new Map();
        try {
            const el = document.getElementById("specialties-json");
            if (el) {
                const data = JSON.parse(el.textContent || "[]");
                if (Array.isArray(data)) {
                    data.forEach((item) => {
                        const code = item.Code ?? item.code;
                        const name = item.Name ?? item.name;
                        if (code && name) {
                            specialtyCacheMap.set(String(code), String(name));
                        }
                    });
                }
            }
        } catch (e) {
            console.error("Error parsing specialties json:", e);
        }
    }
    return specialtyCacheMap;
}

function matchesKeywordTokens(dokter, tokens, specialtyMap) {
    if (tokens.length === 0) return true;

    const nama = (dokter.paramedicName || '').toLowerCase();
    const specCode = String(dokter.specialtyCode || '');
    const specNameDirect = (dokter.specialtyName || '').toLowerCase();
    const specNameMapped = (specialtyMap.get(specCode) || '').toLowerCase();
    const serviceUnitName = (dokter.serviceUnitName || '').toLowerCase();

    const scheduleUnits = [];
    if (dokter.schedule) {
        for (const day of state.days) {
            const slots = dokter.schedule[day] || [];
            for (const slot of slots) {
                if (slot.serviceUnitName) {
                    scheduleUnits.push(slot.serviceUnitName.toLowerCase());
                }
            }
        }
    }

    const combinedSearchText = [
        nama,
        specNameDirect,
        specNameMapped,
        serviceUnitName,
        ...scheduleUnits
    ].join(' ');

    return tokens.every((token) => combinedSearchText.includes(token));
}

export function getSpecialtyCounts(keyword = '') {
    const counts = new Map();
    const hasKeyword = typeof keyword === 'string' && keyword.trim() !== '';
    const tokens = hasKeyword
        ? keyword.trim().toLowerCase().split(/\s+/).filter((t) => t.length > 0)
        : [];
    const specialtyMap = getSpecialtyMap();

    for (const dokter of state.fullDoctorList) {
        if (!matchesKeywordTokens(dokter, tokens, specialtyMap)) continue;
        const code = String(dokter.specialtyCode || '');
        if (code) {
            counts.set(code, (counts.get(code) || 0) + 1);
        }
    }
    return counts;
}

export function filterDoctors(keyword = '', selectedSpecialtyCodes = []) {
    const hasKeyword = typeof keyword === 'string' && keyword.trim() !== '';
    const hasSpecialties = Array.isArray(selectedSpecialtyCodes) && selectedSpecialtyCodes.length > 0;

    if (!hasKeyword && !hasSpecialties) {
        state.filteredDoctorList = state.fullDoctorList;
    } else {
        const specialtyMap = getSpecialtyMap();
        const tokens = hasKeyword
            ? keyword.trim().toLowerCase().split(/\s+/).filter((t) => t.length > 0)
            : [];
        const specSet = hasSpecialties
            ? new Set(selectedSpecialtyCodes.map((c) => String(c)))
            : null;

        state.filteredDoctorList = state.fullDoctorList.filter((dokter) => {
            // 1. Filter spesialisasi (OR logic antar spesialisasi yang dipilih)
            if (specSet) {
                const docSpecCode = String(dokter.specialtyCode || '');
                if (!specSet.has(docSpecCode)) {
                    return false;
                }
            }

            // 2. Filter kata kunci teks (AND logic antar token kata)
            if (!matchesKeywordTokens(dokter, tokens, specialtyMap)) {
                return false;
            }

            return true;
        });
    }
    state.currentPage = 1;
}

export async function fetchAllDokter() {
    try {
        const csrfTokenElement = document.querySelector(
            'meta[name="csrf-token"]',
        );
        const csrfToken = csrfTokenElement
            ? csrfTokenElement.getAttribute("content")
            : "";

        const response = await fetch(
            `/dokter/all-dokter?_t=${new Date().getTime()}`,
            {
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                    "Cache-Control": "no-cache",
                },
            },
        );

        if (!response.ok)
            throw new Error(`${response.status}: Data tidak tersedia`);

        const data = await response.json();
        if (!data || data.length === 0) return false;

        const doctorList = preprocessingApiData(data);
        setDoctorContext(doctorList);
        return true;
    } catch (error) {
        console.error("Error fetching all doctors:", error);
        return false;
    }
}

export async function fetchDokterBySpecialtyCode(specialtyCode) {
    try {
        const response = await fetch(`/dokter/${encodeURIComponent(specialtyCode)}`);
        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const data = await response.json();
        if (data.Success === false) return false;
        const { ScheduleRoutine } = data;

        if (!ScheduleRoutine || ScheduleRoutine.length === 0) {
            setDoctorContext([]);
            return true;
        }

        const doctorList = preprocessingApiData(ScheduleRoutine, specialtyCode);
        setDoctorContext(doctorList);
        return true;
    } catch (error) {
        console.error("Error fetching specialty doctors:", error);
        return false;
    }
}

// Browser memanggil server Web; server Web yang menyimpan secret dan menghubungi CMS.
export async function fetchDoctorProfile(code, signal) {
    const locale = document.documentElement.lang?.toLowerCase().startsWith('en') ? 'en' : 'id';
    const response = await fetch(`/dokter/profil/${encodeURIComponent(code)}?lang=${locale}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept-Language': locale },
        cache: 'no-store',
        signal,
    });
    if (response.status === 404) return { status: 'not_found', profile: null };
    if (!response.ok) throw new Error('Profil tidak dapat dimuat');
    const payload = await response.json();
    if (!payload.success || payload.data?.paramedic_code !== String(code)) throw new Error('Profil tidak cocok');
    return { status: 'ready', profile: payload.data };
}
