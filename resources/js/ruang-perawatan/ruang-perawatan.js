import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

/**
 * Ruang Perawatan Page Interactive Controller
 * Mengelola pencarian multi-atribut secara real-time, status counter kamar,
 * serta koreografi animasi GSAP ScrollTrigger.
 */
function initRuangPerawatanPage() {
    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const discoveryBar = document.getElementById("room-discovery-bar");
    const searchInput = document.getElementById("room-search-input");
    const searchClearBtn = document.getElementById("room-search-clear");
    const visibleCountEl = document.getElementById("room-visible-count");
    const roomCols = Array.from(document.querySelectorAll(".room-card-col"));
    const emptyFilterCard = document.getElementById("room-filter-empty");
    const resetSearchBtn = document.getElementById("btn-reset-room-search");
    const ctaCard = document.getElementById("room-cta-card");

    // 1. Koreografi GSAP ScrollTrigger saat halaman dimuat & di-scroll
    if (!prefersReducedMotion) {
        if (discoveryBar) {
            gsap.fromTo(
                discoveryBar,
                { opacity: 0, y: 20 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.5,
                    ease: "power3.out",
                    clearProps: "transform,opacity"
                }
            );
        }

        roomCols.forEach((col, idx) => {
            gsap.fromTo(
                col,
                { opacity: 0, y: 28 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.52,
                    delay: (idx % 3) * 0.07,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: col,
                        start: "top 88%",
                        toggleActions: "play none none reset"
                    }
                }
            );
        });

        if (ctaCard) {
            gsap.fromTo(
                ctaCard,
                { opacity: 0, y: 28 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.55,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: ctaCard,
                        start: "top 90%",
                        toggleActions: "play none none reset"
                    }
                }
            );
        }
    }

    if (roomCols.length === 0) return;

    // 2. Pencarian Multi-Atribut Real-Time (Nama, Spesifikasi, Deskripsi, & Tag Fasilitas)
    function applyRoomSearch(rawQuery) {
        const query = (rawQuery || "").trim().toLowerCase();
        let matchCount = 0;
        const newlyVisibleCols = [];

        if (searchClearBtn) {
            if (query.length > 0) {
                searchClearBtn.removeAttribute("hidden");
            } else {
                searchClearBtn.setAttribute("hidden", "hidden");
            }
        }

        roomCols.forEach((col) => {
            const nameText = (col.getAttribute("data-name") || "").toLowerCase();
            const specsText = (col.getAttribute("data-specs") || "").toLowerCase();
            const descText = (col.getAttribute("data-desc") || "").toLowerCase();
            const tagsText = (col.getAttribute("data-tags") || "").toLowerCase();

            const isMatch =
                query === "" ||
                nameText.includes(query) ||
                specsText.includes(query) ||
                descText.includes(query) ||
                tagsText.includes(query);

            if (isMatch) {
                col.removeAttribute("hidden");
                matchCount++;
                newlyVisibleCols.push(col);
            } else {
                col.setAttribute("hidden", "hidden");
            }
        });

        if (visibleCountEl) {
            visibleCountEl.textContent = String(matchCount);
        }

        if (emptyFilterCard) {
            if (matchCount === 0) {
                emptyFilterCard.removeAttribute("hidden");
                if (!prefersReducedMotion) {
                    gsap.fromTo(
                        emptyFilterCard,
                        { opacity: 0, y: 14 },
                        { opacity: 1, y: 0, duration: 0.32, ease: "power2.out" }
                    );
                }
            } else {
                emptyFilterCard.setAttribute("hidden", "hidden");
            }
        }

        if (!prefersReducedMotion && newlyVisibleCols.length > 0) {
            gsap.fromTo(
                newlyVisibleCols,
                { opacity: 0.35, y: 10 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.3,
                    stagger: 0.04,
                    ease: "power2.out",
                    overwrite: "auto"
                }
            );
        }

        ScrollTrigger.refresh();
    }

    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            applyRoomSearch(e.target.value);
        });

        searchInput.addEventListener("keydown", (e) => {
            if (e.key === "Escape" && searchInput.value !== "") {
                e.preventDefault();
                searchInput.value = "";
                applyRoomSearch("");
            }
        });
    }

    if (searchClearBtn && searchInput) {
        searchClearBtn.addEventListener("click", () => {
            searchInput.value = "";
            applyRoomSearch("");
            searchInput.focus();
        });
    }

    if (resetSearchBtn && searchInput) {
        resetSearchBtn.addEventListener("click", () => {
            searchInput.value = "";
            applyRoomSearch("");
            searchInput.focus();
        });
    }
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initRuangPerawatanPage);
} else {
    initRuangPerawatanPage();
}
