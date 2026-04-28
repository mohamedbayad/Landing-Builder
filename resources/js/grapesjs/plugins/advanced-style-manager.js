/**
 * Plugin: advanced-style-manager
 *
 * Registers comprehensive Style Manager sectors covering:
 *   - Typography
 *   - Decorations
 *   - Background Image  (deferred – the background plugin owns this sector)
 *   - Effects & Filters
 *   - Flexbox & Grid
 *   - Animations
 *   - Transform
 *   - Extra
 *
 * Each sector is added via the Style Manager API so they coexist cleanly
 * with any sectors already created by GrapesJS defaults or other plugins.
 */

const SECTORS = [
    // ─── Typography ──────────────────────────────────────────────
    {
        name: 'Typography',
        open: false,
        buildProps: [
            'font-family',
            'font-size',
            'font-weight',
            'letter-spacing',
            'color',
            'line-height',
            'text-align',
            'text-decoration',
            'text-shadow',
            'text-transform',
            'font-style',
        ],
        properties: [
            {
                property: 'font-family',
                type: 'select',
                defaults: 'Arial, sans-serif',
                list: [
                    { value: 'Arial, sans-serif', name: 'Arial' },
                    { value: 'Helvetica, sans-serif', name: 'Helvetica' },
                    { value: 'Georgia, serif', name: 'Georgia' },
                    { value: 'Times New Roman, serif', name: 'Times New Roman' },
                    { value: 'Courier New, monospace', name: 'Courier New' },
                    { value: 'Verdana, sans-serif', name: 'Verdana' },
                    { value: 'system-ui, sans-serif', name: 'System UI' },
                ],
            },
            {
                property: 'font-weight',
                type: 'select',
                defaults: '400',
                list: [
                    { value: '100', name: 'Thin (100)' },
                    { value: '200', name: 'Extra Light (200)' },
                    { value: '300', name: 'Light (300)' },
                    { value: '400', name: 'Normal (400)' },
                    { value: '500', name: 'Medium (500)' },
                    { value: '600', name: 'Semi Bold (600)' },
                    { value: '700', name: 'Bold (700)' },
                    { value: '800', name: 'Extra Bold (800)' },
                    { value: '900', name: 'Black (900)' },
                ],
            },
        ],
    },

    // ─── Decorations ─────────────────────────────────────────────
    {
        name: 'Decorations',
        open: false,
        buildProps: [
            'background-color',
            'border-radius',
            'border',
            'box-shadow',
            'background',
        ],
        properties: [
            {
                type: 'slider',
                property: 'border-radius',
                defaults: 0,
                step: 1,
                max: 100,
                units: ['px', '%', 'rem'],
            },
            {
                property: 'box-shadow',
                type: 'stack',
                layerSeparator: ', ',
                properties: [
                    {
                        type: 'integer',
                        property: 'box-shadow-h',
                        default: '0',
                        units: ['px'],
                    },
                    {
                        type: 'integer',
                        property: 'box-shadow-v',
                        default: '0',
                        units: ['px'],
                    },
                    {
                        type: 'integer',
                        property: 'box-shadow-blur',
                        default: '5',
                        units: ['px'],
                    },
                    {
                        type: 'integer',
                        property: 'box-shadow-spread',
                        default: '0',
                        units: ['px'],
                    },
                    {
                        type: 'color',
                        property: 'box-shadow-color',
                        default: 'rgba(0,0,0,0.3)',
                    },
                ],
            },
        ],
    },

    // ─── Background Image ────────────────────────────────────────
    {
        name: 'Background Image',
        open: false,
        buildProps: [
            'background-image',
            'background-repeat',
            'background-position',
            'background-attachment',
            'background-size',
        ],
        properties: [
            {
                property: 'background-image',
                type: 'file',
                functionName: 'url',
                full: true,
            },
            {
                property: 'background-repeat',
                type: 'select',
                defaults: 'repeat',
                list: [
                    { value: 'repeat', name: 'Repeat' },
                    { value: 'repeat-x', name: 'Repeat Horizontally' },
                    { value: 'repeat-y', name: 'Repeat Vertically' },
                    { value: 'no-repeat', name: 'No Repeat' },
                    { value: 'space', name: 'Space' },
                    { value: 'round', name: 'Round' },
                ],
            },
            {
                property: 'background-position',
                type: 'select',
                defaults: 'left top',
                list: [
                    { value: 'left top', name: 'Left Top' },
                    { value: 'left center', name: 'Left Center' },
                    { value: 'left bottom', name: 'Left Bottom' },
                    { value: 'right top', name: 'Right Top' },
                    { value: 'right center', name: 'Right Center' },
                    { value: 'right bottom', name: 'Right Bottom' },
                    { value: 'center top', name: 'Center Top' },
                    { value: 'center center', name: 'Center' },
                    { value: 'center bottom', name: 'Center Bottom' },
                ],
            },
            {
                property: 'background-attachment',
                type: 'select',
                defaults: 'scroll',
                list: [
                    { value: 'scroll', name: 'Scroll' },
                    { value: 'fixed', name: 'Fixed' },
                    { value: 'local', name: 'Local' },
                ],
            },
            {
                property: 'background-size',
                type: 'select',
                defaults: 'auto',
                list: [
                    { value: 'auto', name: 'Auto' },
                    { value: 'cover', name: 'Cover' },
                    { value: 'contain', name: 'Contain' },
                    { value: '100% 100%', name: 'Stretch' },
                ],
            },
        ],
    },

    // ─── Effects & Filters ───────────────────────────────────────
    {
        name: 'Effects & Filters',
        open: false,
        buildProps: ['opacity', 'filter', 'backdrop-filter', 'mix-blend-mode'],
        properties: [
            {
                type: 'slider',
                property: 'opacity',
                defaults: 1,
                step: 0.01,
                max: 1,
                min: 0,
            },
            {
                property: 'filter',
                type: 'composite',
                properties: [
                    {
                        name: 'Blur',
                        property: 'blur',
                        type: 'slider',
                        defaults: 0,
                        units: ['px'],
                        max: 20,
                        functionName: 'blur',
                    },
                    {
                        name: 'Brightness',
                        property: 'brightness',
                        type: 'slider',
                        defaults: 100,
                        units: ['%'],
                        max: 200,
                        functionName: 'brightness',
                    },
                    {
                        name: 'Contrast',
                        property: 'contrast',
                        type: 'slider',
                        defaults: 100,
                        units: ['%'],
                        max: 200,
                        functionName: 'contrast',
                    },
                    {
                        name: 'Grayscale',
                        property: 'grayscale',
                        type: 'slider',
                        defaults: 0,
                        units: ['%'],
                        max: 100,
                        functionName: 'grayscale',
                    },
                    {
                        name: 'Hue Rotate',
                        property: 'hue-rotate',
                        type: 'slider',
                        defaults: 0,
                        units: ['deg'],
                        max: 360,
                        functionName: 'hue-rotate',
                    },
                    {
                        name: 'Invert',
                        property: 'invert',
                        type: 'slider',
                        defaults: 0,
                        units: ['%'],
                        max: 100,
                        functionName: 'invert',
                    },
                    {
                        name: 'Saturate',
                        property: 'saturate',
                        type: 'slider',
                        defaults: 100,
                        units: ['%'],
                        max: 200,
                        functionName: 'saturate',
                    },
                    {
                        name: 'Sepia',
                        property: 'sepia',
                        type: 'slider',
                        defaults: 0,
                        units: ['%'],
                        max: 100,
                        functionName: 'sepia',
                    },
                ],
            },
            {
                property: 'mix-blend-mode',
                type: 'select',
                defaults: 'normal',
                list: [
                    { value: 'normal', name: 'Normal' },
                    { value: 'multiply', name: 'Multiply' },
                    { value: 'screen', name: 'Screen' },
                    { value: 'overlay', name: 'Overlay' },
                    { value: 'darken', name: 'Darken' },
                    { value: 'lighten', name: 'Lighten' },
                    { value: 'color-dodge', name: 'Color Dodge' },
                    { value: 'color-burn', name: 'Color Burn' },
                    { value: 'difference', name: 'Difference' },
                    { value: 'exclusion', name: 'Exclusion' },
                    { value: 'hue', name: 'Hue' },
                    { value: 'saturation', name: 'Saturation' },
                    { value: 'color', name: 'Color' },
                    { value: 'luminosity', name: 'Luminosity' },
                ],
            },
        ],
    },

    // ─── Flexbox & Grid ──────────────────────────────────────────
    {
        name: 'Flexbox & Grid',
        open: false,
        properties: [
            {
                property: 'display',
                type: 'select',
                defaults: 'block',
                list: [
                    { value: 'block', name: 'Block' },
                    { value: 'inline-block', name: 'Inline Block' },
                    { value: 'inline', name: 'Inline' },
                    { value: 'flex', name: 'Flex' },
                    { value: 'inline-flex', name: 'Inline Flex' },
                    { value: 'grid', name: 'Grid' },
                    { value: 'inline-grid', name: 'Inline Grid' },
                    { value: 'none', name: 'None' },
                ],
            },
            // — Flexbox
            {
                property: 'flex-direction',
                type: 'radio',
                defaults: 'row',
                list: [
                    { value: 'row', name: '→', title: 'Row' },
                    { value: 'row-reverse', name: '←', title: 'Row Reverse' },
                    { value: 'column', name: '↓', title: 'Column' },
                    { value: 'column-reverse', name: '↑', title: 'Column Reverse' },
                ],
            },
            {
                property: 'justify-content',
                type: 'radio',
                defaults: 'flex-start',
                list: [
                    { value: 'flex-start', name: 'Start', title: 'Flex Start' },
                    { value: 'flex-end', name: 'End', title: 'Flex End' },
                    { value: 'center', name: 'Center', title: 'Center' },
                    { value: 'space-between', name: 'Between', title: 'Space Between' },
                    { value: 'space-around', name: 'Around', title: 'Space Around' },
                    { value: 'space-evenly', name: 'Evenly', title: 'Space Evenly' },
                ],
            },
            {
                property: 'align-items',
                type: 'radio',
                defaults: 'stretch',
                list: [
                    { value: 'flex-start', name: 'Start', title: 'Start' },
                    { value: 'flex-end', name: 'End', title: 'End' },
                    { value: 'center', name: 'Center', title: 'Center' },
                    { value: 'stretch', name: 'Stretch', title: 'Stretch' },
                    { value: 'baseline', name: 'Baseline', title: 'Baseline' },
                ],
            },
            {
                property: 'flex-wrap',
                type: 'select',
                defaults: 'nowrap',
                list: [
                    { value: 'nowrap', name: 'No Wrap' },
                    { value: 'wrap', name: 'Wrap' },
                    { value: 'wrap-reverse', name: 'Wrap Reverse' },
                ],
            },
            {
                property: 'gap',
                type: 'composite',
                properties: [
                    { name: 'Row Gap', property: 'row-gap', type: 'integer', units: ['px', 'rem', '%', 'em'], defaults: '0px' },
                    { name: 'Column Gap', property: 'column-gap', type: 'integer', units: ['px', 'rem', '%', 'em'], defaults: '0px' },
                ],
            },
            // — Grid
            {
                property: 'grid-template-columns',
                type: 'text',
                defaults: 'none',
                placeholder: 'e.g., repeat(3, 1fr) or 1fr 2fr 1fr',
            },
            {
                property: 'grid-template-rows',
                type: 'text',
                defaults: 'none',
                placeholder: 'e.g., repeat(2, 100px) or auto 1fr',
            },
            {
                property: 'grid-auto-flow',
                type: 'select',
                defaults: 'row',
                list: [
                    { value: 'row', name: 'Row' },
                    { value: 'column', name: 'Column' },
                    { value: 'dense', name: 'Dense' },
                    { value: 'row dense', name: 'Row Dense' },
                    { value: 'column dense', name: 'Column Dense' },
                ],
            },
            {
                property: 'grid-auto-columns',
                type: 'text',
                defaults: 'auto',
                placeholder: 'e.g., minmax(100px, 1fr)',
            },
            {
                property: 'grid-auto-rows',
                type: 'text',
                defaults: 'auto',
                placeholder: 'e.g., minmax(100px, auto)',
            },
        ],
    },

    // ─── Animations ──────────────────────────────────────────────
    {
        name: 'Animations',
        open: false,
        properties: [
            {
                property: 'transition',
                type: 'composite',
                properties: [
                    {
                        name: 'Property',
                        property: 'transition-property',
                        type: 'text',
                        defaults: 'all',
                        placeholder: 'e.g., opacity, transform, color',
                    },
                    {
                        name: 'Duration',
                        property: 'transition-duration',
                        type: 'integer',
                        units: ['s', 'ms'],
                        defaults: '0.3s',
                        min: 0,
                    },
                    {
                        name: 'Timing Function',
                        property: 'transition-timing-function',
                        type: 'select',
                        defaults: 'ease',
                        list: [
                            { value: 'ease', name: 'Ease' },
                            { value: 'linear', name: 'Linear' },
                            { value: 'ease-in', name: 'Ease In' },
                            { value: 'ease-out', name: 'Ease Out' },
                            { value: 'ease-in-out', name: 'Ease In-Out' },
                            { value: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)', name: 'Bounce' },
                            { value: 'cubic-bezier(0.175, 0.885, 0.32, 1.275)', name: 'Back' },
                        ],
                    },
                    {
                        name: 'Delay',
                        property: 'transition-delay',
                        type: 'integer',
                        units: ['s', 'ms'],
                        defaults: '0s',
                        min: 0,
                    },
                ],
            },
            {
                property: 'animation',
                type: 'composite',
                properties: [
                    {
                        name: 'Name',
                        property: 'animation-name',
                        type: 'text',
                        defaults: 'none',
                        placeholder: 'Animation name from @keyframes',
                    },
                    {
                        name: 'Duration',
                        property: 'animation-duration',
                        type: 'integer',
                        units: ['s', 'ms'],
                        defaults: '1s',
                        min: 0,
                    },
                    {
                        name: 'Timing Function',
                        property: 'animation-timing-function',
                        type: 'select',
                        defaults: 'ease',
                        list: [
                            { value: 'ease', name: 'Ease' },
                            { value: 'linear', name: 'Linear' },
                            { value: 'ease-in', name: 'Ease In' },
                            { value: 'ease-out', name: 'Ease Out' },
                            { value: 'ease-in-out', name: 'Ease In-Out' },
                        ],
                    },
                    {
                        name: 'Iteration Count',
                        property: 'animation-iteration-count',
                        type: 'select',
                        defaults: '1',
                        list: [
                            { value: '1', name: 'Once' },
                            { value: '2', name: 'Twice' },
                            { value: '3', name: 'Three Times' },
                            { value: 'infinite', name: 'Infinite' },
                        ],
                    },
                    {
                        name: 'Direction',
                        property: 'animation-direction',
                        type: 'select',
                        defaults: 'normal',
                        list: [
                            { value: 'normal', name: 'Normal' },
                            { value: 'reverse', name: 'Reverse' },
                            { value: 'alternate', name: 'Alternate' },
                            { value: 'alternate-reverse', name: 'Alternate Reverse' },
                        ],
                    },
                    {
                        name: 'Fill Mode',
                        property: 'animation-fill-mode',
                        type: 'select',
                        defaults: 'none',
                        list: [
                            { value: 'none', name: 'None' },
                            { value: 'forwards', name: 'Forwards' },
                            { value: 'backwards', name: 'Backwards' },
                            { value: 'both', name: 'Both' },
                        ],
                    },
                ],
            },
        ],
    },

    // ─── Transform ───────────────────────────────────────────────
    {
        name: 'Transform',
        open: false,
        properties: [
            {
                property: 'transform',
                type: 'composite',
                properties: [
                    {
                        name: 'Rotate',
                        property: 'rotate',
                        type: 'slider',
                        units: ['deg'],
                        defaults: '0deg',
                        min: -360,
                        max: 360,
                        step: 1,
                    },
                    {
                        name: 'Scale X',
                        property: 'scale-x',
                        type: 'slider',
                        defaults: 1,
                        min: 0,
                        max: 5,
                        step: 0.1,
                    },
                    {
                        name: 'Scale Y',
                        property: 'scale-y',
                        type: 'slider',
                        defaults: 1,
                        min: 0,
                        max: 5,
                        step: 0.1,
                    },
                    {
                        name: 'Translate X',
                        property: 'translate-x',
                        type: 'integer',
                        units: ['px', '%', 'rem'],
                        defaults: '0px',
                    },
                    {
                        name: 'Translate Y',
                        property: 'translate-y',
                        type: 'integer',
                        units: ['px', '%', 'rem'],
                        defaults: '0px',
                    },
                    {
                        name: 'Skew X',
                        property: 'skew-x',
                        type: 'slider',
                        units: ['deg'],
                        defaults: '0deg',
                        min: -90,
                        max: 90,
                    },
                    {
                        name: 'Skew Y',
                        property: 'skew-y',
                        type: 'slider',
                        units: ['deg'],
                        defaults: '0deg',
                        min: -90,
                        max: 90,
                    },
                ],
            },
            {
                property: 'transform-origin',
                type: 'select',
                defaults: 'center center',
                list: [
                    { value: 'top left', name: 'Top Left' },
                    { value: 'top center', name: 'Top Center' },
                    { value: 'top right', name: 'Top Right' },
                    { value: 'center left', name: 'Center Left' },
                    { value: 'center center', name: 'Center' },
                    { value: 'center right', name: 'Center Right' },
                    { value: 'bottom left', name: 'Bottom Left' },
                    { value: 'bottom center', name: 'Bottom Center' },
                    { value: 'bottom right', name: 'Bottom Right' },
                ],
            },
            {
                property: 'transform-style',
                type: 'radio',
                defaults: 'flat',
                list: [
                    { value: 'flat', name: 'Flat' },
                    { value: 'preserve-3d', name: '3D' },
                ],
            },
        ],
    },

    // ─── Extra ────────────────────────────────────────────────────
    {
        name: 'Extra',
        open: false,
        buildProps: ['perspective', 'cursor', 'pointer-events', 'overflow', 'z-index'],
        properties: [
            {
                property: 'cursor',
                type: 'select',
                defaults: 'auto',
                list: [
                    { value: 'auto', name: 'Auto' },
                    { value: 'default', name: 'Default' },
                    { value: 'pointer', name: 'Pointer' },
                    { value: 'grab', name: 'Grab' },
                    { value: 'grabbing', name: 'Grabbing' },
                    { value: 'move', name: 'Move' },
                    { value: 'text', name: 'Text' },
                    { value: 'not-allowed', name: 'Not Allowed' },
                    { value: 'help', name: 'Help' },
                    { value: 'wait', name: 'Wait' },
                    { value: 'crosshair', name: 'Crosshair' },
                    { value: 'zoom-in', name: 'Zoom In' },
                    { value: 'zoom-out', name: 'Zoom Out' },
                ],
            },
            {
                property: 'pointer-events',
                type: 'radio',
                defaults: 'auto',
                list: [
                    { value: 'auto', name: 'Auto' },
                    { value: 'none', name: 'None' },
                ],
            },
            {
                property: 'overflow',
                type: 'select',
                defaults: 'visible',
                list: [
                    { value: 'visible', name: 'Visible' },
                    { value: 'hidden', name: 'Hidden' },
                    { value: 'scroll', name: 'Scroll' },
                    { value: 'auto', name: 'Auto' },
                    { value: 'clip', name: 'Clip' },
                ],
            },
            {
                property: 'overflow-x',
                type: 'select',
                defaults: 'visible',
                list: [
                    { value: 'visible', name: 'Visible' },
                    { value: 'hidden', name: 'Hidden' },
                    { value: 'scroll', name: 'Scroll' },
                    { value: 'auto', name: 'Auto' },
                ],
            },
            {
                property: 'overflow-y',
                type: 'select',
                defaults: 'visible',
                list: [
                    { value: 'visible', name: 'Visible' },
                    { value: 'hidden', name: 'Hidden' },
                    { value: 'scroll', name: 'Scroll' },
                    { value: 'auto', name: 'Auto' },
                ],
            },
            {
                property: 'perspective',
                type: 'integer',
                units: ['px'],
                defaults: 'none',
                min: 0,
            },
        ],
    },
];

/**
 * Register the advanced Style Manager sectors on the editor.
 *
 * @param {import('grapesjs').Editor} editor
 */
export default function advancedStyleManagerPlugin(editor) {
    if (editor.__advancedStyleManagerReady) return;
    editor.__advancedStyleManagerReady = true;

    const sm = editor.StyleManager;
    if (!sm?.addSector) {
        console.warn('[advanced-style-manager] StyleManager API unavailable – skipping.');
        return;
    }

    SECTORS.forEach((sector) => {
        // Avoid duplicating a sector if one with the same name already exists.
        const existingById = sm.getSector(sector.name);
        if (existingById) return;

        // Also check by display-name (some built-ins use lowercase ids).
        let exists = false;
        try {
            sm.getSectors().forEach((s) => {
                const n = String(s.getName ? s.getName() : s.get('name') || '').toLowerCase();
                if (n === sector.name.toLowerCase()) exists = true;
            });
        } catch { /* ignore */ }
        if (exists) return;

        sm.addSector(sector.name, {
            name: sector.name,
            open: sector.open ?? false,
            buildProps: sector.buildProps || [],
            properties: sector.properties || [],
        });
    });
}
