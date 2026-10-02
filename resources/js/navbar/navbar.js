/**
 * navbar.js — Revisi Penuh
 *
 * Bertanggung jawab atas:
 * 1. Sticky scroll-shrink: menambahkan class .is-stuck pada #main-navbar
 *    ketika halaman discroll ke bawah (menggunakan IntersectionObserver).
 * 2. Hover dropdown: mengubah trigger .nav-tentang-kami dari klik → hover
 *    dengan delay menutup agar user bisa memindahkan kursor ke dalam menu.
 */

(function () {
    "use strict";

    // ─── Konfigurasi ───────────────────────────────────────────────────────
    const CLOSE_DELAY_MS = 280;

    // ─── 1. Sticky Shrink via IntersectionObserver ─────────────────────────
    function initStickyNavbar() {
        const navbar = document.getElementById("main-navbar");
        if (!navbar) return;

        // Sentinel: elemen 1px tepat setelah top-bar.
        // Saat sentinel keluar viewport (scroll ke bawah), navbar "menempel".
        // PENTING: sentinel harus berada di normal flow (bukan position:absolute),
        // supaya posisinya benar-benar setelah top-bar. Sebelumnya absolute membuat
        // sentinel berada di pojok atas dokumen sehingga .is-stuck aktif terlalu dini
        // (navbar mengecil padahal belum benar-benar menempel di atas).
        const sentinel = document.createElement("div");
        sentinel.id = "navbar-scroll-sentinel";
        sentinel.style.cssText = "position:relative;width:100%;height:1px;pointer-events:none;";
        document.body.insertBefore(sentinel, navbar);

        const observer = new IntersectionObserver(
            ([entry]) => {
                navbar.classList.toggle("is-stuck", !entry.isIntersecting);
            },
            { threshold: 0 }
        );

        observer.observe(sentinel);
    }

    // ─── 2. Hover Dropdown ──────────────────────────────────────────────────

    function disableBootstrapClickToggle(navItem) {
        const toggle = navItem.querySelector(".nav-link.dropdown-toggle");
        if (!toggle) return;
        // Hapus atribut agar Bootstrap tidak ikut men-toggle dropdown
        // (konflik dengan sistem hover/klik kustom di bawah).
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

        // Deteksi perangkat tanpa hover (touch). Pada perangkat tersebut dropdown
        // dikelola via klik, bukan hover, supaya tidak terbuka/tertutup secara tidak stabil.
        const canHover = window.matchMedia("(hover: hover)").matches;

        if (canHover) {
            navItem.addEventListener("mouseenter", openDropdown);
            navItem.addEventListener("mouseleave", scheduleClose);
            // Link "Tentang Kami" memakai href="#" hanya sebagai placeholder;
            // cegah lompat ke atas saat diklik agar tidak mengganggu UX.
            navItem.addEventListener("click", (e) => {
                if (navItem.querySelector(".nav-link.dropdown-toggle").getAttribute("href") === "#") {
                    e.preventDefault();
                }
            });
        } else {
            navItem.addEventListener("click", (e) => {
                if (navItem.classList.contains("hover-open")) {
                    scheduleClose();
                } else {
                    openDropdown();
                }
                e.preventDefault();
            });
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

    // ─── 3. Staggered Offcanvas Menu Items ──────────────────────────────────
    // Setiap item menu offcanvas slide-in berurutan saat offcanvas dibuka.
    function initOffcanvasAnimation() {
        const offcanvas = document.getElementById("mobileOffcanvas");
        if (!offcanvas) return;

        offcanvas.addEventListener("show.bs.offcanvas", () => {
            const items = offcanvas.querySelectorAll(".offcanvas-nav-link");
            items.forEach((item, i) => {
                item.style.opacity = "0";
                item.style.transform = "translateX(20px)";
                item.style.transition = `opacity 0.3s ease ${i * 0.06}s, transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) ${i * 0.06}s`;
            });
        });

        offcanvas.addEventListener("shown.bs.offcanvas", () => {
            const items = offcanvas.querySelectorAll(".offcanvas-nav-link");
            items.forEach((item) => {
                item.style.opacity = "1";
                item.style.transform = "translateX(0)";
            });
        });

        offcanvas.addEventListener("hide.bs.offcanvas", () => {
            const items = offcanvas.querySelectorAll(".offcanvas-nav-link");
            items.forEach((item) => {
                item.style.opacity = "0";
                item.style.transform = "translateX(20px)";
                item.style.transition = "none";
            });
        });
    }

    // ─── 4. Search Modal — Auto-focus + Cleanup Backdrop ─────────────────────
    function initSearchModal() {
        const searchModal = document.getElementById("searchModal");
        if (!searchModal) return;

        searchModal.addEventListener("shown.bs.modal", () => {
            const input = searchModal.querySelector(".search-input");
            if (input) input.focus();
        });

        // Fix: sisa .modal-backdrop dari modal yang tidak sempat dibersihkan
        // dapat membuat layar tertutup blur (bug "layar gelap tidak bisa di-scroll").
        // Bersihkan backdrop orphan setelah modal benar-benar tertutup.
        searchModal.addEventListener("hidden.bs.modal", () => {
            document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());
        });
    }

    // ─── 5. Navbar Padding — Hilangkan offset dari class .py-2 ───────────────
    // #main-navbar memakai .py-2 (Bootstrap) yang bertabrakan dengan
    // padding-top/padding-bottom kustom di navbar-dropdown.css, sehingga
    // animasi shrink (is-stuck) tidak berjalan mulus. Kita override langsung
    // via inline style agar konsisten di semua breakpoint.
    function normalizeNavbarPadding() {
        const navbar = document.getElementById("main-navbar");
        if (!navbar) return;
        navbar.style.paddingTop = "";
        navbar.style.paddingBottom = "";
        navbar.classList.remove("py-2");
    }

    // ─── Init ───────────────────────────────────────────────────────────────
    function init() {
        normalizeNavbarPadding();
        initStickyNavbar();
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