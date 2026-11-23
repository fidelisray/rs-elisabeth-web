import { gsap } from "gsap";
import {
    fetchAllDokter,
    fetchDoctorProfile,
    filterDoctors,
    getAvailablePolis,
    getPoliCounts,
    getSpecialtyCounts,
    matchesPoliOptionQuery,
    state,
} from './dokterService.js';
import { renderDoctorPage, renderModal, renderProfile, openBookingModal, showLoading, showError, t, getLocale, escapeHtml } from './dokterUI.js';

// Elements
const filterForm = document.getElementById("doctorFilterForm");
const searchInput = document.getElementById("searchKeyword");
const btnClearSearch = document.getElementById("btnClearSearch");
const btnResetAll = document.getElementById("btnResetAll");
const sidebarResultCount = document.getElementById("sidebarResultCount");
const resultCountBtn = document.getElementById("resultCountBtn");
const mobileFilterCount = document.getElementById("mobileFilterCount");
const chipContainers = document.querySelectorAll("[data-chips]");
const filterTabs = document.querySelectorAll(".doctor-filter-tab");
const tabFilterSpecialty = document.getElementById("tabFilterSpecialty");
const tabFilterPoli = document.getElementById("tabFilterPoli");
const panelFilterSpecialty = document.getElementById("panelFilterSpecialty");
const panelFilterPoli = document.getElementById("panelFilterPoli");
const specialtyCountBadge = document.getElementById("specialtyCountBadge");
const specialtySearchInput = document.getElementById("specialtySearchInput");
const specialtyListEl = document.getElementById("specialtyCheckboxes");
const specialtyCheckboxes = specialtyListEl ? specialtyListEl.querySelectorAll(".specialty-checkbox") : [];
const specialtyOptions = specialtyListEl ? specialtyListEl.querySelectorAll(".specialty-option") : [];
const specialtyEmpty = document.getElementById("specialtyEmpty");
const btnSelectAllSpecs = document.getElementById("btnSelectAllSpecs");
const btnClearSpecs = document.getElementById("btnClearSpecs");
const poliCountBadge = document.getElementById("poliCountBadge");
const poliSearchInput = document.getElementById("poliSearchInput");
const poliListEl = document.getElementById("poliCheckboxes");
const poliLoadingEl = document.getElementById("poliLoading");
const poliEmpty = document.getElementById("poliEmpty");
const poliUnavailable = document.getElementById("poliUnavailable");
const btnSelectAllPolis = document.getElementById("btnSelectAllPolis");
const btnClearPolis = document.getElementById("btnClearPolis");
const detailDokterModal = document.getElementById("detailDokter");
const doctorFilterOffcanvas = document.getElementById("doctorFilterOffcanvas");
const sidebarCol = document.querySelector(".doctor-sidebar-col");

function initStickySidebarNavbarSync() {
    const mainNavbar = document.getElementById("main-navbar");
    if (!mainNavbar || !sidebarCol) return;

    const STICKY_TOP_NAV_VISIBLE = "88px";
    const STICKY_TOP_NAV_HIDDEN = "16px";
    const reducedMotionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
    let lastNavbarHiddenState = mainNavbar.classList.contains("navbar-hidden");

    const syncSidebarStickyOffset = (isNavbarHidden, immediate = false) => {
        const targetTop = isNavbarHidden ? STICKY_TOP_NAV_HIDDEN : STICKY_TOP_NAV_VISIBLE;

        if (immediate || reducedMotionQuery.matches || window.innerWidth < 1200) {
            gsap.set(sidebarCol, { "--doc-sticky-top": targetTop });
            return;
        }

        gsap.to(sidebarCol, {
            "--doc-sticky-top": targetTop,
            duration: 0.35,
            ease: "power3.out",
            overwrite: "auto",
        });
    };

    syncSidebarStickyOffset(lastNavbarHiddenState, true);

    const observer = new MutationObserver(() => {
        const isNavbarHidden = mainNavbar.classList.contains("navbar-hidden");
        if (isNavbarHidden === lastNavbarHiddenState) return;
        lastNavbarHiddenState = isNavbarHidden;
        syncSidebarStickyOffset(isNavbarHidden, false);
    });

    observer.observe(mainNavbar, {
        attributes: true,
        attributeFilter: ["class"],
    });
}

function switchFilterTab(tabKey, moveFocus = false) {
    const isPoli = tabKey === "poli";

    if (tabFilterSpecialty) {
        tabFilterSpecialty.classList.toggle("is-active", !isPoli);
        tabFilterSpecialty.setAttribute("aria-selected", isPoli ? "false" : "true");
        tabFilterSpecialty.tabIndex = isPoli ? -1 : 0;
    }

    if (tabFilterPoli) {
        tabFilterPoli.classList.toggle("is-active", isPoli);
        tabFilterPoli.setAttribute("aria-selected", isPoli ? "true" : "false");
        tabFilterPoli.tabIndex = isPoli ? 0 : -1;
    }

    if (panelFilterSpecialty) {
        panelFilterSpecialty.hidden = isPoli;
    }

    if (panelFilterPoli) {
        panelFilterPoli.hidden = !isPoli;
    }

    if (moveFocus) {
        const targetTab = isPoli ? tabFilterPoli : tabFilterSpecialty;
        targetTab?.focus();
    }
}

function getPoliCheckboxes() {
    return poliListEl ? poliListEl.querySelectorAll(".poli-checkbox") : [];
}

function getPoliOptions() {
    return poliListEl ? poliListEl.querySelectorAll(".poli-option") : [];
}

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

function getSelectedPolis() {
    const selected = [];
    getPoliCheckboxes().forEach((cb) => {
        if (cb.checked) {
            selected.push({
                code: cb.value,
                name: cb.dataset.name || cb.value,
            });
        }
    });
    return selected;
}

function renderPoliFilterOptions(availablePolis = []) {
    if (!poliListEl) return;

    if (poliLoadingEl) {
        poliLoadingEl.hidden = true;
    }

    poliListEl.querySelectorAll(".poli-option").forEach((el) => el.remove());

    if (!Array.isArray(availablePolis) || availablePolis.length === 0) {
        if (poliEmpty) poliEmpty.hidden = true;
        if (poliUnavailable) poliUnavailable.hidden = false;
        return;
    }

    if (poliUnavailable) poliUnavailable.hidden = true;
    if (poliEmpty) poliEmpty.hidden = true;

    const fragment = document.createDocumentFragment();
    availablePolis.forEach((poli) => {
        const safeId = `poli_${String(poli.code).replace(/[^a-zA-Z0-9_-]/g, "_")}`;
        const option = document.createElement("div");
        option.className = "specialty-option poli-option";
        option.dataset.poliCode = poli.code;
        option.innerHTML = `
            <input class="specialty-checkbox poli-checkbox"
                   type="checkbox"
                   value="${escapeHtml(poli.code)}"
                   id="${escapeHtml(safeId)}"
                   data-name="${escapeHtml(poli.name)}"
                   data-raw-name="${escapeHtml(poli.rawName || poli.name)}">
            <label class="specialty-label poli-label" for="${escapeHtml(safeId)}">
                <span class="specialty-name poli-name">${escapeHtml(poli.name)}</span>
                <span class="specialty-count" data-count aria-hidden="true">${escapeHtml(String(poli.totalDoctors ?? 0))}</span>
            </label>
        `;
        fragment.appendChild(option);
    });

    poliListEl.insertBefore(fragment, poliEmpty || poliUnavailable || null);
    filterPoliListByQuery();
}

function updateSpecialtyCounts(keyword = "", selectedPoliCodes = []) {
    const counts = getSpecialtyCounts(keyword, selectedPoliCodes);
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

function updatePoliCounts(keyword = "", selectedSpecialtyCodes = []) {
    const counts = getPoliCounts(keyword, selectedSpecialtyCodes);
    getPoliOptions().forEach((option) => {
        const cb = option.querySelector(".poli-checkbox");
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

function filterPoliListByQuery() {
    if (!poliSearchInput) return;
    const options = getPoliOptions();
    if (options.length === 0) {
        if (poliEmpty) poliEmpty.hidden = true;
        return;
    }

    const query = poliSearchInput.value.trim();
    let visibleCount = 0;

    options.forEach((option) => {
        const cb = option.querySelector(".poli-checkbox");
        const nameEl = option.querySelector(".poli-name");
        const displayLabel = nameEl ? nameEl.textContent : option.textContent;
        const rawLabel = cb?.dataset.rawName || "";
        const code = cb?.value || "";
        const isMatch = matchesPoliOptionQuery(query, displayLabel, rawLabel, code);
        option.style.display = isMatch ? "" : "none";
        if (isMatch) visibleCount++;
    });

    if (poliEmpty) {
        poliEmpty.hidden = visibleCount > 0;
    }
}

function renderActiveChips(keyword = "", selectedSpecs = [], selectedPolis = []) {
    const trimmedKeyword = (keyword || "").trim();
    const hasChips =
        trimmedKeyword.length > 0 ||
        selectedSpecs.length > 0 ||
        selectedPolis.length > 0;

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
                <button type="button" data-remove-spec="${escapeHtml(spec.code)}" aria-label="${escapeHtml(t('removeSpecialtyFilter'))}: ${escapeHtml(spec.name)}">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </li>
        `;
    });

    selectedPolis.forEach((poli) => {
        chipsHtml += `
            <li class="doctor-chip doctor-chip--poli">
                <i class="fa-regular fa-hospital doctor-chip-icon" aria-hidden="true"></i>
                <span>${escapeHtml(poli.name)}</span>
                <button type="button" data-remove-poli="${escapeHtml(poli.code)}" aria-label="${escapeHtml(t('removePoliFilter'))}: ${escapeHtml(poli.name)}">
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

function updateFilterControls(keyword = "", selectedSpecCodes = [], selectedPoliCodes = []) {
    const trimmedKeyword = (keyword || "").trim();
    const hasKeyword = trimmedKeyword.length > 0;
    const specCount = selectedSpecCodes.length;
    const poliCount = selectedPoliCodes.length;
    const totalActiveFilters = (hasKeyword ? 1 : 0) + specCount + poliCount;
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

    if (poliCountBadge) {
        poliCountBadge.textContent = String(poliCount);
        poliCountBadge.hidden = poliCount === 0;
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

function updateSearchSummary(keyword = "", specialtyNames = [], poliNames = []) {
    const summaryContainer = document.getElementById("search-summary-container");
    const summaryLabel = document.getElementById("search-summary-label");
    const summaryKeyword = document.getElementById("search-summary-keyword");
    const summaryCount = document.getElementById("search-summary-count");

    if (summaryContainer && summaryLabel && summaryKeyword && summaryCount) {
        const trimmed = (keyword || "").trim();
        const hasKeyword = trimmed.length > 0;
        const hasSpecs = specialtyNames.length > 0;
        const hasPolis = poliNames.length > 0;

        if (!hasKeyword && !hasSpecs && !hasPolis) {
            summaryContainer.classList.add("d-none");
        } else {
            summaryLabel.innerText = t('searchResult') || (getLocale() === 'en' ? 'Search Results:' : 'Hasil Pencarian:');

            const groupLabels = [];
            if (hasSpecs) groupLabels.push(specialtyNames.join(', '));
            if (hasPolis) groupLabels.push(poliNames.join(', '));

            let summaryText = "";
            if (hasKeyword && groupLabels.length > 0) {
                summaryText = `"${trimmed}" (${groupLabels.join('; ')})`;
            } else if (hasKeyword) {
                summaryText = `"${trimmed}"`;
            } else {
                summaryText = groupLabels.join('; ');
            }

            summaryKeyword.innerText = summaryText;
            summaryCount.innerText = state.filteredDoctorList.length;
            summaryContainer.classList.remove("d-none");
        }
    }
}

function syncUrl(keyword = "", selectedSpecCodes = [], selectedPoliCodes = []) {
    const params = new URLSearchParams();
    const trimmed = (keyword || "").trim();
    if (trimmed) {
        params.set("search", trimmed);
    }
    if (selectedSpecCodes.length > 0) {
        params.set("spesialis", selectedSpecCodes.join(","));
    }
    if (selectedPoliCodes.length > 0) {
        params.set("poli", selectedPoliCodes.join(","));
    }
    const queryString = params.toString();
    const nextUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;
    window.history.replaceState({}, document.title, nextUrl);
}

function isMobileDrawerOpen() {
    return Boolean(doctorFilterOffcanvas && doctorFilterOffcanvas.classList.contains("show") && window.innerWidth < 1200);
}

function scrollToContentTop() {
    if (isMobileDrawerOpen()) return;

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
    const selectedSpecCodes = selectedSpecs.map((s) => s.code);
    const selectedSpecNames = selectedSpecs.map((s) => s.name);
    const selectedPolis = getSelectedPolis();
    const selectedPoliCodes = selectedPolis.map((p) => p.code);
    const selectedPoliNames = selectedPolis.map((p) => p.name);

    filterDoctors(keyword, selectedSpecCodes, selectedPoliCodes);
    updateSpecialtyCounts(keyword, selectedPoliCodes);
    updatePoliCounts(keyword, selectedSpecCodes);
    renderActiveChips(keyword, selectedSpecs, selectedPolis);
    updateFilterControls(keyword, selectedSpecCodes, selectedPoliCodes);
    updateSearchSummary(keyword, selectedSpecNames, selectedPoliNames);
    syncUrl(keyword, selectedSpecCodes, selectedPoliCodes);
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
    getPoliCheckboxes().forEach((cb) => {
        cb.checked = false;
    });
    if (specialtySearchInput) {
        specialtySearchInput.value = "";
        filterSpecialtyListByQuery();
    }
    if (poliSearchInput) {
        poliSearchInput.value = "";
        filterPoliListByQuery();
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
        const shouldScroll = !isMobileDrawerOpen();
        triggerFilter(false, false, shouldScroll);
    });

    searchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            triggerFilter(true, true, true);
        } else if (e.key === "Escape") {
            if (searchInput.value) {
                searchInput.value = "";
                triggerFilter(true, false, !isMobileDrawerOpen());
            }
        }
    });
}

if (btnClearSearch) {
    btnClearSearch.addEventListener("click", () => {
        if (searchInput) {
            searchInput.value = "";
            searchInput.focus({ preventScroll: true });
        }
        triggerFilter(true, false, !isMobileDrawerOpen());
    });
}

if (doctorFilterOffcanvas) {
    doctorFilterOffcanvas.addEventListener("hidden.bs.offcanvas", () => {
        scrollToContentTop();
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
            return;
        }

        const removePoliBtn = e.target.closest("[data-remove-poli]");
        if (removePoliBtn) {
            const code = removePoliBtn.getAttribute("data-remove-poli");
            getPoliCheckboxes().forEach((cb) => {
                if (cb.value === code) cb.checked = false;
            });
            triggerFilter(true, false, true);
        }
    });
});

// Segmented tab events (Spesialisasi | Poliklinik)
filterTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
        switchFilterTab(tab.dataset.filterTab, false);
    });

    tab.addEventListener("keydown", (e) => {
        if (e.key === "ArrowRight" || e.key === "ArrowLeft") {
            e.preventDefault();
            const nextKey = tab.dataset.filterTab === "specialty" ? "poli" : "specialty";
            switchFilterTab(nextKey, true);
        } else if (e.key === "Home") {
            e.preventDefault();
            switchFilterTab("specialty", true);
        } else if (e.key === "End") {
            e.preventDefault();
            switchFilterTab("poli", true);
        }
    });
});

// Specialty Multi-select Events
if (specialtySearchInput) {
    specialtySearchInput.addEventListener("input", filterSpecialtyListByQuery);
    specialtySearchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
        } else if (e.key === "Escape" && specialtySearchInput.value) {
            e.preventDefault();
            specialtySearchInput.value = "";
            filterSpecialtyListByQuery();
        }
    });
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

// Poliklinik Multi-select Events
if (poliSearchInput) {
    poliSearchInput.addEventListener("input", filterPoliListByQuery);
    poliSearchInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
        } else if (e.key === "Escape" && poliSearchInput.value) {
            e.preventDefault();
            poliSearchInput.value = "";
            filterPoliListByQuery();
        }
    });
}

if (poliListEl) {
    poliListEl.addEventListener("change", (e) => {
        if (e.target.closest(".poli-checkbox")) {
            triggerFilter(false, false, true);
        }
    });
}

if (btnSelectAllPolis) {
    btnSelectAllPolis.addEventListener("click", () => {
        getPoliCheckboxes().forEach((cb) => {
            const option = cb.closest(".poli-option");
            const isVisible = !option || option.style.display !== "none";
            if (isVisible && !cb.disabled) {
                cb.checked = true;
            }
        });
        triggerFilter(true, false, true);
    });
}

if (btnClearPolis) {
    btnClearPolis.addEventListener("click", () => {
        getPoliCheckboxes().forEach((cb) => {
            cb.checked = false;
        });
        triggerFilter(true, false, true);
    });
}

// Initialization
document.addEventListener("DOMContentLoaded", async () => {
    initStickySidebarNavbarSync();
    showLoading();

    const urlParams = new URLSearchParams(window.location.search);
    const initialKeyword = urlParams.get("search") || urlParams.get("q") || urlParams.get("nama") || urlParams.get("klinik");
    const specialtyParam = urlParams.get("spesialis") || urlParams.get("specialty_code");
    const poliParam = urlParams.get("poli") || urlParams.get("poliklinik") || urlParams.get("poli_code");

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
        const availablePolis = getAvailablePolis();
        renderPoliFilterOptions(availablePolis);

        let matchedPoliFromUrl = false;
        if (poliParam) {
            const requestedPoliTokens = poliParam
                .split(",")
                .map((s) => s.trim())
                .filter(Boolean);
            const poliCodePattern = /^[a-z]{2,4}[- ]?\d+$/i;

            getPoliCheckboxes().forEach((cb) => {
                const lowerCode = cb.value.toLowerCase();
                const rawName = (cb.dataset.rawName || "").toLowerCase();
                const matchesCode = requestedPoliTokens.some(
                    (token) => lowerCode === token.toLowerCase(),
                );
                const matchesName = requestedPoliTokens.some((token) => {
                    if (poliCodePattern.test(token)) return false;
                    return rawName !== "" && rawName.includes(token.toLowerCase());
                });
                if (matchesCode || matchesName) {
                    cb.checked = true;
                    matchedPoliFromUrl = true;
                }
            });
        }

        if (matchedPoliFromUrl && !specialtyParam) {
            switchFilterTab("poli", false);
        }

        applyFilters(false);
    } else {
        if (poliLoadingEl) poliLoadingEl.hidden = true;
        if (poliUnavailable) poliUnavailable.hidden = false;
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

// ------------------------------
// Interactive Booking Slot Click
// ------------------------------
document.addEventListener("click", function (event) {
    const slotBtn = event.target.closest(".jadwal-slot-interactive");
    if (!slotBtn) return;

    event.preventDefault();
    const bookingData = {
        paramedicCode: slotBtn.dataset.bookingDoctor,
        paramedicName: slotBtn.dataset.bookingDoctorName,
        specialtyName: slotBtn.dataset.bookingSpecialty,
        dayNumber: parseInt(slotBtn.dataset.bookingDay, 10),
        timeSlot: slotBtn.dataset.bookingTime,
        poliCode: slotBtn.dataset.bookingPoli,
        poliName: slotBtn.dataset.bookingPoliName,
        sesi: parseInt(slotBtn.dataset.bookingSesi, 10) || 1,
    };

    openBookingModal(bookingData);
});
