/**
 * Plugin: Editor Animation Safe Mode
 *
 * Goal:
 * - Keep GSAP/ScrollTrigger "pinned" sections editable/visible in GrapesJS canvas
 * - Prevent runtime pin styles (height:1px, inset, fixed pin styles, etc.)
 *   from leaking into saved HTML
 *
 * Scope:
 * - Editor iframe only
 * - Export-time HTML string sanitization only
 * - No preview/publish runtime override
 */
export default function editorAnimationSafeModePlugin(editor) {
    const GSAP_SECTION_SELECTOR = '[data-gsap-section], #solution';
    const GSAP_ITEM_SELECTOR = '[data-gsap-item], .slide-solution';

    const RUNTIME_LAYOUT_PROPS = [
        'position',
        'top',
        'right',
        'bottom',
        'left',
        'inset',
        'width',
        'height',
        'max-width',
        'max-height',
        'min-height',
        'margin',
        'padding',
        'box-sizing',
        'transform',
        'translate',
        'rotate',
        'scale',
    ];

    const SLIDE_VISIBILITY_PROPS = [
        'display',
        'opacity',
        'visibility',
        'transform',
        'filter',
        'pointer-events',
    ];

    const hasPinRuntimeSignature = (styleText) => {
        if (!styleText) return false;
        return /(position\s*:\s*fixed|inset\s*:|translate\s*:|rotate\s*:|scale\s*:|max-height\s*:\s*1px|height\s*:\s*1px|top\s*:\s*0px|left\s*:\s*0px)/i.test(styleText);
    };

    const stripInlineProps = (el, props) => {
        if (!el || !el.style) return;
        props.forEach((prop) => el.style.removeProperty(prop));
        if (!(el.getAttribute('style') || '').trim()) {
            el.removeAttribute('style');
        }
    };

    const unwrapPinSpacers = (doc) => {
        const spacers = Array.from(doc.querySelectorAll('.pin-spacer'));
        spacers.forEach((spacer) => {
            if (!spacer.querySelector(GSAP_SECTION_SELECTOR)) return;
            const parent = spacer.parentNode;
            if (!parent) return;
            while (spacer.firstChild) {
                parent.insertBefore(spacer.firstChild, spacer);
            }
            parent.removeChild(spacer);
        });
    };

    const sanitizeGsapRuntimeState = (doc) => {
        // Remove pin/spacer wrappers captured from editor runtime.
        unwrapPinSpacers(doc);

        const sections = Array.from(doc.querySelectorAll(GSAP_SECTION_SELECTOR));
        sections.forEach((section) => {
            const sectionStyle = section.getAttribute('style') || '';
            if (hasPinRuntimeSignature(sectionStyle)) {
                stripInlineProps(section, RUNTIME_LAYOUT_PROPS);
            }

            const runtimeStyledNodes = Array.from(section.querySelectorAll('[style]'));
            runtimeStyledNodes.forEach((node) => {
                const styleText = node.getAttribute('style') || '';
                if (!hasPinRuntimeSignature(styleText)) return;
                stripInlineProps(node, RUNTIME_LAYOUT_PROPS);
            });

            // In saved HTML we never want editor/runtime hidden-state on GSAP items.
            const items = Array.from(section.querySelectorAll(`${GSAP_ITEM_SELECTOR}[style]`));
            items.forEach((item) => {
                stripInlineProps(item, SLIDE_VISIBILITY_PROPS);
            });
        });
    };

    const sanitizeHtmlForExport = (html) => {
        if (typeof html !== 'string' || html.trim() === '') return html;

        try {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            sanitizeGsapRuntimeState(doc);
            return doc.body?.innerHTML || html;
        } catch (error) {
            console.warn('[GrapesJS] animation-safe export sanitize failed:', error);
            return html;
        }
    };

    const injectEditorOnlyCss = () => {
        const canvasDoc = editor.Canvas.getDocument();
        if (!canvasDoc) return;
        if (canvasDoc.getElementById('editor-animation-safe-mode-css')) return;

        const style = canvasDoc.createElement('style');
        style.id = 'editor-animation-safe-mode-css';
        style.textContent = `
            :is(${GSAP_SECTION_SELECTOR}) {
                position: relative !important;
                min-height: 100vh !important;
                height: auto !important;
                max-height: none !important;
                overflow: visible !important;
            }
            :is(${GSAP_SECTION_SELECTOR}) .ui-layer {
                position: relative !important;
                inset: auto !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                gap: 16px !important;
                overflow: visible !important;
                pointer-events: auto !important;
            }
            :is(${GSAP_SECTION_SELECTOR}) :is(${GSAP_ITEM_SELECTOR}) {
                position: relative !important;
                inset: auto !important;
                display: flex !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                filter: none !important;
                pointer-events: auto !important;
            }
        `;
        canvasDoc.head.appendChild(style);
    };

    // Editor-only runtime guard: avoid ScrollTrigger pinning the section in iframe.
    const patchAnimationRuntimeInEditor = () => {
        const frameWin = editor.Canvas.getWindow();
        const frameDoc = editor.Canvas.getDocument();
        if (!frameWin || frameWin.__gjsAnimationSafeModePatched) return;
        frameWin.__gjsAnimationSafeModePatched = true;

        const isInMarkedSection = (node) => {
            if (!node || typeof node !== 'object') return false;
            if (typeof node.matches === 'function' && node.matches(GSAP_SECTION_SELECTOR)) return true;
            if (typeof node.closest === 'function' && node.closest(GSAP_SECTION_SELECTOR)) return true;
            return false;
        };

        const resolveMarkedTrigger = (trigger) => {
            if (!trigger) return false;
            if (typeof trigger === 'string') {
                const el = frameDoc?.querySelector(trigger);
                return isInMarkedSection(el);
            }
            if (Array.isArray(trigger)) {
                return trigger.some((item) => resolveMarkedTrigger(item));
            }
            if (typeof trigger.length === 'number' && typeof trigger !== 'function' && !trigger.nodeType) {
                return Array.from(trigger).some((item) => resolveMarkedTrigger(item));
            }
            return isInMarkedSection(trigger);
        };

        const tryPatch = () => {
            const ScrollTrigger = frameWin.ScrollTrigger;
            if (!ScrollTrigger || ScrollTrigger.__gjsAnimationSafePatched) return false;

            const originalCreate = typeof ScrollTrigger.create === 'function'
                ? ScrollTrigger.create.bind(ScrollTrigger)
                : null;

            if (originalCreate) {
                ScrollTrigger.create = (config = {}) => {
                    if (!resolveMarkedTrigger(config.trigger)) {
                        return originalCreate(config);
                    }
                    const safeConfig = { ...config, pin: false, pinSpacing: false };
                    return originalCreate(safeConfig);
                };
            }

            ScrollTrigger.__gjsAnimationSafePatched = true;
            return true;
        };

        if (tryPatch()) return;

        const timer = frameWin.setInterval(() => {
            if (tryPatch()) {
                frameWin.clearInterval(timer);
            }
        }, 120);

        frameWin.setTimeout(() => frameWin.clearInterval(timer), 8000);
    };

    editor.Commands.add('animation-safe:prepare-html-export', {
        run(_editor, _sender, options = {}) {
            const rawHtml = typeof options.html === 'string' ? options.html : '';
            return sanitizeHtmlForExport(rawHtml);
        },
    });

    editor.on('load', patchAnimationRuntimeInEditor);
    editor.on('canvas:frame:load', () => {
        injectEditorOnlyCss();
        patchAnimationRuntimeInEditor();
    });
}
