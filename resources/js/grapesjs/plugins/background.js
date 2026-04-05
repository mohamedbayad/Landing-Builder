/**
 * Plugin: background
 *
 * Extends the existing "Background" Style Manager sector with:
 * - Overlay controls (solid/gradient)
 * - Blend mode and parallax
 * - Live pseudo-element overlay rules scoped by component id
 * - Optional trait mirror for overlay settings
 *
 * This plugin upgrades the existing background system in-place and avoids
 * creating competing background sectors/plugins.
 */

const STYLE_KEYS = {
    overlayEnabled: '--bg-overlay-enabled',
    overlayType: '--bg-overlay-type',
    overlayColor: '--bg-overlay-color',
    overlayOpacity: '--bg-overlay-opacity',
    gradientDirection: '--bg-gradient-direction',
    gradientColorStart: '--bg-gradient-color-start',
    gradientColorEnd: '--bg-gradient-color-end',
    blendMode: '--bg-overlay-blend-mode',
};

const TRAIT_KEYS = {
    overlayEnabled: 'bg_overlay_enabled',
    overlayType: 'bg_overlay_type',
    overlayColor: 'bg_overlay_color',
    overlayOpacity: 'bg_overlay_opacity',
};

const DEFAULTS = {
    sectorId: 'background',
    sectorName: 'Background',
    idPrefix: 'bg-',
    enableTraits: true,
    defaultOverlayOnImage: true,
    defaultOverlayColor: '#000000',
    defaultOverlayOpacity: 0.3,
    renameSectorTo: '',
};

const OVERLAY_TYPES = ['none', 'solid', 'gradient'];
const BLEND_MODES = ['normal', 'multiply', 'overlay', 'darken', 'lighten'];

const DIRECTION_OPTIONS = [
    { value: 'to bottom', name: 'Top -> Bottom' },
    { value: 'to top', name: 'Bottom -> Top' },
    { value: 'to right', name: 'Left -> Right' },
    { value: 'to left', name: 'Right -> Left' },
    { value: 'to bottom right', name: 'Top Left -> Bottom Right' },
    { value: 'to bottom left', name: 'Top Right -> Bottom Left' },
];

const HEX_PATTERN = /^#([\da-f]{3,4}|[\da-f]{6}|[\da-f]{8})$/i;
const RGB_PATTERN = /^rgba?\(([^)]+)\)$/i;

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
const mapStyleValue = (value, fallback = '') => (value == null ? fallback : String(value).trim());

const parseBoolean = (value, fallback = false) => {
    if (typeof value === 'boolean') return value;
    if (value === 1 || value === '1') return true;
    if (value === 0 || value === '0') return false;
    if (typeof value === 'string') {
        const normalized = value.trim().toLowerCase();
        if (['true', 'yes', 'on'].includes(normalized)) return true;
        if (['false', 'no', 'off', 'none', ''].includes(normalized)) return false;
    }
    return fallback;
};

const sanitizeOpacity = (value, fallback = 0.3) => {
    const n = Number.parseFloat(value);
    if (Number.isNaN(n)) return clamp(fallback, 0, 1);
    return clamp(n, 0, 1);
};

const hasNonNoneBackgroundImage = (value) => {
    if (typeof value !== 'string') return false;
    const normalized = value.trim().toLowerCase();
    return Boolean(normalized && normalized !== 'none');
};

const hexToRgba = (hex, opacity = 1) => {
    if (typeof hex !== 'string') return `rgba(0, 0, 0, ${sanitizeOpacity(opacity, 1)})`;
    const clean = hex.trim();
    if (!HEX_PATTERN.test(clean)) return `rgba(0, 0, 0, ${sanitizeOpacity(opacity, 1)})`;

    let raw = clean.replace('#', '');
    if (raw.length === 3 || raw.length === 4) {
        raw = raw.split('').map((x) => `${x}${x}`).join('');
    }

    const hasAlpha = raw.length === 8;
    const r = Number.parseInt(raw.slice(0, 2), 16);
    const g = Number.parseInt(raw.slice(2, 4), 16);
    const b = Number.parseInt(raw.slice(4, 6), 16);
    const a = hasAlpha ? Number.parseInt(raw.slice(6, 8), 16) / 255 : 1;
    return `rgba(${r}, ${g}, ${b}, ${clamp(a * sanitizeOpacity(opacity, 1), 0, 1)})`;
};

const withOpacity = (color, opacity = 1) => {
    const alpha = sanitizeOpacity(opacity, 1);
    if (typeof color !== 'string' || !color.trim()) return `rgba(0, 0, 0, ${alpha})`;
    const normalized = color.trim();

    if (HEX_PATTERN.test(normalized)) return hexToRgba(normalized, alpha);

    const rgbMatch = normalized.match(RGB_PATTERN);
    if (rgbMatch) {
        const channels = rgbMatch[1].split(',').map((part) => part.trim());
        const [r = '0', g = '0', b = '0'] = channels;
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    if (alpha >= 1) return normalized;
    return `color-mix(in srgb, ${normalized} ${Math.round(alpha * 100)}%, transparent)`;
};

const buildGradient = ({ direction, colorStart, colorEnd, opacity }) => (
    `linear-gradient(${direction || 'to bottom'}, ${withOpacity(colorStart || '#000000', opacity)} 0%, ${withOpacity(colorEnd || '#000000', opacity)} 100%)`
);

const toNumberString = (value, fallback) => String(sanitizeOpacity(value, fallback));

const toSelector = (id) => {
    if (!id) return '';
    if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
        return `#${CSS.escape(id)}`;
    }
    return `[id="${String(id).replace(/\\/g, '\\\\').replace(/"/g, '\\"')}"]`;
};

const isRenderableComponent = (component) => {
    if (!component || typeof component.get !== 'function') return false;
    const type = String(component.get('type') || '').toLowerCase();
    const tag = String(component.get('tagName') || '').toLowerCase();
    return type !== 'textnode' && tag !== 'script' && tag !== 'style';
};

const walkComponents = (component, callback) => {
    if (!component) return;
    callback(component);
    const children = component.components && component.components();
    if (!children || !children.forEach) return;
    children.forEach((child) => walkComponents(child, callback));
};

const getPropertyCollection = (sector) => {
    if (!sector) return null;
    return sector.get ? sector.get('properties') : null;
};

const findProperty = (sector, propertyName) => {
    const props = getPropertyCollection(sector);
    if (!props) return null;
    let found = null;
    props.forEach((item) => {
        const value = item.get ? item.get('property') : item.property;
        if (value === propertyName) found = item;
    });
    return found;
};

const ensureProperty = (sector, def) => {
    if (!sector || !def?.property) return;
    const existing = findProperty(sector, def.property);
    if (existing) {
        if (def.list && existing.set) existing.set('list', def.list);
        if (def.name && existing.set) existing.set('name', def.name);
        return;
    }

    const props = getPropertyCollection(sector);
    if (props?.add) {
        props.add(def);
    }
};

export default function backgroundPlugin(editor, opts = {}) {
    if (editor.__backgroundPluginReady) return;
    editor.__backgroundPluginReady = true;

    const options = { ...DEFAULTS, ...opts };
    const cssComposer = editor.Css;
    const styleManager = editor.StyleManager;
    const backgroundByCid = new Map();
    const internalChanges = new Set();
    const queuedSync = new Map();

    const raf = window.requestAnimationFrame || ((cb) => setTimeout(cb, 16));
    const cancelRaf = window.cancelAnimationFrame || clearTimeout;

    const runInternal = (component, callback) => {
        if (!component) return;
        internalChanges.add(component.cid);
        try {
            callback();
        } finally {
            internalChanges.delete(component.cid);
        }
    };

    const isInternalChange = (component) => Boolean(component && internalChanges.has(component.cid));
    const getStyle = (component) => (component?.getStyle ? component.getStyle() || {} : {});

    const getComponentId = (component) => {
        const attrs = component.getAttributes ? component.getAttributes() || {} : {};
        const existing = mapStyleValue(attrs.id);
        if (existing) return existing;

        const source = mapStyleValue(component.getId ? component.getId() : '');
        const safe = source
            ? source.replace(/[^a-z0-9_-]/gi, '-').replace(/^-+|-+$/g, '')
            : `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
        const id = /^[a-z_]/i.test(safe) ? `${options.idPrefix}${safe}` : `${options.idPrefix}x-${safe}`;

        runInternal(component, () => component.addAttributes({ id }));
        return id;
    };

    const getOverlayConfig = (component) => {
        const style = getStyle(component);
        const rawType = mapStyleValue(style[STYLE_KEYS.overlayType], 'none').toLowerCase();
        const type = OVERLAY_TYPES.includes(rawType) ? rawType : 'none';
        const enabled = style[STYLE_KEYS.overlayEnabled] == null
            ? type !== 'none'
            : parseBoolean(style[STYLE_KEYS.overlayEnabled], false);
        const blendMode = mapStyleValue(style[STYLE_KEYS.blendMode], 'normal').toLowerCase();

        return {
            enabled,
            type,
            color: mapStyleValue(style[STYLE_KEYS.overlayColor], options.defaultOverlayColor),
            opacity: sanitizeOpacity(style[STYLE_KEYS.overlayOpacity], options.defaultOverlayOpacity),
            gradientDirection: mapStyleValue(style[STYLE_KEYS.gradientDirection], 'to bottom'),
            gradientColorStart: mapStyleValue(style[STYLE_KEYS.gradientColorStart], '#000000'),
            gradientColorEnd: mapStyleValue(style[STYLE_KEYS.gradientColorEnd], '#000000'),
            blendMode: BLEND_MODES.includes(blendMode) ? blendMode : 'normal',
        };
    };

    const removeRuleSafe = (selector) => {
        if (!selector) return;
        const rule = cssComposer?.getRule ? cssComposer.getRule(selector) : null;
        if (!rule) return;
        if (typeof cssComposer.remove === 'function') {
            cssComposer.remove(rule);
            return;
        }
        if (typeof rule.setStyle === 'function') {
            rule.setStyle({});
        }
    };

    const buildOverlayBackground = (config) => {
        if (!config || !config.enabled || config.type === 'none') return '';
        if (config.type === 'gradient') {
            return buildGradient({
                direction: config.gradientDirection,
                colorStart: config.gradientColorStart,
                colorEnd: config.gradientColorEnd,
                opacity: config.opacity,
            });
        }
        return withOpacity(config.color, config.opacity);
    };

    const ensureOverlayDefaultsForImage = (component) => {
        if (!options.defaultOverlayOnImage) return;
        const style = getStyle(component);
        const current = mapStyleValue(style['background-image']);
        const previous = backgroundByCid.get(component.cid) || '';

        backgroundByCid.set(component.cid, current);

        const hasNewImage = hasNonNoneBackgroundImage(current) && !hasNonNoneBackgroundImage(previous);
        if (!hasNewImage) return;

        const hasOverlayConfig = [
            STYLE_KEYS.overlayEnabled,
            STYLE_KEYS.overlayType,
            STYLE_KEYS.overlayColor,
            STYLE_KEYS.overlayOpacity,
        ].some((key) => mapStyleValue(style[key]) !== '');
        if (hasOverlayConfig) return;

        runInternal(component, () => {
            component.addStyle({
                [STYLE_KEYS.overlayEnabled]: 'true',
                [STYLE_KEYS.overlayType]: 'solid',
                [STYLE_KEYS.overlayColor]: options.defaultOverlayColor,
                [STYLE_KEYS.overlayOpacity]: toNumberString(options.defaultOverlayOpacity, 0.3),
                [STYLE_KEYS.gradientDirection]: 'to bottom',
                [STYLE_KEYS.gradientColorStart]: '#000000',
                [STYLE_KEYS.gradientColorEnd]: '#000000',
                [STYLE_KEYS.blendMode]: 'normal',
            });
        });
    };

    // Scoped pseudo-element rules keep overlay between background and content.
    const applyOverlayRules = (component) => {
        if (!isRenderableComponent(component)) return;
        const id = getComponentId(component);
        const selector = toSelector(id);
        if (!selector) return;

        const beforeSelector = `${selector}::before`;
        const childrenSelector = `${selector} > *`;
        const config = getOverlayConfig(component);
        const active = config.enabled && config.type !== 'none';

        if (!active) {
            removeRuleSafe(beforeSelector);
            removeRuleSafe(childrenSelector);
            return;
        }

        const style = getStyle(component);
        const position = mapStyleValue(style.position, 'static').toLowerCase();
        if (!position || position === 'static') {
            runInternal(component, () => component.addStyle({ position: 'relative' }));
        }

        cssComposer.setRule(
            beforeSelector,
            {
                content: '""',
                position: 'absolute',
                inset: '0',
                background: buildOverlayBackground(config),
                'mix-blend-mode': config.blendMode,
                'z-index': '1',
                'pointer-events': 'none',
            },
            { addStyles: false }
        );

        cssComposer.setRule(
            childrenSelector,
            {
                position: 'relative',
                'z-index': '2',
            },
            { addStyles: false }
        );
    };

    const traitDefinitions = [
        {
            type: 'checkbox',
            name: TRAIT_KEYS.overlayEnabled,
            label: 'Overlay Enabled',
            valueTrue: 'true',
            valueFalse: 'false',
        },
        {
            type: 'select',
            name: TRAIT_KEYS.overlayType,
            label: 'Overlay Type',
            options: [
                { id: 'none', label: 'None' },
                { id: 'solid', label: 'Solid' },
                { id: 'gradient', label: 'Gradient' },
            ],
        },
        { type: 'color', name: TRAIT_KEYS.overlayColor, label: 'Overlay Color' },
        {
            type: 'number',
            name: TRAIT_KEYS.overlayOpacity,
            label: 'Overlay Opacity',
            min: 0,
            max: 1,
            step: 0.01,
        },
    ];

    const syncTraitsFromStyles = (component) => {
        if (!options.enableTraits || !isRenderableComponent(component) || isInternalChange(component)) return;
        const attrs = component.getAttributes ? component.getAttributes() || {} : {};
        const config = getOverlayConfig(component);
        const next = {
            [TRAIT_KEYS.overlayEnabled]: String(config.enabled),
            [TRAIT_KEYS.overlayType]: config.type,
            [TRAIT_KEYS.overlayColor]: config.color,
            [TRAIT_KEYS.overlayOpacity]: toNumberString(config.opacity, options.defaultOverlayOpacity),
        };

        const patch = {};
        Object.entries(next).forEach(([key, value]) => {
            if (String(attrs[key] ?? '') !== String(value)) patch[key] = value;
        });

        if (!Object.keys(patch).length) return;
        runInternal(component, () => component.addAttributes(patch));
    };

    const syncStylesFromTraits = (component) => {
        if (!options.enableTraits || !isRenderableComponent(component) || isInternalChange(component)) return;
        const attrs = component.getAttributes ? component.getAttributes() || {} : {};
        const hasAny = Object.values(TRAIT_KEYS).some((key) => attrs[key] != null);
        if (!hasAny) return;

        const patch = {};
        if (attrs[TRAIT_KEYS.overlayEnabled] != null) {
            patch[STYLE_KEYS.overlayEnabled] = parseBoolean(attrs[TRAIT_KEYS.overlayEnabled], false) ? 'true' : 'false';
        }
        if (attrs[TRAIT_KEYS.overlayType] != null) {
            const rawType = String(attrs[TRAIT_KEYS.overlayType]).toLowerCase();
            patch[STYLE_KEYS.overlayType] = OVERLAY_TYPES.includes(rawType) ? rawType : 'none';
        }
        if (attrs[TRAIT_KEYS.overlayColor] != null) {
            patch[STYLE_KEYS.overlayColor] = String(attrs[TRAIT_KEYS.overlayColor]).trim() || options.defaultOverlayColor;
        }
        if (attrs[TRAIT_KEYS.overlayOpacity] != null) {
            patch[STYLE_KEYS.overlayOpacity] = toNumberString(attrs[TRAIT_KEYS.overlayOpacity], options.defaultOverlayOpacity);
        }

        if (!Object.keys(patch).length) return;
        runInternal(component, () => component.addStyle(patch));
    };

    const ensureTraits = (component) => {
        if (!options.enableTraits || !isRenderableComponent(component)) return;
        if (typeof component.getTraits !== 'function' || typeof component.addTrait !== 'function') return;

        const existing = component.getTraits();
        const names = new Set();
        if (existing?.forEach) {
            existing.forEach((trait) => names.add(trait.get ? trait.get('name') : trait.name));
        }

        traitDefinitions.forEach((trait) => {
            if (!names.has(trait.name)) component.addTrait(trait);
        });

        syncTraitsFromStyles(component);
    };

    const syncComponent = (component, { fromStyleUpdate = false } = {}) => {
        if (!isRenderableComponent(component)) return;
        if (fromStyleUpdate) {
            ensureOverlayDefaultsForImage(component);
        } else {
            backgroundByCid.set(component.cid, mapStyleValue(getStyle(component)['background-image']));
        }

        applyOverlayRules(component);
        if (options.enableTraits) syncTraitsFromStyles(component);
    };

    // Batch rapid UI changes (eg slider drag) without duplicate work.
    const queueComponentSync = (component, syncOptions = {}) => {
        if (!component || !component.cid) return;
        if (queuedSync.has(component.cid)) return;
        const frame = raf(() => {
            queuedSync.delete(component.cid);
            syncComponent(component, syncOptions);
        });
        queuedSync.set(component.cid, frame);
    };

    const removeComponentRules = (component) => {
        if (!isRenderableComponent(component)) return;
        const id = mapStyleValue(component.getAttributes?.()?.id);
        const selector = toSelector(id);
        if (!selector) return;
        removeRuleSafe(`${selector}::before`);
        removeRuleSafe(`${selector} > *`);
    };

    const getBackgroundSector = () => {
        const direct = styleManager.getSector(options.sectorId);
        if (direct) return direct;
        const sectors = styleManager.getSectors();
        let found = null;
        sectors.forEach((sector) => {
            const name = String(sector.getName ? sector.getName() : sector.get('name') || '').toLowerCase();
            if (!found && name === 'background') found = sector;
        });
        return found;
    };

    const ensureBackgroundControls = () => {
        if (!styleManager?.addSector) return;

        let sector = getBackgroundSector();
        if (!sector) {
            styleManager.addSector(options.sectorId, { name: options.sectorName, open: false, properties: [] });
            sector = getBackgroundSector();
        }
        if (!sector) return;

        if (options.renameSectorTo && sector.set) {
            sector.set('name', options.renameSectorTo);
        }

        // Base (keeps backward compatibility with existing bg properties)
        ensureProperty(sector, { name: 'Background Color', property: 'background-color', type: 'color', defaults: 'transparent' });
        ensureProperty(sector, { name: 'Background Image', property: 'background-image', type: 'file', functionName: 'url', defaults: 'none', full: true });
        ensureProperty(sector, {
            name: 'Background Size',
            property: 'background-size',
            type: 'select',
            defaults: 'auto',
            list: [
                { value: 'cover', name: 'Cover' },
                { value: 'contain', name: 'Contain' },
                { value: 'auto', name: 'Auto' },
            ],
        });
        ensureProperty(sector, {
            name: 'Background Position',
            property: 'background-position',
            type: 'select',
            defaults: 'center center',
            list: [
                { value: 'left top', name: 'Left Top' },
                { value: 'left center', name: 'Left Center' },
                { value: 'left bottom', name: 'Left Bottom' },
                { value: 'center top', name: 'Center Top' },
                { value: 'center center', name: 'Center' },
                { value: 'center bottom', name: 'Center Bottom' },
                { value: 'right top', name: 'Right Top' },
                { value: 'right center', name: 'Right Center' },
                { value: 'right bottom', name: 'Right Bottom' },
            ],
        });
        ensureProperty(sector, {
            name: 'Background Repeat',
            property: 'background-repeat',
            type: 'select',
            defaults: 'no-repeat',
            list: [
                { value: 'no-repeat', name: 'No Repeat' },
                { value: 'repeat', name: 'Repeat' },
                { value: 'repeat-x', name: 'Repeat X' },
                { value: 'repeat-y', name: 'Repeat Y' },
            ],
        });

        // Overlay
        ensureProperty(sector, {
            name: 'Overlay Enabled',
            property: STYLE_KEYS.overlayEnabled,
            type: 'select',
            defaults: 'false',
            list: [
                { value: 'false', name: 'Off' },
                { value: 'true', name: 'On' },
            ],
        });
        ensureProperty(sector, {
            name: 'Overlay Type',
            property: STYLE_KEYS.overlayType,
            type: 'select',
            defaults: 'none',
            list: [
                { value: 'none', name: 'None' },
                { value: 'solid', name: 'Solid Color' },
                { value: 'gradient', name: 'Gradient' },
            ],
        });
        ensureProperty(sector, {
            name: 'Overlay Color',
            property: STYLE_KEYS.overlayColor,
            type: 'color',
            defaults: options.defaultOverlayColor,
        });
        ensureProperty(sector, {
            name: 'Overlay Opacity',
            property: STYLE_KEYS.overlayOpacity,
            type: 'slider',
            defaults: options.defaultOverlayOpacity,
            min: 0,
            max: 1,
            step: 0.01,
        });
        ensureProperty(sector, {
            name: 'Gradient Color #1',
            property: STYLE_KEYS.gradientColorStart,
            type: 'color',
            defaults: '#000000',
        });
        ensureProperty(sector, {
            name: 'Gradient Color #2',
            property: STYLE_KEYS.gradientColorEnd,
            type: 'color',
            defaults: '#000000',
        });
        ensureProperty(sector, {
            name: 'Gradient Direction',
            property: STYLE_KEYS.gradientDirection,
            type: 'select',
            defaults: 'to bottom',
            list: DIRECTION_OPTIONS,
        });

        // Advanced
        ensureProperty(sector, {
            name: 'Blend Mode',
            property: STYLE_KEYS.blendMode,
            type: 'select',
            defaults: 'normal',
            list: BLEND_MODES.map((mode) => ({
                value: mode,
                name: mode.charAt(0).toUpperCase() + mode.slice(1),
            })),
        });
        ensureProperty(sector, {
            name: 'Parallax',
            property: 'background-attachment',
            type: 'select',
            defaults: 'scroll',
            list: [
                { value: 'scroll', name: 'Off' },
                { value: 'fixed', name: 'On (Fixed)' },
            ],
        });
    };

    ensureBackgroundControls();

    editor.on('load', () => {
        const wrapper = editor.getWrapper();
        walkComponents(wrapper, (component) => {
            if (!isRenderableComponent(component)) return;
            backgroundByCid.set(component.cid, mapStyleValue(getStyle(component)['background-image']));
            applyOverlayRules(component);
        });
    });

    editor.on('component:selected', (component) => {
        if (!component) return;
        ensureTraits(component);
        queueComponentSync(component);
    });

    editor.on('component:update:style', (component) => {
        if (!component || isInternalChange(component)) return;
        queueComponentSync(component, { fromStyleUpdate: true });
    });

    editor.on('component:styleUpdate', (component) => {
        if (!component || isInternalChange(component)) return;
        queueComponentSync(component, { fromStyleUpdate: true });
    });

    editor.on('component:update:attributes', (component) => {
        if (!component || isInternalChange(component)) return;
        syncStylesFromTraits(component);
        queueComponentSync(component);
    });

    editor.on('component:add', (component) => {
        walkComponents(component, (child) => {
            if (!isRenderableComponent(child)) return;
            backgroundByCid.set(child.cid, mapStyleValue(getStyle(child)['background-image']));
            queueComponentSync(child);
        });
    });

    editor.on('component:remove', (component) => {
        walkComponents(component, (child) => {
            if (!isRenderableComponent(child)) return;
            removeComponentRules(child);
            backgroundByCid.delete(child.cid);
            const frame = queuedSync.get(child.cid);
            if (frame) {
                cancelRaf(frame);
                queuedSync.delete(child.cid);
            }
        });
    });

    editor.on('destroy', () => {
        queuedSync.forEach((frame) => cancelRaf(frame));
        queuedSync.clear();
        backgroundByCid.clear();
        internalChanges.clear();
    });
}
