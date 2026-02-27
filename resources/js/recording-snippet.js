(function () {
    // 1. Get or create visitor_id (persistent across sessions - localStorage)
    let visitorId = localStorage.getItem('_rec_vid');
    if (!visitorId) {
        visitorId = 'v_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
        localStorage.setItem('_rec_vid', visitorId);
    }

    // 2. Get or create session_id
    const urlParams = new URLSearchParams(window.location.search);
    let sessionId = urlParams.get('_sid')
        || sessionStorage.getItem('_rec_sid');

    if (!sessionId) {
        sessionId = 's_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
        sessionStorage.setItem('_rec_sid', sessionId);
    } else {
        sessionStorage.setItem('_rec_sid', sessionId);
    }

    // 3. Generate page_id for this specific page visit
    const pageId = 'p_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
    const enteredAt = Date.now();

    // 4. Detect page type from meta tag or URL
    const pageType = '{{PAGE_TYPE}}'; // 'landing' | 'checkout' | 'thankyou'
    const landingPageId = '{{LANDING_PAGE_ID}}';
    const apiBase = '{{API_BASE_URL}}'; // e.g. https://yourapp.com/api/rec

    const utmParams = {};
    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(key => {
        const val = urlParams.get(key);
        if (val) utmParams[key] = val;
    });
    const storedUtms = sessionStorage.getItem('_rec_utms');
    const finalUtms = storedUtms ? JSON.parse(storedUtms) : utmParams;
    if (Object.keys(utmParams).length) sessionStorage.setItem('_rec_utms', JSON.stringify(utmParams));

    const initPayload = {
        session_id: sessionId,
        visitor_id: visitorId,
        landing_page_id: landingPageId,
        screen_width: window.screen.width,
        screen_height: window.screen.height,
        referrer: document.referrer || '',
        utm_params: finalUtms
    };

    fetch(apiBase + '/session/init', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(initPayload),
        keepalive: true
    });

    let events = [];

    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/rrweb@1.1.3/dist/rrweb.min.js';
    script.onload = function () {
        rrweb.record({
            emit(event) {
                events.push(event);

                // Flush immediately on FullSnapshot (type 2) to guarantee DOM capture
                if (event.type === 2) {
                    flushEvents(false);
                } else if (events.length >= 50) {
                    flushEvents(false);
                }
            },
            recordCanvas: false,
            collectFonts: false,
            maskAllInputs: false,
            maskInputOptions: {
                password: true,
                email: false,
            },
            blockSelector: '.rec-block',
            ignoreClass: 'rec-ignore',
            sampling: {
                mousemove: 100,
                mouseInteraction: true,
                scroll: 200,
                input: 'last',
            },
        });
    };
    document.head.appendChild(script);

    // Also flush periodically so unload doesn't have a massive pending payload
    setInterval(() => flushEvents(false), 5000);

    function flushEvents(isFinal) {
        if (events.length === 0) return;

        const eventsToSend = [...events];
        events = [];

        const compressed = typeof LZString !== 'undefined'
            ? LZString.compressToBase64(JSON.stringify(eventsToSend))
            : JSON.stringify(eventsToSend);

        const payload = JSON.stringify({
            session_id: sessionId,
            page_id: pageId,
            page_type: pageType,
            url: window.location.href,
            events: compressed,
            events_count: eventsToSend.length,
            entered_at: enteredAt,
        });

        if (isFinal && navigator.sendBeacon) {
            const blob = new Blob([payload], { type: 'application/json' });
            navigator.sendBeacon(apiBase + '/events', blob);
        } else {
            // keepalive is removed here because it enforces a strict 64kb limit in Chromium
            fetch(apiBase + '/events', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: payload
            }).catch(() => {
                try {
                    const failed = JSON.parse(localStorage.getItem('_rec_failed') || '[]');
                    failed.push({ payload, timestamp: Date.now() });
                    if (failed.length > 5) failed.shift();
                    localStorage.setItem('_rec_failed', JSON.stringify(failed));
                } catch (e) { }
            });
        }
    }

    function appendSessionToLinks() {
        document.querySelectorAll('a[href]').forEach(link => {
            try {
                const url = new URL(link.href, window.location.origin);
                if (url.origin === window.location.origin) {
                    if (!url.searchParams.get('_sid')) {
                        url.searchParams.set('_sid', sessionId);
                        link.href = url.toString();
                    }
                }
            } catch (e) { }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', appendSessionToLinks);
    } else {
        appendSessionToLinks();
    }

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.querySelector('[name="_sid"]')) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_sid';
            input.value = sessionId;
            form.appendChild(input);
        }
    });

    function handleUnload() {
        flushEvents(true);

        const endPayload = JSON.stringify({
            session_id: sessionId,
            page_id: pageId,
            exited_at: Date.now(),
            duration_ms: Date.now() - enteredAt,
        });

        if (navigator.sendBeacon) {
            navigator.sendBeacon(apiBase + '/session/end',
                new Blob([endPayload], { type: 'application/json' })
            );
        }

        if (pageType === 'thankyou') {
            if (navigator.sendBeacon) {
                navigator.sendBeacon(apiBase + '/convert',
                    new Blob([JSON.stringify({ session_id: sessionId })],
                        { type: 'application/json' })
                );
            }
        }
    }

    window.addEventListener('pagehide', handleUnload);
    window.addEventListener('beforeunload', handleUnload);

})();
