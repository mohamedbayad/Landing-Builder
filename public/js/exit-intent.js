/**
 * Exit Intent Runtime — Public pages
 *
 * Detects [data-exit-intent] elements and activates them based on their
 * data-trigger attribute:
 *   exit       → Mouse leaves viewport (desktop only)
 *   scroll-50  → User scrolls past 50%
 *   scroll-75  → User scrolls past 75%
 *   timer-10   → After 10 seconds on page
 *   timer-30   → After 30 seconds on page
 *
 * data-frequency controls how often the popup shows:
 *   once          → Once per session (sessionStorage)
 *   once-per-day  → Once per 24h (localStorage)
 *   always        → Every trigger
 */
(function () {
    'use strict';

    // Do not run frontend popup behavior inside GrapesJS canvas/editor mode.
    if (window.__GJS_EDITOR_MODE || document.documentElement.hasAttribute('data-gjs-editor-canvas')) {
        return;
    }

    const popups = document.querySelectorAll('[data-exit-intent]');
    if (!popups.length) return;

    popups.forEach(popup => {
        const trigger = popup.getAttribute('data-trigger') || 'exit';
        const frequency = popup.getAttribute('data-frequency') || 'once';
        const delayMs = parseInt(popup.getAttribute('data-delay-ms') || '0', 10);
        const popupId = 'exit-popup-' + (popup.id || Math.random().toString(36).substr(2, 6));

        // Hide initially
        popup.style.display = 'none';
        popup.style.position = 'fixed';
        popup.style.top = '0';
        popup.style.left = '0';
        popup.style.width = '100%';
        popup.style.height = '100%';
        popup.style.zIndex = '99998';

        // --- Frequency guard ---
        function shouldShow() {
            if (frequency === 'always') return true;
            if (frequency === 'once') {
                return !sessionStorage.getItem(popupId);
            }
            if (frequency === 'once-per-day') {
                const last = localStorage.getItem(popupId);
                if (!last) return true;
                return Date.now() - parseInt(last, 10) > 86400000;
            }
            return true;
        }

        function markShown() {
            if (frequency === 'once') {
                sessionStorage.setItem(popupId, '1');
            } else if (frequency === 'once-per-day') {
                localStorage.setItem(popupId, Date.now().toString());
            }
        }

        function showPopup() {
            if (!shouldShow()) return;

            const doShow = () => {
                popup.style.display = 'flex';
                popup.style.animation = 'exitFadeIn 0.3s ease-out';
                markShown();
            };

            if (delayMs > 0) {
                setTimeout(doShow, delayMs);
            } else {
                doShow();
            }
        }

        function hidePopup() {
            popup.style.animation = 'exitFadeOut 0.2s ease-in';
            setTimeout(() => { popup.style.display = 'none'; }, 200);
        }

        // --- Close button ---
        popup.querySelectorAll('[data-exit-close]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                hidePopup();
            });
        });

        // Close on overlay click (not inner content)
        popup.addEventListener('click', (e) => {
            if (e.target === popup) hidePopup();
        });

        // --- Trigger setup ---
        let triggered = false;

        if (trigger === 'exit') {
            document.addEventListener('mouseleave', (e) => {
                if (triggered) return;
                if (e.clientY < 5) {
                    triggered = true;
                    showPopup();
                }
            });
            // Mobile fallback: show on back button attempt
            window.addEventListener('beforeunload', () => {
                if (!triggered) showPopup();
            });
        } else if (trigger.startsWith('scroll-')) {
            const threshold = parseInt(trigger.split('-')[1], 10) || 50;
            window.addEventListener('scroll', () => {
                if (triggered) return;
                const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                if (scrollPercent >= threshold) {
                    triggered = true;
                    showPopup();
                }
            }, { passive: true });
        } else if (trigger.startsWith('timer-')) {
            const seconds = parseInt(trigger.split('-')[1], 10) || 10;
            setTimeout(() => {
                if (!triggered) {
                    triggered = true;
                    showPopup();
                }
            }, seconds * 1000);
        }
    });

    // Inject animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes exitFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes exitFadeOut {
            from { opacity: 1; }
            to   { opacity: 0; }
        }
    `;
    document.head.appendChild(style);
})();
