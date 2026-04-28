export class Toast {
    static show(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const normalizedType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
        const id = `toast-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
        const toast = document.createElement('div');
        toast.id = id;
        toast.setAttribute('role', normalizedType === 'error' ? 'alert' : 'status');
        toast.setAttribute('aria-live', normalizedType === 'error' ? 'assertive' : 'polite');

        const icons = {
            success:
                '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0L3.296 9.21a1 1 0 011.414-1.415l4.04 4.04 6.543-6.545a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>',
            error:
                '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 114.293 4.293 8 8 0 0118 10zM9 6a1 1 0 012 0v4a1 1 0 11-2 0V6zm1 8a1.25 1.25 0 100-2.5A1.25 1.25 0 0010 14z" clip-rule="evenodd" /></svg>',
            warning:
                '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l6.518 11.593c.75 1.334-.213 2.983-1.742 2.983H3.48c-1.53 0-2.492-1.65-1.742-2.983L8.257 3.1zM11 8a1 1 0 10-2 0v3a1 1 0 102 0V8zm-1 7a1.25 1.25 0 100-2.5A1.25 1.25 0 0010 15z" clip-rule="evenodd" /></svg>',
            info:
                '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10A8 8 0 11.002 10 8 8 0 0118 10zm-8.75-3a.75.75 0 011.5 0v.25a.75.75 0 01-1.5 0V7zm0 2.5a.75.75 0 000 1.5h.5v2a.75.75 0 001.5 0v-2h.5a.75.75 0 000-1.5h-2.5z" clip-rule="evenodd" /></svg>',
        };

        const safeMessage = this.escapeHtml(String(message ?? ''));

        toast.className = `pro-toast pro-toast--${normalizedType}`;
        toast.innerHTML = `
            <div class="pro-toast__icon">${icons[normalizedType]}</div>
            <div class="pro-toast__message">${safeMessage}</div>
            <button type="button" class="pro-toast__close" aria-label="Close notification">
                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        `;

        container.appendChild(toast);
        const closeButton = toast.querySelector('.pro-toast__close');
        closeButton?.addEventListener('click', () => this.dismiss(toast));

        requestAnimationFrame(() => {
            toast.classList.add('is-visible');
        });

        window.setTimeout(() => this.dismiss(toast), duration);
    }

    static success(message) {
        this.show(message, 'success');
    }

    static error(message) {
        this.show(message, 'error');
    }

    static info(message) {
        this.show(message, 'info');
    }

    static warning(message) {
        this.show(message, 'warning');
    }

    static dismiss(toastEl) {
        if (!toastEl || !toastEl.parentElement) return;
        toastEl.classList.remove('is-visible');
        window.setTimeout(() => toastEl.remove(), 220);
    }

    static escapeHtml(value) {
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
}

window.Toast = Toast;
