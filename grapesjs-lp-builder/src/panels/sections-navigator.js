/**
 * Sections Navigator panel for LP Builder.
 * Replaces the layer-focused view with a section-first accordion and quick traits.
 */

import detectElements from '../utils/element-detector';
import {
    BADGE_ICONS,
    collapseDynamicSection,
    expandDynamicSection,
    scheduleDynamicReCollapse,
    updateCanvasSectionSelection,
} from '../utils/badge-injector';

const SECTION_TYPES = new Set(['standard-section', 'gsap-animated', 'threejs-scene']);

const GSAP_ANIMATIONS = [
    'fadeInUp',
    'fadeInDown',
    'fadeInLeft',
    'fadeInRight',
    'slideInLeft',
    'slideInRight',
    'zoomIn',
    'zoomOut',
    'bounceIn',
    'flipInX',
    'flipInY',
    'rotateIn',
];

const GSAP_TRIGGERS = ['scroll', 'load', 'hover', 'click'];
const GSAP_DURATIONS = ['0.2', '0.4', '0.6', '0.8', '1', '1.2', '1.5', '2', '3', '5'];
const THREE_SCENE_TYPES = ['particles', 'rotating-cube', 'sphere', 'wave', 'globe', 'rings'];
const SLIDE_CLASS = 'slide-solution';

const debounce = (fn, waitMs) => {
    let timer = 0;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), waitMs);
    };
};

const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

const optionHtml = (items, selected) => items
    .map((item) => `<option value="${escapeHtml(item)}" ${String(item) === String(selected) ? 'selected' : ''}>${escapeHtml(item)}</option>`)
    .join('');

const getAttrs = (component) => component?.getAttributes?.() || {};

const getSectionType = (component) => String(
    getAttrs(component)['data-gjs-type'] || component?.get?.('type') || '',
).trim();

const getSectionName = (component) => {
    const attrs = getAttrs(component);
    return String(attrs['data-label'] || attrs.id || 'Section').trim();
};

const getClassTokens = (component) => String(getAttrs(component).class || '')
    .split(/\s+/)
    .map((token) => token.trim())
    .filter(Boolean);

const hasClassToken = (component, token) => getClassTokens(component).includes(token);

const getTagName = (component) => String(component?.get?.('tagName') || '').trim().toLowerCase();

const getComponentText = (component) => {
    if (!component) {
        return '';
    }

    const fromEl = component.getEl?.();
    if (fromEl) {
        return String(fromEl.innerText || fromEl.textContent || '').replace(/\s+/g, ' ').trim();
    }

    const content = component.get?.('content');
    return String(content || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
};

const walkChildren = (component, visitor, depth = 0, maxDepth = 10) => {
    if (!component || depth > maxDepth) {
        return;
    }

    const children = component.components?.();
    if (!children || typeof children.forEach !== 'function') {
        return;
    }

    children.forEach((child) => {
        visitor(child, depth + 1);
        walkChildren(child, visitor, depth + 1, maxDepth);
    });
};

const findSlideComponents = (root) => {
    const slides = [];

    if (!root) {
        return slides;
    }

    if (hasClassToken(root, SLIDE_CLASS)) {
        slides.push(root);
    }

    walkChildren(root, (child) => {
        if (hasClassToken(child, SLIDE_CLASS)) {
            slides.push(child);
        }
    });

    return slides;
};

const findFirstDescendant = (root, predicate) => {
    let found = null;
    walkChildren(root, (child) => {
        if (!found && predicate(child)) {
            found = child;
        }
    });
    return found;
};

const isHeading = (component) => /^h[1-6]$/.test(getTagName(component));
const isParagraph = (component) => getTagName(component) === 'p';
const isIconLike = (component) => {
    const tag = getTagName(component);
    if (tag === 'svg' || tag === 'i') {
        return true;
    }
    if (tag === 'span') {
        const className = getClassTokens(component).join(' ');
        return /(material-icons|material-symbols|icon|fa-|lucide)/i.test(className);
    }
    return false;
};

const getSlideDescriptor = (slide, index = 0) => {
    const heading = findFirstDescendant(slide, isHeading);
    const description = findFirstDescendant(slide, isParagraph);
    const icon = findFirstDescendant(slide, isIconLike);
    const iconTag = getTagName(icon).toUpperCase();
    const iconClass = getClassTokens(icon).join(' ');

    return {
        slide,
        heading,
        description,
        icon,
        title: getComponentText(heading) || `Slide ${index + 1}`,
        text: getComponentText(description),
        iconLabel: icon ? (iconTag === 'SPAN' || iconTag === 'I' ? `${iconTag} ${iconClass}`.trim() : iconTag) : 'No icon detected',
    };
};

const applyTextToComponent = (component, value) => {
    if (!component) {
        return;
    }

    const nextValue = String(value ?? '');

    try {
        component.components(nextValue);
    } catch {
        component.set('content', nextValue);
    }

    component.view?.render?.();
};

const resolveSlidesRoot = (selectedComponent) => {
    if (!selectedComponent) {
        return null;
    }

    // Limit slide discovery to the currently selected section scope only.
    // This prevents slide controls from leaking in when another section
    // (eg. 3D/GSAP) is selected.
    let sectionScope = selectedComponent;
    while (sectionScope) {
        const type = getSectionType(sectionScope);
        if (SECTION_TYPES.has(type)) {
            break;
        }
        sectionScope = sectionScope.parent?.();
    }

    if (sectionScope) {
        const scopedSlides = findSlideComponents(sectionScope);
        return scopedSlides.length > 0 ? sectionScope : null;
    }

    let cursor = selectedComponent;
    while (cursor) {
        if (getSectionType(cursor) === 'wrapper') {
            break;
        }
        const slides = findSlideComponents(cursor);
        if (slides.length > 0) {
            return cursor;
        }
        cursor = cursor.parent?.();
    }

    return null;
};

const sectionMeta = (type) => {
    if (type === 'gsap-animated') {
        return {
            icon: BADGE_ICONS.gsap,
            short: 'GSAP',
            className: 'lp-nav-kind--gsap',
        };
    }
    if (type === 'threejs-scene') {
        return {
            icon: BADGE_ICONS.three,
            short: '3D',
            className: 'lp-nav-kind--three',
        };
    }
    return {
        icon: BADGE_ICONS.standard,
        short: 'STD',
        className: 'lp-nav-kind--std',
    };
};

const getTopLevelSections = (editor) => {
    const wrapper = editor.getWrapper?.();
    const children = wrapper?.components?.();
    if (!children || typeof children.forEach !== 'function') {
        return [];
    }

    const sections = [];
    children.forEach((component) => {
        const type = getSectionType(component);
        if (SECTION_TYPES.has(type)) {
            sections.push(component);
        }
    });
    return sections;
};

const hostStyles = `
.lp-navigator-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 260px;
  background: #11151b;
  border-top: 1px solid rgba(255,255,255,.08);
  color: #d8dee9;
  font-family: sans-serif;
}
.lp-nav-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  font-size: 12px;
  font-weight: 700;
  color: #f7f8fb;
}
.lp-nav-list {
  flex: 1;
  overflow: auto;
  padding: 8px;
}
.lp-section-row {
  display: grid;
  grid-template-columns: 22px 1fr auto 14px;
  gap: 8px;
  align-items: center;
  width: 100%;
  min-height: 36px;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: inherit;
  padding: 7px 8px;
  text-align: left;
  cursor: pointer;
}
.lp-section-row:hover,
.lp-section-row.is-selected {
  background: rgba(238,237,254,.12);
}
.lp-nav-icon {
  width: 22px;
  height: 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 5px;
}
.lp-nav-icon--gsap { background: #EEEDFE; color: #534AB7; }
.lp-nav-icon--three { background: #E1F5EE; color: #0F6E56; }
.lp-nav-icon--std { background: #F1EFE8; color: #5F5E5A; }
.lp-section-name {
  min-width: 0;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: 12px;
  font-weight: 600;
}
.lp-nav-kind {
  border-radius: 999px;
  padding: 2px 6px;
  font-size: 10px;
  font-weight: 700;
}
.lp-nav-kind--gsap { background: #EEEDFE; color: #534AB7; }
.lp-nav-kind--three { background: #E1F5EE; color: #0F6E56; }
.lp-nav-kind--std { background: #F1EFE8; color: #5F5E5A; }
.lp-section-chevron {
  color: #98a2b3;
  font-size: 10px;
  transform: rotate(0deg);
  transition: transform .12s ease;
}
.lp-section-row.is-open .lp-section-chevron {
  transform: rotate(90deg);
}
.lp-elements {
  display: grid;
  gap: 2px;
  margin: 2px 0 8px 30px;
}
.lp-el-row {
  display: grid;
  grid-template-columns: 28px 1fr;
  gap: 8px;
  align-items: center;
  min-height: 44px;
  border-radius: 6px;
  padding: 6px 7px;
  cursor: pointer;
}
.lp-el-row:hover,
.lp-el-row.is-selected {
  background: #EEEDFE;
  color: #534AB7;
}
.lp-el-icon {
  width: 28px;
  height: 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 6px;
  background: var(--color-background-secondary, #171d25);
  color: currentColor;
}
.lp-el-info {
  min-width: 0;
}
.lp-el-type {
  font-size: 10px;
  font-weight: 700;
  color: #98a2b3;
}
.lp-el-content {
  margin-top: 2px;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: 12px;
  color: #d8dee9;
}
.lp-el-row.is-selected .lp-el-type,
.lp-el-row:hover .lp-el-type {
  color: rgba(83,74,183,.72);
}
.lp-el-row.is-selected .lp-el-content {
  color: #534AB7;
}
.lp-nav-empty {
  padding: 18px 10px;
  font-size: 12px;
  color: #98a2b3;
  text-align: center;
}
.lp-nav-traits {
  border-top: 1px solid rgba(255,255,255,.08);
  padding: 10px;
  display: grid;
  gap: 8px;
}
.lp-nav-traits-row {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 6px;
}
.lp-nav-traits input,
.lp-nav-traits select {
  width: 100%;
  min-width: 0;
  height: 28px;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 5px;
  background: #0b0f15;
  color: #f7f8fb;
  font-size: 11px;
  padding: 0 7px;
}
.lp-nav-traits input[type="color"] {
  padding: 2px;
}
.lp-nav-traits input[type="range"] {
  padding: 0;
}
.lp-nav-slides {
  display: grid;
  gap: 10px;
}
.lp-nav-slides-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 11px;
  color: #98a2b3;
}
.lp-nav-slide-list {
  display: grid;
  gap: 6px;
  max-height: 220px;
  overflow: auto;
  padding-right: 2px;
}
.lp-nav-slide-card {
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 6px;
  background: #0b0f15;
  color: #d8dee9;
  text-align: left;
  padding: 7px 8px;
  cursor: pointer;
}
.lp-nav-slide-card:hover,
.lp-nav-slide-card.is-active {
  border-color: rgba(83,74,183,.72);
  background: rgba(83,74,183,.14);
}
.lp-nav-slide-index {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 18px;
  padding: 0 6px;
  border-radius: 999px;
  border: 1px solid rgba(255,255,255,.16);
  font-size: 10px;
  margin-right: 6px;
}
.lp-nav-slide-title {
  display: inline;
  font-size: 12px;
  font-weight: 700;
}
.lp-nav-slide-desc {
  margin-top: 4px;
  font-size: 11px;
  color: #98a2b3;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}
.lp-nav-slide-fields {
  display: grid;
  gap: 7px;
  padding-top: 2px;
}
.lp-nav-slide-fields label {
  display: grid;
  gap: 4px;
  font-size: 10px;
  color: #98a2b3;
  text-transform: uppercase;
  letter-spacing: .04em;
}
.lp-nav-slide-fields textarea {
  width: 100%;
  min-height: 74px;
  border: 1px solid rgba(255,255,255,.12);
  border-radius: 5px;
  background: #0b0f15;
  color: #f7f8fb;
  font-size: 11px;
  padding: 7px;
  resize: vertical;
}
.lp-nav-slide-icon-row {
  display: flex;
  gap: 6px;
  align-items: center;
  justify-content: space-between;
}
.lp-nav-slide-icon-text {
  min-width: 0;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  font-size: 11px;
  color: #d8dee9;
}
.lp-nav-slide-icon-btn {
  border: 1px solid rgba(255,255,255,.16);
  border-radius: 5px;
  height: 28px;
  padding: 0 8px;
  background: #171d25;
  color: #f7f8fb;
  font-size: 11px;
  cursor: pointer;
}
.lp-nav-slide-icon-btn:hover {
  border-color: rgba(83,74,183,.72);
}
`;

const ensureHostStyles = () => {
    if (document.getElementById('lp-navigator-host-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'lp-navigator-host-styles';
    style.textContent = hostStyles;
    document.head.appendChild(style);
};

const resolveHost = (editor) => {
    const root = editor.getContainer?.() || document;
    const candidates = Array.from(root.querySelectorAll('.gjs-pn-views-container'));

    const scoreContainer = (node) => {
        if (!node) return -1;
        let score = 0;
        if (node.querySelector('.gjs-trt-traits')) score += 6;
        if (node.querySelector('.gjs-sm-sectors')) score += 4;
        if (node.querySelector('.gjs-layers')) score += 3;
        if (node.closest('.gjs-pn-panel')) score += 1;
        return score;
    };

    const viewsContainer = candidates.length > 0
        ? candidates.reduce((best, current) => (
            scoreContainer(current) > scoreContainer(best) ? current : best
        ))
        : (root.querySelector?.('.gjs-pn-panel.gjs-pn-views-container')
            || root.querySelector?.('.gjs-pn-views-container')
            || root);

    if (!viewsContainer) {
        return null;
    }

    let panel = document.getElementById('lp-navigator-panel');
    if (!panel) {
        panel = document.createElement('div');
        panel.id = 'lp-navigator-panel';
        panel.className = 'lp-navigator-panel';
    }

    if (panel.parentElement !== viewsContainer) {
        viewsContainer.insertBefore(panel, viewsContainer.firstChild || null);
    }

    return panel;
};

/**
 * Register and render the LP Sections Navigator.
 * @param {import('grapesjs').Editor} editor
 */
export default function registerSectionsNavigator(editor) {
    ensureHostStyles();

    if (!editor.Panels.getPanel('lp-navigator')) {
        editor.Panels.addPanel({
            id: 'lp-navigator',
            label: 'Sections',
            visible: true,
        });
    }

    const state = {
        open: new Set(),
        selectedCid: '',
        host: null,
        activeSlideByRoot: new Map(),
    };

    const highlightNavigatorRow = (cid) => {
        state.selectedCid = cid || '';
        if (!state.host) {
            return;
        }

        state.host.querySelectorAll('.is-selected').forEach((node) => node.classList.remove('is-selected'));
        if (!cid) {
            return;
        }

        state.host.querySelectorAll('[data-cid]').forEach((node) => {
            if (node.getAttribute('data-cid') === cid) {
                node.classList.add('is-selected');
            }
        });
    };

    const scrollToComponent = (component) => {
        const el = component?.getEl?.();
        if (el?.scrollIntoView) {
            el.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }
    };

    const scrollNavigatorToRow = (cid) => {
        if (!state.host || !cid) {
            return;
        }
        const row = Array.from(state.host.querySelectorAll('[data-lp-section]'))
            .find((node) => node.getAttribute('data-lp-section') === cid);
        row?.scrollIntoView?.({ block: 'center', behavior: 'smooth' });
    };

    const focusNavigatorSection = (cid) => {
        if (!cid) {
            return;
        }
        state.open.clear();
        state.open.add(cid);
        render();
        highlightNavigatorRow(cid);
        scrollNavigatorToRow(cid);
    };

    const renderSlideTraits = (component) => {
        const slidesRoot = resolveSlidesRoot(component);
        if (!slidesRoot) {
            return '';
        }

        const slides = findSlideComponents(slidesRoot);
        if (slides.length === 0) {
            return '';
        }

        const rootCid = slidesRoot.cid;
        const selectedSlide = hasClassToken(component, SLIDE_CLASS) ? component : null;
        const storedActive = state.activeSlideByRoot.get(rootCid);
        const activeCid = slides.some((slide) => slide.cid === storedActive)
            ? storedActive
            : (slides.some((slide) => slide.cid === selectedSlide?.cid) ? selectedSlide.cid : slides[0].cid);

        state.activeSlideByRoot.set(rootCid, activeCid);
        const activeSlide = slides.find((slide) => slide.cid === activeCid) || slides[0];
        const activeDescriptor = getSlideDescriptor(activeSlide, slides.findIndex((slide) => slide.cid === activeSlide.cid));

        const cardsHtml = slides.map((slide, index) => {
            const descriptor = getSlideDescriptor(slide, index);
            return `
                <button type="button"
                    class="lp-nav-slide-card ${slide.cid === activeCid ? 'is-active' : ''}"
                    data-lp-slide-card="${escapeHtml(slide.cid)}"
                    data-lp-slide-root="${escapeHtml(rootCid)}">
                    <span class="lp-nav-slide-index">${index + 1}</span>
                    <span class="lp-nav-slide-title">${escapeHtml(descriptor.title || `Slide ${index + 1}`)}</span>
                    <div class="lp-nav-slide-desc">${escapeHtml(descriptor.text || 'No description')}</div>
                </button>
            `;
        }).join('');

        return `
            <div class="lp-nav-traits lp-nav-slides" data-lp-slide-editor="${escapeHtml(rootCid)}">
                <div class="lp-nav-slides-head">
                    <span>Slides</span>
                    <span>${slides.length}</span>
                </div>
                <div class="lp-nav-slide-list">
                    ${cardsHtml}
                </div>
                <div class="lp-nav-slide-fields">
                    <label>
                        Title
                        <input data-lp-slide-field="title" data-lp-slide-target="${escapeHtml(activeSlide.cid)}" type="text" value="${escapeHtml(activeDescriptor.title || '')}">
                    </label>
                    <label>
                        Description
                        <textarea data-lp-slide-field="description" data-lp-slide-target="${escapeHtml(activeSlide.cid)}">${escapeHtml(activeDescriptor.text || '')}</textarea>
                    </label>
                    <div class="lp-nav-slide-icon-row">
                        <span class="lp-nav-slide-icon-text" title="${escapeHtml(activeDescriptor.iconLabel)}">${escapeHtml(activeDescriptor.iconLabel)}</span>
                        <button type="button"
                            class="lp-nav-slide-icon-btn"
                            data-lp-slide-action="select-icon"
                            data-lp-slide-target="${escapeHtml(activeSlide.cid)}">
                            Select Icon
                        </button>
                    </div>
                </div>
            </div>
        `;
    };

    const renderTraits = (component) => {
        if (!component) {
            return '';
        }

        const slideTraits = renderSlideTraits(component);
        if (slideTraits) {
            return slideTraits;
        }

        const attrs = getAttrs(component);
        const type = getSectionType(component);

        if (type === 'gsap-animated') {
            return `
                <div class="lp-nav-traits" data-lp-traits="${escapeHtml(component.cid)}">
                    <div class="lp-nav-traits-row">
                        <select data-lp-attr="data-gsap-animation">${optionHtml(GSAP_ANIMATIONS, attrs['data-gsap-animation'] || 'fadeInUp')}</select>
                        <select data-lp-attr="data-gsap-duration">${optionHtml(GSAP_DURATIONS, attrs['data-gsap-duration'] || '1')}</select>
                        <select data-lp-attr="data-gsap-trigger">${optionHtml(GSAP_TRIGGERS, attrs['data-gsap-trigger'] || 'scroll')}</select>
                    </div>
                </div>
            `;
        }

        if (type === 'threejs-scene') {
            return `
                <div class="lp-nav-traits" data-lp-traits="${escapeHtml(component.cid)}">
                    <div class="lp-nav-traits-row">
                        <select data-lp-attr="data-scene-type">${optionHtml(THREE_SCENE_TYPES, attrs['data-scene-type'] || 'particles')}</select>
                        <input data-lp-attr="data-scene-color" type="color" value="${escapeHtml(attrs['data-scene-color'] || '#5b8cff')}">
                        <input data-lp-attr="data-scene-height" type="number" min="100" max="1000" step="50" value="${escapeHtml(attrs['data-scene-height'] || '400')}">
                    </div>
                    <div class="lp-nav-traits-row">
                        <input data-lp-attr="data-scene-speed" type="range" min="0.1" max="3" step="0.1" value="${escapeHtml(attrs['data-scene-speed'] || '1')}">
                        <input data-lp-attr="data-particle-count" type="number" min="10" max="500" step="10" value="${escapeHtml(attrs['data-particle-count'] || '120')}">
                        <span></span>
                    </div>
                </div>
            `;
        }

        if (type === 'standard-section') {
            return `
                <div class="lp-nav-traits" data-lp-traits="${escapeHtml(component.cid)}">
                    <input data-lp-attr="data-label" type="text" value="${escapeHtml(attrs['data-label'] || attrs.id || 'Section')}">
                </div>
            `;
        }

        return '';
    };

    const componentByCid = (cid) => {
        if (!cid) {
            return null;
        }

        let found = null;
        const walk = (component) => {
            if (!component || found) {
                return;
            }
            if (component.cid === cid) {
                found = component;
                return;
            }
            const children = component.components?.();
            if (children && typeof children.forEach === 'function') {
                children.forEach(walk);
            }
        };

        walk(editor.getWrapper?.());
        return found;
    };

    const render = () => {
        state.host = resolveHost(editor);
        if (!state.host) {
            return;
        }

        const sections = getTopLevelSections(editor);
        const selected = editor.getSelected?.();
        const selectedCid = selected?.cid || '';
        const sectionHtml = sections.map((section) => {
            const type = getSectionType(section);
            const meta = sectionMeta(type);
            const isOpen = state.open.has(section.cid);
            const isSelected = selectedCid === section.cid;
            const elements = isOpen ? detectElements(section) : [];

            return `
                <div class="lp-section-item">
                    <button type="button" class="lp-section-row ${isOpen ? 'is-open' : ''} ${isSelected ? 'is-selected' : ''}" data-lp-section="${escapeHtml(section.cid)}" data-cid="${escapeHtml(section.cid)}">
                        <span class="lp-nav-icon lp-nav-icon--${type === 'gsap-animated' ? 'gsap' : type === 'threejs-scene' ? 'three' : 'std'}">${meta.icon}</span>
                        <span class="lp-section-name">${escapeHtml(getSectionName(section))}</span>
                        <span class="lp-nav-kind ${meta.className}">${meta.short}</span>
                        <span class="lp-section-chevron">&#9654;</span>
                    </button>
                    ${isOpen ? `
                        <div class="lp-elements">
                            ${elements.map((item) => `
                                <div class="lp-el-row ${selectedCid === item.gjs_component.cid ? 'is-selected' : ''}" data-lp-element="${escapeHtml(item.gjs_component.cid)}" data-lp-parent="${escapeHtml(section.cid)}" data-cid="${escapeHtml(item.gjs_component.cid)}">
                                    <div class="lp-el-icon">${item.icon}</div>
                                    <div class="lp-el-info">
                                        <div class="lp-el-type">${escapeHtml(item.typeLabel)}</div>
                                        <div class="lp-el-content">${escapeHtml(item.contentPreview || item.tag)}</div>
                                    </div>
                                </div>
                            `).join('') || '<div class="lp-nav-empty">No elements</div>'}
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');

        state.host.innerHTML = `
            <div class="lp-nav-head">
                <span>Sections</span>
                <span>${sections.length}</span>
            </div>
            <div class="lp-nav-list">
                ${sectionHtml || '<div class="lp-nav-empty">No LP sections</div>'}
            </div>
            ${renderTraits(selected)}
        `;

        highlightNavigatorRow(selectedCid);
    };

    const refreshNavigator = debounce(render, 100);

    const selectComponent = (component) => {
        if (!component) {
            return;
        }
        editor.select(component);
        updateCanvasSectionSelection(editor, component);
        scrollToComponent(component);
        highlightNavigatorRow(component.cid);
    };

    document.addEventListener('click', (event) => {
        if (!state.host?.contains(event.target)) {
            return;
        }

        const sectionRow = event.target.closest('[data-lp-section]');
        if (sectionRow) {
            const component = componentByCid(sectionRow.getAttribute('data-lp-section'));
            if (!component) {
                return;
            }

            if (state.open.has(component.cid)) {
                state.open.delete(component.cid);
            } else {
                state.open.add(component.cid);
            }
            selectComponent(component);
            render();
            return;
        }

        const elementRow = event.target.closest('[data-lp-element]');
        if (elementRow) {
            const component = componentByCid(elementRow.getAttribute('data-lp-element'));
            const parentSection = componentByCid(elementRow.getAttribute('data-lp-parent'));
            selectComponent(component);
            if (parentSection) {
                expandDynamicSection(parentSection, parentSection.getEl?.(), editor, { temporary: true });
                scheduleDynamicReCollapse(parentSection, parentSection.getEl?.(), editor, 2000);
            }
            highlightNavigatorRow(component?.cid);
            return;
        }

        const slideCard = event.target.closest('[data-lp-slide-card]');
        if (slideCard) {
            const slide = componentByCid(slideCard.getAttribute('data-lp-slide-card'));
            const rootCid = slideCard.getAttribute('data-lp-slide-root');
            if (slide && rootCid) {
                state.activeSlideByRoot.set(rootCid, slide.cid);
                selectComponent(slide);
                render();
            }
            return;
        }

        const iconBtn = event.target.closest('[data-lp-slide-action="select-icon"]');
        if (iconBtn) {
            const slide = componentByCid(iconBtn.getAttribute('data-lp-slide-target'));
            const descriptor = getSlideDescriptor(slide);
            if (descriptor.icon) {
                selectComponent(descriptor.icon);
            } else if (slide) {
                selectComponent(slide);
            }
        }
    });

    const updateTraitFromEvent = (event) => {
        if (!state.host?.contains(event.target)) {
            return;
        }

        const slideField = event.target.closest('[data-lp-slide-field]');
        if (slideField) {
            if (event.type !== 'change') {
                return;
            }

            const slide = componentByCid(slideField.getAttribute('data-lp-slide-target'));
            if (!slide) {
                return;
            }

            const descriptor = getSlideDescriptor(slide);
            const fieldName = slideField.getAttribute('data-lp-slide-field');

            if (fieldName === 'title' && descriptor.heading) {
                applyTextToComponent(descriptor.heading, slideField.value);
            }

            if (fieldName === 'description' && descriptor.description) {
                applyTextToComponent(descriptor.description, slideField.value);
            }

            return;
        }

        const field = event.target.closest('[data-lp-attr]');
        const traitBox = event.target.closest('[data-lp-traits]');
        if (!field || !traitBox) {
            return;
        }

        const component = componentByCid(traitBox.getAttribute('data-lp-traits'));
        if (!component) {
            return;
        }

        component.addAttributes({
            [field.getAttribute('data-lp-attr')]: field.value,
        });
    };

    document.addEventListener('input', updateTraitFromEvent);
    document.addEventListener('change', updateTraitFromEvent);

    editor.on('component:add', refreshNavigator);
    editor.on('component:remove', refreshNavigator);
    editor.on('component:update', refreshNavigator);
    editor.on('component:selected', (component) => {
        updateCanvasSectionSelection(editor, component);
        highlightNavigatorRow(component?.cid);
        refreshNavigator();
    });
    editor.on('lp:section:focus', ({ cid } = {}) => {
        focusNavigatorSection(cid);
    });
    editor.on('canvas:frame:load', () => {
        const doc = editor.Canvas.getDocument?.();
        doc?.addEventListener?.('click', () => {
            getTopLevelSections(editor).forEach((section) => {
                const el = section.getEl?.();
                if (el?.__lpTemporaryExpanded) {
                    collapseDynamicSection(section, el, editor);
                }
            });
        });
    });

    editor.on('load', render);
    editor.on('canvas:frame:load', refreshNavigator);
    render();
}
