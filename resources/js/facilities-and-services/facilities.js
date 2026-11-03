import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

/**
 * Facilities & Services Page Interactive Controller
 * Mengelola navigasi tab fasilitas, pencarian langsung, filter kategori,
 * aksesibilitas keyboard, dan koreografi animasi GSAP.
 */
function initFacilitiesShowcase() {
    const facilityButtons = Array.from(document.querySelectorAll(".btn-facility"));
    const facilityDetails = Array.from(document.querySelectorAll(".facility-detail-item"));
    const searchInput = document.getElementById("facility-search-input");
    const searchClearBtn = document.getElementById("facility-search-clear");
    const categoryPills = Array.from(document.querySelectorAll(".facility-cat-pill"));
    const visibleCountEl = document.getElementById("facility-visible-count");
    const sidebarCountBadge = document.getElementById("sidebar-count-badge");
    const emptyFilterState = document.getElementById("facility-filter-empty");
    const resetFilterBtn = document.getElementById("btn-reset-facility-filter");
    const discoveryBar = document.getElementById("facilities-discovery");
    const sidebarNav = document.getElementById("facility-sidebar-nav");
    const detailStage = document.getElementById("facility-detail-stage");
    const facilityListContainer = document.querySelector(".facility-list");

    if (facilityButtons.length === 0) return;

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    let activeTargetId = null;
    let activeCategory = "all";
    let searchQuery = "";
    let detailTimeline = null;

    function centerActiveCardInStrip(activeBtn, smooth = true) {
        if (!activeBtn || !facilityListContainer) return;

        if (window.innerWidth < 992) {
            const containerRect = facilityListContainer.getBoundingClientRect();
            const btnRect = activeBtn.getBoundingClientRect();
            const currentScroll = facilityListContainer.scrollLeft;
            const targetScroll =
                currentScroll +
                (btnRect.left - containerRect.left) -
                containerRect.width / 2 +
                btnRect.width / 2;

            facilityListContainer.scrollTo({
                left: Math.max(0, targetScroll),
                behavior: smooth && !prefersReducedMotion ? "smooth" : "auto"
            });
        } else {
            activeBtn.scrollIntoView({
                block: "nearest",
                behavior: smooth && !prefersReducedMotion ? "smooth" : "auto"
            });
        }
    }

    function animateDetailPanel(detailEl) {
        if (!detailEl || prefersReducedMotion) return;

        if (detailTimeline) {
            detailTimeline.kill();
        }

        const img = detailEl.querySelector(".facility-img-wrapper img");
        const tag = detailEl.querySelector(".facility-tag");
        const title = detailEl.querySelector(".facility-name");
        const divider = detailEl.querySelector(".facility-divider");
        const desc = detailEl.querySelector(".facility-desc");
        const highlights = Array.from(detailEl.querySelectorAll(".facility-highlight-badge"));
        const ctaButtons = Array.from(detailEl.querySelectorAll(".facility-cta a"));

        const contentTargets = [tag, title, divider, desc].filter(Boolean);

        detailTimeline = gsap.timeline({
            defaults: { ease: "power2.out" }
        });

        if (img) {
            detailTimeline.fromTo(
                img,
                { scale: 1.05, opacity: 0.35 },
                { scale: 1, opacity: 1, duration: 0.5 },
                0
            );
        }

        if (contentTargets.length > 0) {
            detailTimeline.fromTo(
                contentTargets,
                { y: 14, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.38, stagger: 0.05 },
                0.06
            );
        }

        if (highlights.length > 0) {
            detailTimeline.fromTo(
                highlights,
                { y: 10, opacity: 0, scale: 0.96 },
                { y: 0, opacity: 1, scale: 1, duration: 0.3, stagger: 0.03 },
                0.18
            );
        }

        if (ctaButtons.length > 0) {
            detailTimeline.fromTo(
                ctaButtons,
                { y: 10, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.32, stagger: 0.06 },
                0.24
            );
        }
    }

    function activateFacility(targetId, options = {}) {
        const { animate = true, updateHash = false, centerStrip = true } = options;

        facilityButtons.forEach((btn) => {
            const isMatch = btn.getAttribute("data-target") === targetId;
            btn.classList.toggle("active", isMatch);
            btn.setAttribute("aria-selected", isMatch ? "true" : "false");
            btn.setAttribute("tabindex", isMatch ? "0" : "-1");
        });

        facilityDetails.forEach((detail) => {
            const isMatch = detail.id === targetId;
            detail.classList.toggle("active", isMatch);
            if (isMatch) {
                detail.removeAttribute("hidden");
            } else {
                detail.setAttribute("hidden", "hidden");
            }
        });

        activeTargetId = targetId;

        const activeBtn = facilityButtons.find(
            (btn) => btn.getAttribute("data-target") === targetId
        );
        const activeDetail = document.getElementById(targetId);

        if (centerStrip && activeBtn) {
            centerActiveCardInStrip(activeBtn, animate);
        }

        if (animate && activeDetail) {
            animateDetailPanel(activeDetail);
        }

        if (updateHash && history.replaceState) {
            history.replaceState(null, "", `#${targetId}`);
        }
    }

    function getVisibleButtons() {
        return facilityButtons.filter((btn) => !btn.hasAttribute("hidden"));
    }

    function applyFilterAndSearch() {
        const normalizedQuery = searchQuery.trim().toLowerCase();
        let matchCount = 0;
        const newlyVisibleButtons = [];

        facilityButtons.forEach((btn) => {
            const itemCat = (btn.getAttribute("data-category") || "").toLowerCase();
            const itemName = (btn.getAttribute("data-name") || "").toLowerCase();
            const itemKeywords = (btn.getAttribute("data-keywords") || "").toLowerCase();

            const matchesCategory =
                activeCategory === "all" || itemCat === activeCategory;
            const matchesQuery =
                normalizedQuery === "" ||
                itemName.includes(normalizedQuery) ||
                itemCat.includes(normalizedQuery) ||
                itemKeywords.includes(normalizedQuery);

            if (matchesCategory && matchesQuery) {
                btn.removeAttribute("hidden");
                matchCount++;
                newlyVisibleButtons.push(btn);
            } else {
                btn.setAttribute("hidden", "hidden");
            }
        });

        if (visibleCountEl) {
            visibleCountEl.textContent = String(matchCount);
        }
        if (sidebarCountBadge) {
            sidebarCountBadge.textContent = String(matchCount);
        }

        if (searchClearBtn) {
            if (normalizedQuery.length > 0) {
                searchClearBtn.removeAttribute("hidden");
            } else {
                searchClearBtn.setAttribute("hidden", "hidden");
            }
        }

        if (matchCount === 0) {
            facilityDetails.forEach((detail) => {
                detail.classList.remove("active");
                detail.setAttribute("hidden", "hidden");
            });
            if (emptyFilterState) {
                emptyFilterState.removeAttribute("hidden");
                if (!prefersReducedMotion) {
                    gsap.fromTo(
                        emptyFilterState,
                        { opacity: 0, y: 12 },
                        { opacity: 1, y: 0, duration: 0.32, ease: "power2.out" }
                    );
                }
            }
            return;
        }

        if (emptyFilterState) {
            emptyFilterState.setAttribute("hidden", "hidden");
        }

        const isCurrentActiveStillVisible = newlyVisibleButtons.some(
            (btn) => btn.getAttribute("data-target") === activeTargetId
        );

        if (!isCurrentActiveStillVisible && newlyVisibleButtons.length > 0) {
            const firstTarget = newlyVisibleButtons[0].getAttribute("data-target");
            activateFacility(firstTarget, {
                animate: true,
                updateHash: false,
                centerStrip: true
            });
        }
    }

    function resetAllFilters() {
        searchQuery = "";
        activeCategory = "all";

        if (searchInput) {
            searchInput.value = "";
        }

        categoryPills.forEach((pill) => {
            const isAll = pill.getAttribute("data-category") === "all";
            pill.classList.toggle("active", isAll);
            pill.setAttribute("aria-pressed", isAll ? "true" : "false");
        });

        applyFilterAndSearch();
    }

    // Event listeners untuk klik tab fasilitas
    facilityButtons.forEach((btn) => {
        btn.addEventListener("click", () => {
            const targetId = btn.getAttribute("data-target");
            if (targetId === activeTargetId) return;

            activateFacility(targetId, {
                animate: true,
                updateHash: true,
                centerStrip: true
            });
        });

        // Keyboard navigation (ArrowUp, ArrowDown, ArrowLeft, ArrowRight, Home, End)
        btn.addEventListener("keydown", (e) => {
            const visibleBtns = getVisibleButtons();
            const currentIndex = visibleBtns.indexOf(btn);
            if (currentIndex === -1) return;

            let nextIndex = null;

            if (e.key === "ArrowDown" || e.key === "ArrowRight") {
                e.preventDefault();
                nextIndex = (currentIndex + 1) % visibleBtns.length;
            } else if (e.key === "ArrowUp" || e.key === "ArrowLeft") {
                e.preventDefault();
                nextIndex = (currentIndex - 1 + visibleBtns.length) % visibleBtns.length;
            } else if (e.key === "Home") {
                e.preventDefault();
                nextIndex = 0;
            } else if (e.key === "End") {
                e.preventDefault();
                nextIndex = visibleBtns.length - 1;
            }

            if (nextIndex !== null && visibleBtns[nextIndex]) {
                const nextBtn = visibleBtns[nextIndex];
                nextBtn.focus();
                const targetId = nextBtn.getAttribute("data-target");
                activateFacility(targetId, {
                    animate: true,
                    updateHash: true,
                    centerStrip: true
                });
            }
        });
    });

    // Event listener untuk pencarian langsung
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            searchQuery = e.target.value || "";
            applyFilterAndSearch();
        });

        searchInput.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && searchInput.value !== "") {
                e.preventDefault();
                searchInput.value = "";
                searchQuery = "";
                applyFilterAndSearch();
            }
        });
    }

    if (searchClearBtn) {
        searchClearBtn.addEventListener("click", () => {
            if (searchInput) {
                searchInput.value = "";
                searchInput.focus();
            }
            searchQuery = "";
            applyFilterAndSearch();
        });
    }

    // Event listener untuk pill kategori
    categoryPills.forEach((pill) => {
        pill.addEventListener("click", () => {
            const selectedCat = pill.getAttribute("data-category") || "all";
            if (selectedCat === activeCategory) return;

            activeCategory = selectedCat;
            categoryPills.forEach((p) => {
                const isSelected = p === pill;
                p.classList.toggle("active", isSelected);
                p.setAttribute("aria-pressed", isSelected ? "true" : "false");
            });

            applyFilterAndSearch();
        });
    });

    if (resetFilterBtn) {
        resetFilterBtn.addEventListener("click", () => {
            resetAllFilters();
            if (searchInput) {
                searchInput.focus();
            }
        });
    }

    // Penanganan deep-link hash URL (misal #facility-icu dari halaman Beranda)
    const hash = window.location.hash;
    let initialTarget = null;

    if (hash && hash.length > 1) {
        const hashId = decodeURIComponent(hash.substring(1));
        const matchingBtn = facilityButtons.find(
            (btn) => btn.getAttribute("data-target") === hashId
        );
        if (matchingBtn) {
            initialTarget = hashId;
        }
    }

    if (initialTarget) {
        activateFacility(initialTarget, {
            animate: false,
            updateHash: false,
            centerStrip: true
        });

        setTimeout(() => {
            if (detailStage) {
                const offset = 110;
                const y = detailStage.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({
                    top: y,
                    behavior: prefersReducedMotion ? "auto" : "smooth"
                });
            }
        }, 120);
    } else {
        const firstTargetId = facilityButtons[0].getAttribute("data-target");
        activateFacility(firstTargetId, {
            animate: false,
            updateHash: false,
            centerStrip: false
        });
    }

    // Koreografi GSAP ScrollTrigger saat halaman dimuat
    if (!prefersReducedMotion) {
        if (discoveryBar) {
            gsap.from(discoveryBar, {
                y: 22,
                opacity: 0,
                duration: 0.55,
                ease: "power2.out",
                clearProps: "all"
            });
        }

        if (sidebarNav && detailStage) {
            ScrollTrigger.create({
                trigger: "#facilities-workspace",
                start: "top 88%",
                once: true,
                onEnter: () => {
                    gsap.from(facilityButtons.slice(0, 10), {
                        y: 16,
                        opacity: 0,
                        duration: 0.42,
                        stagger: 0.045,
                        ease: "power2.out",
                        clearProps: "transform,opacity"
                    });

                    gsap.from(detailStage, {
                        y: 24,
                        opacity: 0,
                        duration: 0.58,
                        delay: 0.08,
                        ease: "power2.out",
                        clearProps: "transform,opacity"
                    });
                }
            });
        }
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initFacilitiesShowcase);
} else {
    initFacilitiesShowcase();
}
