/**
 * Entry point for grapesjs-lp-builder plugin.
 * Registers section components, panel controls, runtime script injection,
 * and exports manifest/export helpers.
 */

import registerStandardSection from './components/standard-section';
import registerGsapAnimated from './components/gsap-animated';
import registerThreejsScene from './components/threejs-scene';
import registerAnimationControls from './panels/animation-controls';
import registerSectionsNavigator from './panels/sections-navigator';
import { injectLpBuilderCanvasStyles } from './utils/badge-injector';
import { loadManifest } from './utils/manifest-loader';
import { exportTemplate } from './utils/export-handler';

const PLUGIN_ID = 'grapesjs-lp-builder';

const DEFAULT_OPTIONS = Object.freeze({
    gsap: true,
    threejs: true,
    sectionsNavigator: true,
    gsapVersion: '3.12.2',
    threeVersion: 'r128',
    onReady: null,
    debug: false,
});

const debugLog = (options, ...args) => {
    if (options.debug) {
        // eslint-disable-next-line no-console
        console.info('[grapesjs-lp-builder]', ...args);
    }
};

const buildRuntimeUrls = (options) => {
    const gsapVersion = options.gsapVersion || DEFAULT_OPTIONS.gsapVersion;
    const threeVersion = options.threeVersion || DEFAULT_OPTIONS.threeVersion;

    return [
        `https://cdnjs.cloudflare.com/ajax/libs/gsap/${gsapVersion}/gsap.min.js`,
        `https://cdnjs.cloudflare.com/ajax/libs/gsap/${gsapVersion}/ScrollTrigger.min.js`,
        `https://cdnjs.cloudflare.com/ajax/libs/three.js/${threeVersion}/three.min.js`,
    ];
};

const injectScript = (doc, src) => new Promise((resolve, reject) => {
    const existing = doc.querySelector(`script[data-lp-builder-src="${src}"]`) ||
        Array.from(doc.querySelectorAll('script[src]')).find((node) => node.getAttribute('src') === src);

    if (existing) {
        if (existing.getAttribute('data-lp-builder-loaded') === 'true') {
            resolve(existing);
            return;
        }

        const onLoad = () => {
            existing.setAttribute('data-lp-builder-loaded', 'true');
            resolve(existing);
        };
        const onError = () => reject(new Error('Failed to load: ' + src));
        existing.addEventListener('load', onLoad, { once: true });
        existing.addEventListener('error', onError, { once: true });
        return;
    }

    const script = doc.createElement('script');
    script.async = false;
    script.src = src;
    script.setAttribute('data-lp-builder-src', src);
    script.setAttribute('data-lp-builder-loaded', 'false');

    script.onload = () => {
        script.setAttribute('data-lp-builder-loaded', 'true');
        resolve(script);
    };
    script.onerror = () => {
        reject(new Error('Failed to load: ' + src));
    };

    doc.body.appendChild(script);
});

const dispatchReady = (editor, options, win, doc) => {
    if (!doc || doc.__lpBuilderReadyDispatched) {
        return;
    }

    doc.__lpBuilderReadyDispatched = true;
    doc.dispatchEvent(new win.CustomEvent('lp:ready', { detail: { plugin: PLUGIN_ID } }));

    if (typeof options.onReady === 'function') {
        options.onReady(editor, { win, doc });
    }
};

const ensureRuntime = async (editor, options) => {
    const doc = editor.Canvas.getDocument?.();
    const win = editor.Canvas.getWindow?.();

    if (!doc || !win || !doc.body) {
        return;
    }

    if (doc.__lpBuilderScriptsLoading) {
        return;
    }

    doc.__lpBuilderScriptsLoading = true;

    const urls = buildRuntimeUrls(options);

    try {
        for (const src of urls) {
            await injectScript(doc, src);
        }

        injectLpBuilderCanvasStyles(doc);
        dispatchReady(editor, options, win, doc);
        debugLog(options, 'Runtime loaded and lp:ready dispatched.');
    } catch (error) {
        debugLog(options, error.message || 'Runtime load failed.');
    } finally {
        doc.__lpBuilderScriptsLoading = false;
    }
};

/**
 * GrapesJS plugin function.
 * @param {import('grapesjs').Editor} editor
 * @param {object} pluginOptions
 */
function lpBuilderPlugin(editor, pluginOptions = {}) {
    const options = { ...DEFAULT_OPTIONS, ...(pluginOptions || {}) };

    registerStandardSection(editor);

    if (options.gsap) {
        registerGsapAnimated(editor);
    }

    if (options.threejs) {
        registerThreejsScene(editor);
    }

    registerAnimationControls(editor);
    if (options.sectionsNavigator) {
        registerSectionsNavigator(editor);
    }

    editor.on('load', () => {
        ensureRuntime(editor, options);
    });

    editor.on('canvas:frame:load', () => {
        ensureRuntime(editor, options);
    });
}

lpBuilderPlugin.pluginName = PLUGIN_ID;
lpBuilderPlugin.id = PLUGIN_ID;
lpBuilderPlugin.loadManifest = loadManifest;
lpBuilderPlugin.exportTemplate = exportTemplate;

export { PLUGIN_ID, DEFAULT_OPTIONS, loadManifest, exportTemplate };
export default lpBuilderPlugin;
