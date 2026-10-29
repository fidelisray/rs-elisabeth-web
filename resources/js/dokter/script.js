import { fetchAllDokter, fetchDoctorProfile, filterDoctors, getSpecialtyCounts, state } from './dokterService.js';
import { renderDoctorPage, renderModal, renderProfile, showLoading, showError, t, getLocale, escapeHtml } from './dokterUI.js';

// Elements
const filterForm = document.getElementById("doctorFilterForm");
const searchInput = document.getElementById("searchKeyword");
const btnClearSearch = document.getElementById("btnClearSearch");
const btnResetAll = document.getElementById("btnResetAll");
const sidebarResultCount = document.getElementById("sidebarResultCount");
const resultCountBtn = document.getElementById("resultCountBtn");
const mobileFilterCount = document.getElementById("mobileFilterCount");
const chipContainers = document.querySelectorAll("[data-chips]");
const specialtyCountBadge = document.getElementById("specialtyCountBadge");
const specialtySearchInput = document.getElementById("specialtySearchInput");
const specialtyCheckboxes = document.querySelectorAll(".specialty-checkbox");
const specialtyOptions = document.querySelectorAll(".specialty-option");
const specialtyEmpty = document.getElementById("specialtyEmpty");
const btnSelectAllSpecs = document.getElementById("btnSelectAllSpecs");
const btnClearSpecs = document.getElementById("btnClearSpecs");
const detailDokterModal = document.getElementById("detailDokter");

function getSelectedSpecialties() {
    const selected = [];
    specialtyCheckboxes.forEach((cb) => {
        if (cb.checked) {
            selected.push({
                code: cb.value,
                name: cb.dataset.name || cb.value,
            });
        }
    });
    return selected;
}

function getSelectedSpecialtyCodes() {
    return getSelectedSpecialties().map((s) => s.code);
}

function getSelectedSpecialtyNames() {
    return getSelectedSpecialties().map((s) => s.name);
}

function updateSpecialtyCounts(keyword = "") {
    const counts = getSpecialtyCounts(keyword);
    specialtyOptions.forEach((option) => {
        const cb = option.querySelector(".specialty-checkbox");
        const countEl = option.querySelector("[data-count]");
        if (!cb) return;

        const count = counts.get(String(cb.value)) || 0;
        if (countEl) {
            countEl.textContent = String(count);
        }

        const isChecked = cb.checked;
        option.classList.toggle("is-selected", isChecked);
        option.classList.toggle("is-empty", count === 0);
        cb.disabled = count === 0 && !isChecked;
    });
}

function filterSpecialtyListByQuery() {
    if (!specialtySearchInput) return;
    const query = specialtySearchInput.value.trim().toLowerCase();
    let visibleCount = 0;

    specialtyOptions.forEach((option) => {
        const nameEl = option.querySelector(".specialty-name");
        const labelText = (nameEl ? nameEl.textContent : option.textContent).toLowerCase();
        const isMatch = query === "" || labelText.includes(query);
        option.style.display = isMatch ? "" : "none";
        if (isMatch) visibleCount++;
    });

    if (specialtyEmpty) {
        specialtyEmpty.hidden = visibleCount > 0;
    }
}

function renderActiveChips(keyword = "", selectedSpecs = []) {
    const trimmedKeyword = (keyword || "").trim();
    const hasChips = trimmedKeyword.length > 0 || selectedSpecs.length > 0;

    let chipsHtml = "";
    if (trimmedKeyword.length > 0) {
        chipsHtml += `
            <li class="doctor-chip">
                <span>&ldquo;${escapeHtml(trimmedKeyword)}&rdquo;</span>
                <button type="button" data-remove-keyword aria-label="${escapeHtml(t('clearKeyword'))}: ${escapeHtml(trimmedKeyword)}">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </li>
        `;
    }

    selectedSpecs.forEach((spec) => {
        chipsHtml += `
            <li class="doctor-chip">
                <span>${escapeHtml(spec.name)}</span>
                <button type="button" data-remove-spec="${escapeHtml(spec.code)}" aria-label="${escapeHtml(t('removeFilter'))}: ${escapeHtml(spec.name)}">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </li>
        `;
    });

    chipContainers.forEach((container) => {
        container.innerHTML = chipsHtml;
        container.hidden = !hasChips;
    });
}

function updateFilterControls(keyword = "", selectedCodes = []) {
    const trimmedKeyword = (keyword || "").trim();
    const hasKeyword = trimmedKeyword.length > 0;
    const specCount = selectedCodes.length;
    const totalActiveFilters = (hasKeyword ? 1 : 0) + specCount;
    const totalDoctors = state.filteredDoctorList.length;

    if (btnClearSearch) {
        btnClearSearch.hidden = !hasKeyword;
    }

    if (btnResetAll) {
        btnResetAll.hidden = totalActiveFilters === 0;
    }

    if (specialtyCountBadge) {
        specialtyCountBadge.textContent = String(specCount);
        specialtyCountBadge.hidden = specCount === 0;
    }

    if (mobileFilterCount) {
        mobileFilterCount.textContent = String(totalActiveFilters);
        mobileFilterCount.hidden = totalActiveFilters === 0;
    }

    if (resultCountBtn) {
        resultCountBtn.textContent = String(totalDoctors);
    }

    if (sidebarResultCount) {
        sidebarResultCount.textContent = `${totalDoctors} ${t('doctorsAvailable')}`;
    }
}

function updateSearchSummary(keyword = "", specialtyNames = []) {
    const summaryContainer = document.getElementById("search-summary-container");
    const summaryLabel = document.getElementById("search-summary-label");
    const summaryKeyword = document.getElementById("search-summary-keyword");
    const summaryCount = document.getElementById("search-summary-count");

    if (summaryContainer && summaryLabel && summaryKeyword && summaryCount) {
        const trimmed = (keyword || "").trim();
        const hasKeyword = trimmed.length > 0;
        const hasSpecs = specialtyNames.length > 0;

        if (!hasKeyword && !hasSpecs) {
            summaryContainer.classList.add("d-none");
        } else {
            summaryLabel.innerText = t('searchResult') || (getLocale() === 'en' ? 'Search Results:' : 'Hasil Pencarian:');

            let summaryText = "";
            if (hasKeyword && hasSpecs) {
                summaryText = `"${trimmed}" (${specialtyNames.join(', ')})`;
            } else if (hasKeyword) {
                summaryText = `"${trimmed}"`;
            } else {
                summaryText = specialtyNames.join(', ');
            }

            summaryKeyword.innerText = summaryText;
            summaryCount.innerText = state.filteredDoctorList.length;
            summaryContainer.classList.remove("d-none");
        }
    }
}

function syncUrl(keyword = "", selectedCodes = []) {
    const params = new URLSearchParams();
    const trimmed = (keyword || "").trim();
    if (trimmed) {
        params.set("search", trimmed);
    }
    if (selectedCodes.length > 0) {
        params.set("spesialis", selectedCodes.join(","));
    }
    const queryString = params.toString();
    const nextUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;
    window.history.replaceState({}, document.title, nextUrl);
}

function scrollToContentTop() {
    const contentCol = document.querySelector(".doctor-content-col");
    if (!contentCol) return;

    const navHeight = 90;
    const rect = contentCol.getBoundingClientRect();
    const targetY = window.scrollY + rect.top - navHeight;

    if (window.scrollY > targetY + 10) {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        window.scrollTo({
            top: Math.max(0, targetY),
            behavior: prefersReducedMotion ? 'auto' : 'smooth'
        });
    }
}

function applyFilters(closeOffcanvas = false, shouldScroll = false) {
    const keyword = searchInput ? searchInput.value.trim() : "";
    const selectedSpecs = getSelectedSpecialties();
    const selectedCodes = selectedSpecs.map((s) => s.code);
    const selectedNames = selectedSpecs.map((s) => s.name);

    filterDoctors(keyword, selectedCodes);
    updateSpecialtyCounts(keyword);
    renderActiveChips(keyword, selectedSpecs);
    updateFilterControls(keyword, selectedCodes);
    updateSearchSummary(keyword, selectedNames);
    syncUrl(keyword, selectedCodes);
    renderDoctorPage(handlePageChange);

    if (shouldScroll) {
        scrollToContentTop();
    }

    if (closeOffcanvas) {
        const offcanvasEl = document.getElementById("doctorFilterOffcanvas");
        if (offcanvasEl && window.bootstrap) {
            const offcanvas = window.bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (offcanvas) offcanvas.hide();
        }
    }
}

let filterDebounceTimer;
function triggerFilter(immediate = false, closeOffcanvas = false, shouldScroll = false) {
    clearTimeout(filterDebounceTimer);
    if (immediate) {
        applyFilters(closeOffcanvas, shouldScroll);
    } else {
        filterDebounceTimer = setTimeout(() => {
            applyFilters(closeOffcanvas, shouldScroll);
        }, 250);
    }
}

function resetSearch() {
    if (searchInput) {
        searchInput.value = "";
    }
    specialtyCheckboxes.forEach((cb) => {
        cb.checked = false;
    });
    if (specialtySearchInput) {
        specialtySearchInput.value = "";
        filterSpecialtyListByQuery();
    }
    triggerFilter(true, false, true);
}

function handlePageChange(newPage) {
    state.currentPage = newPage;
    renderDoctorPage(handlePageChange);
    scrollToContentTop();
}

// ------------------------------
// Form & Search Input Events
// ------------------------------
if (filterForm) {
    filterForm.addEventListener("submit", (e) => {
        e.preventDefault();
        triggerFilter(true, true, true);
    });
}

if (searchInput) {
    searchInput.addEventListener("input", () => {
        if (btnClearSearch) {
            btnClearSearch.hidden = searchInput.value.trim().length === 0;
        }
        triggerFilter(false);
    });

    searchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            triggerFilter(true, true, true);
        } else if (e.key === "Escape") {
            if (searchInput.value) {
                searchInput.value = "";
                triggerFilter(true, false, true);
            }
        }
    });
}

if (btnClearSearch) {
    btnClearSearch.addEventListener("click", () => {
        if (searchInput) {
            searchInput.value = "";
            searchInput.focus();
        }
        triggerFilter(true, false, true);
    });
}

if (btnResetAll) {
    btnResetAll.addEventListener("click", resetSearch);
}

// Delegated chip removal across both sidebar & mobile toolbar chip lists
chipContainers.forEach((container) => {
    container.addEventListener("click", (e) => {
        const removeKeyBtn = e.target.closest("[data-remove-keyword]");
        if (removeKeyBtn) {
            if (searchInput) searchInput.value = "";
            triggerFilter(true, false, true);
            return;
        }

        const removeSpecBtn = e.target.closest("[data-remove-spec]");
        if (removeSpecBtn) {
            const code = removeSpecBtn.getAttribute("data-remove-spec");
            specialtyCheckboxes.forEach((cb) => {
                if (cb.value === code) cb.checked = false;
            });
            triggerFilter(true, false, true);
        }
    });
});

// ------------------------------
// Specialty Multi-select Events
// ------------------------------
if (specialtySearchInput) {
    specialtySearchInput.addEventListener("input", filterSpecialtyListByQuery);
}

specialtyCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
        triggerFilter(false, false, true);
    });
});

if (btnSelectAllSpecs) {
    btnSelectAllSpecs.addEventListener("click", () => {
        specialtyCheckboxes.forEach((cb) => {
            const option = cb.closest(".specialty-option");
            const isVisible = !option || option.style.display !== "none";
            if (isVisible && !cb.disabled) {
                cb.checked = true;
            }
        });
        triggerFilter(true, false, true);
    });
}

if (btnClearSpecs) {
    btnClearSpecs.addEventListener("click", () => {
        specialtyCheckboxes.forEach((cb) => {
            cb.checked = false;
        });
        triggerFilter(true, false, true);
    });
}

// ------------------------------
// Initialization
// ------------------------------
document.addEventListener("DOMContentLoaded", async () => {
    showLoading();

    const urlParams = new URLSearchParams(window.location.search);
    const initialKeyword = urlParams.get("search") || urlParams.get("q") || urlParams.get("nama") || urlParams.get("klinik");
    const specialtyParam = urlParams.get("spesialis") || urlParams.get("specialty_code");

    if (specialtyParam) {
        const requestedTokens = specialtyParam
            .split(",")
            .map((s) => s.trim())
            .filter(Boolean);

        specialtyCheckboxes.forEach((cb) => {
            const matchesCode = requestedTokens.some((token) => cb.value.toLowerCase() === token.toLowerCase());
            const matchesName = requestedTokens.some((token) => cb.dataset.name?.toLowerCase().includes(token.toLowerCase()));
            if (matchesCode || matchesName) {
                cb.checked = true;
            }
        });
    }

    if (initialKeyword && searchInput) {
        searchInput.value = initialKeyword;
    }

    const fetchSuccess = await fetchAllDokter();

    if (fetchSuccess) {
        applyFilters(false);
    } else {
        showError();
    }
});

// ------------------------------
// Modal Event Listeners
// ------------------------------
if (detailDokterModal) {
    let profileRequest = null;
    let profileSequence = 0;
    detailDokterModal.addEventListener("show.bs.modal", async (event) => {
        profileRequest?.abort();
        const sequence = ++profileSequence;
        renderProfile('loading');
        const button = event.relatedTarget;
        const id = button?.dataset.id;
        const doctor = state.filteredDoctorList.find((item) => item.entry_id === id);
        if (doctor) {
            renderModal(doctor);
            const controller = new AbortController();
            profileRequest = controller;
            const timeout = setTimeout(() => controller.abort(), 10000);
            try {
                const result = await fetchDoctorProfile(doctor.paramedicCode, controller.signal);
                if (sequence === profileSequence) renderProfile(result.status, result.profile);
            } catch {
                if (sequence === profileSequence) renderProfile('unavailable');
            } finally {
                clearTimeout(timeout);
            }
        } else {
            renderProfile('not_found');
        }
    });

    detailDokterModal.addEventListener("hidden.bs.modal", function () {
        ++profileSequence;
        profileRequest?.abort();
        renderProfile('loading');
        document.getElementById("tentang-tab")?.classList.add("active");
        document.getElementById("jadwal-tab")?.classList.remove("active");
        document.getElementById("tentang-pane")?.classList.add("show", "active");
        document.getElementById("jadwal-pane")?.classList.remove("show", "active");
    });
}

// ------------------------------
// Buat Janji Buttons
// ------------------------------
document.addEventListener("click", function (event) {
    if (event.target.closest(".button-janji") || event.target.closest(".btn-buat-janji")) {
        window.open("https://regonline.rs-elisabeth.com/", "_blank");
    }
});
