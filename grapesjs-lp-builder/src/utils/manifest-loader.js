/**
 * Manifest loader utility for applying section config defaults to GrapesJS components.
 */

const KNOWN_CONFIG_KEYS = {
    'standard-section': [],
    'gsap-animated': [
        'data-gsap-animation',
        'data-gsap-duration',
        'data-gsap-delay',
        'data-gsap-ease',
        'data-gsap-trigger',
        'data-gsap-children',
        'data-gsap-stagger',
    ],
    'threejs-scene': [
        'data-scene-type',
        'data-scene-color',
        'data-scene-bg',
        'data-scene-height',
        'data-scene-speed',
        'data-particle-count',
        'data-threejs-overlay',
        'data-wireframe',
        'data-auto-rotate',
    ],
};

const collectComponents = (component, bucket) => {
    if (!component) {
        return;
    }

    bucket.push(component);

    const children = component.components?.();
    if (!children || typeof children.forEach !== 'function') {
        return;
    }

    children.forEach((child) => collectComponents(child, bucket));
};

const findComponentById = (editor, id) => {
    const wrapper = editor.getWrapper?.();
    if (!wrapper) {
        return null;
    }

    const all = [];
    collectComponents(wrapper, all);

    for (const component of all) {
        const attrs = component.getAttributes?.() || {};
        if (String(attrs.id || '').trim() === id) {
            return component;
        }
    }

    return null;
};

const normalizeConfigValue = (value) => {
    if (typeof value === 'boolean') {
        return value ? 'true' : 'false';
    }
    if (value == null) {
        return '';
    }
    return String(value);
};

/**
 * Load manifest section config values into GrapesJS component attributes.
 * @param {import('grapesjs').Editor} editor
 * @param {object} manifest
 * @returns {{applied: number, skipped: number}}
 */
export function loadManifest(editor, manifest) {
    if (!editor || !manifest || !Array.isArray(manifest.sections)) {
        return { applied: 0, skipped: 0 };
    }

    let applied = 0;
    let skipped = 0;

    manifest.sections.forEach((section) => {
        const id = String(section?.id || '').trim();
        const config = section?.config;

        if (!id || !config || typeof config !== 'object') {
            skipped += 1;
            return;
        }

        const component = findComponentById(editor, id);
        if (!component) {
            skipped += 1;
            return;
        }

        const attrs = component.getAttributes?.() || {};
        const type = String(attrs['data-gjs-type'] || component.get('type') || '').trim();
        const keys = KNOWN_CONFIG_KEYS[type];

        if (!Array.isArray(keys)) {
            skipped += 1;
            return;
        }

        const nextAttrs = { ...attrs };
        keys.forEach((key) => {
            if (Object.prototype.hasOwnProperty.call(config, key)) {
                nextAttrs[key] = normalizeConfigValue(config[key]);
            }
        });

        component.setAttributes(nextAttrs);
        applied += 1;
    });

    return { applied, skipped };
}

export default loadManifest;
