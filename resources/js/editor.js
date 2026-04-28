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
        editor.setStyle(hiddenCss?.textContent ?? '');
    }

    setBootStatus('Preparing canvas...');
    await waitForEditorLoad(editor, 9000);

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
                const response = await fetch(window.editorData.saveUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.editorData.csrfToken,
                    },
                    body: JSON.stringify({
                        grapesjs_json: JSON.stringify(serialized.projectData),
                        html: editor.getHtml(),
                        css: editor.getCss(),
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
