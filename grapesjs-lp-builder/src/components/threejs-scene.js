/**
 * GrapesJS component type for Three.js scenes with live rebuild support.
 */

import { buildScene, disposeScene, normalizeSceneConfig } from '../utils/scene-builder';
import {
    collapseDynamicSection,
    isDynamicSectionCollapsed,
    upsertSectionBadge,
} from '../utils/badge-injector';

const SCENE_TYPES = ['particles', 'rotating-cube', 'sphere', 'wave', 'globe', 'rings'];

const sceneTypeOptions = SCENE_TYPES.map((item) => ({ id: item, name: item }));

const bgOptions = [
    { id: 'transparent', name: 'transparent' },
    { id: '#0b1020', name: '#0b1020' },
    { id: '#111827', name: '#111827' },
    { id: '#ffffff', name: '#ffffff' },
];

const toBoolString = (value, fallback) => {
    if (value == null) {
        return fallback;
    }
    if (typeof value === 'boolean') {
        return value ? 'true' : 'false';
    }
    const normalized = String(value).trim().toLowerCase();
    if (normalized === 'true' || normalized === 'false') {
        return normalized;
    }
    return fallback;
};

/**
 * Register threejs-scene component type.
 * @param {import('grapesjs').Editor} editor
 */
export default function registerThreejsScene(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('threejs-scene', {
        isComponent(el) {
            if (!el || typeof el.getAttribute !== 'function') {
                return false;
            }

            if (el.getAttribute('data-gjs-type') === 'threejs-scene') {
                return { type: 'threejs-scene' };
            }

            return false;
        },

        model: {
            defaults: {
                name: 'Three.js Scene',
                draggable: true,
                droppable: true,
                style: { 'min-height': '80px', position: 'relative' },
                traits: [
                    {
                        type: 'select',
                        name: 'data-scene-type',
                        label: 'Scene type',
                        options: sceneTypeOptions,
                    },
                    {
                        type: 'color',
                        name: 'data-scene-color',
                        label: 'Primary color',
                    },
                    {
                        type: 'select',
                        name: 'data-scene-bg',
                        label: 'Background',
                        options: bgOptions,
                    },
                    {
                        type: 'number',
                        name: 'data-scene-height',
                        label: 'Height (px)',
                        min: 100,
                        max: 1000,
                        step: 50,
                    },
                    {
                        type: 'number',
                        name: 'data-scene-speed',
                        label: 'Speed',
                        min: 0.1,
                        max: 3,
                        step: 0.1,
                    },
                    {
                        type: 'number',
                        name: 'data-particle-count',
                        label: 'Particle count',
                        min: 10,
                        max: 500,
                        step: 10,
                    },
                    {
                        type: 'checkbox',
                        name: 'data-wireframe',
                        label: 'Wireframe mode',
                    },
                    {
                        type: 'checkbox',
                        name: 'data-auto-rotate',
                        label: 'Auto rotate',
                    },
                    {
                        type: 'checkbox',
                        name: 'data-threejs-overlay',
                        label: 'Background mode',
                    },
                ],
                attributes: {
                    'data-scene-type': 'particles',
                    'data-scene-color': '#5b8cff',
                    'data-scene-bg': 'transparent',
                    'data-scene-height': '400',
                    'data-scene-speed': '1',
                    'data-particle-count': '120',
                    'data-wireframe': 'false',
                    'data-auto-rotate': 'true',
                    'data-threejs-overlay': 'true',
                },
            },
        },

        view: {
            init() {
                this.sceneInstance = null;
                this.rebuildTimer = 0;
                this.waitReadyBound = false;
                this.onLpSectionExpanded = ({ cid } = {}) => {
                    if (cid === this.model.cid) {
                        this.rebuildScene();
                    }
                };
                this.onLpSectionCollapsed = ({ cid } = {}) => {
                    if (cid === this.model.cid && this.sceneInstance?.renderer?.domElement) {
                        this.sceneInstance.renderer.domElement.style.visibility = 'hidden';
                    }
                };
                editor.on('lp:section:expanded', this.onLpSectionExpanded);
                editor.on('lp:section:collapsed', this.onLpSectionCollapsed);

                this.listenTo(this.model, 'change:attributes', () => {
                    if (isDynamicSectionCollapsed(this.el)) {
                        collapseDynamicSection(this.model, this.el, editor);
                    } else {
                        upsertSectionBadge(this.model, this.el, editor);
                    }
                    clearTimeout(this.rebuildTimer);
                    this.rebuildTimer = setTimeout(() => {
                        this.rebuildScene();
                    }, 300);
                });
            },

            onRender() {
                collapseDynamicSection(this.model, this.el, editor);
                this.rebuildScene();
            },

            removed() {
                if (this.onLpSectionExpanded) {
                    editor.off('lp:section:expanded', this.onLpSectionExpanded);
                }
                if (this.onLpSectionCollapsed) {
                    editor.off('lp:section:collapsed', this.onLpSectionCollapsed);
                }
                clearTimeout(this.rebuildTimer);
                disposeScene(this.sceneInstance);
                this.sceneInstance = null;
            },

            rebuildScene() {
                if (!this.el) {
                    return;
                }

                upsertSectionBadge(this.model, this.el, editor);
                if (isDynamicSectionCollapsed(this.el)) {
                    if (this.sceneInstance?.renderer?.domElement) {
                        this.sceneInstance.renderer.domElement.style.visibility = 'hidden';
                    }
                    return;
                }

                disposeScene(this.sceneInstance);
                this.sceneInstance = null;

                const doc = this.el.ownerDocument;
                const win = doc?.defaultView;
                if (!win?.THREE) {
                    if (!doc || this.waitReadyBound) {
                        return;
                    }

                    this.waitReadyBound = true;
                    const onceReady = () => {
                        this.waitReadyBound = false;
                        this.rebuildScene();
                        doc.removeEventListener('lp:ready', onceReady);
                    };
                    doc.addEventListener('lp:ready', onceReady, { once: true });
                    return;
                }

                const attrs = this.model.getAttributes() || {};
                const config = normalizeSceneConfig({
                    sceneType: attrs['data-scene-type'],
                    sceneColor: attrs['data-scene-color'],
                    sceneBg: attrs['data-scene-bg'],
                    sceneHeight: attrs['data-scene-height'],
                    sceneSpeed: attrs['data-scene-speed'],
                    particleCount: attrs['data-particle-count'],
                    wireframe: toBoolString(attrs['data-wireframe'], 'false'),
                    autoRotate: toBoolString(attrs['data-auto-rotate'], 'true'),
                    overlay: toBoolString(attrs['data-threejs-overlay'], 'true'),
                });

                let preview = this.el.querySelector(':scope > [data-lp-three-preview]');
                if (!preview) {
                    preview = this.el.ownerDocument.createElement('div');
                    preview.setAttribute('data-lp-three-preview', 'true');
                    preview.setAttribute('data-gjs-selectable', 'false');
                    preview.setAttribute('contenteditable', 'false');
                    preview.style.cssText = 'position:relative;width:100%;min-height:220px;z-index:1;';
                    this.el.appendChild(preview);
                }

                preview.style.visibility = '';
                preview.style.position = 'relative';
                preview.style.pointerEvents = 'auto';
                this.sceneInstance = buildScene(preview, config, { win });
                if (this.sceneInstance?.renderer?.domElement) {
                    this.sceneInstance.renderer.domElement.style.visibility = '';
                }
            },
        },
    });
}
