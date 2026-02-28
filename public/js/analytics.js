(function () {
    // Config
    const ENDPOINT = '/api/track/event';
    const CTA_ENDPOINT = '/analytics/track-click';
    const SCROLL_THRESHOLDS = [25, 50, 75, 90];

    let sessionStarted = false;
    let scrollTracked = new Set();

    // Helper: Get CSRF Token
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    // Helper: Send Event (generic)
    function sendEvent(name, data = {}) {
        const payload = {
            event_name: name,
            event_data: data,
            url: window.location.href
        };

        // Use Navigator.sendBeacon for reliability on page unload
        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
            navigator.sendBeacon(ENDPOINT, blob);
        } else {
            // Fallback
            fetch(ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify(payload)
            }).catch(e => console.error('Analytics Error:', e));
        }
    }

    // Helper: Detect nearest section/position for an element
    function detectPosition(el) {
        // Check for data-position attribute first
        if (el.getAttribute('data-position')) {
            return el.getAttribute('data-position');
        }

        // Walk up the DOM to find a section-like ancestor
        let current = el.parentElement;
        while (current && current !== document.body) {
            // Check for section id or data-section
            if (current.getAttribute('data-section')) {
                return current.getAttribute('data-section');
            }
            if (current.id && current.tagName === 'SECTION') {
                return current.id;
            }
            if (current.tagName === 'SECTION' || current.tagName === 'HEADER' || current.tagName === 'FOOTER' || current.tagName === 'NAV') {
                return current.tagName.toLowerCase();
            }
            current = current.parentElement;
        }
        return 'unknown';
    }

    // Helper: Generate label from element text
    function generateLabel(el) {
        const text = (el.innerText || '').trim().substring(0, 50);
        if (!text) return 'unnamed_cta';
        // Convert to snake_case label
        return 'cta_' + text
            .toLowerCase()
            .replace(/[àâä]/g, 'a')
            .replace(/[éèêë]/g, 'e')
            .replace(/[îï]/g, 'i')
            .replace(/[ôö]/g, 'o')
            .replace(/[ùûü]/g, 'u')
            .replace(/[ç]/g, 'c')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            .substring(0, 60);
    }

    // 1. Initial Page View (Handled by Middleware)
    // We rely on Middleware for the base 'pageview'

    // 2. Click Tracking (CTA) — Enhanced with label/type/position
    document.addEventListener('click', function (e) {
        let target = e.target.closest('a, button, .track-cta, .cta, [data-track]');
        if (target) {
            const hasTrackAttr = target.hasAttribute('data-track');
            const isCta = target.classList.contains('track-cta') || target.classList.contains('cta') || hasTrackAttr;

            if (isCta) {
                // Extract tracking data
                const label = hasTrackAttr ? target.getAttribute('data-track') : generateLabel(target);
                const type = target.getAttribute('data-type') || target.tagName.toLowerCase();
                const position = detectPosition(target);

                const eventData = {
                    text: (target.innerText || '').substring(0, 50),
                    href: target.getAttribute('href'),
                    id: target.id,
                    classes: target.className,
                    label: label,
                    type: type,
                    position: position
                };

                // Send via generic event endpoint (with element fields)
                const payload = {
                    event_name: 'cta_click',
                    event_data: eventData,
                    url: window.location.href,
                    element_label: label,
                    element_type: type,
                    element_position: position
                };

                if (navigator.sendBeacon) {
                    const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
                    navigator.sendBeacon(ENDPOINT, blob);
                } else {
                    fetch(ENDPOINT, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        body: JSON.stringify(payload)
                    }).catch(e => console.error('CTA Track Error:', e));
                }
            }
        }
    });

    // 3. Track all elements with data-track attribute (dedicated endpoint)
    document.querySelectorAll('[data-track]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const label = this.getAttribute('data-track');
            const type = this.getAttribute('data-type') || 'button';
            const position = this.getAttribute('data-position') || detectPosition(this);

            fetch(CTA_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    label: label,
                    type: type,
                    position: position,
                    page_url: window.location.href
                })
            }).catch(function (e) {
                console.error('CTA Dedicated Track Error:', e);
            });
        });
    });

    // 4. Form Interactions
    document.addEventListener('focus', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            const form = e.target.closest('form');
            if (form && !form.dataset.trackedStart) {
                form.dataset.trackedStart = 'true';
                sendEvent('form_start', { form_id: form.id || form.className });
            }
        }
    }, true);

    // 5. Scroll Depth
    window.addEventListener('scroll', function () {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;

        SCROLL_THRESHOLDS.forEach(threshold => {
            if (scrollPercent >= threshold && !scrollTracked.has(threshold)) {
                scrollTracked.add(threshold);
                sendEvent('scroll', { depth: threshold });
            }
        });
    }, { passive: true });

    // 6. Visibility / Time on Page (Heartbeat)
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            sendEvent('heartbeat', { time: Date.now() });
        }
    }, 15000);

})();
