import { state, getSpecialtyMap } from "./dokterService.js";

export const DEFAULT_DOCTOR_PHOTO = new URL(
    "../../images/default.png",
    import.meta.url,
).href;

export function getLocale() {
    return document.documentElement.lang?.toLowerCase().startsWith("en")
        ? "en"
        : "id";
}

export const I18N = {
    id: {
        days: {
            1: "Senin",
            2: "Selasa",
            3: "Rabu",
            4: "Kamis",
            5: "Jumat",
            6: "Sabtu",
            7: "Minggu",
        },
        dayList: [
            "Senin",
            "Selasa",
            "Rabu",
            "Kamis",
            "Jumat",
            "Sabtu",
            "Minggu",
        ],
        generalSpecialist: "Spesialis Umum",
        scheduleNotAvailable: "Jadwal belum tersedia",
        doctorPhoto: "Foto Dokter",
        checkProfile: "Cek Profil",
        makeAppointment: "Buat Janji",
        doctorSchedule: "Jadwal Dokter",
        doctorNotFoundTitle: "Dokter Tidak Ditemukan",
        doctorNotFoundDesc:
            "Maaf, kami tidak dapat menemukan dokter atau klinik yang sesuai dengan kata kunci pencarian Anda. Silakan coba spesialisasi atau nama yang berbeda.",
        loadingDoctors: "Memuat data dokter...",
        modalScheduleNotAvailable:
            "Jadwal praktik belum tersedia untuk saat ini.",
        paginationInputTitle: "Ketik halaman lalu Enter",
        clinicResult: "Hasil Pencarian Klinik:",
        nameResult: "Hasil Pencarian Nama:",
        searchResult: "Hasil Pencarian:",
        specialty: "Spesialisasi:",
        selectSpecialty: "Pilih Spesialisasi",
        doctorsAvailable: "dokter tersedia",
        removeFilter: "Hapus filter",
        clearKeyword: "Hapus kata kunci",
        profileLoading: "Memuat profil dokter...",
        profileEmpty: "Informasi belum diisi.",
        profileNotFound: "Profil dokter belum tersedia.",
        profileUnavailable:
            "Profil belum dapat dimuat. Silakan coba lagi nanti.",
        bookSlot: "Booking",
        clickToBook: "Klik untuk booking",
        confirmBookingTitle: "Konfirmasi Janji Dokter",
        nextUpcomingDate: "Jadwal Terdekat",
        proceedToRegOnline: "Lanjut ke Pendaftaran",
        bookingClinic: "Poliklinik",
        bookingTime: "Jam Praktik",
        bookingSession: "Sesi",
        sessionPrefix: "Sesi",
        bookingDate: "Tanggal Periksa",
        bookingNotice:
            "Anda akan diarahkan ke portal Registrasi Online RS St. Elisabeth dengan rincian jadwal yang terisi otomatis.",
    },
    en: {
        days: {
            1: "Monday",
            2: "Tuesday",
            3: "Wednesday",
            4: "Thursday",
            5: "Friday",
            6: "Saturday",
            7: "Sunday",
        },
        dayList: [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday",
        ],
        generalSpecialist: "General Practitioner",
        scheduleNotAvailable: "Schedule not available",
        doctorPhoto: "Doctor's Photo",
        checkProfile: "View Profile",
        makeAppointment: "Book Appointment",
        doctorSchedule: "Doctor Schedule",
        doctorNotFoundTitle: "Doctor Not Found",
        doctorNotFoundDesc:
            "Sorry, we could not find any doctors or clinics matching your search keywords. Please try a different specialty or doctor name.",
        loadingDoctors: "Loading doctors data...",
        modalScheduleNotAvailable:
            "Practice schedule is not available at the moment.",
        paginationInputTitle: "Type page and press Enter",
        clinicResult: "Clinic Search Results:",
        nameResult: "Name Search Results:",
        searchResult: "Search Results:",
        specialty: "Specialty:",
        selectSpecialty: "Select Specialty",
        doctorsAvailable: "doctors available",
        removeFilter: "Remove filter",
        clearKeyword: "Clear keyword",
        profileLoading: "Loading doctor profile...",
        profileEmpty: "Information has not been provided.",
        profileNotFound: "Doctor profile is not available.",
        profileUnavailable:
            "The profile could not be loaded. Please try again later.",
        bookSlot: "Book",
        clickToBook: "Click to book",
        confirmBookingTitle: "Doctor Appointment Confirmation",
        nextUpcomingDate: "Next Available Date",
        proceedToRegOnline: "Proceed to Registration",
        bookingClinic: "Outpatient Clinic",
        bookingTime: "Practice Hours",
        bookingSession: "Session",
        sessionPrefix: "Session",
        bookingDate: "Examination Date",
        bookingNotice:
            "You will be redirected to the RS St. Elisabeth Online Registration portal with pre-filled appointment details.",
    },
};

export function t(key) {
    const locale = getLocale();
    return I18N[locale]?.[key] ?? I18N["id"][key];
}

export function escapeHtml(value) {
    return String(value ?? "").replace(
        /[&<>"']/g,
        (character) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;",
            })[character],
    );
}

export function calculateNextDate(targetDayNumber, timeSlotStr = "") {
    const now = new Date();
    // JavaScript getDay(): 0 is Sunday, 1 is Monday ... 6 is Saturday
    const currentDayNumber = now.getDay() === 0 ? 7 : now.getDay();

    let daysUntil = parseInt(targetDayNumber, 10) - currentDayNumber;
    if (daysUntil < 0) {
        daysUntil += 7;
    } else if (daysUntil === 0 && timeSlotStr) {
        // Cek jika jam praktik hari ini sudah lewat
        const parts = timeSlotStr.split("-");
        const endPart = (parts[1] || parts[0]).trim();
        const timeMatch = endPart.match(/(\d{1,2})[:.](\d{2})/);
        if (timeMatch) {
            const endHour = parseInt(timeMatch[1], 10);
            const endMin = parseInt(timeMatch[2], 10);
            const slotEndTime = new Date(now);
            slotEndTime.setHours(endHour, endMin, 0, 0);
            if (now > slotEndTime) {
                // Jam praktik hari ini sudah lewat, jadwalkan minggu depan
                daysUntil = 7;
            }
        }
    }

    const targetDate = new Date(now);
    targetDate.setDate(now.getDate() + daysUntil);

    const yyyy = targetDate.getFullYear();
    const mm = String(targetDate.getMonth() + 1).padStart(2, "0");
    const dd = String(targetDate.getDate()).padStart(2, "0");
    const dateIso = `${yyyy}${mm}${dd}`;

    const locale = getLocale();
    const formatted = new Intl.DateTimeFormat(
        locale === "en" ? "en-US" : "id-ID",
        {
            weekday: "long",
            day: "numeric",
            month: "long",
            year: "numeric",
        },
    ).format(targetDate);

    return {
        date: targetDate,
        dateIso,
        formatted,
        isToday: daysUntil === 0,
    };
}

export function buildRegOnlineUrl({
    tgl = "",
    kodepoli = "",
    kodedokter = "",
    sesi = "",
} = {}) {
    const url = new URL("http://172.17.12.17:669/");
    // const url = new URL("https://regonline.rs-elisabeth.com/");
    if (tgl) url.searchParams.set("tgl", tgl);
    if (kodepoli) url.searchParams.set("kodepoli", kodepoli);
    if (kodedokter) url.searchParams.set("kodedokter", kodedokter);
    if (sesi) url.searchParams.set("sesi", String(sesi));
    return url.toString();
}

export function openBookingModal(bookingData) {
    if (!bookingData) return;

    const nextDate = calculateNextDate(
        bookingData.dayNumber,
        bookingData.timeSlot,
    );
    const bookingUrl = buildRegOnlineUrl({
        tgl: nextDate.dateIso,
        kodepoli: bookingData.poliCode,
        kodedokter: bookingData.paramedicCode,
        sesi: bookingData.sesi || 1,
    });

    const photoImg = document.querySelector("#bookingModalDoctorPhoto img");
    if (photoImg) {
        photoImg.src = `https://mobile.rs-elisabeth.com/paramedic/${encodeURIComponent(bookingData.paramedicCode)}.png`;
        photoImg.alt = bookingData.paramedicName || t("doctorPhoto");
        photoImg.onerror = function () {
            this.onerror = null;
            this.src = DEFAULT_DOCTOR_PHOTO;
        };
    }

    const nameEl = document.getElementById("bookingModalDoctorName");
    if (nameEl) nameEl.textContent = bookingData.paramedicName || "-";

    const specEl = document.getElementById("bookingModalDoctorSpec");
    if (specEl) specEl.textContent = bookingData.specialtyName || "-";

    const poliEl = document.getElementById("bookingModalPoliName");
    if (poliEl) poliEl.textContent = bookingData.poliName || "-";

    const timeEl = document.getElementById("bookingModalTime");
    if (timeEl) timeEl.textContent = bookingData.timeSlot || "-";

    const sessionEl = document.getElementById("bookingModalSession");
    if (sessionEl) {
        const sesiNumber = bookingData.sesi || 1;
        sessionEl.textContent = `${t("sessionPrefix")} ${sesiNumber}`;
    }

    const dateFormattedEl = document.getElementById(
        "bookingModalDateFormatted",
    );
    if (dateFormattedEl) dateFormattedEl.textContent = nextDate.formatted;

    const confirmBtn = document.getElementById("bookingModalConfirmBtn");
    if (confirmBtn) {
        confirmBtn.href = bookingUrl;
    }

    const modalEl = document.getElementById("bookingConfirmModal");
    if (modalEl && window.bootstrap?.Modal) {
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

export function renderProfile(status, profile = null) {
    const notice = document.getElementById("doctor-profile-status");
    if (notice) {
        notice.textContent =
            status === "ready"
                ? ""
                : t(
                      {
                          loading: "profileLoading",
                          not_found: "profileNotFound",
                          unavailable: "profileUnavailable",
                      }[status] ?? "profileEmpty",
                  );
        notice.hidden = status === "ready";
    }
    for (const field of ["bio", "location", "experience", "education"]) {
        const element = document.getElementById(`doctor-profile-${field}`);
        if (element)
            element.textContent =
                status === "ready" &&
                typeof profile?.[field] === "string" &&
                profile[field].trim()
                    ? profile[field]
                    : t("profileEmpty");
    }
    if (status === "ready" && profile) {
        const name = document.getElementById("namaDokter");
        if (name) name.textContent = profile.name;
        const specialty = document.querySelector(
            "#detailDokter .speciality-title",
        );
        if (specialty && profile.specialty_name)
            specialty.textContent = profile.specialty_name;
    }
}

export function getSpecialtyName(specialtyCode) {
    let name = t("generalSpecialist");
    if (specialtyCode) {
        const map = getSpecialtyMap();
        if (map && map.has(String(specialtyCode))) {
            name = map.get(String(specialtyCode));
        } else {
            const option = Array.from(
                document.querySelectorAll(".clinic-option"),
            ).find((item) => item.dataset.code === String(specialtyCode));
            if (option) name = option.dataset.value;
        }
    }
    return name
        .toLowerCase()
        .split(" ")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
}

/**
 * Renders the schedule grid for a doctor card.
 * Uses a responsive CSS grid that wraps naturally, no forced horizontal scroll.
 */
export function createScheduleGrid(dokter) {
    const schedule = dokter.schedule;
    const activeDays = state.days.filter((day) => schedule[day].length > 0);

    if (activeDays.length === 0) {
        return {
            html: `
                <div class="d-flex align-items-center justify-content-center flex-column py-4 bg-light rounded-3 w-100 h-100" style="border: 2px dashed rgba(0,0,0,0.05);">
                    <i class="fa-regular fa-calendar-xmark mb-2" style="font-size: 1.5rem; color: var(--secondary-color); opacity: 0.3;"></i>
                    <span class="text-muted small fw-medium">${t("scheduleNotAvailable")}</span>
                </div>
            `,
            gridStyle: "",
        };
    }

    let html = "";
    activeDays.forEach((day) => {
        let slotsHtml = "";
        schedule[day].forEach((item) => {
            const sessionList = Array.isArray(item.sessions) && item.sessions.length > 0
                ? item.sessions
                : item.jam.map((j, idx) => ({ jam: j, sesi: idx + 1 }));

            sessionList.forEach((slot) => {
                const jam = slot.jam;
                const sesiNumber = slot.sesi;
                slotsHtml += `
                    <button type="button" 
                        class="jadwal-slot jadwal-slot-interactive"
                        data-booking-doctor="${escapeHtml(dokter.paramedicCode)}"
                        data-booking-doctor-name="${escapeHtml(dokter.paramedicName)}"
                        data-booking-specialty="${escapeHtml(getSpecialtyName(dokter.specialtyCode))}"
                        data-booking-day="${day}"
                        data-booking-time="${escapeHtml(jam)}"
                        data-booking-poli="${escapeHtml(item.serviceUnitCode)}"
                        data-booking-poli-name="${escapeHtml(item.serviceUnitName)}"
                        data-booking-sesi="${sesiNumber}"
                        title="${escapeHtml(t("clickToBook"))}: ${escapeHtml(dokter.paramedicName)} (${t("days")[day]}, ${escapeHtml(jam)})">
                        <div class="slot-content">
                            <span class="slot-time">
                                <i class="fa-regular fa-clock me-1"></i>${escapeHtml(jam)}
                            </span>
                            <span class="slot-klinik">${escapeHtml(item.serviceUnitName)}</span>
                        </div>
                        <span class="slot-action-badge">
                            <i class="fa-solid fa-calendar-plus me-1"></i>${t("bookSlot")}
                        </span>
                    </button>
                `;
            });
        });
        html += `
            <div class="jadwal-day-col">
                <div class="jadwal-day-name">${t("days")[day]}</div>
                <div class="jadwal-slots">${slotsHtml}</div>
            </div>
        `;
    });

    return { html, gridStyle: "" };
}

export function createDoctorCard(dokter) {
    return `
        <div class="card shadow-sm border-0 mb-3 rounded-4 doctor-card-item">
            <div class="card-body p-3 p-md-4">
                <div class="row align-items-center">
                    <div class="col-12 col-md-4 col-xl-2 border-end text-center mb-3 mb-md-0 doctor-card-photo">
                        <div class="p-2 text-center">
                            <img
                                src="https://mobile.rs-elisabeth.com/paramedic/${encodeURIComponent(dokter.paramedicCode)}.png"
                                class="img-fluid mb-3 rounded-3"
                                style="width:165px;height:200px;object-fit:cover;object-position:top center;"
                                alt="${t("doctorPhoto")}"
                                onerror="this.onerror=null; this.src='${DEFAULT_DOCTOR_PHOTO}';">
                            <span class="badge bg-primary mb-3 d-none fw-bold">
                                    ${escapeHtml(getSpecialtyName(dokter.specialtyCode))}
                            </span>
                            <div class="container my-2 group-btn px-0">
                                <a 
                                    href='#' 
                                    class='btn-cek-profil'
                                    data-bs-toggle='modal'
                                    data-bs-target='#detailDokter'
                                    data-id='${escapeHtml(dokter.entry_id)}'
                                    data-jadwal=''>
                                    <i class="fa-regular fa-user" aria-hidden="true"></i>
                                    <span>${t("checkProfile")}</span>
                                </a>
                                <a
                                    href='https://regonline.rs-elisabeth.com/'
                                    class='btn-buat-janji'
                                    target='_blank'>
                                    <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                                    <span>${t("makeAppointment")}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8 col-xl-10 doctor-card-main">
                        <div class="container-fluid px-2 py-2">
                            <div class="dokter-header mb-0">
                                <h5 class="fw-bold mb-1">${escapeHtml(dokter.paramedicName)}</h5>
                                <span class="speciality-badge">
                                    <p class="speciality-title fw-bold">${escapeHtml(getSpecialtyName(dokter.specialtyCode))}</p>
                                </span>
                            </div>
                            <div class="jadwal-wrapper">
                                <h2 class="jadwal-title">
                                    <i class="fa-regular fa-calendar"></i>
                                    ${t("doctorSchedule")}
                                </h2>
                                <div class="jadwal-scroll">
                                    <div class="jadwal-grid" id="jadwalGrid-${escapeHtml(dokter.paramedicCode)}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

export function renderDoctorPage(onPageChange) {
    const container = document.getElementById("daftar-dokter");

    if (state.filteredDoctorList.length === 0) {
        container.innerHTML = `
            <div class="empty-state-container container text-center py-5 my-4 bg-white rounded-4 shadow-sm" style="max-width: 600px; border: 1px solid rgba(0,0,0,0.05);">
                <div class="empty-state-icon mb-4">
                    <i class="fa-solid fa-user-doctor" style="font-size: 4.5rem; color: var(--secondary-color); opacity: 0.2;"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">${t("doctorNotFoundTitle")}</h4>
                <p class="text-muted mb-0 mx-auto" style="max-width: 80%;">
                    ${t("doctorNotFoundDesc")}
                </p>
            </div>
        `;
        renderPagination(0, onPageChange);
        return;
    }

    const totalPages = Math.ceil(
        state.filteredDoctorList.length / state.itemsPerPage,
    );
    if (state.currentPage > totalPages) state.currentPage = totalPages;
    if (state.currentPage < 1) state.currentPage = 1;

    const startIndex = (state.currentPage - 1) * state.itemsPerPage;
    const pageItems = state.filteredDoctorList.slice(
        startIndex,
        startIndex + state.itemsPerPage,
    );

    container.innerHTML = pageItems.map(createDoctorCard).join("");

    pageItems.forEach((doctor) => {
        const grid = document.getElementById(
            `jadwalGrid-${doctor.paramedicCode}`,
        );
        if (grid) {
            const { html } = createScheduleGrid(doctor);
            grid.innerHTML = html;
            // No forced min-width; grid is now responsive
        }
    });

    renderPagination(totalPages, onPageChange);
}

export function renderPagination(totalPages, onPageChange) {
    let paginationContainer = document.getElementById("doctorPagination");

    if (!paginationContainer) {
        paginationContainer = document.createElement("nav");
        paginationContainer.id = "doctorPagination";
        document
            .getElementById("daftar-dokter")
            .insertAdjacentElement("afterend", paginationContainer);
    }

    if (totalPages <= 1) {
        paginationContainer.innerHTML = "";
        return;
    }

    // --- Dynamic Sliding Window Pagination Logic (±2 Sibling) ---
    let pages = [];
    if (totalPages <= 8) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
        const delta = 2; // ±2 tetangga aktif
        let left = state.currentPage - delta;
        let right = state.currentPage + delta;

        // Normalisasi batas agar minimal ada 5 angka berurutan di awal/akhir
        if (left <= 3) {
            left = 1;
            right = Math.max(5, right);
        }
        if (right >= totalPages - 2) {
            right = totalPages;
            left = Math.min(left, totalPages - 4);
        }

        // Susun array halaman dengan penanda jump input
        if (left > 1) {
            pages.push(1);
            if (left > 2) {
                pages.push("INPUT");
            }
        }

        for (let i = left; i <= right; i++) {
            pages.push(i);
        }

        if (right < totalPages) {
            if (right < totalPages - 1) {
                pages.push("INPUT_2");
            }
            pages.push(totalPages);
        }
    }

    let pagesHtml = "";
    pages.forEach((p) => {
        if (p === "INPUT" || p === "INPUT_2") {
            pagesHtml += `
                <li class="page-item">
                    <input type="number" class="page-link pagination-input jump-page-input" placeholder="..." min="1" max="${totalPages}" title="${t("paginationInputTitle")}">
                </li>
            `;
        } else {
            pagesHtml += `
                <li class="page-item ${p === state.currentPage ? "active" : ""}">
                    <button class="page-link" data-page="${p}">${p}</button>
                </li>
            `;
        }
    });

    paginationContainer.innerHTML = `
        <ul class="pagination custom-pagination justify-content-center mt-4 mb-5">
            <li class="page-item ${state.currentPage === 1 ? "disabled" : ""}">
                <button class="page-link" data-page="${state.currentPage - 1}">&laquo;</button>
            </li>
            ${pagesHtml}
            <li class="page-item ${state.currentPage === totalPages ? "disabled" : ""}">
                <button class="page-link" data-page="${state.currentPage + 1}">&raquo;</button>
            </li>
        </ul>
    `;

    // Click events for buttons
    paginationContainer.querySelectorAll("button.page-link").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            const targetPage = parseInt(e.currentTarget.dataset.page);
            if (!targetPage || targetPage < 1 || targetPage > totalPages)
                return;
            onPageChange(targetPage);
        });
    });

    // Enter event for inputs
    paginationContainer
        .querySelectorAll(".jump-page-input")
        .forEach((input) => {
            input.addEventListener("keypress", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    const targetPage = parseInt(e.target.value);
                    if (
                        targetPage &&
                        targetPage >= 1 &&
                        targetPage <= totalPages
                    ) {
                        onPageChange(targetPage);
                    } else if (targetPage) {
                        // Reset to empty if invalid page
                        e.target.value = "";
                    }
                }
            });
            // Clear placeholder on focus for cleaner UX
            input.addEventListener("focus", (e) => {
                e.target.placeholder = "";
            });
            input.addEventListener("blur", (e) => {
                e.target.placeholder = "...";
                e.target.value = ""; // clear typed text if they didn't press enter
            });
        });
}

export function showLoading() {
    const container = document.getElementById("daftar-dokter");
    if (container) {
        container.innerHTML = `
            <div class="doctor-state container text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">${t("loadingDoctors")}</p>
            </div>
        `;
    }
    const pagination = document.getElementById("doctorPagination");
    if (pagination) pagination.innerHTML = "";
}

export function showError() {
    const container = document.getElementById("daftar-dokter");
    if (container) {
        container.innerHTML = `
            <div class="doctor-state empty-state-container container text-center py-5 my-4 bg-white rounded-4 shadow-sm" style="max-width: 600px; border: 1px solid rgba(0,0,0,0.05);">
                <div class="empty-state-icon mb-4">
                    <i class="fa-solid fa-user-doctor" style="font-size: 4.5rem; color: var(--secondary-color); opacity: 0.2;"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">${t("doctorNotFoundTitle")}</h4>
                <p class="text-muted mb-0 mx-auto" style="max-width: 80%;">
                    ${t("doctorNotFoundDesc")}
                </p>
            </div>
        `;
    }
    const pagination = document.getElementById("doctorPagination");
    if (pagination) pagination.innerHTML = "";
}

export function renderModal(doctorData) {
    const fotoDokter = document.getElementById("foto-dokter");

    if (fotoDokter) {
        fotoDokter.innerHTML = `
            <img src="https://mobile.rs-elisabeth.com/paramedic/${encodeURIComponent(doctorData.paramedicCode)}.png"
            alt="${t("doctorPhoto")}"
            onerror="this.onerror=null; this.src='${DEFAULT_DOCTOR_PHOTO}';">
        `;
    }

    const namaDokter = document.getElementById("namaDokter");
    if (namaDokter) {
        namaDokter.textContent = doctorData.paramedicName;
    }

    const specialtyBadge = document.querySelector(
        "#detailDokter .speciality-title",
    );
    if (specialtyBadge) {
        specialtyBadge.textContent = getSpecialtyName(doctorData.specialtyCode);
    }

    // Render schedule cards in modal — clean responsive grid layout
    let scheduleHtml = "";
    const schedule = doctorData.schedule;
    const activeDays = Object.entries(schedule).filter(
        ([, detail]) => detail.length > 0,
    );

    if (activeDays.length === 0) {
        scheduleHtml = `
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center flex-column py-5 bg-light rounded-3 w-100" style="border: 2px dashed rgba(0,0,0,0.05);">
                    <i class="fa-regular fa-calendar-xmark mb-3" style="font-size: 2rem; color: var(--secondary-color); opacity: 0.3;"></i>
                    <span class="text-muted fw-medium">${t("modalScheduleNotAvailable")}</span>
                </div>
            </div>
        `;
    } else {
        activeDays.forEach(([day, detail]) => {
            let timeSlotsHtml = "";
            detail.forEach((data) => {
                const sessionList = Array.isArray(data.sessions) && data.sessions.length > 0
                    ? data.sessions
                    : data.jam.map((j, idx) => ({ jam: j, sesi: idx + 1 }));

                sessionList.forEach((slot) => {
                    const jam = slot.jam;
                    const sesiNumber = slot.sesi;
                    timeSlotsHtml += `
                        <button type="button"
                            class="modal-time-slot jadwal-slot-interactive"
                            data-booking-doctor="${escapeHtml(doctorData.paramedicCode)}"
                            data-booking-doctor-name="${escapeHtml(doctorData.paramedicName)}"
                            data-booking-specialty="${escapeHtml(getSpecialtyName(doctorData.specialtyCode))}"
                            data-booking-day="${day}"
                            data-booking-time="${escapeHtml(jam)}"
                            data-booking-poli="${escapeHtml(data.serviceUnitCode)}"
                            data-booking-poli-name="${escapeHtml(data.serviceUnitName)}"
                            data-booking-sesi="${sesiNumber}"
                            title="${escapeHtml(t("clickToBook"))}: ${escapeHtml(doctorData.paramedicName)} (${t("days")[day]}, ${escapeHtml(jam)})">
                            <div class="slot-content">
                                <span class="modal-slot-time">
                                    <i class="fa-regular fa-clock me-1"></i>${escapeHtml(jam)}
                                </span>
                                <span class="modal-slot-unit">${escapeHtml(data.serviceUnitName)}</span>
                            </div>
                            <span class="slot-action-badge">
                                <i class="fa-solid fa-calendar-plus me-1"></i>${t("bookSlot")}
                            </span>
                        </button>
                    `;
                });
            });

            scheduleHtml += `
                <div class="col-6 col-md-4 col-lg-3 mb-3">
                    <div class="modal-schedule-card">
                        <div class="modal-day-header">${t("dayList")[parseInt(day) - 1]}</div>
                        <div class="modal-time-list">
                            ${timeSlotsHtml}
                        </div>
                    </div>
                </div>
            `;
        });
    }

    const modalCards = document.getElementById("modal-schedule-cards");
    if (modalCards) {
        modalCards.innerHTML = scheduleHtml;
    }
}
