(function () {
    const scriptEl = document.getElementById('dashboard-ai-assistant-runtime') || document.currentScript;
    if (!scriptEl) {
        return;
    }

    const endpoint = scriptEl.dataset.endpoint || '';
    const currentRoute = scriptEl.dataset.routeName || '';
    const userId = scriptEl.dataset.userId || 'guest';
    const workspaceId = scriptEl.dataset.workspaceId || 'default';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    if (!endpoint || !csrfToken) {
        return;
    }

    const STORAGE_PREFIX = 'db_ai_assistant_v2';
    const HISTORY_KEY = STORAGE_PREFIX + ':history:' + userId + ':' + workspaceId;
    const LAST_ACTIVITY_KEY = STORAGE_PREFIX + ':last-activity:' + userId + ':' + workspaceId;
    const INACTIVITY_MS = 15 * 60 * 1000;
    const DEFAULT_GREETING = 'Hi! I am your dashboard assistant. Ask me how to configure or activate anything.';
    const PREFERS_REDUCED_MOTION = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let inactivityTimer = null;
    let audioContext = null;

    const state = {
        open: false,
        sending: false,
        history: [],
        maxHistory: 10,
        typingRow: null,
    };

    const styleId = 'dashboard-ai-assistant-style';
    if (!document.getElementById(styleId)) {
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .db-ai-assistant-root {
                position: fixed;
                right: 18px;
                bottom: 18px;
                z-index: 2147483647;
                font-family: "Inter", "Segoe UI", sans-serif;
            }
            .db-ai-assistant-toggle {
                border: 0;
                border-radius: 999px;
                min-width: 56px;
                height: 56px;
                padding: 0 16px;
                cursor: pointer;
                color: #fff7ed;
                background: linear-gradient(135deg, #f97316, #ea580c);
                box-shadow: 0 14px 30px rgba(234, 88, 12, .35);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                font-weight: 700;
                font-size: 13px;
            }
            .db-ai-assistant-toggle:hover {
                transform: translateY(-1px);
            }
            .db-ai-assistant-panel {
                width: min(390px, calc(100vw - 20px));
                height: min(560px, calc(100vh - 92px));
                margin-bottom: 12px;
                border: 1px solid rgba(148, 163, 184, .3);
                border-radius: 18px;
                overflow: hidden;
                background: #ffffff;
                box-shadow: 0 24px 46px rgba(2, 6, 23, .22);
                display: none;
                flex-direction: column;
            }
            .dark .db-ai-assistant-panel {
                background: #0f172a;
                border-color: rgba(148, 163, 184, .2);
            }
            .db-ai-assistant-panel.is-open {
                display: flex;
                animation: dbAiPanelIn .18s ease-out;
            }
            .db-ai-assistant-header {
                padding: 10px 12px;
                background: linear-gradient(135deg, #111827, #1f2937);
                color: #f8fafc;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
            }
            .db-ai-assistant-title {
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .02em;
            }
            .db-ai-assistant-clear {
                border: 1px solid rgba(226, 232, 240, .3);
                background: rgba(15, 23, 42, .35);
                color: #e2e8f0;
                border-radius: 8px;
                padding: 4px 8px;
                font-size: 11px;
                cursor: pointer;
                line-height: 1.2;
            }
            .db-ai-assistant-clear:hover {
                background: rgba(30, 41, 59, .55);
            }
            .db-ai-assistant-body {
                flex: 1;
                overflow-y: auto;
                padding: 12px;
                background: #f8fafc;
            }
            .dark .db-ai-assistant-body {
                background: #020617;
            }
            .db-ai-assistant-row {
                display: flex;
                margin-bottom: 10px;
            }
            .db-ai-assistant-row.is-new {
                animation: dbAiMessageIn .22s ease-out both;
            }
            .db-ai-assistant-row.user {
                justify-content: flex-end;
            }
            .db-ai-assistant-bubble {
                max-width: 88%;
                border-radius: 14px;
                padding: 9px 11px;
                font-size: 13px;
                line-height: 1.45;
                white-space: pre-wrap;
                word-break: break-word;
            }
            .db-ai-assistant-row.user .db-ai-assistant-bubble {
                color: #fff7ed;
                background: #ea580c;
                border-bottom-right-radius: 6px;
            }
            .db-ai-assistant-row.assistant .db-ai-assistant-bubble {
                color: #0f172a;
                background: #e2e8f0;
                border-bottom-left-radius: 6px;
            }
            .db-ai-assistant-bubble a {
                color: #0369a1;
                text-decoration: underline;
                text-underline-offset: 2px;
                word-break: break-all;
            }
            .db-ai-assistant-bubble a:hover {
                color: #075985;
            }
            .dark .db-ai-assistant-row.assistant .db-ai-assistant-bubble {
                color: #e2e8f0;
                background: #1e293b;
            }
            .dark .db-ai-assistant-bubble a {
                color: #7dd3fc;
            }
            .dark .db-ai-assistant-bubble a:hover {
                color: #bae6fd;
            }
            .db-ai-assistant-form {
                padding: 10px;
                border-top: 1px solid #e2e8f0;
                background: #ffffff;
                display: flex;
                gap: 8px;
            }
            .dark .db-ai-assistant-form {
                background: #0b1220;
                border-color: #1f2937;
            }
            .db-ai-assistant-input {
                flex: 1;
                border: 1px solid #cbd5e1;
                border-radius: 10px;
                padding: 9px 10px;
                font-size: 13px;
                outline: none;
                background: #ffffff;
                color: #0f172a;
            }
            .dark .db-ai-assistant-input {
                background: #111827;
                border-color: #334155;
                color: #e5e7eb;
            }
            .db-ai-assistant-send {
                border: 0;
                border-radius: 10px;
                background: #ea580c;
                color: #fff7ed;
                min-width: 78px;
                padding: 0 12px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
            }
            .db-ai-assistant-send[disabled] {
                opacity: .6;
                cursor: not-allowed;
            }
            .db-ai-assistant-send.is-loading {
                animation: dbAiButtonPulse .9s ease-in-out infinite;
                padding: 0;
            }
            .db-ai-assistant-send-loader {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 3px;
                width: 100%;
            }
            .db-ai-assistant-send-loader-dot {
                width: 4px;
                height: 4px;
                border-radius: 999px;
                background: currentColor;
                opacity: .55;
                animation: dbAiTypingDot .95s infinite;
            }
            .db-ai-assistant-send-loader-dot:nth-child(2) {
                animation-delay: .14s;
            }
            .db-ai-assistant-send-loader-dot:nth-child(3) {
                animation-delay: .28s;
            }
            .db-ai-assistant-typing {
                display: inline-flex;
                align-items: center;
                gap: 4px;
            }
            .db-ai-assistant-typing-dot {
                width: 6px;
                height: 6px;
                border-radius: 999px;
                background: currentColor;
                opacity: .45;
                animation: dbAiTypingDot 1s infinite;
            }
            .db-ai-assistant-typing-dot:nth-child(2) {
                animation-delay: .18s;
            }
            .db-ai-assistant-typing-dot:nth-child(3) {
                animation-delay: .36s;
            }
            .db-ai-assistant-bubble.is-typing {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 28px;
                padding: 7px 9px;
                line-height: 1;
            }
            .db-ai-assistant-bubble.is-typing .db-ai-assistant-typing {
                gap: 3px;
            }
            .db-ai-assistant-bubble.is-typing .db-ai-assistant-typing-dot {
                width: 4px;
                height: 4px;
            }
            @keyframes dbAiPanelIn {
                from { opacity: 0; transform: translateY(10px) scale(.985); }
                to { opacity: 1; transform: translateY(0) scale(1); }
            }
            @keyframes dbAiMessageIn {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes dbAiButtonPulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.04); }
            }
            @keyframes dbAiTypingDot {
                0%, 80%, 100% { transform: translateY(0); opacity: .35; }
                40% { transform: translateY(-3px); opacity: 1; }
            }
            @media (prefers-reduced-motion: reduce) {
                .db-ai-assistant-panel.is-open,
                .db-ai-assistant-row.is-new,
                .db-ai-assistant-send.is-loading,
                .db-ai-assistant-typing-dot {
                    animation: none !important;
                }
            }
            @media (max-width: 640px) {
                .db-ai-assistant-root { right: 10px; bottom: 10px; }
                .db-ai-assistant-panel { width: calc(100vw - 20px); height: min(72vh, 540px); }
                .db-ai-assistant-toggle { min-width: 56px; padding: 0; }
                .db-ai-assistant-label { display: none; }
            }
        `;
        document.head.appendChild(style);
    }

    const root = document.createElement('div');
    root.className = 'db-ai-assistant-root';
    root.innerHTML = `
        <div class="db-ai-assistant-panel" id="db-ai-assistant-panel" aria-live="polite">
            <div class="db-ai-assistant-header">
                <span class="db-ai-assistant-title">Dashboard AI Assistant</span>
                <button class="db-ai-assistant-clear" id="db-ai-assistant-clear" type="button">Clear</button>
            </div>
            <div class="db-ai-assistant-body" id="db-ai-assistant-body"></div>
            <form class="db-ai-assistant-form" id="db-ai-assistant-form">
                <input class="db-ai-assistant-input" id="db-ai-assistant-input" type="text" maxlength="1400" placeholder="Ask how to do something in dashboard..." autocomplete="off" />
                <button class="db-ai-assistant-send" id="db-ai-assistant-send" type="submit">Send</button>
            </form>
        </div>
        <button class="db-ai-assistant-toggle" id="db-ai-assistant-toggle" type="button" aria-label="Open dashboard assistant">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2c5.5 0 10 3.86 10 8.62 0 4.74-4.5 8.6-10 8.6-.6 0-1.2-.05-1.8-.17l-4.97 2.76a.65.65 0 0 1-.96-.68l.82-5.1A8.2 8.2 0 0 1 2 10.62C2 5.86 6.5 2 12 2Z"></path>
            </svg>
            <span class="db-ai-assistant-label">AI Help</span>
        </button>
    `;
    document.body.appendChild(root);

    const panel = root.querySelector('#db-ai-assistant-panel');
    const body = root.querySelector('#db-ai-assistant-body');
    const toggle = root.querySelector('#db-ai-assistant-toggle');
    const clearBtn = root.querySelector('#db-ai-assistant-clear');
    const form = root.querySelector('#db-ai-assistant-form');
    const input = root.querySelector('#db-ai-assistant-input');
    const sendBtn = root.querySelector('#db-ai-assistant-send');

    if (!panel || !body || !toggle || !clearBtn || !form || !input || !sendBtn) {
        return;
    }

    const ensureAudioContext = function () {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) {
                return null;
            }

            if (!audioContext) {
                audioContext = new Ctx();
            }

            if (audioContext.state === 'suspended') {
                audioContext.resume().catch(function () {
                    // Ignore resume failures.
                });
            }

            return audioContext;
        } catch (_e) {
            return null;
        }
    };

    const playTone = function (ctx, when, frequency, duration, volume) {
        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frequency, when);

        gainNode.gain.setValueAtTime(0.0001, when);
        gainNode.gain.linearRampToValueAtTime(volume, when + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.0001, when + duration);

        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);
        oscillator.start(when);
        oscillator.stop(when + duration + 0.02);
    };

    const playMessageSound = function (role) {
        const ctx = ensureAudioContext();
        if (!ctx) {
            return;
        }

        const base = ctx.currentTime + 0.01;
        const isAssistant = role === 'assistant';

        try {
            if (isAssistant) {
                playTone(ctx, base, 920, 0.05, 0.028);
                playTone(ctx, base + 0.06, 700, 0.06, 0.022);
            } else {
                playTone(ctx, base, 620, 0.04, 0.024);
                playTone(ctx, base + 0.05, 840, 0.05, 0.02);
            }
        } catch (_e) {
            // Ignore audio errors silently.
        }
    };

    const getLastActivity = function () {
        try {
            const raw = localStorage.getItem(LAST_ACTIVITY_KEY);
            const value = Number(raw || 0);
            return Number.isFinite(value) && value > 0 ? value : 0;
        } catch (_e) {
            return 0;
        }
    };

    const setLastActivity = function (timestamp) {
        try {
            localStorage.setItem(LAST_ACTIVITY_KEY, String(timestamp));
        } catch (_e) {
            // Ignore localStorage errors.
        }
    };

    const persistHistory = function () {
        try {
            localStorage.setItem(HISTORY_KEY, JSON.stringify(state.history));
        } catch (_e) {
            // Ignore localStorage errors.
        }
    };

    const clearStoredConversation = function () {
        try {
            localStorage.removeItem(HISTORY_KEY);
            localStorage.removeItem(LAST_ACTIVITY_KEY);
        } catch (_e) {
            // Ignore localStorage errors.
        }
    };

    const isValidHistoryItem = function (item) {
        if (!item || typeof item !== 'object') {
            return false;
        }

        const role = String(item.role || '');
        const content = String(item.content || '').trim();
        return (role === 'user' || role === 'assistant') && content !== '';
    };

    const loadStoredHistory = function () {
        try {
            const raw = localStorage.getItem(HISTORY_KEY);
            if (!raw) {
                return [];
            }

            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) {
                return [];
            }

            return parsed
                .filter(isValidHistoryItem)
                .slice(-state.maxHistory)
                .map(function (item) {
                    return {
                        role: String(item.role),
                        content: String(item.content),
                    };
                });
        } catch (_e) {
            return [];
        }
    };

    const scheduleInactivityClear = function () {
        if (inactivityTimer) {
            window.clearTimeout(inactivityTimer);
            inactivityTimer = null;
        }

        const lastActivity = getLastActivity();
        if (!lastActivity) {
            return;
        }

        const elapsed = Date.now() - lastActivity;
        const remaining = INACTIVITY_MS - elapsed;

        if (remaining <= 0) {
            resetConversation('auto');
            return;
        }

        inactivityTimer = window.setTimeout(function () {
            resetConversation('auto');
        }, remaining);
    };

    const appendLinkOrText = function (fragment, href, label) {
        try {
            const parsed = new URL(href, window.location.origin);
            if (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') {
                throw new Error('Invalid protocol');
            }

            const anchor = document.createElement('a');
            anchor.href = parsed.href;
            anchor.textContent = label;
            if (parsed.origin === window.location.origin) {
                anchor.target = '_self';
            } else {
                anchor.target = '_blank';
                anchor.rel = 'noopener noreferrer';
            }
            fragment.appendChild(anchor);
        } catch (_e) {
            fragment.appendChild(document.createTextNode(label));
        }
    };

    const renderBubbleContent = function (content) {
        const text = String(content ?? '');
        const fragment = document.createDocumentFragment();
        const lines = text.split('\n');
        const tokenRegex = /\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)|(https?:\/\/[^\s]+)/g;

        lines.forEach(function (line, lineIndex) {
            let cursor = 0;
            let match;

            tokenRegex.lastIndex = 0;
            while ((match = tokenRegex.exec(line)) !== null) {
                const start = match.index;
                if (start > cursor) {
                    fragment.appendChild(document.createTextNode(line.slice(cursor, start)));
                }

                if (match[1] && match[2]) {
                    appendLinkOrText(fragment, match[2], match[1]);
                } else if (match[3]) {
                    appendLinkOrText(fragment, match[3], match[3]);
                } else {
                    fragment.appendChild(document.createTextNode(match[0]));
                }

                cursor = start + match[0].length;
            }

            if (cursor < line.length) {
                fragment.appendChild(document.createTextNode(line.slice(cursor)));
            }

            if (lineIndex < lines.length - 1) {
                fragment.appendChild(document.createElement('br'));
            }
        });

        return fragment;
    };

    const pushHistory = function (role, content) {
        state.history.push({ role: role, content: content });
        if (state.history.length > state.maxHistory) {
            state.history = state.history.slice(-state.maxHistory);
        }
    };

    const appendMessage = function (role, content, persist, meta) {
        const options = meta || {};
        const shouldPersist = persist !== false;
        const shouldAnimate = options.animate !== false && !PREFERS_REDUCED_MOTION;
        const shouldPlaySound = options.playSound !== false && !PREFERS_REDUCED_MOTION;

        const row = document.createElement('div');
        row.className = 'db-ai-assistant-row ' + (role === 'user' ? 'user' : 'assistant');
        if (shouldAnimate) {
            row.classList.add('is-new');
        }

        const bubble = document.createElement('div');
        bubble.className = 'db-ai-assistant-bubble';
        bubble.appendChild(renderBubbleContent(content));

        row.appendChild(bubble);
        body.appendChild(row);
        body.scrollTop = body.scrollHeight;

        if (shouldPersist) {
            pushHistory(role, String(content));
            persistHistory();
            setLastActivity(Date.now());
            scheduleInactivityClear();
        }

        if (shouldPlaySound) {
            playMessageSound(role);
        }
    };

    const setOpenState = function (open) {
        state.open = open;
        panel.classList.toggle('is-open', open);
        if (open) {
            input.focus();
        }
    };

    const setSendingState = function (sending) {
        state.sending = sending;
        input.disabled = sending;
        sendBtn.disabled = sending;
        clearBtn.disabled = sending;
        if (sending) {
            sendBtn.innerHTML = '<span class="db-ai-assistant-send-loader" aria-hidden="true"><span class="db-ai-assistant-send-loader-dot"></span><span class="db-ai-assistant-send-loader-dot"></span><span class="db-ai-assistant-send-loader-dot"></span></span>';
        } else {
            sendBtn.textContent = 'Send';
        }
        sendBtn.classList.toggle('is-loading', sending);
    };

    const showTypingIndicator = function () {
        if (state.typingRow) {
            return;
        }

        const row = document.createElement('div');
        row.className = 'db-ai-assistant-row assistant';
        if (!PREFERS_REDUCED_MOTION) {
            row.classList.add('is-new');
        }

        const bubble = document.createElement('div');
        bubble.className = 'db-ai-assistant-bubble';
        bubble.classList.add('is-typing');
        bubble.innerHTML = `
            <span class="db-ai-assistant-typing" aria-label="Assistant is typing">
                <span class="db-ai-assistant-typing-dot"></span>
                <span class="db-ai-assistant-typing-dot"></span>
                <span class="db-ai-assistant-typing-dot"></span>
            </span>
        `;

        row.appendChild(bubble);
        body.appendChild(row);
        body.scrollTop = body.scrollHeight;
        state.typingRow = row;
    };

    const hideTypingIndicator = function () {
        if (!state.typingRow) {
            return;
        }
        state.typingRow.remove();
        state.typingRow = null;
    };

    const resetConversation = function (reason) {
        body.innerHTML = '';
        state.history = [];
        hideTypingIndicator();
        clearStoredConversation();

        if (reason === 'auto') {
            appendMessage('assistant', 'Conversation was auto-cleared after 15 minutes inactivity. Ask me anything.', true, { playSound: false });
            return;
        }

        appendMessage('assistant', DEFAULT_GREETING, true, { playSound: false });
    };

    const initializeConversation = function () {
        const lastActivity = getLastActivity();
        if (lastActivity && (Date.now() - lastActivity) >= INACTIVITY_MS) {
            clearStoredConversation();
        }

        const storedHistory = loadStoredHistory();

        if (storedHistory.length === 0) {
            appendMessage('assistant', DEFAULT_GREETING, true, { playSound: false });
            return;
        }

        state.history = storedHistory.slice(-state.maxHistory);
        state.history.forEach(function (item) {
            appendMessage(item.role, item.content, false, { animate: false, playSound: false });
        });
        if (!getLastActivity()) {
            setLastActivity(Date.now());
        }
        scheduleInactivityClear();
    };

    const sendMessage = async function (text) {
        const historyForRequest = state.history.slice(-8);
        appendMessage('user', text);
        setSendingState(true);
        showTypingIndicator();

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message: text,
                    history: historyForRequest,
                    current_route: currentRoute,
                    current_url: window.location.href,
                    page_title: document.title || '',
                }),
            });

            const payload = await response.json().catch(function () {
                return {};
            });

            if (!response.ok) {
                throw new Error(payload.message || 'Request failed');
            }

            const reply = (payload.reply || '').toString().trim();
            if (!reply) {
                throw new Error('Empty assistant reply');
            }

            hideTypingIndicator();
            appendMessage('assistant', reply);
        } catch (_e) {
            hideTypingIndicator();
            appendMessage('assistant', 'I cannot answer right now. Please try again in a few seconds.');
        } finally {
            hideTypingIndicator();
            setSendingState(false);
            input.focus();
        }
    };

    toggle.addEventListener('click', function () {
        setOpenState(!state.open);
    });

    clearBtn.addEventListener('click', function () {
        if (state.sending) {
            return;
        }
        resetConversation('manual');
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (state.sending) {
            return;
        }

        const text = input.value.trim();
        if (!text) {
            return;
        }

        input.value = '';
        sendMessage(text);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && state.open) {
            setOpenState(false);
        }
    });

    initializeConversation();
})();
