# grapesjs-lp-builder

Production-ready GrapesJS plugin for landing page templates that use section-level `data-gjs-type` metadata, GSAP animation traits, and Three.js scene traits.

## Features

- Section component recognition:
  - `standard-section`
  - `gsap-animated`
  - `threejs-scene`
- Sequential runtime injection in canvas iframe:
  - GSAP core
  - ScrollTrigger
  - Three.js
- Custom iframe event after runtime load: `lp:ready`
- Manifest prefill helper: `loadManifest(editor, manifest)`
- Export helper: `exportTemplate(editor)` with generated self-contained `animations.js`
- Panel controls:
  - Preview Animations
  - Reset Animations

## Installation

### Script tag (UMD/IIFE)

```html
<script src="/path/to/grapesjs.min.js"></script>
<script src="/path/to/grapesjs-lp-builder.js"></script>
```

### ESM

```javascript
import gjsLPBuilder from './grapesjs-lp-builder/src/index.js';
```

## Plugin Options

```javascript
{
  gsap: true,
  threejs: true,
  gsapVersion: '3.12.2',
  threeVersion: 'r128',
  onReady: null,
  debug: false
}
```

## Required Data Attributes

### `gsap-animated`

- `data-gsap-animation`
- `data-gsap-duration`
- `data-gsap-delay`
- `data-gsap-ease`
- `data-gsap-trigger`
- `data-gsap-children`
- `data-gsap-stagger`

### `threejs-scene`

- `data-scene-type`
- `data-scene-color`
- `data-scene-bg`
- `data-scene-height`
- `data-scene-speed`
- `data-particle-count`
- `data-threejs-overlay`
- `data-wireframe`
- `data-auto-rotate`

## Usage Example

```javascript
const editor = grapesjs.init({
  container: '#gjs',
  plugins: [window.gjsLPBuilder],
  pluginsOpts: {
    'grapesjs-lp-builder': {
      gsap: true,
      threejs: true,
    }
  }
});

// After loading template:
window.gjsLPBuilder.loadManifest(editor, manifest);

// On export button click:
const result = window.gjsLPBuilder.exportTemplate(editor);
```

## Export Result

`exportTemplate(editor)` returns:

```javascript
{
  html: string,
  css: string,
  animationsJS: string
}
```

The generated `animationsJS` includes:

- `DOMContentLoaded` wrapper
- `getFromVars()` helper
- `buildScene()` helper
- Per-section GSAP runtime calls
- Per-section Three.js runtime calls
