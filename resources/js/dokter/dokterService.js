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

export function filterDoctors(keyword, type = "nama") {
    if (keyword === "") {
        state.filteredDoctorList = state.fullDoctorList;
    } else {
        const lowerKeyword = keyword.toLowerCase();
        state.filteredDoctorList = state.fullDoctorList.filter((dokter) => {
            if (type === "klinik") {
                // const specName = dokter.specialtyName?.toLowerCase() || "";
                const unitName = dokter.serviceUnitName?.toLowerCase() || "";
                // return specName.includes(lowerKeyword) || unitName.includes(lowerKeyword);
                return unitName.includes(lowerKeyword);
            } else {
                const nama = dokter.paramedicName?.toLowerCase() || "";
                return nama.includes(lowerKeyword);
            }
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
