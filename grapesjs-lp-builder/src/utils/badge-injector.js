/**
 * Canvas badge and collapsed dynamic-section helpers for LP Builder sections.
 * These editor-only overlays make LP sections identifiable without changing
 * the exported section DOM structure.
 */

export const LP_BUILDER_STYLE_ID = 'lp-builder-styles';

export const BADGE_ICONS = Object.freeze({
    gsap: '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2 11.5C5.4 11.5 5.1 4 8 4s2.6 7.5 6 7.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
    three: '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 1.8 13.4 5v6L8 14.2 2.6 11V5L8 1.8Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>',
    standard: '<svg width="13" height="13" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 4h10M3 8h10M3 12h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
});

const SECTION_TYPES = new Set(['standard-section', 'gsap-animated', 'threejs-scene']);
const DYNAMIC_SECTION_TYPES = new Set(['gsap-animated', 'threejs-scene']);
const COLLAPSED_ICON = '\u2922';
const EXPANDED_ICON = '\u2921';

const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const canvasStyles = `
[data-lp-badge] {
  position: absolute;
  top: 8px;
  left: 8px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 8px 3px 5px;
  border-radius: 4px;
  font-family: sans-serif;
  font-size: 11px;
  font-weight: 500;
  pointer-events: none;
  z-index: 9999;
  line-height: 1;
}
.lp-badge--gsap-animated { background: #EEEDFE; color: #534AB7; }
.lp-badge--threejs-scene { background: #E1F5EE; color: #0F6E56; }
.lp-badge--standard-section { background: #F1EFE8; color: #5F5E5A; }
[data-lp-badge] .lp-badge-meta {
  margin-left: 4px;
  opacity: 0.7;
  font-weight: 400;
}
[data-lp-toggle],
[data-lp-collapse] {
  pointer-events: auto;
}
[data-lp-toggle] {
  margin-left: 6px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 11px;
  padding: 0 2px;
  color: inherit;
  opacity: 0.7;
}
[data-lp-collapse] {
  margin-left: 6px;
  border: 0;
  border-radius: 3px;
  background: rgba(255,255,255,0.7);
  color: inherit;
  cursor: pointer;
  font-size: 10px;
  padding: 1px 5px;
}
[data-lp-overlay] {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  z-index: 100;
  transition: background 0.15s;
}
[data-lp-overlay]:hover {
  background: rgba(255,255,255,0.25);
}
.lp-overlay-icon {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.lp-gsap [data-lp-overlay] .lp-overlay-icon { background: #EEEDFE; color: #534AB7; }
.lp-3d [data-lp-overlay] .lp-overlay-icon { background: #E1F5EE; color: #0F6E56; }
.lp-overlay-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}
.lp-overlay-title {
  font-size: 12px;
  font-weight: 600;
  font-family: sans-serif;
}
.lp-gsap [data-lp-overlay] .lp-overlay-title { color: #534AB7; }
.lp-3d [data-lp-overlay] .lp-overlay-title { color: #0F6E56; }
.lp-overlay-hint {
  font-size: 10px;
  font-family: sans-serif;
  color: #888780;
}
.lp-overlay-arrow {
  font-size: 14px;
  color: #888780;
  font-family: sans-serif;
}
.lp-section {
  min-height: 80px;
  position: relative;
}
.lp-section-selected {
  outline: 2px solid #7F77DD !important;
  outline-offset: 2px;
}
`;

/**
 * Inject the LP Builder canvas CSS into the iframe.
 * @param {Document} doc
 */
export function injectLpBuilderCanvasStyles(doc) {
    if (!doc?.head) {
        return;
    }

    let style = doc.getElementById(LP_BUILDER_STYLE_ID);
    if (!style) {
        style = doc.createElement('style');
        style.id = LP_BUILDER_STYLE_ID;
        doc.head.appendChild(style);
    }

    style.textContent = canvasStyles;
}

const resolveSectionType = (model, el) => {
    const attrs = model?.getAttributes?.() || {};
    return String(attrs['data-gjs-type'] || el?.getAttribute?.('data-gjs-type') || model?.get?.('type') || '').trim();
};

const resolveSectionLabel = (model, el) => {
    const attrs = model?.getAttributes?.() || {};
    return String(attrs['data-label'] || attrs.id || el?.getAttribute?.('data-label') || el?.id || 'Section').trim();
};

const resolveBadgeContent = (model, el) => {
    const attrs = model?.getAttributes?.() || {};
    const type = resolveSectionType(model, el);

    if (type === 'gsap-animated') {
        const animation = attrs['data-gsap-animation'] || el?.getAttribute?.('data-gsap-animation') || 'fadeInUp';
        const trigger = attrs['data-gsap-trigger'] || el?.getAttribute?.('data-gsap-trigger') || 'scroll';
        const duration = attrs['data-gsap-duration'] || el?.getAttribute?.('data-gsap-duration') || '1';
        return {
            icon: BADGE_ICONS.gsap,
            label: 'GSAP Animation',
            meta: `${animation} - ${trigger} - ${duration}s`,
        };
    }

    if (type === 'threejs-scene') {
        const sceneType = attrs['data-scene-type'] || el?.getAttribute?.('data-scene-type') || 'particles';
        const height = attrs['data-scene-height'] || el?.getAttribute?.('data-scene-height') || '400';
        return {
            icon: BADGE_ICONS.three,
            label: '3D Scene',
            meta: `${sceneType} - height ${height}px`,
        };
    }

    return {
        icon: BADGE_ICONS.standard,
        label: resolveSectionLabel(model, el),
        meta: '',
    };
};

const getDynamicClass = (type) => (type === 'gsap-animated' ? 'lp-gsap' : 'lp-3d');

const getCollapsedStyle = (type) => {
    const common = [
        'min-height:64px !important',
        'max-height:64px !important',
        'overflow:hidden !important',
        'position:relative !important',
        'border-radius:6px !important',
    ];

    if (type === 'gsap-animated') {
        common.push(
            'background:repeating-linear-gradient(-45deg,#EEEDFE,#EEEDFE 4px,#ffffff 4px,#ffffff 14px) !important',
            'border:1.5px solid #AFA9EC !important',
        );
    } else {
        common.push(
            'background:repeating-linear-gradient(-45deg,#E1F5EE,#E1F5EE 4px,#ffffff 4px,#ffffff 14px) !important',
            'border:1.5px solid #5DCAA5 !important',
        );
    }

    return common.join(';') + ';';
};

const rememberChildStyles = (child) => {
    if (!child.__lpDynamicOriginalStyle) {
        child.__lpDynamicOriginalStyle = {
            visibility: child.style.visibility || '',
            position: child.style.position || '',
            pointerEvents: child.style.pointerEvents || '',
        };
    }
};

const shouldSkipDynamicChild = (child) => (
    child.hasAttribute?.('data-lp-badge') || child.hasAttribute?.('data-lp-overlay')
);

const hideRealChildren = (el) => {
    Array.from(el.children || []).forEach((child) => {
        if (shouldSkipDynamicChild(child)) {
            return;
        }
        rememberChildStyles(child);
        child.style.visibility = 'hidden';
        child.style.position = 'absolute';
        child.style.pointerEvents = 'none';
    });
};

const showRealChildren = (el) => {
    Array.from(el.children || []).forEach((child) => {
        if (shouldSkipDynamicChild(child)) {
            return;
        }
        const original = child.__lpDynamicOriginalStyle || {};
        child.style.visibility = original.visibility || '';
        child.style.position = original.position || '';
        child.style.pointerEvents = original.pointerEvents || '';
    });
};

const upsertOverlay = (model, el, editor) => {
    const type = resolveSectionType(model, el);
    if (!DYNAMIC_SECTION_TYPES.has(type)) {
        return null;
    }

    // Keep the Three.js canvas clean in editor mode (no center helper overlay).
    // Slide/content editing is handled from the sidebar navigator.
    if (type === 'threejs-scene') {
        const existing = el.querySelector(':scope > [data-lp-overlay]');
        if (existing) {
            existing.remove();
        }
        return null;
    }

    let overlay = el.querySelector(':scope > [data-lp-overlay]');
    if (!overlay) {
        overlay = el.ownerDocument.createElement('div');
        overlay.setAttribute('data-lp-overlay', 'true');
        overlay.setAttribute('data-gjs-selectable', 'false');
        overlay.setAttribute('contenteditable', 'false');
    }
    el.appendChild(overlay);

    const isGsap = type === 'gsap-animated';
    overlay.innerHTML = `
        <div class="lp-overlay-icon">${isGsap ? BADGE_ICONS.gsap : BADGE_ICONS.three}</div>
        <div class="lp-overlay-text">
            <span class="lp-overlay-title">${escapeHtml(resolveSectionLabel(model, el))}</span>
            <span class="lp-overlay-hint">Click to edit in sidebar</span>
        </div>
        <div class="lp-overlay-arrow">-&gt;</div>
    `;

    if (!overlay.__lpOverlayClickBound) {
        overlay.__lpOverlayClickBound = true;
        overlay.addEventListener('click', (event) => {
            event.stopPropagation();
            editor?.select?.(model);
            editor?.Panels?.getButton?.('views', 'open-tm')?.set?.('active', true);
            editor?.trigger?.('lp:section:focus', { cid: model.cid });
        });
    }

    overlay.style.display = el.__lpDynamicExpanded ? 'none' : 'flex';
    return overlay;
};

/**
 * Return whether the dynamic section is currently collapsed.
 * @param {HTMLElement} el
 * @returns {boolean}
 */
export function isDynamicSectionCollapsed(el) {
    return !!el?.__lpDynamicCollapsed;
}

/**
 * Inject or update a badge for an LP section component view.
 * @param {import('grapesjs').Component} model
 * @param {HTMLElement} el
 * @param {import('grapesjs').Editor} [editor]
 */
export function upsertSectionBadge(model, el, editor) {
    if (!el) {
        return;
    }

    const type = resolveSectionType(model, el);
    if (!SECTION_TYPES.has(type)) {
        return;
    }

    el.classList.add('lp-section');
    if (!el.style.minHeight) {
        el.style.minHeight = '80px';
    }
    if (!el.style.position || el.style.position === 'static') {
        el.style.position = 'relative';
    }

    let badge = el.querySelector(':scope > [data-lp-badge]');
    if (!badge) {
        badge = el.ownerDocument.createElement('div');
        badge.setAttribute('data-lp-badge', 'true');
        badge.setAttribute('data-gjs-selectable', 'false');
        badge.setAttribute('contenteditable', 'false');
        el.insertBefore(badge, el.firstChild);
    }

    const content = resolveBadgeContent(model, el);
    const isDynamic = DYNAMIC_SECTION_TYPES.has(type);
    badge.className = `lp-section-badge lp-badge--${type}`;
    badge.innerHTML = [
        `<span class="lp-badge-icon">${content.icon}</span>`,
        `<span class="lp-badge-label">${escapeHtml(content.label)}</span>`,
        content.meta ? `<span class="lp-badge-meta">${escapeHtml(content.meta)}</span>` : '',
        isDynamic && el.__lpTemporaryExpanded ? '<button type="button" data-lp-collapse>Collapse</button>' : '',
        isDynamic ? `<button type="button" data-lp-toggle aria-label="Toggle section">${el.__lpDynamicExpanded ? EXPANDED_ICON : COLLAPSED_ICON}</button>` : '',
    ].join('');

    const toggle = badge.querySelector('[data-lp-toggle]');
    if (toggle && editor) {
        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            if (el.__lpDynamicExpanded) {
                collapseDynamicSection(model, el, editor);
            } else {
                expandDynamicSection(model, el, editor);
            }
        });
    }

    const collapse = badge.querySelector('[data-lp-collapse]');
    if (collapse && editor) {
        collapse.addEventListener('click', (event) => {
            event.stopPropagation();
            collapseDynamicSection(model, el, editor);
        });
    }
}

/**
 * Collapse a GSAP/Three.js section into its compact editor placeholder.
 * @param {import('grapesjs').Component} model
 * @param {HTMLElement} el
 * @param {import('grapesjs').Editor} editor
 */
export function collapseDynamicSection(model, el, editor) {
    if (!el) {
        return;
    }

    const type = resolveSectionType(model, el);
    if (!DYNAMIC_SECTION_TYPES.has(type)) {
        upsertSectionBadge(model, el, editor);
        return;
    }

    clearTimeout(el.__lpReCollapseTimer);
    el.__lpDynamicCollapsed = true;
    el.__lpDynamicExpanded = false;
    el.__lpTemporaryExpanded = false;
    el.__lpExpandedStyleSnapshot = el.__lpExpandedStyleSnapshot || el.getAttribute('style') || '';
    el.classList.add(getDynamicClass(type));
    el.style.cssText += getCollapsedStyle(type);
    el.style.outline = '';
    hideRealChildren(el);
    upsertSectionBadge(model, el, editor);
    upsertOverlay(model, el, editor);
    editor?.trigger?.('lp:section:collapsed', { cid: model.cid });
}

/**
 * Expand a collapsed GSAP/Three.js section so real content can be inspected.
 * @param {import('grapesjs').Component} model
 * @param {HTMLElement} el
 * @param {import('grapesjs').Editor} editor
 * @param {{temporary?: boolean}} [options]
 */
export function expandDynamicSection(model, el, editor, options = {}) {
    if (!el) {
        return;
    }

    const type = resolveSectionType(model, el);
    if (!DYNAMIC_SECTION_TYPES.has(type)) {
        return;
    }

    clearTimeout(el.__lpReCollapseTimer);
    el.__lpDynamicCollapsed = false;
    el.__lpDynamicExpanded = true;
    el.__lpTemporaryExpanded = !!options.temporary;
    el.classList.add(getDynamicClass(type));
    el.style.minHeight = '80px';
    el.style.maxHeight = '';
    el.style.height = '';
    el.style.overflow = '';
    el.style.position = 'relative';
    el.style.background = '';
    el.style.border = '';
    el.style.borderRadius = '';
    el.style.outline = options.temporary ? '2px solid #7F77DD' : '';
    showRealChildren(el);

    const overlay = el.querySelector(':scope > [data-lp-overlay]');
    if (overlay) {
        overlay.style.display = 'none';
    }

    upsertSectionBadge(model, el, editor);
    editor?.trigger?.('lp:section:expanded', { cid: model.cid, temporary: !!options.temporary });
}

/**
 * Schedule a dynamic section to collapse again after temporary inspection.
 * @param {import('grapesjs').Component} model
 * @param {HTMLElement} el
 * @param {import('grapesjs').Editor} editor
 * @param {number} delayMs
 */
export function scheduleDynamicReCollapse(model, el, editor, delayMs = 2000) {
    if (!el) {
        return;
    }

    clearTimeout(el.__lpReCollapseTimer);
    el.__lpReCollapseTimer = setTimeout(() => {
        collapseDynamicSection(model, el, editor);
    }, delayMs);
}

/**
 * Update selected outline class in the canvas.
 * @param {import('grapesjs').Editor} editor
 * @param {import('grapesjs').Component|null} component
 */
export function updateCanvasSectionSelection(editor, component) {
    const doc = editor.Canvas.getDocument?.();
    if (!doc) {
        return;
    }

    doc.querySelectorAll('.lp-section-selected').forEach((node) => {
        node.classList.remove('lp-section-selected');
    });

    const attrs = component?.getAttributes?.() || {};
    const type = String(attrs['data-gjs-type'] || component?.get?.('type') || '').trim();
    if (!SECTION_TYPES.has(type)) {
        return;
    }

    component.getEl?.()?.classList.add('lp-section-selected');
}
