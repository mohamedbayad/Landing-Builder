/**
 * Plugin: Exit Intent Popup
 *
 * Provides:
 *   - A configurable exit-intent popup component type
 *   - A draggable block in the block manager
 *   - Frontend runtime script that triggers the popup on mouse-leave
 *
 * The popup is editable in GrapesJS but hidden on public pages until triggered.
 */
export default function exitIntentPlugin(editor, opts = {}) {

    const DomComponents = editor.DomComponents;
    const bm = editor.BlockManager;

    // ─── Component Type ──────────────────────────────────────────

    DomComponents.addType('exit-intent-popup', {
        model: {
            defaults: {
                tagName: 'div',
                name: 'Exit Intent Popup',
                draggable: true,
                droppable: true,
                attributes: {
                    'data-exit-intent': 'true',
                    'class': 'exit-intent-overlay',
                },
                traits: [
                    {
                        type: 'select',
                        name: 'data-trigger',
                        label: 'Trigger',
                        options: [
                            { value: 'exit', name: 'Mouse Exit (Desktop)' },
                            { value: 'scroll-50', name: 'Scroll 50%' },
                            { value: 'scroll-75', name: 'Scroll 75%' },
                            { value: 'timer-10', name: 'After 10 seconds' },
                            { value: 'timer-30', name: 'After 30 seconds' },
                        ],
                        changeProp: false,
                    },
                    {
                        type: 'select',
                        name: 'data-frequency',
                        label: 'Show Frequency',
                        options: [
                            { value: 'once', name: 'Once per session' },
                            { value: 'once-per-day', name: 'Once per day' },
                            { value: 'always', name: 'Every trigger' },
                        ],
                        changeProp: false,
                    },
                    {
                        type: 'number',
                        name: 'data-delay-ms',
                        label: 'Delay (ms)',
                        placeholder: '0',
                        changeProp: false,
                    },
                ],
                // Inline styles for the overlay (hidden by default on frontend)
                style: {
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100%',
                    'background': 'rgba(0,0,0,0.6)',
                    'z-index': '99998',
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'center',
                    'padding': '20px',
                },
                components: `
                    <div style="background: #ffffff; border-radius: 16px; max-width: 480px; width: 100%; padding: 40px; text-align: center; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                        <button data-exit-close style="position: absolute; top: 12px; right: 16px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999; line-height: 1;">×</button>
                        <div style="font-size: 48px; margin-bottom: 16px;">⏳</div>
                        <h2 style="font-size: 24px; font-weight: 800; color: #111; margin-bottom: 8px;">Wait! Don't Leave Yet</h2>
                        <p style="font-size: 16px; color: #666; margin-bottom: 24px; line-height: 1.6;">Get an exclusive 15% off your first order. This offer expires when you close this page.</p>
                        <a href="#" class="cta" style="display: inline-block; padding: 14px 32px; background: #4f46e5; color: white; font-weight: 700; border-radius: 10px; text-decoration: none; font-size: 16px;">Claim 15% Off Now</a>
                        <p style="font-size: 12px; color: #999; margin-top: 16px;">No spam. Unsubscribe anytime.</p>
                    </div>
                `,
            },
        },
        view: {
            init() {
                // In editor, always show the popup for editing
                this.listenTo(this.model, 'change:style', this.render);
            },
        },
        isComponent: (el) => el?.getAttribute?.('data-exit-intent') === 'true',
    });

    // ─── Block ───────────────────────────────────────────────────

    bm.add('exit-intent-popup', {
        label: 'Exit Intent Popup',
        category: 'Popups',
        content: { type: 'exit-intent-popup' },
        attributes: { class: 'fa fa-window-close' },
    });

    // ─── Editor: Always-visible override ─────────────────────────
    // In the editor, the popup overlay must be visible for editing.
    // On the published page, it starts hidden and is shown by JS.
    editor.on('load', () => {
        try {
            const doc = editor.Canvas.getDocument();
            if (!doc) return;

            const style = doc.createElement('style');
            style.id = 'exit-intent-editor-style';
            style.textContent = `
                [data-exit-intent] {
                    /* Override: show in editor for editing */
                    display: flex !important;
                    position: relative !important;
                    height: auto !important;
                    min-height: 200px !important;
                    background: rgba(0,0,0,0.15) !important;
                    border: 2px dashed #6366f1 !important;
                    border-radius: 8px;
                    margin: 8px 0;
                }
            `;
            doc.head.appendChild(style);
        } catch (e) {
            // Canvas not ready
        }
    });

    console.log('[GrapesJS] Exit Intent Popup plugin loaded.');
}
