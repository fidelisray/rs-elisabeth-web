import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

function initElisamedsMockups() {
    const stage = document.querySelector(".elisameds-3d-stage");
    if (!stage) return;

    const cards = Array.from(stage.querySelectorAll(".elisameds-stage-card"));
    const dots = Array.from(document.querySelectorAll(".elisameds-dot"));
    const infoContainer = document.getElementById("elisameds-feature-info");
    const titleEl = infoContainer ? infoContainer.querySelector(".elisameds-info-title") : null;
    const descEl = infoContainer ? infoContainer.querySelector(".elisameds-info-desc") : null;

    if (cards.length === 0) return;

    let activeIndex = 0; // Default kartu pembuka pada Index 0: Layanan Digital Terpadu (elisameds-2)
    let isAnimating = false;
    let autoPlayTimer = null;
    let isPaused = false;
    const autoPlayInterval = 4500;

    // Menghitung posisi siklis (-1 = kiri, 0 = tengah, 1 = kanan, 2/-2 = belakang)
    function getCyclicOffset(cardIndex, active, total) {
        let diff = (cardIndex - active) % total;
        if (diff < -total / 2) diff += total;
        if (diff > total / 2) diff -= total;
        return diff;
    }

    const mm = gsap.matchMedia();

    // 1. Breakpoint Mobile & Tablet (< 992px): Centered Coverflow Slider (4 Kartu)
    mm.add("(max-width: 991.98px)", () => {
        function getPeekDistance() {
            return window.innerWidth < 375 ? 72 : 85;
        }

        function updateCards(instant = false) {
            isAnimating = true;
            const peekX = getPeekDistance();

            cards.forEach((card, idx) => {
                const offset = getCyclicOffset(idx, activeIndex, cards.length);

                let x = 0;
                let scale = 1;
                let opacity = 1;
                let zIndex = 20;

                if (offset === 0) {
                    // Kartu Utama (Tepat di Tengah Halaman)
                    x = 0;
                    scale = 1;
                    opacity = 1;
                    zIndex = 20;
                    card.classList.add("is-active");
                } else if (offset === -1) {
                    // Sayap Kiri (Mengintip Simetris di Kiri)
                    x = -peekX;
                    scale = 0.84;
                    opacity = 0.55;
                    zIndex = 10;
                    card.classList.remove("is-active");
                } else if (offset === 1) {
                    // Sayap Kanan (Mengintip Simetris di Kanan)
                    x = peekX;
                    scale = 0.84;
                    opacity = 0.55;
                    zIndex = 10;
                    card.classList.remove("is-active");
                } else {
                    // Kartu Belakang (Siap meluncur pada putaran berikutnya)
                    x = 0;
                    scale = 0.7;
                    opacity = 0;
                    zIndex = 1;
                    card.classList.remove("is-active");
                }

                if (instant) {
                    gsap.set(card, {
                        xPercent: -50,
                        yPercent: -50,
                        x,
                        scale,
                        opacity,
                        zIndex
                    });
                } else {
                    gsap.to(card, {
                        xPercent: -50,
                        yPercent: -50,
                        x,
                        scale,
                        opacity,
                        zIndex,
                        duration: 0.5,
                        ease: "power2.out",
                        onComplete: () => {
                            isAnimating = false;
                        }
                    });
                }
            });

            // Update Teks Judul & Deskripsi Dinamis
            const activeCard = cards[activeIndex];
            const title = activeCard ? activeCard.getAttribute("data-feature-title") : "";
            const desc = activeCard ? activeCard.getAttribute("data-feature-desc") : "";

            if (infoContainer && titleEl && descEl) {
                if (instant) {
                    titleEl.textContent = title || "";
                    descEl.textContent = desc || "";
                    gsap.set(infoContainer, { opacity: 1, y: 0 });
                } else {
                    gsap.to(infoContainer, {
                        opacity: 0,
                        y: -5,
                        duration: 0.16,
                        ease: "power1.in",
                        onComplete: () => {
                            titleEl.textContent = title || "";
                            descEl.textContent = desc || "";
                            gsap.to(infoContainer, {
                                opacity: 1,
                                y: 0,
                                duration: 0.26,
                                ease: "power1.out"
                            });
                        }
                    });
                }
            }

            // Update status tombol indikator dot
            dots.forEach((dot, dIdx) => {
                dot.classList.toggle("active", dIdx === activeIndex);
            });
        }

        function goToSlide(index) {
            if (index === activeIndex) return;
            activeIndex = index;
            updateCards(false);
            resetAutoPlay();
        }

        // Auto-play management
        function startAutoPlay() {
            stopAutoPlay();
            autoPlayTimer = setInterval(() => {
                if (!isPaused && !isAnimating) {
                    activeIndex = (activeIndex + 1) % cards.length;
                    updateCards(false);
                }
            }, autoPlayInterval);
        }

        function stopAutoPlay() {
            if (autoPlayTimer) {
                clearInterval(autoPlayTimer);
                autoPlayTimer = null;
            }
        }

        function resetAutoPlay() {
            stopAutoPlay();
            startAutoPlay();
        }

        function pauseAutoPlay() {
            isPaused = true;
        }

        function resumeAutoPlay() {
            isPaused = false;
            resetAutoPlay();
        }

        // Posisi awal Coverflow
        updateCards(true);

        // Entrance animation via ScrollTrigger
        ScrollTrigger.create({
            trigger: stage,
            start: "top 80%",
            once: true,
            onEnter: () => {
                gsap.from(cards, {
                    y: 60,
                    opacity: 0,
                    scale: 0.7,
                    stagger: 0.12,
                    duration: 0.8,
                    ease: "back.out(1.1)",
                    onComplete: () => {
                        updateCards(false);
                        startAutoPlay();
                    }
                });
            }
        });

        // Klik pada kartu untuk berpindah ke tengah
        cards.forEach((card, idx) => {
            card.addEventListener("click", () => {
                goToSlide(idx);
            });
        });

        // Klik pada pagination dots
        dots.forEach((dot) => {
            dot.addEventListener("click", () => {
                const targetIdx = parseInt(dot.getAttribute("data-target-index"), 10);
                if (!isNaN(targetIdx)) {
                    goToSlide(targetIdx);
                }
            });
        });

        // Gestur Sentuh: Swipe Horizontal & Pause saat disentuh
        let touchStartX = 0;
        let touchEndX = 0;
        let touchStartY = 0;
        let touchEndY = 0;

        stage.addEventListener("touchstart", (e) => {
            pauseAutoPlay();
            touchStartX = e.changedTouches[0].clientX;
            touchStartY = e.changedTouches[0].clientY;
        }, { passive: true });

        stage.addEventListener("touchend", (e) => {
            touchEndX = e.changedTouches[0].clientX;
            touchEndY = e.changedTouches[0].clientY;
            handleSwipe();
            resumeAutoPlay();
        }, { passive: true });

        // Hover pause pada desktop / pointer devices
        stage.addEventListener("mouseenter", pauseAutoPlay);
        stage.addEventListener("mouseleave", resumeAutoPlay);
        if (infoContainer) {
            infoContainer.addEventListener("mouseenter", pauseAutoPlay);
            infoContainer.addEventListener("mouseleave", resumeAutoPlay);
        }

        function handleSwipe() {
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;

            // Pastikan pergeseran dominan horizontal dan melebihi threshold 35px
            if (Math.abs(deltaX) > 35 && Math.abs(deltaX) > Math.abs(deltaY)) {
                if (deltaX < 0) {
                    // Geser ke kiri: fitur berikutnya
                    goToSlide((activeIndex + 1) % cards.length);
                } else {
                    // Geser ke kanan: fitur sebelumnya
                    goToSlide((activeIndex - 1 + cards.length) % cards.length);
                }
            }
        }

        // Jalankan auto-play jika panggung sudah di viewport
        startAutoPlay();

        return () => {
            stopAutoPlay();
        };
    });

    // 2. Breakpoint Desktop (>= 992px): 3 Kolom Alami + Micro-Interactions
    mm.add("(min-width: 992px)", () => {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }

        // Filter kartu yang aktif di desktop (mengabaikan kartu khusus mobile .d-lg-none)
        const desktopCards = cards.filter((card) => !card.classList.contains("d-lg-none"));

        // Reset seluruh inline transform agar layout desktop murni dan tidak terpengaruh
        cards.forEach((card) => {
            gsap.set(card, {
                clearProps: "all"
            });
            card.classList.remove("is-active");
        });

        // Staggered reveal saat masuk viewport
        ScrollTrigger.create({
            trigger: stage,
            start: "top 75%",
            once: true,
            onEnter: () => {
                gsap.from(desktopCards, {
                    y: 50,
                    opacity: 0,
                    scale: 0.9,
                    duration: 0.8,
                    stagger: 0.18,
                    ease: "power2.out"
                });
            }
        });

        // Micro-interaction 3D Hover Depth pada desktop
        desktopCards.forEach((card) => {
            card.addEventListener("mouseenter", () => {
                gsap.to(card, {
                    y: -10,
                    scale: 1.03,
                    duration: 0.35,
                    ease: "power2.out"
                });
            });
            card.addEventListener("mouseleave", () => {
                gsap.to(card, {
                    y: 0,
                    scale: 1,
                    duration: 0.35,
                    ease: "power2.out"
                });
            });
        });
    });

    // 3. Preferensi Aksesibilitas: prefers-reduced-motion
    mm.add("(prefers-reduced-motion: reduce)", () => {
        if (autoPlayTimer) {
            clearInterval(autoPlayTimer);
            autoPlayTimer = null;
        }
        cards.forEach((card) => {
            gsap.set(card, { clearProps: "all" });
        });
    });
}

// Inisialisasi saat DOM siap
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initElisamedsMockups);
} else {
    initElisamedsMockups();
}
