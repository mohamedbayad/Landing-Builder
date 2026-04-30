/**
 * Element detection for the LP Builder Sections Navigator.
 */

const SECTION_TYPES = new Set(['standard-section', 'gsap-animated', 'threejs-scene']);

export const ELEMENT_ICONS = Object.freeze({
    heading: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 5h10M3 10h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    paragraph: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M3 4h10M3 8h10M3 12h8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>',
    button: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="2.5" y="4.5" width="11" height="7" rx="2" stroke="currentColor" stroke-width="1.4"/><circle cx="8" cy="8" r="1" fill="currentColor"/></svg>',
    image: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="2.5" y="3" width="11" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/><path d="m4.5 11 2.6-3 2 2 1.2-1.4 1.7 2.4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    icon: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m8 2.3 1.5 3.2 3.5.4-2.6 2.4.7 3.5L8 10.1l-3.1 1.7.7-3.5L3 5.9l3.5-.4L8 2.3Z" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round"/></svg>',
    three: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 2 13 5v6l-5 3-5-3V5l5-3Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>',
    block: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="3" y="3" width="10" height="10" rx="1.5" stroke="currentColor" stroke-width="1.3"/></svg>',
    container: '<svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="2.5" y="4.5" width="7" height="7" rx="1.2" stroke="currentColor" stroke-width="1.2"/><rect x="6.5" y="2.5" width="7" height="7" rx="1.2" stroke="currentColor" stroke-width="1.2"/></svg>',
});

const truncate = (value, maxLength) => {
    const text = String(value || '').replace(/\s+/g, ' ').trim();
    return text.length > maxLength ? `${text.slice(0, maxLength).trim()}...` : text;
};

const getTagName = (component, el) => String(
    el?.tagName || component?.get?.('tagName') || component?.get?.('type') || 'div',
).toUpperCase();

const getClassName = (component, el) => String(
    el?.getAttribute?.('class') || component?.getAttributes?.()?.class || '',
);

const getText = (el) => String(el?.innerText || el?.textContent || '').trim();

const isIconElement = (tag, className) => {
    if (tag === 'SVG') {
        return true;
    }
    return tag === 'I' && /(icon|fa-|material-icons|material-symbols|lucide)/i.test(className);
};

const detectElement = (component) => {
    const el = component.getEl?.();
    const tag = getTagName(component, el);
    const className = getClassName(component, el);
    const attrs = component.getAttributes?.() || {};
    const text = getText(el);

    if (/^H[1-6]$/.test(tag)) {
        return {
            tag,
            typeLabel: `Heading ${tag}`,
            icon: ELEMENT_ICONS.heading,
            contentPreview: truncate(text, 45),
        };
    }

    if (tag === 'P') {
        return {
            tag,
            typeLabel: 'Paragraph',
            icon: ELEMENT_ICONS.paragraph,
            contentPreview: truncate(text, 45),
        };
    }

    if (tag === 'BUTTON' || /\bbtn\b|btn-|button/i.test(className)) {
        return {
            tag,
            typeLabel: 'Button',
            icon: ELEMENT_ICONS.button,
            contentPreview: truncate(text, 45),
        };
    }

    if (tag === 'IMG') {
        return {
            tag,
            typeLabel: 'Image',
            icon: ELEMENT_ICONS.image,
            contentPreview: truncate(attrs.alt || el?.getAttribute?.('alt') || 'image', 45),
        };
    }

    if (isIconElement(tag, className)) {
        return {
            tag,
            typeLabel: 'Icon',
            icon: ELEMENT_ICONS.icon,
            contentPreview: truncate(className, 20),
        };
    }

    if (tag === 'CANVAS' && el?.parentElement?.getAttribute?.('data-gjs-type') === 'threejs-scene') {
        return {
            tag,
            typeLabel: 'Three.js Canvas',
            icon: ELEMENT_ICONS.three,
            contentPreview: truncate(el.parentElement.getAttribute('data-scene-type') || 'scene', 45),
        };
    }

    if ((tag === 'DIV' || tag === 'SECTION') && (el?.childElementCount || 0) === 0 && text) {
        return {
            tag,
            typeLabel: 'Block',
            icon: ELEMENT_ICONS.block,
            contentPreview: truncate(text, 45),
        };
    }

    return {
        tag,
        typeLabel: 'Container',
        icon: ELEMENT_ICONS.container,
        contentPreview: `${tag.toLowerCase()} - ${el?.childElementCount || component.components?.().length || 0} children`,
    };
};

/**
 * Detect navigator elements inside a section component.
 * @param {import('grapesjs').Component} component
 * @returns {Array<{gjs_component: import('grapesjs').Component, tag: string, typeLabel: string, icon: string, contentPreview: string}>}
 */
export function detectElements(component) {
    const results = [];

    const walk = (current, depth) => {
        if (!current || depth > 4) {
            return;
        }

        const children = current.components?.();
        if (!children || typeof children.forEach !== 'function') {
            return;
        }

        children.forEach((child) => {
            const attrs = child.getAttributes?.() || {};
            if (SECTION_TYPES.has(String(attrs['data-gjs-type'] || '').trim())) {
                return;
            }

            const detected = detectElement(child);
            results.push({
                gjs_component: child,
                ...detected,
            });

            walk(child, depth + 1);
        });
    };

    walk(component, 1);
    return results;
}

export default detectElements;
