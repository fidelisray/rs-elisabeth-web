import { gsap } from "gsap";

/**
 * navbar.js
 *
 * Bertanggung jawab atas:
 * 1. Smart scroll auto-hide & smooth reveal: menyembunyikan navbar saat scroll ke bawah,
 *    dan memunculkan navbar kembali saat scroll ke atas, dengan elevasi glassmorphism halus.
 * 2. Hover dropdown: mengubah trigger .nav-tentang-kami dari klik ke hover
 *    dengan delay menutup agar user bisa memindahkan kursor ke dalam menu.
 * 3. Staggered animasi menu offcanvas mobile.
 * 4. Pembersihan modal backdrop pencarian.
 */

(function () {
    "use strict";

    // Konfigurasi
    const CLOSE_DELAY_MS = 280;

    // 1. Smart Scroll Auto-Hide & Smooth Reveal
    function initSmartScrollNavbar() {
        const navbar = document.getElementById("main-navbar");
        if (!navbar) return;

        let lastScrollY = Math.max(window.pageYOffset || document.documentElement.scrollTop, 0);
        let ticking = false;

        const SCROLL_TOP_THRESHOLD = 80;
        const DELTA_DOWN_THRESHOLD = 10;
        const DELTA_UP_THRESHOLD = 8;

        function isOverlayOpen() {
            return Boolean(
                document.querySelector("#mobileOffcanvas.show") ||
                document.querySelector(".modal.show")
            );
        }

        function isNearBottom(currentScrollY) {
            const scrollHeight = document.documentElement.scrollHeight;
            const clientHeight = window.innerHeight || document.documentElement.clientHeight;
            return (currentScrollY + clientHeight) >= (scrollHeight - 60);
        }

        function updateNavbar() {
            const currentScrollY = Math.max(window.pageYOffset || document.documentElement.scrollTop, 0);
            const delta = currentScrollY - lastScrollY;

            // Efek elevasi / shadow saat discroll
            navbar.classList.toggle("navbar-scrolled", currentScrollY > 15);

            // Jika menu offcanvas atau modal sedang terbuka, navbar tetap terlihat
            if (isOverlayOpen()) {
                navbar.classList.remove("navbar-hidden");
                navbar.classList.add("navbar-visible");
                lastScrollY = currentScrollY;
                ticking = false;
                return;
            }

            // Saat berada di area puncak halaman atau menyentuh dasar halaman (bottom-edge guard), navbar selalu tampil
            if (currentScrollY < SCROLL_TOP_THRESHOLD || isNearBottom(currentScrollY)) {
                navbar.classList.remove("navbar-hidden");
                navbar.classList.add("navbar-visible");
            } else {
                // Scroll ke bawah: sembunyikan navbar ke atas
                if (delta > DELTA_DOWN_THRESHOLD) {
                    navbar.classList.add("navbar-hidden");
                    navbar.classList.remove("navbar-visible");
                }
                // Scroll ke atas: munculkan navbar kembali
                else if (delta < -DELTA_UP_THRESHOLD) {
                    navbar.classList.remove("navbar-hidden");
                    navbar.classList.add("navbar-visible");
                }
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        window.addEventListener("scroll", () => {
            if (!ticking) {
                window.requestAnimationFrame(updateNavbar);
                ticking = true;
            }
        }, { passive: true });
    }

    // 2. Hover Dropdown
    function disableBootstrapClickToggle(navItem) {
        const toggle = navItem.querySelector(".nav-link.dropdown-toggle");
        if (!toggle) return;
        toggle.removeAttribute("data-bs-toggle");
        toggle.removeAttribute("data-bs-target");
    }

    function attachHoverDropdown(navItem) {
        let closeTimer = null;

        function openDropdown() {
            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }
            navItem.classList.add("hover-open");
        }

        function scheduleClose() {
            closeTimer = setTimeout(() => {
                navItem.classList.remove("hover-open");
                closeTimer = null;
            }, CLOSE_DELAY_MS);
        }

        const canHover = window.matchMedia("(hover: hover)").matches;

        if (canHover) {
            navItem.addEventListener("mouseenter", openDropdown);
            navItem.addEventListener("mouseleave", scheduleClose);
            
            const toggle = navItem.querySelector(".nav-link.dropdown-toggle");
            if (toggle) {
                toggle.addEventListener("click", (e) => {
                    if (toggle.getAttribute("href") === "#") {
                        e.preventDefault();
                    }
                });
            }
        } else {
            const toggle = navItem.querySelector(".nav-link.dropdown-toggle");
            if (toggle) {
                toggle.addEventListener("click", (e) => {
                    if (navItem.classList.contains("hover-open")) {
                        scheduleClose();
                        if (toggle.getAttribute("href") !== "#") return; 
                    } else {
                        openDropdown();
                    }
                    e.preventDefault();
                });
            }
            
            document.addEventListener("click", (e) => {
                if (!navItem.contains(e.target)) scheduleClose();
            });
        }
    }

    function initHoverDropdown() {
        const navTentangKami = document.querySelector(".nav-tentang-kami");
        if (!navTentangKami) return;
        disableBootstrapClickToggle(navTentangKami);
        attachHoverDropdown(navTentangKami);
    }

    // 3. Staggered Offcanvas Menu Items (GSAP Orchestration)
    function initOffcanvasAnimation() {
        const offcanvas = document.getElementById("mobileOffcanvas");
        if (!offcanvas) return;

        const navLinks = Array.from(offcanvas.querySelectorAll(".offcanvas-nav-link"));
        const ctaWrapper = offcanvas.querySelector(".offcanvas-cta-wrapper");
        const animatedElements = [...navLinks];
        if (ctaWrapper) {
            animatedElements.push(ctaWrapper);
        }

        // Set kondisi awal saat offcanvas tertutup
        gsap.set(animatedElements, {
            opacity: 0,
            x: 25
        });

        offcanvas.addEventListener("show.bs.offcanvas", () => {
            // Cek preferensi aksesibilitas pengguna
            if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
                gsap.set(animatedElements, { opacity: 1, x: 0 });
                return;
            }

            gsap.killTweensOf(animatedElements);
            gsap.fromTo(animatedElements, 
                {
                    opacity: 0,
                    x: 25
                },
                {
                    opacity: 1,
                    x: 0,
                    duration: 0.45,
                    stagger: 0.045,
                    delay: 0.15,
                    ease: "back.out(1.1)",
                    clearProps: "transform,opacity"
                }
            );
        });

        offcanvas.addEventListener("hide.bs.offcanvas", () => {
            gsap.killTweensOf(animatedElements);
            gsap.set(animatedElements, {
                opacity: 0,
                x: 25
            });
        });
    }

    // 4. Search Modal Auto-focus & Cleanup Backdrop
    function initSearchModal() {
        const searchModal = document.getElementById("searchModal");
        if (!searchModal) return;

        searchModal.addEventListener("shown.bs.modal", () => {
            const input = searchModal.querySelector(".search-input");
            if (input) input.focus();
        });

        searchModal.addEventListener("hidden.bs.modal", () => {
            document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());
        });
    }

    // 5. Navbar Padding Normalization
    function normalizeNavbarPadding() {
        const navbar = document.getElementById("main-navbar");
        if (!navbar) return;
        navbar.style.paddingTop = "";
        navbar.style.paddingBottom = "";
        navbar.classList.remove("py-2");
    }

    // Init
    function init() {
        normalizeNavbarPadding();
        initSmartScrollNavbar();
        initHoverDropdown();
        initOffcanvasAnimation();
        initSearchModal();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();