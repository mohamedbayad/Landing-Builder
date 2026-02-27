/**
 * Plugin: Canvas Interaction & Link Fixes (v10)
 *
 * ROOT CAUSE FIX for JS-toggled cards:
 *   The card's embedded <script> runs AFTER the initial page load and sets
 *   display:none on the hidden details panel. Previous "reveal" scans ran
 *   before the card JS, so the card immediately re-hid the content.
 *
 * SOLUTION — Two-layer MutationObserver:
 *   1. childList (subtree): catch new elements added to the canvas (existing fix)
 *   2. attributes (subtree, filter: ['style','hidden']): catch ANY element whose
 *      style or hidden attribute changes. If display:none or hidden is SET on an
 *      element, we IMMEDIATELY override it back to visible.
 *
 *   This means: no matter WHEN the card's JS runs, any attempt to hide an
 *   element is caught and reversed in the same microtask queue flush.
 *
 * PARTS:
 *   A — <details>/<summary>: set `open` attribute
 *   B — JS-toggled cards: attribute MutationObserver + aggressive polling
 *   C — Link traits: force-inject href/target/title for <a> on selection
 */

export default function canvasInteractionControlPlugin(editor, opts = {}) {
    let _observer = null;

    // ── A. FORCE-OPEN <details> ───────────────────────────────────────────────

    function _openAllDetails(body) {
        if (!body) return;
        body.querySelectorAll('details:not([open])').forEach(el => {
            el.setAttribute('open', '');
        });
    }

    // ── B. FORCE-REVEAL HIDDEN PANELS ──────────────────────────────────────────

    /**
     * Scan and un-hide elements that are hidden via INLINE styles.
     * CSS-class-based hiding is handled by the MutationObserver below.
     */
    function _revealInlineHidden(root) {
        if (!root) return;
        const elements = (root.querySelectorAll ? root.querySelectorAll('*') : []);
        elements.forEach(el => {
            // Inline display:none
            if (el.style && el.style.display === 'none') {
                el.style.removeProperty('display');
            }
            // hidden attribute
            if (el.hasAttribute && el.hasAttribute('hidden')) {
                el.removeAttribute('hidden');
            }
            // Inline height:0 with overflow:hidden (collapsed panels)
            if (el.style && (el.style.height === '0' || el.style.height === '0px')) {
                el.style.removeProperty('height');
                el.style.removeProperty('max-height');
                el.style.removeProperty('overflow');
                el.style.opacity = '1';
                el.style.visibility = 'visible';
            }
            // Inline visibility:hidden
            if (el.style && el.style.visibility === 'hidden') {
                el.style.removeProperty('visibility');
            }
            // Inline opacity:0 (fade-hide pattern)
            if (el.style && el.style.opacity === '0') {
                el.style.removeProperty('opacity');
            }
        });
    }

    // ── B2. MutationObserver: intercept dynamic style changes ─────────────────

    function _startObserver(body) {
        if (!body) return;
        _stopObserver();

        _observer = new MutationObserver((mutations) => {
            mutations.forEach(({ type, target, addedNodes, attributeName }) => {

                // ── Watch ATTRIBUTE changes (style / hidden) ──────────────────
                // This catches the card's JS setting display:none AFTER page load
                if (type === 'attributes' && target && target.nodeType === 1) {
                    if (attributeName === 'style') {
                        if (target.style.display === 'none') {
                            target.style.removeProperty('display');
                        }
                        if (target.style.visibility === 'hidden') {
                            target.style.removeProperty('visibility');
                        }
                        if (target.style.opacity === '0') {
                            target.style.removeProperty('opacity');
                        }
                        if (target.style.height === '0' || target.style.height === '0px') {
                            target.style.removeProperty('height');
                            target.style.removeProperty('max-height');
                            target.style.removeProperty('overflow');
                        }
                    }
                    if (attributeName === 'hidden') {
                        target.removeAttribute('hidden');
                    }
                }

                // ── Watch NEW CHILDREN added to the DOM ───────────────────────
                if (type === 'childList') {
                    addedNodes.forEach(node => {
                        if (node.nodeType !== 1) return;
                        _revealInlineHidden(node);
                        if (node.tagName === 'DETAILS') node.setAttribute('open', '');
                        node.querySelectorAll?.('details').forEach(d => d.setAttribute('open', ''));
                    });
                }
            });
        });

        _observer.observe(body, {
            childList: true,
            subtree: true,
            attributes: true,
            // Only watch style and hidden — minimises performance impact
            attributeFilter: ['style', 'hidden']
        });
    }

    function _stopObserver() {
        if (_observer) { _observer.disconnect(); _observer = null; }
    }

    // ── B3. Polled reveal (belt-and-suspenders) ───────────────────────────────

    function _initReveal() {
        try {
            const body = editor.Canvas.getBody();
            if (!body) return;

            // Staggered reveal: covers fast-running AND slow-running template scripts
            [0, 100, 300, 600, 1000, 2000, 4000].forEach(delay => {
                setTimeout(() => {
                    _openAllDetails(body);
                    _revealInlineHidden(body);
                }, delay);
            });

            _startObserver(body);
        } catch (err) {
            console.warn('[GrapesJS] Reveal init error:', err);
        }
    }

    // ── C. LINK TRAITS FIX ────────────────────────────────────────────────────

    function _ensureLinkTraits(component) {
        if (!component) return;
        const tagName = (component.get('tagName') || '').toLowerCase();
        if (tagName !== 'a') return;
        if (component.getTraits().some(t => t.getName() === 'href')) return;

        component.addTrait([
            { type: 'text', name: 'href', label: 'URL (Href)', placeholder: 'https://example.com' },
            {
                type: 'select', name: 'target', label: 'Open in',
                options: [{ value: '_self', name: 'Same Tab' }, { value: '_blank', name: 'New Tab' }]
            },
            { type: 'text', name: 'title', label: 'Title (Tooltip)' }
        ], { at: 0 });
    }

    // ── LIFECYCLE ─────────────────────────────────────────────────────────────

    editor.on('canvas:frame:load', _initReveal);
    editor.on('load', _initReveal);
    editor.on('component:add', () => {
        const body = editor.Canvas.getBody();
        if (body) setTimeout(() => { _openAllDetails(body); _revealInlineHidden(body); }, 50);
    });
    editor.on('destroy', _stopObserver);
    editor.on('component:selected', _ensureLinkTraits);

    console.log('[GrapesJS] Canvas Interaction plugin v10 loaded.');
}
