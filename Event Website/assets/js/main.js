// Frontend behavior and placeholders for future integrations

document.addEventListener('DOMContentLoaded', () => {
    // Note: Demo forms are no longer used - all forms are functional

    // 3D Tilt Effect for cards
    const tiltCards = document.querySelectorAll('[data-tilt]');
    tiltCards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
        });
    });

    // Reveal animations on scroll
    const revealElements = document.querySelectorAll('[data-reveal]');
    
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                // Also set inline styles for elements that need it
                if (entry.target.classList.contains('sd-title') || 
                    entry.target.classList.contains('sd-subtitle') ||
                    entry.target.classList.contains('sd-kicker')) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }

                // Count-up animation for stats
                if (entry.target.classList.contains('sd-stat-row')) {
                    const numbers = entry.target.querySelectorAll('.sd-stat-number[data-count-target]');
                    numbers.forEach(el => {
                        const target = parseInt(el.getAttribute('data-count-target') || '0', 10);
                        if (!Number.isFinite(target)) return;
                        let current = 0;
                        const duration = 1200;
                        const start = performance.now();

                        function step(now) {
                            const progress = Math.min((now - start) / duration, 1);
                            const eased = 1 - Math.pow(1 - progress, 3);
                            current = Math.round(target * eased);
                            el.textContent = current.toLocaleString();
                            if (progress < 1) {
                                requestAnimationFrame(step);
                            }
                        }

                        requestAnimationFrame(step);
                    });
                }

                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    revealElements.forEach(el => {
        revealObserver.observe(el);
    });

    // Bounce.js: apply bounce animations when elements with data-bounce enter viewport
    if (typeof Bounce !== 'undefined') {
        const bounceElements = document.querySelectorAll('[data-bounce]');
        const bounceObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const type = (el.getAttribute('data-bounce') || 'up').toLowerCase();
                const bounce = new Bounce();
                if (type === 'up') {
                    bounce.translate({ x: 0, y: 80 }).scale({ x: 1, y: 1 }).applyTo([el]);
                } else if (type === 'in') {
                    bounce.scale({ x: 0.3, y: 0.3 }).applyTo([el]);
                } else if (type === 'left') {
                    bounce.translate({ x: -60, y: 0 }).applyTo([el]);
                } else if (type === 'right') {
                    bounce.translate({ x: 60, y: 0 }).applyTo([el]);
                } else {
                    bounce.translate({ x: 0, y: 80 }).applyTo([el]);
                }
                bounceObserver.unobserve(el);
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
        bounceElements.forEach(el => bounceObserver.observe(el));
    }
});

