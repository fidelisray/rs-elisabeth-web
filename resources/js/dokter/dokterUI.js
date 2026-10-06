import { state } from './dokterService.js';

const HARI_INDONESIA = {
    1: "Senin", 2: "Selasa", 3: "Rabu", 4: "Kamis",
    5: "Jumat", 6: "Sabtu", 7: "Minggu",
};

function getSpecialtyName(specialtyCode) {
    let name = "Spesialis Umum";
    if (specialtyCode) {
        const option = document.querySelector(`.clinic-option[data-code="${specialtyCode}"]`);
        if (option) name = option.dataset.value;
    }
    return name.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
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
                    <span class="text-muted small fw-medium">Jadwal belum tersedia</span>
                </div>
            `,
            gridStyle: ''
        };
    }

    let html = '';
    activeDays.forEach((day) => {
        let slotsHtml = '';
        schedule[day].forEach((item) => {
            item.jam.forEach((jam) => {
                slotsHtml += `
                    <div class="jadwal-slot">
                        <span class="slot-time">${jam}</span>
                        <span class="slot-klinik">${item.serviceUnitName}</span>
                    </div>
                `;
            });
        });
        html += `
            <div class="jadwal-day-col">
                <div class="jadwal-day-name">${HARI_INDONESIA[day]}</div>
                <div class="jadwal-slots">${slotsHtml}</div>
            </div>
        `;
    });

    return { html, gridStyle: '' };
}

export function createDoctorCard(dokter) {
    return `
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-12 col-md-3 border-end">
                        <div class="p-4 text-center">
                            <img
                                src="https://mobile.rs-elisabeth.com/paramedic/${dokter.paramedicCode}.png"
                                class="rounded-circle img-fluid mb-3"
                                style="width:180px;height:180px;object-fit:cover;"
                                alt="Foto Dokter"
                                onerror="this.onerror=null; this.src='/images/default.png';">
                            <span class="badge bg-primary mb-3 d-none fw-bold">
                                    ${getSpecialtyName(dokter.specialtyCode)}
                            </span>
                            <div class="container my-3 group-btn">
                                <a 
                                    href='#' 
                                    class='btn-cek-profil'
                                    data-bs-toggle='modal'
                                    data-bs-target='#detailDokter'
                                    data-id='${dokter.entry_id}'
                                    data-jadwal=''>Cek Profil</a>
                                <a
                                    href='https://regonline.rs-elisabeth.com/'
                                    class='btn-buat-janji'
                                    target='_blank'>Buat Janji</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-9 mx-auto">
                        <div class="container py-4">
                            <div class="dokter-header mb-0">
                                <h5 class="fw-bold mb-1">${dokter.paramedicName}</h5>
                                <span class="speciality-badge">
                                    <p class="speciality-title fw-bold">${getSpecialtyName(dokter.specialtyCode)}</p>
                                </span>
                            </div>
                            <div class="jadwal-wrapper">
                                <h2 class="jadwal-title">
                                    <i class="fa-regular fa-calendar"></i>
                                    Jadwal Dokter
                                </h2>
                                <div class="jadwal-scroll">
                                    <div class="jadwal-grid" id="jadwalGrid-${dokter.paramedicCode}">
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
                <h4 class="fw-bold text-dark mb-3">Dokter Tidak Ditemukan</h4>
                <p class="text-muted mb-0 mx-auto" style="max-width: 80%;">
                    Maaf, kami tidak dapat menemukan dokter atau klinik yang sesuai dengan kata kunci pencarian Anda. Silakan coba spesialisasi atau nama yang berbeda.
                </p>
            </div>
        `;
        renderPagination(0, onPageChange);
        return;
    }

    const totalPages = Math.ceil(state.filteredDoctorList.length / state.itemsPerPage);
    if (state.currentPage > totalPages) state.currentPage = totalPages;
    if (state.currentPage < 1) state.currentPage = 1;

    const startIndex = (state.currentPage - 1) * state.itemsPerPage;
    const pageItems = state.filteredDoctorList.slice(startIndex, startIndex + state.itemsPerPage);

    container.innerHTML = pageItems.map(createDoctorCard).join("");

    pageItems.forEach((doctor) => {
        const grid = document.getElementById(`jadwalGrid-${doctor.paramedicCode}`);
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
        document.getElementById("daftar-dokter").insertAdjacentElement("afterend", paginationContainer);
    }

    if (totalPages <= 1) {
        paginationContainer.innerHTML = "";
        return;
    }

    // --- Smart Pagination Logic ---
    let pages = [];
    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) pages.push(i);
    } else {
        if (state.currentPage <= 3) {
            pages = [1, 2, 3, 'INPUT', totalPages - 1, totalPages];
        } else if (state.currentPage >= totalPages - 2) {
            pages = [1, 2, 'INPUT', totalPages - 2, totalPages - 1, totalPages];
        } else {
            pages = [1, 'INPUT', state.currentPage - 1, state.currentPage, state.currentPage + 1, 'INPUT_2', totalPages];
        }
    }

    let pagesHtml = "";
    pages.forEach(p => {
        if (p === 'INPUT' || p === 'INPUT_2') {
            pagesHtml += `
                <li class="page-item">
                    <input type="number" class="page-link pagination-input jump-page-input" placeholder="..." min="1" max="${totalPages}" title="Ketik halaman lalu Enter">
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
            if (!targetPage || targetPage < 1 || targetPage > totalPages) return;
            onPageChange(targetPage);
        });
    });

    // Enter event for inputs
    paginationContainer.querySelectorAll(".jump-page-input").forEach((input) => {
        input.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                const targetPage = parseInt(e.target.value);
                if (targetPage && targetPage >= 1 && targetPage <= totalPages) {
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
            <div class="container text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Memuat data dokter...</p>
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
            <div class="empty-state-container container text-center py-5 my-4 bg-white rounded-4 shadow-sm" style="max-width: 600px; border: 1px solid rgba(0,0,0,0.05);">
                <div class="empty-state-icon mb-4">
                    <i class="fa-solid fa-user-doctor" style="font-size: 4.5rem; color: var(--secondary-color); opacity: 0.2;"></i>
                </div>
                <h4 class="fw-bold text-dark mb-3">Dokter Tidak Ditemukan</h4>
                <p class="text-muted mb-0 mx-auto" style="max-width: 80%;">
                    Maaf, kami tidak dapat menemukan dokter atau klinik yang sesuai dengan kata kunci pencarian Anda. Silakan coba spesialisasi atau nama yang berbeda.
                </p>
            </div>
        `;
    }
    const pagination = document.getElementById("doctorPagination");
    if (pagination) pagination.innerHTML = "";
}

export function renderModal(doctorData) {
    const hari = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
    const fotoDokter = document.getElementById("foto-dokter");

    if (fotoDokter) {
        fotoDokter.innerHTML = `
            <img src="https://mobile.rs-elisabeth.com/paramedic/${doctorData.paramedicCode}.png" 
            alt="Doctor"
            onerror="this.onerror=null; this.src='/images/default.png';">
        `;
    }

    const namaDokter = document.getElementById("namaDokter");
    if (namaDokter) {
        namaDokter.textContent = doctorData.paramedicName;
    }

    const specialtyBadge = document.querySelector("#detailDokter .speciality-title");
    if (specialtyBadge) {
        specialtyBadge.textContent = getSpecialtyName(doctorData.specialtyCode);
    }

    // Render schedule cards in modal — clean responsive grid layout
    let scheduleHtml = '';
    const schedule = doctorData.schedule;
    const activeDays = Object.entries(schedule).filter(([, detail]) => detail.length > 0);

    if (activeDays.length === 0) {
        scheduleHtml = `
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center flex-column py-5 bg-light rounded-3 w-100" style="border: 2px dashed rgba(0,0,0,0.05);">
                    <i class="fa-regular fa-calendar-xmark mb-3" style="font-size: 2rem; color: var(--secondary-color); opacity: 0.3;"></i>
                    <span class="text-muted fw-medium">Jadwal praktik belum tersedia untuk saat ini.</span>
                </div>
            </div>
        `;
    } else {
        activeDays.forEach(([day, detail]) => {
            let timeSlotsHtml = '';
            detail.forEach((data) => {
                data.jam.forEach((jam) => {
                    timeSlotsHtml += `
                        <div class="modal-time-slot">
                            <span class="modal-slot-time">${jam}</span>
                            <span class="modal-slot-unit">${data.serviceUnitName}</span>
                        </div>
                    `;
                });
            });

            scheduleHtml += `
                <div class="col-6 col-md-4 col-lg-3 mb-3">
                    <div class="modal-schedule-card">
                        <div class="modal-day-header">${hari[parseInt(day) - 1]}</div>
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
