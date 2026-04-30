import grapesjs from 'grapesjs';
import 'grapesjs/dist/css/grapes.min.css';
import {
    initFunnelEditorPluginSystem,
    MODE_EDITOR,
    parseStoredProjectData,
} from './grapesjs/editor-plugin-system';

const WORKSPACE_PLUGIN_TAILWIND = 'tailwind-css';
const WORKSPACE_PLUGIN_MATERIAL_ICONS = 'google-material-icons';
const TAILWIND_READY_PROBE_ID = 'funnel-editor-tailwind-ready-probe';

const sleep = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));

const hasWorkspacePlugin = (workspacePlugins, slug) => {
    const target = String(slug || '').trim().toLowerCase();
    if (!target || !Array.isArray(workspacePlugins)) {
        return false;
    }

    return workspacePlugins.some((plugin) => String(plugin?.slug || '').trim().toLowerCase() === target);
};

const waitForEditorLoad = (editor, timeoutMs = 8000) => new Promise((resolve) => {
    if (editor.getModel()?.get('ready')) {
        resolve(true);
        return;
    }

    let settled = false;
    const done = (value) => {
        if (settled) return;
        settled = true;
        resolve(value);
    };

    const timer = window.setTimeout(() => done(false), timeoutMs);
    editor.once('load', () => {
        window.clearTimeout(timer);
        done(true);
    });
});

const isTailwindReadyInCanvas = (editor) => {
    const frameDoc = editor.Canvas.getDocument?.();
    if (!frameDoc?.body || !frameDoc.defaultView) {
        return false;
    }

    let probe = frameDoc.getElementById(TAILWIND_READY_PROBE_ID);
    if (!probe) {
        probe = frameDoc.createElement('div');
        probe.id = TAILWIND_READY_PROBE_ID;
        probe.className = 'hidden';
        probe.textContent = '.';
        probe.style.position = 'absolute';
        probe.style.left = '-9999px';
        probe.style.top = '-9999px';
        frameDoc.body.appendChild(probe);
    }

    const computed = frameDoc.defaultView.getComputedStyle(probe);
    return computed.display === 'none';
};

const waitForTailwindReadyInCanvas = async (editor, { timeoutMs = 10000, pollMs = 120 } = {}) => {
    const startedAt = Date.now();
    while (Date.now() - startedAt < timeoutMs) {
        if (isTailwindReadyInCanvas(editor)) {
            return true;
        }
        await sleep(pollMs);
    }
    return false;
};

const waitForMaterialIconsInCanvas = async (editor, { timeoutMs = 6000, pollMs = 120 } = {}) => {
    const startedAt = Date.now();
    while (Date.now() - startedAt < timeoutMs) {
        const frameDoc = editor.Canvas.getDocument?.();
        const hostDocReady = !!document.getElementById('funnel-editor-material-icons-font');
        const frameReady = !!frameDoc?.getElementById?.('funnel-editor-material-icons-font');
        if (hostDocReady && frameReady) {
            return true;
        }
        await sleep(pollMs);
    }
    return false;
};

const extractCssImportUrls = (rawCss) => {
    const source = String(rawCss || '');
    if (!source.trim()) {
        return { importUrls: [], cssWithoutImports: '' };
    }

    const importUrls = [];
    const cssWithoutImports = source.replace(
        /@import\s+url\(\s*(['"]?)([^'")]+)\1\s*\)\s*;?/gi,
        (_full, _quote, url) => {
            const candidate = String(url || '').trim();
            if (candidate) {
                importUrls.push(candidate);
            }
            return '';
        }
    );

    return {
        importUrls: Array.from(new Set(importUrls)),
        cssWithoutImports: cssWithoutImports.trim(),
    };
};

const ensureImportedStylesInCanvas = (editor, importUrls = []) => {
    const frameDoc = editor.Canvas.getDocument?.();
    if (!frameDoc?.head || !Array.isArray(importUrls) || importUrls.length === 0) {
        return;
    }

    importUrls.forEach((href, index) => {
        const safeKey = String(href)
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            .slice(0, 48) || `import-${index}`;
        const linkId = `funnel-editor-css-import-${index}-${safeKey}`;

        let link = frameDoc.getElementById(linkId);
        if (!link) {
            link = frameDoc.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            frameDoc.head.appendChild(link);
        }

        if (link.getAttribute('href') !== href) {
            link.setAttribute('href', href);
        }
    });
};

const detectTemplateStorageBase = (...sources) => {
    const combined = sources
        .map((value) => String(value || ''))
        .join('\n');
    const match = combined.match(/\/storage\/builder-templates\/[^"' )]+/i);
    if (!match || !match[0]) {
        return '';
    }

    return String(match[0]).replace(/\/assets\/.*$/i, '').replace(/\/+$/, '');
};

const resolveTemplateAssetUrl = (rawUrl, templateStorageBase = '') => {
    const url = String(rawUrl || '').trim();
    if (!url) {
        return '';
    }

    if (/^(?:https?:)?\/\//i.test(url) || /^(?:data:|blob:|mailto:|tel:|javascript:|#)/i.test(url)) {
        return url;
    }

    if (url.startsWith('/storage/')) {
        return url;
    }

    if (!templateStorageBase) {
        return url;
    }

    let normalized = url.replace(/^[./]+/, '');
    while (normalized.startsWith('../')) {
        normalized = normalized.slice(3);
    }

    if (normalized.startsWith('assets/')) {
        return `${templateStorageBase}/${normalized}`;
    }

    if (url.startsWith('/assets/')) {
        return `${templateStorageBase}${url}`;
    }

    return url;
};

const extractScriptEntries = (rawJs, templateStorageBase = '') => {
    const source = String(rawJs || '').trim();
    if (!source) {
        return [];
    }

    const hasScriptTags = /<script\b/i.test(source);
    if (!hasScriptTags) {
        return [{ inlineCode: source, type: 'text/javascript' }];
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(`<div>${source}</div>`, 'text/html');
    const scripts = Array.from(doc.querySelectorAll('script'));

    return scripts.map((script) => {
        const src = resolveTemplateAssetUrl(script.getAttribute('src') || '', templateStorageBase);
        const type = String(script.getAttribute('type') || 'text/javascript').trim().toLowerCase();
        const rawInlineCode = (script.textContent || '').trim();
        let importMapJson = '';

        if (!src && (type === 'importmap' || (type === 'text/javascript' && rawInlineCode.startsWith('{') && rawInlineCode.includes('"imports"')))) {
            importMapJson = rawInlineCode;
        }

        return {
            src,
            type,
            async: script.hasAttribute('async'),
            defer: script.hasAttribute('defer'),
            noModule: script.hasAttribute('nomodule'),
            crossOrigin: script.getAttribute('crossorigin') || '',
            integrity: script.getAttribute('integrity') || '',
            referrerPolicy: script.getAttribute('referrerpolicy') || '',
            importMapJson,
            inlineCode: src || importMapJson ? '' : rawInlineCode,
        };
    }).filter((entry) => Boolean(entry.src || entry.inlineCode || entry.importMapJson));
};

const maybeRewriteModuleSourceForTemplate = async (entry, templateStorageBase = '') => {
    if (!entry?.src || entry.type !== 'module') {
        return null;
    }

    if (!templateStorageBase || !entry.src.includes('/storage/builder-templates/')) {
        return null;
    }

    try {
        const response = await fetch(entry.src, { credentials: 'same-origin' });
        if (!response.ok) {
            return null;
        }

        const raw = await response.text();
        if (!raw) {
            return null;
        }

        const rewritten = raw.replace(
            /(['"`])\/assets\//g,
            `$1${templateStorageBase}/assets/`
        );

        if (rewritten === raw) {
            return null;
        }

        return URL.createObjectURL(new Blob([rewritten], { type: 'text/javascript' }));
    } catch {
        return null;
    }
};

const loadScriptEntryInFrame = async (frameDoc, entry, index, templateStorageBase = '') => new Promise(async (resolve) => {
    if (!frameDoc?.body) {
        resolve(false);
        return;
    }

    if (entry.importMapJson) {
        if (!frameDoc.head) {
            resolve(false);
            return;
        }

        const importMapId = `funnel-editor-importmap-${index}`;
        let importMap = frameDoc.getElementById(importMapId);
        if (!importMap) {
            importMap = frameDoc.createElement('script');
            importMap.id = importMapId;
            importMap.setAttribute('data-funnel-editor-injected-script', '1');
            importMap.type = 'importmap';
            frameDoc.head.appendChild(importMap);
        }

        importMap.textContent = entry.importMapJson;
        resolve(true);
        return;
    }

    const script = frameDoc.createElement('script');
    script.setAttribute('data-funnel-editor-injected-script', '1');
    script.setAttribute('data-funnel-editor-script-index', String(index));

    if (entry.type) {
        script.type = entry.type;
    }
    if (entry.async) {
        script.async = true;
    }
    if (entry.defer) {
        script.defer = true;
    }
    if (entry.noModule) {
        script.noModule = true;
    }
    if (entry.crossOrigin) {
        script.crossOrigin = entry.crossOrigin;
    }
    if (entry.integrity) {
        script.integrity = entry.integrity;
    }
    if (entry.referrerPolicy) {
        script.referrerPolicy = entry.referrerPolicy;
    }

    if (entry.src) {
        script.onload = () => resolve(true);
        script.onerror = () => resolve(false);
        const moduleBlobUrl = await maybeRewriteModuleSourceForTemplate(entry, templateStorageBase);
        script.src = moduleBlobUrl || entry.src;
        frameDoc.body.appendChild(script);
        return;
    }

    script.textContent = entry.inlineCode || '';
    frameDoc.body.appendChild(script);
    resolve(true);
});

const ensureScriptsInCanvas = async (editor, scriptEntries = [], templateStorageBase = '') => {
    if (!Array.isArray(scriptEntries) || scriptEntries.length === 0) {
        return;
    }

    const frameDoc = editor.Canvas.getDocument?.();
    if (!frameDoc?.body) {
        return;
    }

    frameDoc
        .querySelectorAll('script[data-funnel-editor-injected-script="1"]')
        .forEach((node) => node.remove());

    for (let index = 0; index < scriptEntries.length; index += 1) {
        await loadScriptEntryInFrame(frameDoc, scriptEntries[index], index, templateStorageBase);
    }
};

const isElementVisiblyRendered = (doc, element) => {
    if (!doc?.defaultView || !element) {
        return false;
    }

    const style = doc.defaultView.getComputedStyle(element);
    const opacity = Number.parseFloat(style.opacity || '0');
    const displayOk = style.display !== 'none';
    const visibilityOk = style.visibility !== 'hidden';
    const hasBox = (element.offsetWidth > 0 || element.offsetHeight > 0);

    return displayOk && visibilityOk && opacity > 0.05 && hasBox;
};

const ensureSolutionSectionContentVisible = (editor) => {
    const frameDoc = editor.Canvas.getDocument?.();
    const frameWin = editor.Canvas.getWindow?.();
    if (!frameDoc?.body) {
        return;
    }

    const section = frameDoc.querySelector('#solution');
    if (!section) {
        return;
    }

    const slides = Array.from(section.querySelectorAll('.slide-solution'));
    if (slides.length === 0) {
        return;
    }

    const hasGsapRuntime = Boolean(frameWin?.gsap && frameWin?.ScrollTrigger);
    if (hasGsapRuntime) {
        return;
    }

    const anyVisible = slides.some((slide) => isElementVisiblyRendered(frameDoc, slide));
    if (anyVisible) {
        return;
    }

    const cardsContainer = section.querySelector('.relative.w-full.max-w-2xl');
    if (cardsContainer) {
        cardsContainer.style.setProperty('position', 'relative', 'important');
        cardsContainer.style.setProperty('min-height', '360px', 'important');
    }

    slides.forEach((slide, index) => {
        if (index === 0) {
            slide.style.opacity = '1';
            slide.style.visibility = 'visible';
            slide.style.display = 'flex';
            slide.style.transform = 'none';
        } else {
            slide.style.opacity = '0';
            slide.style.visibility = 'hidden';
        }
    });

    const uiLayer = section.querySelector('.ui-layer');
    if (uiLayer) {
        uiLayer.style.opacity = '1';
        uiLayer.style.visibility = 'visible';
    }
};

const ensureEditorSolutionPatchStyles = (editor) => {
    const frameDoc = editor.Canvas.getDocument?.();
    if (!frameDoc?.head) {
        return;
    }

    const styleId = 'funnel-editor-solution-visibility-patch';
    if (frameDoc.getElementById(styleId)) {
        return;
    }

    const style = frameDoc.createElement('style');
    style.id = styleId;
    style.textContent = `
        #solution {
            position: relative !important;
            min-height: 100vh !important;
            overflow: hidden !important;
        }
        #solution #webgl-container-solution {
            z-index: 1 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        #solution .ui-layer {
            position: absolute !important;
            inset: 0 !important;
            z-index: 20 !important;
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
        }
        #solution .reveal {
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
        }
        #solution .slide-solution {
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
            filter: none !important;
        }
    `;
    frameDoc.head.appendChild(style);
};

const setupGsapEditorScrollBridge = (editor) => {
    const frameWin = editor.Canvas.getWindow?.();
    const frameDoc = editor.Canvas.getDocument?.();
    const iframeEl = editor.Canvas.getFrameEl?.();

    if (!frameWin || !frameDoc || !iframeEl) {
        return false;
    }

    const gsap = frameWin.gsap;
    const ScrollTrigger = frameWin.ScrollTrigger;
    if (!gsap || !ScrollTrigger) {
        return false;
    }

    if (frameWin.__funnelEditorGsapBridgeReady) {
        return true;
    }

    let canvasScroller = null;
    try {
        canvasScroller = iframeEl.closest('.gjs-cv-canvas')
            || iframeEl.ownerDocument?.querySelector('.gjs-cv-canvas');
    } catch {
        canvasScroller = null;
    }

    if (!canvasScroller) {
        return false;
    }

    const proxyTarget = frameDoc.scrollingElement || frameDoc.documentElement || frameDoc.body;
    if (!proxyTarget) {
        return false;
    }

    try {
        ScrollTrigger.defaults({ scroller: proxyTarget });
        ScrollTrigger.scrollerProxy(proxyTarget, {
            scrollTop(value) {
                if (typeof value === 'number') {
                    canvasScroller.scrollTop = value;
                }
                return canvasScroller.scrollTop || 0;
            },
            getBoundingClientRect() {
                return {
                    top: 0,
                    left: 0,
                    width: frameWin.innerWidth,
                    height: frameWin.innerHeight,
                };
            },
            pinType: 'transform',
        });

        const onCanvasScroll = () => ScrollTrigger.update();
        canvasScroller.addEventListener('scroll', onCanvasScroll, { passive: true });
        frameWin.addEventListener('resize', () => ScrollTrigger.refresh());

        frameWin.__funnelEditorGsapBridgeReady = true;
        frameWin.__funnelEditorGsapBridgeCleanup = () => {
            try {
                canvasScroller.removeEventListener('scroll', onCanvasScroll);
            } catch {
                // Ignore cleanup errors.
            }
        };

        ScrollTrigger.refresh();
        return true;
    } catch {
        return false;
    }
};

document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('gjs-editor');
    if (!container || !window.editorData) {
        return;
    }

    const bootOverlay = document.getElementById('editor-boot-overlay');
    const bootStatus = document.getElementById('editor-boot-status');
    const setBootStatus = (message) => {
        if (bootStatus) {
            bootStatus.textContent = message;
        }
    };
    const releaseBootOverlay = () => {
        document.body.classList.remove('editor-booting');
        if (bootOverlay) {
            bootOverlay.setAttribute('aria-hidden', 'true');
            window.setTimeout(() => bootOverlay.remove(), 260);
        }
    };

    const hiddenHtml = document.getElementById('gjs-html');
    const hiddenCss = document.getElementById('gjs-css');
    const hiddenJs = document.getElementById('gjs-js');
    const htmlSource = hiddenHtml?.innerHTML ?? '';
    const cssSource = hiddenCss?.textContent ?? '';
    const jsSource = hiddenJs?.textContent ?? '';
    const { importUrls: cssImportUrls, cssWithoutImports } = extractCssImportUrls(cssSource);
    const templateStorageBase = detectTemplateStorageBase(cssSource, htmlSource, jsSource);
    const jsScriptEntries = extractScriptEntries(jsSource, templateStorageBase);
    const projectData = parseStoredProjectData(window.editorData.grapesJsJson);
    const hasProjectData = !window.editorData.forceHtmlMode
        && projectData
        && (Array.isArray(projectData.pages) ? projectData.pages.length > 0 : Object.keys(projectData).length > 0);
    const editorMode = String(window.editorData.editorMode || MODE_EDITOR).toLowerCase();
    const savedStatusEl = document.getElementById('editor-last-saved');

    const setSavedStatus = (message) => {
        if (!savedStatusEl) {
            return;
        }

        savedStatusEl.textContent = message;
    };

    setBootStatus('Starting editor engine...');
    const editor = grapesjs.init({
        container: '#gjs-editor',
        height: '100%',
        storageManager: false,
        noticeOnUnload: false,
    });

    setBootStatus('Loading plugin registry...');
    const pluginSystem = initFunnelEditorPluginSystem(editor, {
        mode: editorMode,
        landingId: window.editorData.landingId,
        pageId: window.editorData.pageId,
        initialSeoMeta: window.editorData.initialSeoMeta || {},
        workspacePlugins: window.editorData.workspacePlugins || [],
    });

    setBootStatus('Loading page structure...');
    if (hasProjectData) {
        editor.loadProjectData(projectData);
        const savedMeta = projectData?.__funnel_builder_plugin_system?.seoMeta;
        if (savedMeta && typeof savedMeta === 'object') {
            editor.getModel().set('funnelSeoMeta', savedMeta);
        }
    } else {
        editor.setComponents(hiddenHtml?.innerHTML ?? '');
        editor.setStyle(cssWithoutImports);
    }

    setBootStatus('Preparing canvas...');
    await waitForEditorLoad(editor, 9000);
    ensureImportedStylesInCanvas(editor, cssImportUrls);
    await ensureScriptsInCanvas(editor, jsScriptEntries, templateStorageBase);
    setupGsapEditorScrollBridge(editor);
    window.setTimeout(() => setupGsapEditorScrollBridge(editor), 350);
    window.setTimeout(() => setupGsapEditorScrollBridge(editor), 1000);
    ensureEditorSolutionPatchStyles(editor);
    ensureSolutionSectionContentVisible(editor);
    window.setTimeout(() => ensureSolutionSectionContentVisible(editor), 900);
    window.setTimeout(() => ensureSolutionSectionContentVisible(editor), 2200);
    editor.on('canvas:frame:load', async () => {
        ensureImportedStylesInCanvas(editor, cssImportUrls);
        await ensureScriptsInCanvas(editor, jsScriptEntries, templateStorageBase);
        setupGsapEditorScrollBridge(editor);
        window.setTimeout(() => setupGsapEditorScrollBridge(editor), 350);
        window.setTimeout(() => setupGsapEditorScrollBridge(editor), 1000);
        ensureEditorSolutionPatchStyles(editor);
        ensureSolutionSectionContentVisible(editor);
        window.setTimeout(() => ensureSolutionSectionContentVisible(editor), 900);
        window.setTimeout(() => ensureSolutionSectionContentVisible(editor), 2200);
    });

    const workspacePlugins = window.editorData.workspacePlugins || [];
    if (hasWorkspacePlugin(workspacePlugins, WORKSPACE_PLUGIN_TAILWIND)) {
        setBootStatus('Applying Tailwind styles...');
        await waitForTailwindReadyInCanvas(editor, { timeoutMs: 12000, pollMs: 120 });
    }

    if (hasWorkspacePlugin(workspacePlugins, WORKSPACE_PLUGIN_MATERIAL_ICONS)) {
        setBootStatus('Loading icon fonts...');
        await waitForMaterialIconsInCanvas(editor, { timeoutMs: 7000, pollMs: 120 });
    }

    setBootStatus('Finalizing editor...');
    await sleep(120);
    releaseBootOverlay();

    let autosaveTimer = null;
    const autosaveSnapshotKey = `funnel-editor:autosave:${window.editorData.landingId}:${window.editorData.pageId}`;
    const persistAutosaveSnapshot = () => {
        if (autosaveTimer) {
            window.clearTimeout(autosaveTimer);
        }

        autosaveTimer = window.setTimeout(() => {
            try {
                const serialized = pluginSystem.serialize();
                localStorage.setItem(autosaveSnapshotKey, JSON.stringify({
                    savedAt: Date.now(),
                    projectData: serialized.projectData,
                }));
                setSavedStatus('Draft autosaved locally');
            } catch {
                // Ignore autosave failures.
            }
        }, 1000);
    };

    editor.on('component:update', persistAutosaveSnapshot);
    editor.on('component:add', persistAutosaveSnapshot);
    editor.on('component:remove', persistAutosaveSnapshot);
    editor.on('style:property:update', persistAutosaveSnapshot);

    const restoreSnapshotButton = document.getElementById('btn-restore-snapshot');
    if (restoreSnapshotButton) {
        restoreSnapshotButton.addEventListener('click', () => {
            const raw = localStorage.getItem(autosaveSnapshotKey);
            if (!raw) {
                if (window.Toast?.error) {
                    window.Toast.error('No local autosave snapshot found.');
                }
                return;
            }

            try {
                const parsed = JSON.parse(raw);
                if (parsed?.projectData) {
                    editor.loadProjectData(parsed.projectData);
                    if (window.Toast?.success) {
                        window.Toast.success('Local autosave restored.');
                    }
                    setSavedStatus('Restored from local autosave');
                }
            } catch {
                if (window.Toast?.error) {
                    window.Toast.error('Failed to restore autosave snapshot.');
                }
            }
        });
    }

    const saveBlockButton = document.getElementById('btn-save-block');
    if (saveBlockButton) {
        saveBlockButton.addEventListener('click', () => {
            editor.runCommand('funnel-saved-blocks:save-selected');
        });
    }

    const insertTemplateButton = document.getElementById('btn-insert-template');
    if (insertTemplateButton) {
        insertTemplateButton.addEventListener('click', () => {
            editor.runCommand('funnel-template:insert', {
                templateId: 'hero-proof-cta',
            });
        });
    }

    const seoButton = document.getElementById('btn-seo');
    if (seoButton) {
        seoButton.addEventListener('click', () => {
            editor.runCommand('funnel-seo:open');
        });
    }

    fetch(`/landings/${window.editorData.landingId}/media`)
        .then((response) => (response.ok ? response.json() : []))
        .then((assets) => {
            if (Array.isArray(assets) && assets.length > 0) {
                editor.AssetManager.add(assets);
            }
        })
        .catch(() => {
            // Ignore asset loading errors in the editor boot flow.
        });

    const btnSave = document.getElementById('btn-save');
    if (btnSave) {
        btnSave.addEventListener('click', async () => {
            const originalText = btnSave.textContent;
            btnSave.disabled = true;
            btnSave.textContent = 'Saving...';
            setSavedStatus('Saving...');

            try {
                const serialized = pluginSystem.serialize();
                if (!serialized.validation.valid) {
                    const firstError = serialized.validation.errors[0] || 'Invalid funnel structure detected.';
                    throw new Error(firstError);
                }

                const seoMeta = editor.getModel().get('funnelSeoMeta') || {};
                const mergedCss = [
                    ...cssImportUrls.map((url) => `@import url('${url}');`),
                    editor.getCss(),
                ].filter(Boolean).join('\n');
                const response = await fetch(window.editorData.saveUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.editorData.csrfToken,
                    },
                    body: JSON.stringify({
                        grapesjs_json: JSON.stringify(serialized.projectData),
                        html: editor.getHtml(),
                        css: mergedCss,
                        js: hiddenJs?.textContent ?? '',
                        seo_meta: seoMeta,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Save failed');
                }

                if (window.Toast?.success) {
                    window.Toast.success('Saved successfully.');
                }

                localStorage.removeItem(autosaveSnapshotKey);
                setSavedStatus(`Saved at ${new Date().toLocaleTimeString()}`);
            } catch {
                if (window.Toast?.error) {
                    window.Toast.error('Failed to save.');
                }
                setSavedStatus('Save failed');
            } finally {
                btnSave.disabled = false;
                btnSave.textContent = originalText;
            }
        });
    }

    const btnPreview = document.getElementById('btn-preview');
    if (btnPreview && window.editorData.previewUrl) {
        btnPreview.addEventListener('click', () => {
            window.open(window.editorData.previewUrl, '_blank', 'noopener');
        });
    }

    setSavedStatus('Ready');
    window.editorInstance = editor;
    window.funnelPluginSystem = pluginSystem;
});
