# GrapesJS Complete Setup Guide
## Advanced Style Manager + Tailwind CSS Autocomplete

---

# PART 1: Advanced Style Manager Configuration

## Complete CSS Properties for Professional Editor

```javascript
const editor = grapesjs.init({
  container: '#gjs',
  height: '100vh',
  width: 'auto',
  
  styleManager: {
    sectors: [
      // =======================
      // 1. TYPOGRAPHY
      // =======================
      {
        name: 'Typography',
        open: false,
        buildProps: [
          'font-family', 'font-size', 'font-weight', 'letter-spacing', 
          'color', 'line-height', 'text-align', 'text-decoration',
          'text-shadow', 'text-transform', 'font-style'
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
        ]
      },

      // =======================
      // 2. DECORATIONS
      // =======================
      {
        name: 'Decorations',
        open: false,
        buildProps: ['background-color', 'border-radius', 'border', 'box-shadow', 'background'],
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
        ]
      },

      // =======================
      // 3. BACKGROUND IMAGE
      // =======================
      {
        name: 'Background Image',
        open: false,
        buildProps: [
          'background-image', 'background-repeat', 'background-position',
          'background-attachment', 'background-size'
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
        ]
      },

      // =======================
      // 4. EFFECTS & FILTERS
      // =======================
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
        ]
      },

      // =======================
      // 5. FLEXBOX & GRID
      // =======================
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
          
          // FLEXBOX PROPERTIES
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
              { 
                name: 'Row Gap', 
                property: 'row-gap', 
                type: 'integer', 
                units: ['px', 'rem', '%', 'em'], 
                defaults: '0px' 
              },
              { 
                name: 'Column Gap', 
                property: 'column-gap', 
                type: 'integer', 
                units: ['px', 'rem', '%', 'em'], 
                defaults: '0px' 
              },
            ],
          },
          
          // GRID PROPERTIES
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
        ]
      },

      // =======================
      // 6. ANIMATIONS & TRANSITIONS
      // =======================
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
        ]
      },

      // =======================
      // 7. TRANSFORM
      // =======================
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
        ]
      },

      // =======================
      // 8. EXTRA OPTIONS
      // =======================
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
        ]
      },
    ],
  },
});
```

---

# PART 2: Tailwind CSS Autocomplete Plugin

## Method 1: Using Pre-built Plugin (Easiest)

```bash
npm install grapesjs-tailwind
```

```javascript
import grapesjs from 'grapesjs';
import tailwindPlugin from 'grapesjs-tailwind';

const editor = grapesjs.init({
  container: '#gjs',
  plugins: [tailwindPlugin],
  pluginsOpts: {
    'grapesjs-tailwind': {
      config: {
        theme: {
          extend: {
            colors: {
              primary: '#3b82f6',
              secondary: '#8b5cf6',
            }
          }
        }
      },
      autocomplete: true,
      showPreview: true,
    }
  },
  canvas: {
    styles: ['https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css']
  }
});
```

---

## Method 2: Custom Autocomplete Plugin (Full Control)

```javascript
// Complete Tailwind Classes Database
const TAILWIND_CLASSES = {
  // LAYOUT
  display: ['block', 'inline-block', 'inline', 'flex', 'inline-flex', 'grid', 'inline-grid', 'hidden', 'contents'],
  
  // FLEXBOX
  flexDirection: ['flex-row', 'flex-row-reverse', 'flex-col', 'flex-col-reverse'],
  flexWrap: ['flex-wrap', 'flex-wrap-reverse', 'flex-nowrap'],
  justifyContent: ['justify-start', 'justify-end', 'justify-center', 'justify-between', 'justify-around', 'justify-evenly'],
  alignItems: ['items-start', 'items-end', 'items-center', 'items-baseline', 'items-stretch'],
  alignContent: ['content-start', 'content-end', 'content-center', 'content-between', 'content-around', 'content-evenly'],
  gap: ['gap-0', 'gap-1', 'gap-2', 'gap-3', 'gap-4', 'gap-5', 'gap-6', 'gap-8', 'gap-10', 'gap-12', 'gap-16', 'gap-20', 'gap-24'],
  
  // SPACING - PADDING
  padding: [
    'p-0', 'p-0.5', 'p-1', 'p-1.5', 'p-2', 'p-2.5', 'p-3', 'p-3.5', 'p-4', 'p-5', 'p-6', 'p-7', 'p-8', 'p-9', 'p-10', 'p-11', 'p-12', 'p-14', 'p-16', 'p-20', 'p-24', 'p-28', 'p-32', 'p-36', 'p-40', 'p-44', 'p-48', 'p-52', 'p-56', 'p-60', 'p-64', 'p-72', 'p-80', 'p-96',
    'px-0', 'px-1', 'px-2', 'px-3', 'px-4', 'px-5', 'px-6', 'px-8', 'px-10', 'px-12', 'px-16', 'px-20', 'px-24',
    'py-0', 'py-1', 'py-2', 'py-3', 'py-4', 'py-5', 'py-6', 'py-8', 'py-10', 'py-12', 'py-16', 'py-20', 'py-24',
    'pt-0', 'pt-1', 'pt-2', 'pt-4', 'pt-6', 'pt-8', 'pt-10', 'pt-12', 'pt-16', 'pt-20', 'pt-24',
    'pr-0', 'pr-1', 'pr-2', 'pr-4', 'pr-6', 'pr-8', 'pr-10', 'pr-12', 'pr-16', 'pr-20', 'pr-24',
    'pb-0', 'pb-1', 'pb-2', 'pb-4', 'pb-6', 'pb-8', 'pb-10', 'pb-12', 'pb-16', 'pb-20', 'pb-24',
    'pl-0', 'pl-1', 'pl-2', 'pl-4', 'pl-6', 'pl-8', 'pl-10', 'pl-12', 'pl-16', 'pl-20', 'pl-24',
  ],
  
  // SPACING - MARGIN
  margin: [
    'm-0', 'm-1', 'm-2', 'm-3', 'm-4', 'm-5', 'm-6', 'm-8', 'm-10', 'm-12', 'm-16', 'm-20', 'm-24', 'm-auto',
    'mx-0', 'mx-1', 'mx-2', 'mx-3', 'mx-4', 'mx-5', 'mx-6', 'mx-8', 'mx-10', 'mx-12', 'mx-16', 'mx-auto',
    'my-0', 'my-1', 'my-2', 'my-3', 'my-4', 'my-5', 'my-6', 'my-8', 'my-10', 'my-12', 'my-16',
    'mt-0', 'mt-1', 'mt-2', 'mt-4', 'mt-6', 'mt-8', 'mt-10', 'mt-12', 'mt-16', 'mt-auto',
    'mr-0', 'mr-1', 'mr-2', 'mr-4', 'mr-6', 'mr-8', 'mr-10', 'mr-12', 'mr-16', 'mr-auto',
    'mb-0', 'mb-1', 'mb-2', 'mb-4', 'mb-6', 'mb-8', 'mb-10', 'mb-12', 'mb-16', 'mb-auto',
    'ml-0', 'ml-1', 'ml-2', 'ml-4', 'ml-6', 'ml-8', 'ml-10', 'ml-12', 'ml-16', 'ml-auto',
    '-m-1', '-m-2', '-m-3', '-m-4', '-mx-1', '-mx-2', '-my-1', '-my-2'
  ],
  
  // WIDTH & HEIGHT
  width: [
    'w-0', 'w-1', 'w-2', 'w-3', 'w-4', 'w-5', 'w-6', 'w-8', 'w-10', 'w-12', 'w-16', 'w-20', 'w-24', 'w-32', 'w-40', 'w-48', 'w-56', 'w-64', 'w-72', 'w-80', 'w-96',
    'w-auto', 'w-full', 'w-screen', 'w-min', 'w-max', 'w-fit',
    'w-1/2', 'w-1/3', 'w-2/3', 'w-1/4', 'w-2/4', 'w-3/4', 'w-1/5', 'w-2/5', 'w-3/5', 'w-4/5', 'w-1/6', 'w-5/6', 'w-1/12', 'w-11/12'
  ],
  height: [
    'h-0', 'h-1', 'h-2', 'h-3', 'h-4', 'h-5', 'h-6', 'h-8', 'h-10', 'h-12', 'h-16', 'h-20', 'h-24', 'h-32', 'h-40', 'h-48', 'h-56', 'h-64', 'h-72', 'h-80', 'h-96',
    'h-auto', 'h-full', 'h-screen', 'h-min', 'h-max', 'h-fit',
    'h-1/2', 'h-1/3', 'h-2/3', 'h-1/4', 'h-3/4', 'h-1/5', 'h-2/5', 'h-3/5', 'h-4/5', 'h-1/6', 'h-5/6'
  ],
  
  // TYPOGRAPHY
  fontSize: ['text-xs', 'text-sm', 'text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl', 'text-4xl', 'text-5xl', 'text-6xl', 'text-7xl', 'text-8xl', 'text-9xl'],
  fontWeight: ['font-thin', 'font-extralight', 'font-light', 'font-normal', 'font-medium', 'font-semibold', 'font-bold', 'font-extrabold', 'font-black'],
  fontStyle: ['italic', 'not-italic'],
  textAlign: ['text-left', 'text-center', 'text-right', 'text-justify', 'text-start', 'text-end'],
  textTransform: ['uppercase', 'lowercase', 'capitalize', 'normal-case'],
  textDecoration: ['underline', 'overline', 'line-through', 'no-underline'],
  lineHeight: ['leading-none', 'leading-tight', 'leading-snug', 'leading-normal', 'leading-relaxed', 'leading-loose'],
  letterSpacing: ['tracking-tighter', 'tracking-tight', 'tracking-normal', 'tracking-wide', 'tracking-wider', 'tracking-widest'],
  
  // COLORS
  textColors: [
    'text-white', 'text-black', 'text-transparent', 'text-current',
    'text-slate-50', 'text-slate-100', 'text-slate-200', 'text-slate-300', 'text-slate-400', 'text-slate-500', 'text-slate-600', 'text-slate-700', 'text-slate-800', 'text-slate-900',
    'text-gray-50', 'text-gray-100', 'text-gray-200', 'text-gray-300', 'text-gray-400', 'text-gray-500', 'text-gray-600', 'text-gray-700', 'text-gray-800', 'text-gray-900',
    'text-red-50', 'text-red-100', 'text-red-200', 'text-red-300', 'text-red-400', 'text-red-500', 'text-red-600', 'text-red-700', 'text-red-800', 'text-red-900',
    'text-orange-50', 'text-orange-100', 'text-orange-200', 'text-orange-300', 'text-orange-400', 'text-orange-500', 'text-orange-600', 'text-orange-700', 'text-orange-800', 'text-orange-900',
    'text-amber-50', 'text-amber-100', 'text-amber-200', 'text-amber-300', 'text-amber-400', 'text-amber-500', 'text-amber-600', 'text-amber-700', 'text-amber-800', 'text-amber-900',
    'text-yellow-50', 'text-yellow-100', 'text-yellow-200', 'text-yellow-300', 'text-yellow-400', 'text-yellow-500', 'text-yellow-600', 'text-yellow-700', 'text-yellow-800', 'text-yellow-900',
    'text-lime-50', 'text-lime-100', 'text-lime-200', 'text-lime-300', 'text-lime-400', 'text-lime-500', 'text-lime-600', 'text-lime-700', 'text-lime-800', 'text-lime-900',
    'text-green-50', 'text-green-100', 'text-green-200', 'text-green-300', 'text-green-400', 'text-green-500', 'text-green-600', 'text-green-700', 'text-green-800', 'text-green-900',
    'text-emerald-50', 'text-emerald-100', 'text-emerald-200', 'text-emerald-300', 'text-emerald-400', 'text-emerald-500', 'text-emerald-600', 'text-emerald-700', 'text-emerald-800', 'text-emerald-900',
    'text-teal-50', 'text-teal-100', 'text-teal-200', 'text-teal-300', 'text-teal-400', 'text-teal-500', 'text-teal-600', 'text-teal-700', 'text-teal-800', 'text-teal-900',
    'text-cyan-50', 'text-cyan-100', 'text-cyan-200', 'text-cyan-300', 'text-cyan-400', 'text-cyan-500', 'text-cyan-600', 'text-cyan-700', 'text-cyan-800', 'text-cyan-900',
    'text-sky-50', 'text-sky-100', 'text-sky-200', 'text-sky-300', 'text-sky-400', 'text-sky-500', 'text-sky-600', 'text-sky-700', 'text-sky-800', 'text-sky-900',
    'text-blue-50', 'text-blue-100', 'text-blue-200', 'text-blue-300', 'text-blue-400', 'text-blue-500', 'text-blue-600', 'text-blue-700', 'text-blue-800', 'text-blue-900',
    'text-indigo-50', 'text-indigo-100', 'text-indigo-200', 'text-indigo-300', 'text-indigo-400', 'text-indigo-500', 'text-indigo-600', 'text-indigo-700', 'text-indigo-800', 'text-indigo-900',
    'text-violet-50', 'text-violet-100', 'text-violet-200', 'text-violet-300', 'text-violet-400', 'text-violet-500', 'text-violet-600', 'text-violet-700', 'text-violet-800', 'text-violet-900',
    'text-purple-50', 'text-purple-100', 'text-purple-200', 'text-purple-300', 'text-purple-400', 'text-purple-500', 'text-purple-600', 'text-purple-700', 'text-purple-800', 'text-purple-900',
    'text-fuchsia-50', 'text-fuchsia-100', 'text-fuchsia-200', 'text-fuchsia-300', 'text-fuchsia-400', 'text-fuchsia-500', 'text-fuchsia-600', 'text-fuchsia-700', 'text-fuchsia-800', 'text-fuchsia-900',
    'text-pink-50', 'text-pink-100', 'text-pink-200', 'text-pink-300', 'text-pink-400', 'text-pink-500', 'text-pink-600', 'text-pink-700', 'text-pink-800', 'text-pink-900',
    'text-rose-50', 'text-rose-100', 'text-rose-200', 'text-rose-300', 'text-rose-400', 'text-rose-500', 'text-rose-600', 'text-rose-700', 'text-rose-800', 'text-rose-900',
  ],
  
  bgColors: [
    'bg-white', 'bg-black', 'bg-transparent', 'bg-current',
    'bg-slate-50', 'bg-slate-100', 'bg-slate-200', 'bg-slate-300', 'bg-slate-400', 'bg-slate-500', 'bg-slate-600', 'bg-slate-700', 'bg-slate-800', 'bg-slate-900',
    'bg-gray-50', 'bg-gray-100', 'bg-gray-200', 'bg-gray-300', 'bg-gray-400', 'bg-gray-500', 'bg-gray-600', 'bg-gray-700', 'bg-gray-800', 'bg-gray-900',
    'bg-red-50', 'bg-red-100', 'bg-red-200', 'bg-red-300', 'bg-red-400', 'bg-red-500', 'bg-red-600', 'bg-red-700', 'bg-red-800', 'bg-red-900',
    'bg-blue-50', 'bg-blue-100', 'bg-blue-200', 'bg-blue-300', 'bg-blue-400', 'bg-blue-500', 'bg-blue-600', 'bg-blue-700', 'bg-blue-800', 'bg-blue-900',
    'bg-green-50', 'bg-green-100', 'bg-green-200', 'bg-green-300', 'bg-green-400', 'bg-green-500', 'bg-green-600', 'bg-green-700', 'bg-green-800', 'bg-green-900',
    'bg-yellow-50', 'bg-yellow-100', 'bg-yellow-200', 'bg-yellow-300', 'bg-yellow-400', 'bg-yellow-500', 'bg-yellow-600', 'bg-yellow-700', 'bg-yellow-800', 'bg-yellow-900',
    'bg-purple-50', 'bg-purple-100', 'bg-purple-200', 'bg-purple-300', 'bg-purple-400', 'bg-purple-500', 'bg-purple-600', 'bg-purple-700', 'bg-purple-800', 'bg-purple-900',
    'bg-pink-50', 'bg-pink-100', 'bg-pink-200', 'bg-pink-300', 'bg-pink-400', 'bg-pink-500', 'bg-pink-600', 'bg-pink-700', 'bg-pink-800', 'bg-pink-900',
  ],
  
  // BORDERS
  borderWidth: ['border', 'border-0', 'border-2', 'border-4', 'border-8', 'border-t', 'border-r', 'border-b', 'border-l'],
  borderColor: ['border-gray-200', 'border-gray-300', 'border-gray-400', 'border-red-500', 'border-blue-500', 'border-green-500'],
  borderRadius: [
    'rounded-none', 'rounded-sm', 'rounded', 'rounded-md', 'rounded-lg', 'rounded-xl', 'rounded-2xl', 'rounded-3xl', 'rounded-full',
    'rounded-t-none', 'rounded-t', 'rounded-t-lg', 'rounded-t-full',
    'rounded-r-none', 'rounded-r', 'rounded-r-lg', 'rounded-r-full',
    'rounded-b-none', 'rounded-b', 'rounded-b-lg', 'rounded-b-full',
    'rounded-l-none', 'rounded-l', 'rounded-l-lg', 'rounded-l-full'
  ],
  
  // EFFECTS
  shadow: ['shadow-none', 'shadow-sm', 'shadow', 'shadow-md', 'shadow-lg', 'shadow-xl', 'shadow-2xl', 'shadow-inner'],
  opacity: ['opacity-0', 'opacity-5', 'opacity-10', 'opacity-20', 'opacity-25', 'opacity-30', 'opacity-40', 'opacity-50', 'opacity-60', 'opacity-70', 'opacity-75', 'opacity-80', 'opacity-90', 'opacity-95', 'opacity-100'],
  blur: ['blur-none', 'blur-sm', 'blur', 'blur-md', 'blur-lg', 'blur-xl', 'blur-2xl', 'blur-3xl'],
  
  // TRANSITIONS
  transition: ['transition-none', 'transition-all', 'transition', 'transition-colors', 'transition-opacity', 'transition-shadow', 'transition-transform'],
  duration: ['duration-75', 'duration-100', 'duration-150', 'duration-200', 'duration-300', 'duration-500', 'duration-700', 'duration-1000'],
  ease: ['ease-linear', 'ease-in', 'ease-out', 'ease-in-out'],
  
  // TRANSFORM
  scale: ['scale-0', 'scale-50', 'scale-75', 'scale-90', 'scale-95', 'scale-100', 'scale-105', 'scale-110', 'scale-125', 'scale-150'],
  rotate: ['rotate-0', 'rotate-1', 'rotate-2', 'rotate-3', 'rotate-6', 'rotate-12', 'rotate-45', 'rotate-90', 'rotate-180', '-rotate-180', '-rotate-90', '-rotate-45'],
  
  // POSITION
  position: ['static', 'fixed', 'absolute', 'relative', 'sticky'],
  inset: ['inset-0', 'inset-x-0', 'inset-y-0', 'top-0', 'right-0', 'bottom-0', 'left-0'],
  zIndex: ['z-0', 'z-10', 'z-20', 'z-30', 'z-40', 'z-50', 'z-auto'],
  
  // INTERACTIVITY
  cursor: ['cursor-auto', 'cursor-default', 'cursor-pointer', 'cursor-wait', 'cursor-text', 'cursor-move', 'cursor-not-allowed', 'cursor-grab'],
  
  // RESPONSIVE & STATE PREFIXES
  responsive: ['sm:', 'md:', 'lg:', 'xl:', '2xl:'],
  states: ['hover:', 'focus:', 'active:', 'disabled:', 'group-hover:', 'focus-within:'],
};

// Flatten all classes into single array
const ALL_TAILWIND_CLASSES = Object.values(TAILWIND_CLASSES).flat();

// Custom Tailwind Autocomplete Plugin
const tailwindAutocomplete = (editor) => {
  // Add Tailwind CSS to canvas
  editor.on('load', () => {
    const canvas = editor.Canvas.getFrameEl();
    if (canvas && canvas.contentDocument) {
      const head = canvas.contentDocument.head;
      if (!head.querySelector('#tailwind-css')) {
        const link = document.createElement('link');
        link.id = 'tailwind-css';
        link.rel = 'stylesheet';
        link.href = 'https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css';
        head.appendChild(link);
      }
    }
  });

  // Enhanced Class Trait with Autocomplete
  editor.on('component:selected', (component) => {
    const traits = component.get('traits');
    const classTrait = traits.where({ name: 'class' })[0];
    
    if (!classTrait) {
      component.addTrait({
        type: 'text',
        name: 'class',
        label: 'Tailwind Classes',
        placeholder: 'Type to search classes...'
      });
    }
  });

  // Create autocomplete dropdown
  editor.TraitManager.addType('class-autocomplete', {
    createInput({ trait }) {
      const el = document.createElement('div');
      el.innerHTML = `
        <div style="position: relative;">
          <input 
            type="text" 
            class="tailwind-class-input"
            placeholder="Type Tailwind classes..."
            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 12px;"
          />
          <div class="tailwind-autocomplete" style="
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
          "></div>
        </div>
      `;
      
      const input = el.querySelector('.tailwind-class-input');
      const dropdown = el.querySelector('.tailwind-autocomplete');
      
      input.addEventListener('input', (e) => {
        const value = e.target.value.split(' ').pop().trim();
        
        if (value.length < 2) {
          dropdown.style.display = 'none';
          return;
        }
        
        const matches = ALL_TAILWIND_CLASSES
          .filter(cls => cls.toLowerCase().includes(value.toLowerCase()))
          .slice(0, 15);
        
        if (matches.length > 0) {
          dropdown.innerHTML = matches.map(cls => `
            <div class="autocomplete-item" data-class="${cls}" style="
              padding: 8px 12px;
              cursor: pointer;
              font-family: monospace;
              font-size: 12px;
              border-bottom: 1px solid #f0f0f0;
              transition: background 0.2s;
            ">${cls}</div>
          `).join('');
          
          dropdown.style.display = 'block';
          
          dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
              item.style.background = '#3b82f6';
              item.style.color = 'white';
            });
            
            item.addEventListener('mouseleave', () => {
              item.style.background = 'white';
              item.style.color = 'black';
            });
            
            item.addEventListener('click', () => {
              const classes = input.value.split(' ');
              classes[classes.length - 1] = item.dataset.class;
              input.value = classes.join(' ') + ' ';
              dropdown.style.display = 'none';
              input.focus();
              
              // Update component
              const component = editor.getSelected();
              if (component) {
                component.setClass(input.value.trim());
              }
            });
          });
        } else {
          dropdown.style.display = 'none';
        }
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', (e) => {
        if (!el.contains(e.target)) {
          dropdown.style.display = 'none';
        }
      });
      
      return el;
    }
  });
};

// Initialize Editor with Plugin
const editor = grapesjs.init({
  container: '#gjs',
  height: '100vh',
  plugins: [tailwindAutocomplete],
  canvas: {
    styles: ['https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css']
  }
});
```

---