import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

function initElisamedsMockups() {
    const stage = document.querySelector(".elisameds-3d-stage");
    if (!stage) return;

    const cards = Array.from(stage.querySelectorAll(".elisameds-stage-card"));
    const pills = Array.from(document.querySelectorAll(".elisameds-nav-pill"));
    if (cards.length === 0) return;

    let activeIndex = 1; // Default sorotan tengah pada kartu ke-2
    let isAnimating = false;

    // Menghitung posisi siklis (-1 = kiri, 0 = tengah, 1 = kanan)
    function getCyclicOffset(cardIndex, active, total = 3) {
        let diff = (cardIndex - active) % total;
        if (diff < -1) diff += total;
        if (diff > 1) diff -= total;
        return diff;
    }

    const mm = gsap.matchMedia();

    // 1. Breakpoint Mobile & Tablet (< 992px)
    mm.add("(max-width: 991.98px)", () => {
        function updateCards(instant = false) {
            isAnimating = true;

            cards.forEach((card, idx) => {
                const offset = getCyclicOffset(idx, activeIndex, cards.length);

                let xPercent = 0;
                let yPercent = 0;
                let scale = 1;
                let rotateZ = 0;
                let opacity = 1;
                let zIndex = 10;
                let shadow = "0 20px 45px rgba(15, 23, 42, 0.22)";

                if (offset === 0) {
                    // Kartu Utama (Tengah Depan)
                    xPercent = 0;
                    yPercent = 0;
                    scale = 1;
                    rotateZ = 0;
                    opacity = 1;
                    zIndex = 20;
                    shadow = "0 28px 65px rgba(2, 97, 153, 0.35)";
                    card.classList.add("is-active");
                } else if (offset === -1) {
                    // Sayap Kiri (Merekah Miring)
                    xPercent = -44;
                    yPercent = 6;
                    scale = 0.82;
                    rotateZ = -6;
                    opacity = 0.78;
                    zIndex = 10;
                    shadow = "0 14px 30px rgba(15, 23, 42, 0.16)";
                    card.classList.remove("is-active");
                } else if (offset === 1) {
                    // Sayap Kanan (Merekah Miring)
                    xPercent = 44;
                    yPercent = 6;
                    scale = 0.82;
                    rotateZ = 6;
                    opacity = 0.78;
                    zIndex = 10;
                    shadow = "0 14px 30px rgba(15, 23, 42, 0.16)";
                    card.classList.remove("is-active");
                }

                if (instant) {
                    gsap.set(card, {
                        xPercent,
                        yPercent,
                        scale,
                        rotateZ,
                        opacity,
                        zIndex,
                        boxShadow: shadow
                    });
                } else {
                    gsap.to(card, {
                        xPercent,
                        yPercent,
                        scale,
                        rotateZ,
                        opacity,
                        zIndex,
                        boxShadow: shadow,
                        duration: 0.55,
                        ease: "power3.out",
                        onComplete: () => {
                            isAnimating = false;
                        }
                    });
                }
            });

            // Update status tombol indikator pill
            pills.forEach((pill, pIdx) => {
                pill.classList.toggle("active", pIdx === activeIndex);
            });
        }

        // Posisi awal
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
                    duration: 0.85,
                    ease: "back.out(1.2)",
                    onComplete: () => {
                        updateCards(false);
                    }
                });
            }
        });

        // Ambient Floating Levitation halus pada kartu aktif
        const floatAnim = gsap.to(stage.querySelector(".elisameds-stage-card.is-active") || cards[1], {
            y: "-=8",
            duration: 2.4,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut"
        });

        // Navigasi via klik pada kartu
        cards.forEach((card, idx) => {
            card.addEventListener("click", () => {
                if (idx !== activeIndex) {
                    activeIndex = idx;
                    updateCards(false);
                }
            });
        });

        // Navigasi via indikator segmented pill
        pills.forEach((pill) => {
            pill.addEventListener("click", () => {
                const targetIdx = parseInt(pill.getAttribute("data-target-index"), 10);
                if (!isNaN(targetIdx) && targetIdx !== activeIndex) {
                    activeIndex = targetIdx;
                    updateCards(false);
                }
            });
        });

        // Navigasi via Touch Swipe Gesture
        let touchStartX = 0;
        let touchEndX = 0;

        stage.addEventListener("touchstart", (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        stage.addEventListener("touchend", (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const threshold = 40;
            const deltaX = touchEndX - touchStartX;

            if (Math.abs(deltaX) > threshold) {
                if (deltaX < 0) {
                    // Swipe kiri: lanjut ke fitur berikutnya
                    activeIndex = (activeIndex + 1) % cards.length;
                } else {
                    // Swipe kanan: kembali ke fitur sebelumnya
                    activeIndex = (activeIndex - 1 + cards.length) % cards.length;
                }
                updateCards(false);
            }
        }

        return () => {
            floatAnim.kill();
        };
    });

    // 2. Breakpoint Desktop (>= 992px)
    mm.add("(min-width: 992px)", () => {
        // Reset properties agar layout 3 kolom desktop tetap 100% natural
        cards.forEach((card) => {
            gsap.set(card, {
                clearProps: "xPercent,yPercent,scale,rotateZ,opacity,zIndex,boxShadow"
            });
        });

        // Entrance staggered reveal
        ScrollTrigger.create({
            trigger: stage,
            start: "top 75%",
            once: true,
            onEnter: () => {
                gsap.from(cards, {
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
        cards.forEach((card) => {
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

