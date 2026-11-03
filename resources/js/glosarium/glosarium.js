import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

document.addEventListener("DOMContentLoaded", () => {
    const groupCards = Array.from(document.querySelectorAll(".glossary-group-card"));
    if (groupCards.length === 0) {
        return;
    }

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const prefixPills = Array.from(document.querySelectorAll(".glossary-prefix-pill"));
    const filterInput = document.getElementById("glossaryLocalFilter");
    const filterClearBtn = document.getElementById("glossaryFilterClear");
    const filterResetBtn = document.getElementById("glossaryResetFilterBtn");
    const filterEmptyCard = document.getElementById("glossaryFilterEmpty");
    const filterKeywordEl = document.getElementById("glossaryFilterKeyword");
    const visibleCountEl = document.getElementById("glossaryVisibleCount");
    const visibleGroupsEl = document.getElementById("glossaryVisibleGroups");
    const stickySidebar = document.querySelector(".glossary-sidebar-sticky");
    const mainNavbar = document.getElementById("main-navbar");

    let highlightTimeout = null;
    let isProgrammaticScroll = false;

    if (stickySidebar && mainNavbar) {
        const syncStickyOffset = () => {
            const isNavbarHidden = mainNavbar.classList.contains("navbar-hidden");
            stickySidebar.classList.toggle("is-navbar-visible", !isNavbarHidden);
        };

        syncStickyOffset();

        const navbarObserver = new MutationObserver(syncStickyOffset);
        navbarObserver.observe(mainNavbar, {
            attributes: true,
            attributeFilter: ["class"],
        });
    }

    function setActivePrefix(targetId) {
        prefixPills.forEach((pill) => {
            const isMatch = pill.dataset.target === targetId;
            pill.classList.toggle("active", isMatch);

            if (isMatch) {
                const track = pill.closest(".mobile-prefix-track, .glossary-prefix-grid");
                if (track) {
                    const pillRect = pill.getBoundingClientRect();
                    const trackRect = track.getBoundingClientRect();
                    if (pillRect.left < trackRect.left || pillRect.right > trackRect.right) {
                        track.scrollTo({
                            left: pill.offsetLeft - track.clientWidth / 2 + pill.clientWidth / 2,
                            behavior: "smooth",
                        });
                    }
                }
            }
        });
    }

    if (!prefersReducedMotion) {
        const headerEl = document.querySelector(".glossary-directory-header");
        const sideCards = document.querySelectorAll(".glossary-side-card, .glossary-side-cta-stack .cta-card");

        if (headerEl) {
            gsap.fromTo(
                headerEl,
                { opacity: 0, y: 18 },
                { opacity: 1, y: 0, duration: 0.5, ease: "power3.out", clearProps: "transform,opacity" }
            );
        }

        if (sideCards.length > 0) {
            gsap.fromTo(
                sideCards,
                { opacity: 0, x: 18 },
                {
                    opacity: 1,
                    x: 0,
                    duration: 0.5,
                    stagger: 0.08,
                    ease: "power3.out",
                    clearProps: "transform,opacity",
                }
            );
        }

        groupCards.forEach((card) => {
            const termItems = card.querySelectorAll(".glossary-term-card");

            gsap.fromTo(
                card,
                { opacity: 0, y: 28 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.5,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: card,
                        start: "top 88%",
                        toggleActions: "play none none reset",
                    },
                }
            );

            if (termItems.length > 0) {
                gsap.fromTo(
                    termItems,
                    { opacity: 0, y: 14 },
                    {
                        opacity: 1,
                        y: 0,
                        duration: 0.36,
                        stagger: 0.018,
                        ease: "power2.out",
                        scrollTrigger: {
                            trigger: card,
                            start: "top 86%",
                            toggleActions: "play none none reset",
                        },
                    }
                );
            }
        });
    }

    groupCards.forEach((card) => {
        ScrollTrigger.create({
            trigger: card,
            start: "top 35%",
            end: "bottom 35%",
            onEnter: () => {
                if (!isProgrammaticScroll) {
                    setActivePrefix(card.id);
                }
            },
            onEnterBack: () => {
                if (!isProgrammaticScroll) {
                    setActivePrefix(card.id);
                }
            },
        });
    });

    prefixPills.forEach((pill) => {
        pill.addEventListener("click", () => {
            const targetId = pill.dataset.target;
            const targetCard = document.getElementById(targetId);
            if (!targetCard || targetCard.style.display === "none") {
                return;
            }

            isProgrammaticScroll = true;
            setActivePrefix(targetId);

            const isMobile = window.innerWidth < 992;
            const offset = isMobile ? 132 : 95;
            const targetTop = targetCard.getBoundingClientRect().top + window.scrollY - offset;

            window.scrollTo({
                top: Math.max(0, targetTop),
                behavior: prefersReducedMotion ? "auto" : "smooth",
            });

            groupCards.forEach((c) => c.classList.remove("is-highlighted"));
            targetCard.classList.add("is-highlighted");

            if (!prefersReducedMotion) {
                gsap.fromTo(
                    targetCard,
                    { scale: 0.99 },
                    { scale: 1, duration: 0.45, ease: "back.out(1.7)" }
                );
            }

            clearTimeout(highlightTimeout);
            highlightTimeout = setTimeout(() => {
                targetCard.classList.remove("is-highlighted");
                isProgrammaticScroll = false;
            }, 900);
        });
    });

    function applyLocalFilter(rawQuery) {
        const query = rawQuery.trim().toLowerCase();
        let totalVisibleTerms = 0;
        let totalVisibleGroups = 0;
        const newlyVisibleCards = [];

        if (filterClearBtn) {
            filterClearBtn.style.display = query.length > 0 ? "inline-flex" : "none";
        }

        groupCards.forEach((groupCard) => {
            const termCards = Array.from(groupCard.querySelectorAll(".glossary-term-card"));
            const groupCountEl = groupCard.querySelector(".group-visible-count");
            let visibleInGroup = 0;

            termCards.forEach((termCard) => {
                const termText = termCard.dataset.term || "";
                const isMatch = query === "" || termText.includes(query);

                if (isMatch) {
                    termCard.style.display = "";
                    visibleInGroup++;
                    totalVisibleTerms++;
                    newlyVisibleCards.push(termCard);
                } else {
                    termCard.style.display = "none";
                }
            });

            if (groupCountEl) {
                groupCountEl.textContent = visibleInGroup;
            }

            const hasVisible = visibleInGroup > 0;
            groupCard.style.display = hasVisible ? "" : "none";
            if (hasVisible) {
                totalVisibleGroups++;
            }

            prefixPills.forEach((pill) => {
                if (pill.dataset.target === groupCard.id) {
                    pill.style.display = hasVisible ? "" : "none";
                }
            });
        });

        if (visibleCountEl) {
            visibleCountEl.textContent = totalVisibleTerms;
        }
        if (visibleGroupsEl) {
            visibleGroupsEl.textContent = totalVisibleGroups;
        }

        if (filterEmptyCard) {
            const isEmpty = totalVisibleTerms === 0 && query.length > 0;
            filterEmptyCard.style.display = isEmpty ? "block" : "none";
            if (isEmpty && filterKeywordEl) {
                filterKeywordEl.textContent = rawQuery.trim();
            }
        }

        const firstVisibleGroup = groupCards.find((card) => card.style.display !== "none");
        if (firstVisibleGroup) {
            setActivePrefix(firstVisibleGroup.id);
        }

        if (!prefersReducedMotion && newlyVisibleCards.length > 0 && newlyVisibleCards.length <= 80) {
            gsap.fromTo(
                newlyVisibleCards,
                { opacity: 0.35, y: 6 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.24,
                    stagger: 0.008,
                    ease: "power2.out",
                    overwrite: "auto",
                }
            );
        }

        ScrollTrigger.refresh();
    }

    if (filterInput) {
        filterInput.addEventListener("input", (e) => {
            applyLocalFilter(e.target.value);
        });
    }

    if (filterClearBtn && filterInput) {
        filterClearBtn.addEventListener("click", () => {
            filterInput.value = "";
            applyLocalFilter("");
            filterInput.focus();
        });
    }

    if (filterResetBtn && filterInput) {
        filterResetBtn.addEventListener("click", () => {
            filterInput.value = "";
            applyLocalFilter("");
            filterInput.focus();
        });
    }
});
