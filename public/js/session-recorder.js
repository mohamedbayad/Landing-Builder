/**
 * Session Recorder - rrweb based session recording for landing pages
 * 
 * Features:
 * - Cross-page AND cross-reload session continuity via sessionStorage
 * - Always sends session_id to backend for update-or-create logic
 * - Inline stylesheet capture for accurate replay
 * - Batched event sending to minimize network overhead
 * - CRITICAL: sendBeacon for page exits and redirects (Thank You page)
 * 
 * Usage: Include this script on any landing page with:
 * <script src="https://cdn.jsdelivr.net/npm/rrweb@latest/dist/rrweb.min.js"></script>
 * <script src="/js/session-recorder.js" data-landing-page-id="123"></script>
 */

(function () {
    'use strict';

    // Configuration
    const CONFIG = {
        batchInterval: 12000, // Send events every 12 seconds
        apiBaseUrl: '/api',
        maxEventsPerBatch: 200, // Reduced from 500 to prevent oversized payloads
        maxPayloadSize: 5 * 1024 * 1024, // 5MB max payload size
        sessionKey: 'rrweb_session_id',
        sessionPageKey: 'rrweb_landing_page_id',
        sessionExpiry: 30 * 60 * 1000, // 30 minutes session expiry
        debug: true, // Enable debug logging
        immediateFlushOnSnapshot: true, // Send FullSnapshot immediately
        // Pages that redirect quickly - force immediate sending
        criticalPages: ['/thank-you', '/thankyou', '/success', '/confirmation', '/checkout/success'],
    };

    // State - DEFINED AT TOP LEVEL FOR PROPER SCOPE
    let sessionId = null;
    let eventBuffer = []; // Buffer for events - accessible to all functions
    let startTime = Date.now();
    let stopFn = null;
    let batchTimer = null;
    let pendingSnapshot = null; // Track if we have an unsent snapshot
    let isCriticalPage = false; // Is this a page that might redirect quickly?
    let isExiting = false; // Prevent double-sending on exit

    // Debug logger
    function debugLog(...args) {
        if (CONFIG.debug) {
            console.log('[SessionRecorder]', ...args);
        }
    }

    // Check if current page is a critical (redirect-prone) page
    function checkIfCriticalPage() {
        const path = window.location.pathname.toLowerCase();
        const isCritical = CONFIG.criticalPages.some(p => path.includes(p));
        if (isCritical) {
            debugLog('CRITICAL PAGE DETECTED:', path, '- Will send events immediately');
        }
        return isCritical;
    }

    // Generate UUID
    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // Get landing page ID from script data attribute
    const scriptTag = document.currentScript || document.querySelector('script[data-landing-page-id]');
    const landingPageId = scriptTag ? scriptTag.getAttribute('data-landing-page-id') : null;

    debugLog('Initializing with landing page ID:', landingPageId);

    if (!landingPageId) {
        console.warn('[SessionRecorder] No landing-page-id provided. Recording disabled.');
        return;
    }

    // Check if rrweb is loaded
    if (typeof rrweb === 'undefined') {
        console.warn('[SessionRecorder] rrweb not loaded. Recording disabled.');
        return;
    }

    // Initialize critical page check
    isCriticalPage = checkIfCriticalPage();

    /**
     * Check for existing session in sessionStorage
     * Sessions persist across reload and navigation within the same tab
     */
    function checkExistingSession() {
        try {
            const storedSessionId = sessionStorage.getItem(CONFIG.sessionKey);
            const storedTimestamp = sessionStorage.getItem(CONFIG.sessionKey + '_timestamp');

            debugLog('Checking sessionStorage - sessionId:', storedSessionId, 'timestamp:', storedTimestamp);

            if (storedSessionId && storedTimestamp) {
                const elapsed = Date.now() - parseInt(storedTimestamp, 10);

                // Check if session is still valid (within expiry time)
                if (elapsed < CONFIG.sessionExpiry) {
                    sessionId = storedSessionId;
                    // Update timestamp to extend session
                    sessionStorage.setItem(CONFIG.sessionKey + '_timestamp', Date.now().toString());
                    debugLog('Continuing existing session:', sessionId, '(elapsed:', Math.round(elapsed / 1000), 's)');
                    return true;
                } else {
                    debugLog('Session expired, clearing...');
                    clearStoredSession();
                }
            }
        } catch (e) {
            console.warn('[SessionRecorder] sessionStorage not available:', e);
        }

        // No existing session - generate new one immediately
        sessionId = generateUUID();
        storeSession(sessionId);
        debugLog('Created new session ID:', sessionId);
        return false;
    }

    /**
     * Store session ID in sessionStorage
     */
    function storeSession(id) {
        try {
            sessionStorage.setItem(CONFIG.sessionKey, id);
            sessionStorage.setItem(CONFIG.sessionKey + '_timestamp', Date.now().toString());
            sessionStorage.setItem(CONFIG.sessionPageKey, landingPageId);
            debugLog('Session stored in sessionStorage:', id);
        } catch (e) {
            console.warn('[SessionRecorder] Failed to store session:', e);
        }
    }

    /**
     * Clear stored session
     */
    function clearStoredSession() {
        try {
            sessionStorage.removeItem(CONFIG.sessionKey);
            sessionStorage.removeItem(CONFIG.sessionKey + '_timestamp');
            sessionStorage.removeItem(CONFIG.sessionPageKey);
            debugLog('Cleared stored session');
        } catch (e) {
            // Silently fail
        }
    }

    /**
     * Calculate current session duration in seconds
     */
    function getDuration() {
        return Math.floor((Date.now() - startTime) / 1000);
    }

    /**
     * Send events using sendBeacon (for critical exits)
     * This is more reliable for page unloads/redirects
     */
    function sendWithBeacon(events) {
        if (events.length === 0) return false;

        const payload = {
            events: events,
            duration: getDuration(),
            landing_page_id: parseInt(landingPageId, 10),
            session_id: sessionId,
        };

        const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });

        debugLog('Using sendBeacon to send', events.length, 'events');

        try {
            const success = navigator.sendBeacon(`${CONFIG.apiBaseUrl}/record-session`, blob);
            debugLog('sendBeacon result:', success ? 'queued' : 'failed');
            return success;
        } catch (e) {
            console.error('[SessionRecorder] sendBeacon failed:', e);
            return false;
        }
    }

    /**
     * Send events to the server (async fetch)
     */
    async function sendWithFetch(events) {
        if (events.length === 0) return;

        const payload = {
            events: events,
            duration: getDuration(),
            landing_page_id: parseInt(landingPageId, 10),
            session_id: sessionId,
        };

        debugLog('POSTing', events.length, 'events to', `${CONFIG.apiBaseUrl}/record-session`);

        try {
            const response = await fetch(`${CONFIG.apiBaseUrl}/record-session`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            debugLog('Response status:', response.status);

            if (response.ok) {
                const data = await response.json();
                debugLog('Server response:', data.action, '- session:', data.session_id);

                if (data.session_id && data.session_id !== sessionId) {
                    sessionId = data.session_id;
                    storeSession(sessionId);
                }
            } else {
                const errorText = await response.text();
                console.error('[SessionRecorder] Server error:', response.status, errorText);
            }
        } catch (error) {
            console.error('[SessionRecorder] Failed to send events:', error);
        }
    }

    /**
     * Send events to the server
     * Uses sendBeacon for critical exits, fetch for normal sending
     */
    async function sendEvents(events, isBeacon = false) {
        if (events.length === 0) {
            debugLog('No events to send, skipping');
            return;
        }

        debugLog('Attempting to send', events.length, 'events. isBeacon:', isBeacon);

        if (isBeacon || isCriticalPage) {
            // Use sendBeacon for reliability
            sendWithBeacon(events);
        } else {
            // Normal async fetch
            await sendWithFetch(events);
        }
    }

    /**
     * Flush buffered events
     */
    function flushBuffer(isBeacon = false) {
        debugLog('Flush called - buffer size:', eventBuffer.length, 'isBeacon:', isBeacon);

        if (eventBuffer.length === 0) {
            debugLog('Buffer empty, nothing to flush');
            return;
        }

        const eventsToSend = eventBuffer.slice(0, CONFIG.maxEventsPerBatch);
        eventBuffer = eventBuffer.slice(CONFIG.maxEventsPerBatch);

        debugLog('Flushing', eventsToSend.length, 'events, remaining in buffer:', eventBuffer.length);

        sendEvents(eventsToSend, isBeacon);
    }

    /**
     * Force flush all events immediately using beacon
     * Used for critical page exits
     */
    function forceFlushAll() {
        if (isExiting) {
            debugLog('Already exiting, skip double flush');
            return;
        }
        isExiting = true;

        debugLog('FORCE FLUSH ALL - buffer size:', eventBuffer.length);

        if (eventBuffer.length === 0) return;

        // Send all events at once using beacon
        sendWithBeacon(eventBuffer);
        eventBuffer = [];
    }

    /**
     * Start the batch timer
     */
    function startBatchTimer() {
        // On critical pages, use shorter interval
        const interval = isCriticalPage ? 2000 : CONFIG.batchInterval;
        debugLog('Starting batch timer with interval:', interval, 'ms');

        batchTimer = setInterval(() => {
            debugLog('--- Batch timer fired ---');
            debugLog('Current buffer size:', eventBuffer.length);
            flushBuffer(isCriticalPage); // Use beacon on critical pages
        }, interval);
    }

    /**
     * Initialize rrweb recording
     */
    function initRecording() {
        debugLog('=== Initializing rrweb recording ===');

        // Check for existing session or create new one
        checkExistingSession();

        debugLog('Starting rrweb.record() with session:', sessionId);

        stopFn = rrweb.record({
            emit: function (event) {
                // DEBUG: Log every captured event type
                debugLog('Event captured! Type:', event.type, '| Total in buffer:', eventBuffer.length + 1);

                // Push to buffer
                eventBuffer.push(event);

                // CRITICAL: If this is a FullSnapshot (type 2), handle specially
                if (event.type === 2) {
                    debugLog('FullSnapshot detected!');

                    if (isCriticalPage) {
                        // On critical pages, send immediately with beacon
                        debugLog('Critical page - sending snapshot immediately with beacon');
                        setTimeout(() => {
                            sendWithBeacon(eventBuffer);
                            eventBuffer = [];
                        }, 50);
                    } else if (CONFIG.immediateFlushOnSnapshot) {
                        // Normal pages - flush async
                        debugLog('Flushing snapshot asynchronously...');
                        setTimeout(() => {
                            flushBuffer(false);
                        }, 100);
                    }
                }

                // Log buffer size periodically
                if (eventBuffer.length % 10 === 0) {
                    debugLog('Buffer milestone:', eventBuffer.length, 'events');
                }
            },
            // CRITICAL: Inline stylesheets to fix visual glitches
            inlineStylesheet: true,
            // Collect fonts for accurate replay
            collectFonts: true,
            // Record canvas content
            recordCanvas: false,
            // Sampling settings for performance AND mouse tracking
            sampling: {
                scroll: 150,
                media: 800,
                input: 'last',
                // FIX: Enable mouse recording with throttling (every 100ms)
                mousemove: 100, // Capture mouse position every 100ms
                mouseInteraction: true, // Capture clicks, mousedown, mouseup
            },
            // Privacy settings
            maskAllInputs: false,
            maskInputOptions: {
                password: true,
            },
            // Inline images
            inlineImages: false,
            // Record cross-origin iframes
            recordCrossOriginIframes: false,
        });

        debugLog('rrweb.record() initialized, stopFn:', !!stopFn);

        startBatchTimer();

        // On critical pages, send initial snapshot faster
        const initialDelay = isCriticalPage ? 500 : 3000;
        debugLog('Scheduling initial flush in', initialDelay, 'ms...');

        setTimeout(() => {
            debugLog('Initial flush timer fired - buffer size:', eventBuffer.length);
            if (eventBuffer.length > 0) {
                flushBuffer(isCriticalPage);
            } else {
                debugLog('WARNING: No events captured after initial delay!');
            }
        }, initialDelay);
    }

    /**
     * Handle page visibility changes (tab switch, minimize)
     */
    function handleVisibilityChange() {
        if (document.hidden) {
            debugLog('Page hidden, flushing buffer with beacon...');
            // Use beacon since page might be closing
            if (eventBuffer.length > 0) {
                sendWithBeacon(eventBuffer);
                eventBuffer = [];
            }
        }
    }

    /**
     * Handle page unload - send remaining events
     */
    function handleBeforeUnload() {
        debugLog('Page unloading, force flushing all events...');
        if (batchTimer) {
            clearInterval(batchTimer);
        }
        forceFlushAll();
    }

    /**
     * Handle page hide (more reliable than beforeunload on mobile)
     */
    function handlePageHide(event) {
        debugLog('Page hide event, persisted:', event.persisted);
        if (batchTimer) {
            clearInterval(batchTimer);
        }
        forceFlushAll();
    }

    // Event listeners - multiple for maximum compatibility
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('beforeunload', handleBeforeUnload);
    window.addEventListener('pagehide', handlePageHide);

    // Additional listener for client-side navigations (SPA)
    window.addEventListener('unload', handleBeforeUnload);

    // Start recording when DOM is ready
    if (document.readyState === 'loading') {
        debugLog('DOM still loading, waiting for DOMContentLoaded...');
        document.addEventListener('DOMContentLoaded', initRecording);
    } else {
        debugLog('DOM already ready, starting immediately');
        initRecording();
    }

    // Expose API for manual control and debugging
    window.SessionRecorder = {
        stop: function () {
            debugLog('Manual stop called');
            if (stopFn) {
                stopFn();
            }
            if (batchTimer) {
                clearInterval(batchTimer);
            }
            flushBuffer(true); // Use beacon
        },
        getSessionId: function () {
            return sessionId;
        },
        clearSession: function () {
            clearStoredSession();
            sessionId = null;
        },
        isRecording: function () {
            return stopFn !== null;
        },
        getBufferSize: function () {
            return eventBuffer.length;
        },
        forceFlush: function () {
            debugLog('Manual flush triggered');
            flushBuffer(true);
        },
        forceFlushBeacon: function () {
            debugLog('Manual beacon flush triggered');
            forceFlushAll();
        },
        isCriticalPage: function () {
            return isCriticalPage;
        },
        getDebugInfo: function () {
            return {
                sessionId: sessionId,
                bufferSize: eventBuffer.length,
                landingPageId: landingPageId,
                isRecording: !!stopFn,
                duration: getDuration(),
                isCriticalPage: isCriticalPage,
            };
        }
    };

    debugLog('SessionRecorder loaded. Critical page:', isCriticalPage);
    debugLog('Use window.SessionRecorder.getDebugInfo() to inspect state.');

})();
