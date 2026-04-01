/**
 * Plugin: Device Visibility
 *
 * Adds a "Visibility" trait to ALL components, allowing users to hide/show
 * elements per device breakpoint:
 *   - All Devices (default)
 *   - Desktop Only
 *   - Tablet & Desktop Only
 *   - Mobile Only
 *   - Hidden (all devices)
 *
 * Uses Tailwind utility classes: hidden, md:block, lg:block, etc.
 * Also injects editor CSS so that hidden elements remain visible (with a badge)
 * during editing.
 */
export default function deviceVisibilityPlugin(editor, opts = {}) {

    const VISIBILITY_OPTIONS = [
        { value: '', name: 'All Devices' },
        { value: 'desktop-only', name: 'Desktop Only (≥1024px)' },
        { value: 'tablet-up', name: 'Tablet & Desktop (≥768px)' },
        { value: 'mobile-only', name: 'Mobile Only (<768px)' },
        { value: 'hidden-all', name: 'Hidden (all devices)' },
    ];

    // CSS class mappings
    const VISIBILITY_CLASSES = {
        '': [],                                        // no class changes
        'desktop-only': ['hidden', 'lg:block'],       // hidden by default, shown ≥1024px
        'tablet-up': ['hidden', 'md:block'],          // hidden by default, shown ≥768px
        'mobile-only': ['block', 'md:hidden'],        // shown by default, hidden ≥768px
        'hidden-all': ['!hidden'],                    // force hidden
    };

    const ALL_VISIBILITY_CLASSES = new Set(
        Object.values(VISIBILITY_CLASSES).flat()
    );

    // ─── Add trait to all components ─────────────────────────────

    // Override the default component type to include visibility trait
    const defaultType = editor.DomComponents.getType('default');
    const defaultModel = defaultType.model;

    editor.DomComponents.addType('default', {
        model: {
            defaults: {
                ...defaultModel.prototype.defaults,
                traits: [
                    ...(defaultModel.prototype.defaults.traits || []),
                    {
                        type: 'select',
                        name: 'data-visibility',
                        label: '📱 Visibility',
                        options: VISIBILITY_OPTIONS,
                        changeProp: false,
                    },
                ],
            },
            init() {
                defaultModel.prototype.init?.call(this);
                // Listen for trait changes
                this.on('change:attributes:data-visibility', this._handleVisibilityChange);
            },
            _handleVisibilityChange() {
                const val = this.getAttributes()['data-visibility'] || '';
                const currentClasses = (this.getClasses?.() || []).filter(c => !ALL_VISIBILITY_CLASSES.has(c));
                const newClasses = [...currentClasses, ...(VISIBILITY_CLASSES[val] || [])];
                this.setClass(newClasses);
            },
        },
    });

    // ─── Editor-only: show hidden components with badge ──────────

    editor.on('load', () => {
        try {
            const doc = editor.Canvas.getDocument();
            if (!doc) return;

            const style = doc.createElement('style');
            style.id = 'device-visibility-editor-style';
            style.textContent = `
                /* In editor, override visibility so all elements stay editable */
                [data-visibility="desktop-only"],
                [data-visibility="tablet-up"],
                [data-visibility="mobile-only"],
                [data-visibility="hidden-all"] {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 0.5 !important;
                    position: relative !important;
                    outline: 1px dashed #f59e0b !important;
                }

                [data-visibility="desktop-only"]::before,
                [data-visibility="tablet-up"]::before,
                [data-visibility="mobile-only"]::before,
                [data-visibility="hidden-all"]::before {
                    content: attr(data-visibility);
                    position: absolute;
                    top: 2px;
                    right: 2px;
                    background: #f59e0b;
                    color: #000;
                    font-size: 9px;
                    font-weight: 700;
                    padding: 2px 6px;
                    border-radius: 4px;
                    text-transform: uppercase;
                    z-index: 10;
                    pointer-events: none;
                    font-family: -apple-system, sans-serif;
                    letter-spacing: 0.03em;
                }
            `;
            doc.head.appendChild(style);
        } catch (e) {
            // Canvas not ready
        }
    });

    console.log('[GrapesJS] Device Visibility plugin loaded.');
}
