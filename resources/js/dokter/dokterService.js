export const state = {
    itemsPerPage: 8,
    fullDoctorList: [],
    filteredDoctorList: [],
    currentPage: 1,
    days: [1, 2, 3, 4, 5, 6, 7],
};

export function preprocessingApiData(doctorData, knownSpecialtyCode = null) {
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
        doc.schedule[item.DayNumber].push({
            jam: typeof item.OperationalTimeName === 'string' ? item.OperationalTimeName.split("|") : [],
            serviceUnitCode: item.ServiceUnitCode,
            serviceUnitName: item.ServiceUnitName,
        });
    });

    return [...doctorMap.values()];
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
