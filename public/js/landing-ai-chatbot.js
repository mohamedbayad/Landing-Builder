(function () {
    const scriptEl = document.getElementById('lp-ai-chatbot-runtime') || document.currentScript;

    if (!scriptEl) {
        return;
    }

    const endpoint = scriptEl.dataset.endpoint || '/api/public/ai-chat';
    const metaLandingId = Number(document.querySelector('meta[name="landing-id"]')?.getAttribute('content') || 0);
    const landingId = Number(scriptEl.dataset.landingId || 0) || metaLandingId;
    const pageIdRaw = scriptEl.dataset.pageId || '';
    const pageId = pageIdRaw !== '' ? Number(pageIdRaw) : null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const state = {
        isOpen: false,
        isSending: false,
        history: [],
        maxHistory: 10,
    };

    const styleTagId = 'lp-ai-chatbot-style';
    if (!document.getElementById(styleTagId)) {
        const style = document.createElement('style');
        style.id = styleTagId;
        style.textContent = `
            .lp-ai-chatbot-root {
                position: fixed;
                right: 18px;
                bottom: 18px;
                z-index: 2147483647;
                font-family: "Inter", "Segoe UI", sans-serif;
            }

            .lp-ai-chatbot-toggle {
                width: 56px;
                height: 56px;
                border-radius: 999px;
                border: 0;
                cursor: pointer;
                color: #ffffff;
                background: linear-gradient(135deg, #0f766e, #0ea5a4);
                box-shadow: 0 12px 28px rgba(15, 118, 110, 0.34);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: transform .18s ease, box-shadow .18s ease;
                -webkit-appearance: none;
                appearance: none;
            }

            .lp-ai-chatbot-toggle:hover {
                transform: translateY(-1px);
                box-shadow: 0 16px 30px rgba(15, 118, 110, 0.38);
            }

            .lp-ai-chatbot-panel {
                width: min(360px, calc(100vw - 24px));
                height: min(520px, calc(100vh - 96px));
                margin-bottom: 12px;
                border-radius: 18px;
                border: 1px solid rgba(148, 163, 184, 0.35);
                overflow: hidden;
                background: #ffffff;
                box-shadow: 0 24px 50px rgba(2, 6, 23, 0.22);
                display: none;
                flex-direction: column;
            }

            .lp-ai-chatbot-panel.is-open {
                display: flex;
            }

            .lp-ai-chatbot-header {
                padding: 12px 14px;
                color: #f8fafc;
                background: linear-gradient(135deg, #0f172a, #1e293b);
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .02em;
            }

            .lp-ai-chatbot-body {
                flex: 1;
                overflow-y: auto;
                padding: 12px;
                background: #f8fafc;
            }

            .lp-ai-chatbot-row {
                display: flex;
                margin-bottom: 10px;
            }

            .lp-ai-chatbot-row.user {
                justify-content: flex-end;
            }

            .lp-ai-chatbot-bubble {
                max-width: 86%;
                border-radius: 14px;
                padding: 9px 11px;
                font-size: 13px;
                line-height: 1.45;
                white-space: pre-wrap;
                word-break: break-word;
            }

            .lp-ai-chatbot-row.user .lp-ai-chatbot-bubble {
                color: #ecfeff;
                background: #0f766e;
                border-bottom-right-radius: 6px;
            }

            .lp-ai-chatbot-row.assistant .lp-ai-chatbot-bubble {
                color: #0f172a;
                background: #e2e8f0;
                border-bottom-left-radius: 6px;
            }

            .lp-ai-chatbot-form {
                padding: 10px;
                border-top: 1px solid #e2e8f0;
                background: #ffffff;
                display: flex;
                gap: 8px;
            }

            .lp-ai-chatbot-input {
                flex: 1;
                border: 1px solid #cbd5e1;
                border-radius: 10px;
                padding: 9px 10px;
                font-size: 13px;
                line-height: 1.3;
                outline: none;
            }

            .lp-ai-chatbot-input:focus {
                border-color: #0f766e;
                box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15);
            }

            .lp-ai-chatbot-send {
                border: 0;
                border-radius: 10px;
                background: #0f766e;
                color: #ecfeff;
                min-width: 74px;
                padding: 0 12px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
            }

            .lp-ai-chatbot-send[disabled] {
                opacity: 0.6;
                cursor: not-allowed;
            }

            @media (max-width: 640px) {
                .lp-ai-chatbot-root {
                    right: 10px;
                    bottom: 10px;
                }

                .lp-ai-chatbot-panel {
                    width: calc(100vw - 20px);
                    height: min(72vh, 520px);
                }
            }
        `;
        document.head.appendChild(style);
    }

    const root = document.createElement('div');
    root.className = 'lp-ai-chatbot-root';

    root.innerHTML = `
        <div class="lp-ai-chatbot-panel" aria-live="polite">
            <div class="lp-ai-chatbot-header">AI Assistant</div>
            <div class="lp-ai-chatbot-body" id="lp-ai-chatbot-body"></div>
            <form class="lp-ai-chatbot-form" id="lp-ai-chatbot-form">
                <input
                    class="lp-ai-chatbot-input"
                    id="lp-ai-chatbot-input"
                    type="text"
                    maxlength="1200"
                    placeholder="Ask anything about this offer..."
                    autocomplete="off"
                />
                <button class="lp-ai-chatbot-send" id="lp-ai-chatbot-send" type="submit">Send</button>
            </form>
        </div>
        <button class="lp-ai-chatbot-toggle" id="lp-ai-chatbot-toggle" type="button" aria-label="Open AI assistant">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2c5.52 0 10 3.85 10 8.6 0 4.76-4.48 8.61-10 8.61-.65 0-1.3-.06-1.92-.18l-4.85 2.69c-.43.24-.96-.13-.89-.61l.8-5.02C3.19 14.55 2 12.67 2 10.6 2 5.85 6.48 2 12 2Zm0 1.8c-4.56 0-8.2 3.08-8.2 6.8 0 1.78.85 3.37 2.42 4.62l.37.29-.5 3.12 3.02-1.67.4.08c.82.17 1.66.25 2.49.25 4.56 0 8.2-3.08 8.2-6.81 0-3.71-3.64-6.8-8.2-6.8Z"></path>
            </svg>
        </button>
    `;

    document.body.appendChild(root);

    const panel = root.querySelector('#lp-ai-chatbot-panel') || root.querySelector('.lp-ai-chatbot-panel');
    const toggleBtn = root.querySelector('#lp-ai-chatbot-toggle');
    const form = root.querySelector('#lp-ai-chatbot-form');
    const input = root.querySelector('#lp-ai-chatbot-input');
    const sendBtn = root.querySelector('#lp-ai-chatbot-send');
    const body = root.querySelector('#lp-ai-chatbot-body');

    if (!panel || !toggleBtn || !form || !input || !sendBtn || !body) {
        return;
    }

    const appendMessage = function (role, content) {
        const row = document.createElement('div');
        row.className = 'lp-ai-chatbot-row ' + (role === 'user' ? 'user' : 'assistant');

        const bubble = document.createElement('div');
        bubble.className = 'lp-ai-chatbot-bubble';
        bubble.textContent = content;

        row.appendChild(bubble);
        body.appendChild(row);
        body.scrollTop = body.scrollHeight;
    };

    const setOpenState = function (open) {
        state.isOpen = open;
        panel.classList.toggle('is-open', open);
        toggleBtn.setAttribute('aria-label', open ? 'Close AI assistant' : 'Open AI assistant');
        toggleBtn.innerHTML = open
            ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.12L10.59 12 5.7 16.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.88a1 1 0 0 0 0-1.41Z"></path></svg>'
            : '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2c5.52 0 10 3.85 10 8.6 0 4.76-4.48 8.61-10 8.61-.65 0-1.3-.06-1.92-.18l-4.85 2.69c-.43.24-.96-.13-.89-.61l.8-5.02C3.19 14.55 2 12.67 2 10.6 2 5.85 6.48 2 12 2Zm0 1.8c-4.56 0-8.2 3.08-8.2 6.8 0 1.78.85 3.37 2.42 4.62l.37.29-.5 3.12 3.02-1.67.4.08c.82.17 1.66.25 2.49.25 4.56 0 8.2-3.08 8.2-6.81 0-3.71-3.64-6.8-8.2-6.8Z"></path></svg>';

        if (open) {
            input.focus();
        }
    };

    const setSendingState = function (sending) {
        state.isSending = sending;
        input.disabled = sending;
        sendBtn.disabled = sending;
        sendBtn.textContent = sending ? '...' : 'Send';
    };

    const pushToHistory = function (role, content) {
        state.history.push({ role: role, content: content });
        if (state.history.length > state.maxHistory) {
            state.history = state.history.slice(-state.maxHistory);
        }
    };

    const sendMessage = async function (messageText) {
        const historyForRequest = state.history.slice(-8);

        appendMessage('user', messageText);
        pushToHistory('user', messageText);
        setSendingState(true);

        try {
            if (!landingId) {
                throw new Error('Missing landing context');
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };

            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    landing_id: landingId,
                    page_id: pageId,
                    message: messageText,
                    history: historyForRequest,
                    current_url: window.location.href,
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
                throw new Error('Assistant reply was empty');
            }

            appendMessage('assistant', reply);
            pushToHistory('assistant', reply);
        } catch (error) {
            const fallback = 'Sorry, I cannot answer right now. Please try again in a moment.';
            const friendly = (error && typeof error.message === 'string' && error.message.trim() !== '')
                ? error.message.trim()
                : fallback;
            appendMessage('assistant', friendly);
        } finally {
            setSendingState(false);
            input.focus();
        }
    };

    toggleBtn.addEventListener('click', function () {
        setOpenState(!state.isOpen);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (state.isSending) {
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
        if (event.key === 'Escape' && state.isOpen) {
            setOpenState(false);
        }
    });

    appendMessage('assistant', 'Hi, I am your AI assistant. Ask me anything about this page or offer.');
})();
