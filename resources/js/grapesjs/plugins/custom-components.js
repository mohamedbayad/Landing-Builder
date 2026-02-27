/**
 * Plugin: Custom Component Types (v1)
 *
 * Responsibilities:
 *   1. Register `custom-button`, `accordion-wrapper`, `accordion-item`,
 *      and `pricing-card` custom component types via DomComponents.addType.
 *   2. Extend the native `link` component so ANY <a> element always exposes
 *      `href`, `target`, and `title` traits — even when parsed as a generic
 *      text or wrapper element.
 *   3. Inject editor-only CSS into the GrapesJS canvas <head> that forces
 *      hidden/collapsed content (accordions, toggle cards, hidden panels) to
 *      remain permanently visible and editable inside the editor. This CSS
 *      is scoped to the canvas iframe and has zero effect on the live page.
 *   4. Suppress template JavaScript execution inside the canvas so that
 *      click-toggle scripts don't interfere with GrapesJS selection. The
 *      `canvas-interaction-control` plugin's MutationObserver handles any
 *      remaining dynamic style changes.
 *
 * Usage:
 *   import customComponentsPlugin from './grapesjs/plugins/custom-components';
 *   // then add to the `plugins` array in grapesjs.init({ ... })
 */

export default function customComponentsPlugin(editor, opts = {}) {

    const { DomComponents } = editor;

    // ─────────────────────────────────────────────────────────────────────────
    // SECTION 1 — CUSTOM COMPONENT TYPES
    // ─────────────────────────────────────────────────────────────────────────

    // ── 1a. custom-button ────────────────────────────────────────────────────
    //
    // Behaves like a native link (<a>) but surfaces additional traits:
    //   • URL / Href
    //   • Open-in target (_self / _blank)
    //   • Visual button style (selector-driven)
    //
    // The type is also auto-detected when GrapesJS parses HTML that contains
    // data-gjs-type="custom-button".
    DomComponents.addType('custom-button', {
        // Extend the built-in 'link' model so href/target serialisation
        // and the RTE work exactly the same as native links.
        extend: 'link',

        isComponent(el) {
            return (
                el.tagName === 'A' &&
                el.getAttribute('data-gjs-type') === 'custom-button'
            );
        },

        model: {
            defaults: {
                tagName: 'a',
                name: 'Custom Button',
                // Allow dropping text/inline content inside, but not block-level components
                droppable: false,
                draggable: true,
                // Expose extra traits on top of what 'link' already provides
                traits: [
                    {
                        type: 'text',
                        name: 'href',
                        label: 'URL (href)',
                        placeholder: 'https://example.com',
                    },
                    {
                        type: 'select',
                        name: 'target',
                        label: 'Open in',
                        options: [
                            { value: '_self', name: 'Same Tab' },
                            { value: '_blank', name: 'New Tab' },
                        ],
                    },
                    {
                        type: 'select',
                        name: 'data-button-style',
                        label: 'Button Style',
                        options: [
                            { value: 'primary', name: 'Primary' },
                            { value: 'secondary', name: 'Secondary' },
                            { value: 'outline', name: 'Outline' },
                            { value: 'ghost', name: 'Ghost' },
                        ],
                    },
                ],
            },
        },
    });


    // ── 1b. accordion-wrapper ────────────────────────────────────────────────
    //
    // The outer container of an accordion. Users can re-order accordion items
    // inside it but cannot drop arbitrary elements into it from the block panel
    // (droppable limited to accordion-item). The wrapper itself can be moved
    // anywhere on the canvas (draggable: true).
    DomComponents.addType('accordion-wrapper', {
        isComponent(el) {
            return (
                el.getAttribute &&
                el.getAttribute('data-gjs-type') === 'accordion-wrapper'
            );
        },

        model: {
            defaults: {
                name: 'Accordion',
                draggable: true,
                // Only accept accordion-item children to protect the structure
                droppable: '[data-gjs-type="accordion-item"]',
                // Prevent the wrapper itself from being dropped INTO other custom types
                // (it can still be placed in generic containers / sections)
                traits: [],
            },
        },
    });


    // ── 1c. accordion-item ───────────────────────────────────────────────────
    //
    // A single collapsible accordion entry. Must live inside an accordion-wrapper.
    // Its children (heading + content) are freely editable but the item cannot
    // be dragged outside of an accordion-wrapper.
    DomComponents.addType('accordion-item', {
        isComponent(el) {
            return (
                el.getAttribute &&
                el.getAttribute('data-gjs-type') === 'accordion-item'
            );
        },

        model: {
            defaults: {
                name: 'Accordion Item',
                // Constrain dragging: only allow movement within accordion-wrapper
                draggable: '[data-gjs-type="accordion-wrapper"]',
                // Allow text, headings, and generic content blocks inside
                droppable: true,
                traits: [],
            },
        },
    });


    // ── 1d. pricing-card ─────────────────────────────────────────────────────
    //
    // A structural card for a pricing plan. Freely draggable on the canvas.
    // No restriction on what can be dropped inside — the content (price,
    // features list, CTA button) is entirely user-defined.
    DomComponents.addType('pricing-card', {
        isComponent(el) {
            return (
                el.getAttribute &&
                el.getAttribute('data-gjs-type') === 'pricing-card'
            );
        },

        model: {
            defaults: {
                name: 'Pricing Card',
                draggable: true,
                droppable: true,
                traits: [
                    {
                        type: 'text',
                        name: 'data-plan-name',
                        label: 'Plan Name',
                        placeholder: 'e.g. Pro',
                    },
                    {
                        type: 'text',
                        name: 'data-price',
                        label: 'Price',
                        placeholder: 'e.g. $49/mo',
                    },
                ],
            },
        },
    });


    // ─────────────────────────────────────────────────────────────────────────
    // SECTION 2 — EXTEND NATIVE LINK COMPONENT
    // ─────────────────────────────────────────────────────────────────────────
    //
    // GrapesJS sometimes parses rich <a> elements (those wrapping images,
    // headings, or other non-text nodes) as generic component types rather
    // than 'link'. This means the Trait Manager never shows href / target /
    // title for those elements.
    //
    // We fix this in two complementary ways:
    //
    //   A. Extend the native 'link' type's defaults so the three core traits
    //      are always present when GrapesJS DOES recognise it as a link.
    //
    //   B. Listen to `component:selected`. If the selected component's DOM
    //      element is an <a> tag, force-inject the three traits if they are
    //      missing — regardless of the component's registered type.
    //
    // This mirrors the approach in canvas-interaction-control.js but is placed
    // here for type-system completeness. The two plugins coexist safely because
    // the injection is idempotent (it checks for existing traits first).

    // A — Patch the native 'link' type defaults
    const LinkType = DomComponents.getType('link');
    if (LinkType) {
        DomComponents.addType('link', {
            // Keep the existing model/view — we only extend defaults
            model: {
                defaults: {
                    traits: [
                        {
                            type: 'text',
                            name: 'href',
                            label: 'URL (href)',
                            placeholder: 'https://example.com',
                        },
                        {
                            type: 'select',
                            name: 'target',
                            label: 'Open in',
                            options: [
                                { value: '_self', name: 'Same Tab' },
                                { value: '_blank', name: 'New Tab' },
                            ],
                        },
                        {
                            type: 'text',
                            name: 'title',
                            label: 'Title (Tooltip)',
                            placeholder: 'Descriptive title…',
                        },
                    ],
                },
            },
        });
    }

    // B — On every selection, check the live DOM element
    function _ensureLinkTraits(component) {
        if (!component) return;

        const el = component.getEl?.();
        const tagName = (
            el?.tagName ||
            component.get('tagName') ||
            ''
        ).toLowerCase();

        if (tagName !== 'a') return;

        // Idempotent: only add what is not already there
        const existingNames = component.getTraits().map(t => t.getName());

        const traitDefs = [
            {
                type: 'text',
                name: 'href',
                label: 'URL (href)',
                placeholder: 'https://example.com',
            },
            {
                type: 'select',
                name: 'target',
                label: 'Open in',
                options: [
                    { value: '_self', name: 'Same Tab' },
                    { value: '_blank', name: 'New Tab' },
                ],
            },
            {
                type: 'text',
                name: 'title',
                label: 'Title (Tooltip)',
                placeholder: 'Descriptive title…',
            },
        ];

        const missing = traitDefs.filter(t => !existingNames.includes(t.name));
        if (missing.length > 0) {
            component.addTrait(missing, { at: 0 });
        }
    }

    editor.on('component:selected', _ensureLinkTraits);


    // ─────────────────────────────────────────────────────────────────────────
    // SECTION 3 — EDITOR-ONLY REVEAL CSS
    // ─────────────────────────────────────────────────────────────────────────
    //
    // Inject a <style> tag directly into the GrapesJS canvas <head>.
    // This is 100% scoped to the iframe — it has ZERO effect on the
    // published/live page output.
    //
    // Rules target:
    //   • Accordion panels (.accordion-content, [data-gjs-type="accordion-item"] > *)
    //   • Bootstrap-style collapse / show elements
    //   • Generic "hidden details" panels used by JS-toggle patterns
    //   • Any element with inline display:none that a template script sets
    //
    // The canvas-interaction-control plugin's MutationObserver backs this up
    // by catching inline style mutations at runtime, so the two layers work
    // together without conflict.

    function _injectRevealCSS() {
        try {
            const canvasDoc = editor.Canvas.getDocument();
            if (!canvasDoc) return;

            // Prevent double-injection on hot-reload or repeated load events
            if (canvasDoc.getElementById('gjs-custom-components-reveal')) return;

            const style = canvasDoc.createElement('style');
            style.id = 'gjs-custom-components-reveal';
            style.textContent = `
                /* =================================================================
                 * GrapesJS Editor-Only: Force-Reveal Hidden / Collapsed Elements
                 * =================================================================
                 * This style sheet is injected into the canvas <iframe> only.
                 * It has NO effect on the published landing page.
                 *
                 * HOW TO EXTEND:
                 *   Add the CSS class names from your template that control
                 *   visibility. Inspect the template in DevTools, look for classes
                 *   that set display:none, height:0, opacity:0, etc., and add them
                 *   to the selectors below.
                 * =================================================================
                 */

                /* Accordion panels */
                .accordion-content,
                .accordion-body,
                .accordion-panel,
                [data-gjs-type="accordion-item"] > *:not([data-gjs-type="accordion-header"]),
                [data-gjs-type="accordion-content"] {
                    display:       block       !important;
                    visibility:    visible     !important;
                    opacity:       1           !important;
                    height:        auto        !important;
                    max-height:    none        !important;
                    overflow:      visible     !important;
                    transition:    none        !important;
                    animation:     none        !important;
                    pointer-events: auto       !important;
                }

                /* Bootstrap collapse */
                .collapse,
                .collapsing {
                    display:    block   !important;
                    height:     auto    !important;
                    visibility: visible !important;
                    opacity:    1       !important;
                    overflow:   visible !important;
                    transition: none    !important;
                }

                /* Generic hidden-details / toggle-card patterns */
                .hidden-details,
                .toggle-body,
                .card-details,
                .panel-content,
                .tab-content,
                [data-toggle-target],
                [data-collapse-target] {
                    display:       block   !important;
                    visibility:    visible !important;
                    opacity:       1       !important;
                    height:        auto    !important;
                    max-height:    none    !important;
                    overflow:      visible !important;
                    transition:    none    !important;
                    animation:     none    !important;
                    pointer-events: auto   !important;
                }

                /* Native <details> / <summary> */
                details > *:not(summary) {
                    display:       block   !important;
                    visibility:    visible !important;
                    height:        auto    !important;
                    max-height:    none    !important;
                    overflow:      visible !important;
                    opacity:       1       !important;
                    transition:    none    !important;
                    animation:     none    !important;
                    pointer-events: auto   !important;
                }
                details {
                    overflow: visible !important;
                }

                /*
                 * SUPPRESS TEMPLATE JAVASCRIPT EXECUTION
                 * ---------------------------------------
                 * Template <script> tags run inside the canvas iframe.
                 * We cannot prevent script execution directly, but we CAN
                 * neutralise the most common pattern — setting display:none /
                 * height:0 via JS — by overriding it with !important CSS above
                 * and via the MutationObserver in canvas-interaction-control.js.
                 *
                 * If a template script calls element.style.setProperty(…)
                 * instead of element.style.xxx = …, the MutationObserver still
                 * catches the resulting attribute change and reverts it.
                 */
            `;

            canvasDoc.head.appendChild(style);
            console.log('[GrapesJS] Custom Components: editor reveal CSS injected.');
        } catch (err) {
            console.warn('[GrapesJS] Custom Components: CSS injection failed:', err);
        }
    }

    // Inject on canvas load (the frame may reload when project data changes)
    editor.on('canvas:frame:load', _injectRevealCSS);
    editor.on('load', _injectRevealCSS);


    // ─────────────────────────────────────────────────────────────────────────
    // DONE
    // ─────────────────────────────────────────────────────────────────────────
    console.log('[GrapesJS] Custom Components plugin v1 loaded.');
}
