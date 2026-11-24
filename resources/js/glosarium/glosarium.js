import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { initGlossarySearchBoxes } from "./glossary-search";

gsap.registerPlugin(ScrollTrigger);

document.addEventListener("DOMContentLoaded", () => {
    initGlossarySearchBoxes();

    const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // Strip internal attribution parameter (?src=...) from the browser URL bar after server tracking.
    const detailShell = document.querySelector(".glossary-detail-shell");
    if (detailShell && window.history && typeof window.history.replaceState === "function") {
        try {
            const currentUrl = new URL(window.location.href);
            if (currentUrl.searchParams.has("src")) {
                currentUrl.searchParams.delete("src");
                const cleanQuery = currentUrl.searchParams.toString();
                const cleanUrl = currentUrl.pathname + (cleanQuery ? `?${cleanQuery}` : "") + currentUrl.hash;
                window.history.replaceState(window.history.state || {}, document.title, cleanUrl);
            }
        } catch (err) {
            // Fallback silently if URL constructor fails.
        }
    }

    if (detailShell && !prefersReducedMotion) {
        const revealBlocks = Array.from(detailShell.querySelectorAll(".gsap-detail-reveal"));
        const paragraphs = Array.from(detailShell.querySelectorAll(".gsap-detail-paragraph"));
        const relatedCard = detailShell.querySelector(".glossary-related-card");
        const relatedItems = Array.from(detailShell.querySelectorAll(".gsap-related-item"));

        revealBlocks.forEach((block) => {
            gsap.fromTo(
                block,
                { opacity: 0, y: 28 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.55,
                    ease: "power3.out",
                    scrollTrigger: {
                        trigger: block,
                        start: "top 88%",
                        toggleActions: "play none none reset",
                    },
                }
            );
        });

        paragraphs.forEach((para, idx) => {
            gsap.fromTo(
                para,
                { opacity: 0, y: 18 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.45,
                    delay: Math.min(idx * 0.05, 0.25),
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: para,
                        start: "top 90%",
                        toggleActions: "play none none reset",
                    },
                }
            );
        });

        if (relatedCard && relatedItems.length > 0) {
            gsap.fromTo(
                relatedItems,
                { opacity: 0, y: 14 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.36,
                    stagger: 0.03,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: relatedCard,
                        start: "top 85%",
                        toggleActions: "play none none reset",
                    },
                }
            );
        }
    }

    // Interactive Quick-Preview Strip & entrance animation on Explore landing page.
    const previewContainer = document.getElementById("popularTermPreview");
    const previewTitleEl = document.getElementById("popularPreviewTitle");
    const previewExcerptEl = document.getElementById("popularPreviewExcerpt");
    const previewLinkEl = document.getElementById("popularPreviewLink");
    const popularPills = Array.from(document.querySelectorAll(".term-pill[data-term-excerpt]"));

    if (previewContainer && popularPills.length > 0) {
        const updatePopularPreview = (pill) => {
            const title = pill.dataset.termTitle || "";
            const excerpt = pill.dataset.termExcerpt || "";
            const url = pill.dataset.termUrl || pill.getAttribute("href") || "#";

            if (!title) {
                return;
            }

            popularPills.forEach((p) => p.classList.toggle("is-preview-active", p === pill));

            if (previewTitleEl) {
                previewTitleEl.textContent = title;
            }
            if (previewExcerptEl && excerpt) {
                previewExcerptEl.textContent = excerpt;
            }
            if (previewLinkEl) {
                previewLinkEl.setAttribute("href", url);
            }
        };

        popularPills.forEach((pill) => {
            pill.addEventListener("mouseenter", () => updatePopularPreview(pill));
            pill.addEventListener("focus", () => updatePopularPreview(pill));
        });
    }

    const exploreSection = document.getElementById("explore-services");
    if (exploreSection && !prefersReducedMotion) {
        const statCards = Array.from(exploreSection.querySelectorAll(".gsap-explore-stat"));
        const popularCard = exploreSection.querySelector(".gsap-explore-popular");
        const explorePills = Array.from(exploreSection.querySelectorAll(".gsap-explore-pill"));

        if (statCards.length > 0) {
            gsap.fromTo(
                statCards,
                { opacity: 0, y: 18 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.45,
                    stagger: 0.06,
                    ease: "power3.out",
                    clearProps: "transform,opacity",
                }
            );
        }

        if (popularCard) {
            gsap.fromTo(
                popularCard,
                { opacity: 0, y: 22 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.5,
                    delay: 0.1,
                    ease: "power3.out",
                    clearProps: "transform,opacity",
                }
            );
        }

        if (explorePills.length > 0) {
            gsap.fromTo(
                explorePills,
                { opacity: 0, y: 10 },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.34,
                    stagger: 0.022,
                    delay: 0.16,
                    ease: "power2.out",
                    clearProps: "transform,opacity",
                }
            );
        }
    }

    const groupCards = Array.from(document.querySelectorAll(".glossary-group-card"));
    if (groupCards.length === 0) {
        return;
    }

    const prefixPills = Array.from(document.querySelectorAll(".glossary-prefix-pill"));
    const filterInput = document.getElementById("glossaryLocalFilter");
    const filterClearBtn = document.getElementById("glossaryFilterClear");
    const filterResetBtn = document.getElementById("glossaryResetFilterBtn");
    const filterEmptyCard = document.getElementById("glossaryFilterEmpty");
    const filterKeywordEl = document.getElementById("glossaryFilterKeyword");
    const visibleCountEl = document.getElementById("glossaryVisibleCount");
    const visibleTermsLabelEl = document.getElementById("glossaryVisibleTermsLabel");
    const visibleGroupsEl = document.getElementById("glossaryVisibleGroups");
    const visibleGroupsLabelEl = document.getElementById("glossaryVisibleGroupsLabel");
    const sideGroupsCountEl = document.getElementById("glossarySideGroupsCount");
    const sideGroupsLabelEl = document.getElementById("glossarySideGroupsLabel");
    const relevantMatchesCard = document.querySelector(".relevant-matches-card");
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

    function syncCountLabel(labelEl, count) {
        if (!labelEl) {
            return;
        }
        const text = count === 1 ? labelEl.dataset.singular : labelEl.dataset.plural;
        if (text) {
            labelEl.textContent = text;
        }
    }

    function applyLocalFilter(rawQuery) {
        const query = rawQuery.trim().toLowerCase();
        let totalVisibleTerms = 0;
        let totalVisibleGroups = 0;
        const newlyVisibleCards = [];

        if (filterClearBtn) {
            filterClearBtn.style.display = query.length > 0 ? "inline-flex" : "none";
        }

        if (relevantMatchesCard) {
            relevantMatchesCard.style.display = query.length > 0 ? "none" : "";
        }

        groupCards.forEach((groupCard) => {
            const termCards = Array.from(groupCard.querySelectorAll(".glossary-term-card"));
            const groupCountEl = groupCard.querySelector(".group-visible-count");
            const groupLabelEl = groupCard.querySelector(".group-visible-label");
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
            syncCountLabel(groupLabelEl, visibleInGroup);

            const hasVisible = visibleInGroup > 0;
            groupCard.style.display = hasVisible ? "" : "none";
            if (hasVisible) {
                totalVisibleGroups++;
            }

            prefixPills.forEach((pill) => {
                if (pill.dataset.target === groupCard.id) {
                    pill.style.display = hasVisible ? "" : "none";
                    const pillCountEl = pill.querySelector(".prefix-count");
                    if (pillCountEl) {
                        pillCountEl.textContent = visibleInGroup;
                    }
                }
            });
        });

        if (visibleCountEl) {
            visibleCountEl.textContent = totalVisibleTerms;
        }
        syncCountLabel(visibleTermsLabelEl, totalVisibleTerms);

        if (visibleGroupsEl) {
            visibleGroupsEl.textContent = totalVisibleGroups;
        }
        syncCountLabel(visibleGroupsLabelEl, totalVisibleGroups);

        if (sideGroupsCountEl) {
            sideGroupsCountEl.textContent = totalVisibleGroups;
        }
        syncCountLabel(sideGroupsLabelEl, totalVisibleGroups);

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
