(function () {
    // Config
    const ENDPOINT = '/api/track/event';
    const SCROLL_THRESHOLDS = [25, 50, 75, 90];

    let sessionStarted = false;
    let scrollTracked = new Set();

    // Helper: Send Event
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(payload)
            }).catch(e => console.error('Analytics Error:', e));
        }
    }

    // 1. Initial Page View (Handled by Middleware, but we can send metadata if needed)
    // We rely on Middleware for the base 'pageview', but accurate time-on-page requires frontend pings or exit events.

    // 2. Click Tracking (CTA)
    document.addEventListener('click', function (e) {
        let target = e.target.closest('a, button, .track-cta, .cta');
        if (target) {
            const isCta = target.classList.contains('track-cta') || target.classList.contains('cta') || target.hasAttribute('data-track');
            const data = {
                text: target.innerText?.substring(0, 50),
                href: target.getAttribute('href'),
                id: target.id,
                classes: target.className
            };

            if (isCta) {
                sendEvent('cta_click', data);
            }
        }
    });

    // 3. Form Interactions
    document.addEventListener('focus', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            const form = e.target.closest('form');
            if (form && !form.dataset.trackedStart) {
                form.dataset.trackedStart = 'true';
                sendEvent('form_start', { form_id: form.id || form.className });
            }
        }
    }, true);

    // 4. Scroll Depth
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

    // 5. Visibility / Time on Page (Heartbeat or Exit)
    // Simple Ping every 15s to keep session alive and measure duration accurately
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            sendEvent('heartbeat', { time: Date.now() });
        }
    }, 15000);

})();
