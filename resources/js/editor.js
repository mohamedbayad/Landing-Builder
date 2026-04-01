import grapesjs, { usePlugin } from 'grapesjs';
import grapesjsIcons from 'grapesjs-icons';
import 'iconify-icon';
import 'grapesjs/dist/css/grapes.min.css';
import grapesjsTailwind from 'grapesjs-tailwind';
import grapesjsPresetWebpage from 'grapesjs-preset-webpage';
// grapesjs-plugin-forms removed Ã¢â‚¬â€ editorOverrides.js defines all form component types
import SpacingTool from './grapesjs/plugins/spacing-tool';
import editorOverrides from './editor-overrides';
import landingParserPlugin from './grapesjs/landing-parser-plugin';
import countdownPlugin from './grapesjs/countdown-plugin';
import SidebarContentEditing from './grapesjs/plugins/sidebar-content-editing';
import CanvasInteractionControl from './grapesjs/plugins/canvas-interaction-control';
import CustomComponents from './grapesjs/plugins/custom-components';
import keyboardShortcutsPlugin from './grapesjs/plugins/keyboard-shortcuts';
import contextMenuPlugin from './grapesjs/plugins/context-menu';
import conversionBlocksPlugin from './grapesjs/plugins/conversion-blocks';
import aiAssistantPlugin from './grapesjs/plugins/ai-assistant';
import exitIntentPlugin from './grapesjs/plugins/exit-intent';
import deviceVisibilityPlugin from './grapesjs/plugins/device-visibility';
import advancedEditingControlsPlugin from './grapesjs/plugins/advanced-editing-controls';
import editorAnimationSafeModePlugin from './grapesjs/plugins/editor-animation-safe-mode';
import lpSliderPlugin from './grapesjs/plugins/lp-slider';

document.addEventListener('DOMContentLoaded', () => {

    // Ensure the container exists before init (Debug check)
    if (!document.getElementById('gjs-canvas-frame')) {
        console.error('GrapesJS Container #gjs-canvas-frame not found!');
        return;
    }

    const editor = grapesjs.init({
        container: '#gjs-canvas-frame',
        height: '100%',
        width: '100%', // Make sure canvas takes full width
        fromElement: false, // We load manually
        storageManager: false,
        assetManager: {
            upload: `/landings/${window.editorData.landingId}/media`, // Use relative path or full URL
            uploadName: 'files', // Controller expects 'files'
            multiUpload: true,
            autoAdd: true,
            headers: {
                'X-CSRF-TOKEN': window.editorData.csrfToken
            }
        },
        selectorManager: { componentFirst: true },
        deviceManager: {
            devices: [
                {
                    name: 'Desktop',
                    width: '', // Default
                },
                {
                    name: 'Tablet',
                    width: '768px',
                    widthMedia: '768px', // CSS media query condition
                },
                {
                    name: 'Mobile Portrait',
                    width: '320px',
                    widthMedia: '480px', // CSS media query condition
                },
            ]
        },

        // Define where standard panels go
        blockManager: {
            appendTo: '#blocks-container',
        },
        layerManager: {
            appendTo: '#layers-container',
        },
        traitManager: {
            appendTo: '#panel-traits',
        },
        styleManager: {
            appendTo: '#panel-styles',
            sectors: [{
                name: 'Layout',
                open: false,
                buildProps: ['display', 'float', 'overflow'],
                properties: [
                    { name: 'Display', property: 'display', type: 'select', defaults: 'block',
                      list: [{value:'block',name:'Block'},{value:'inline-block',name:'Inline Block'},{value:'inline',name:'Inline'},{value:'flex',name:'Flex'},{value:'grid',name:'Grid'},{value:'none',name:'None'},{value:'inline-flex',name:'Inline Flex'}] },
                    { name: 'Overflow', property: 'overflow', type: 'select', defaults: 'visible',
                      list: [{value:'visible',name:'Visible'},{value:'hidden',name:'Hidden'},{value:'auto',name:'Auto'},{value:'scroll',name:'Scroll'}] },
                ]
            }, {
                name: 'Flexbox',
                open: false,
                buildProps: ['flex-direction', 'flex-wrap', 'justify-content', 'align-items', 'gap', 'align-self', 'flex-grow', 'flex-shrink'],
                properties: [
                    { name: 'Direction', property: 'flex-direction', type: 'select', defaults: 'row',
                      list: [{value:'row',name:'Row'},{value:'row-reverse',name:'Row Reverse'},{value:'column',name:'Column'},{value:'column-reverse',name:'Column Reverse'}] },
                    { name: 'Wrap', property: 'flex-wrap', type: 'select', defaults: 'nowrap',
                      list: [{value:'nowrap',name:'No Wrap'},{value:'wrap',name:'Wrap'},{value:'wrap-reverse',name:'Wrap Reverse'}] },
                    { name: 'Justify', property: 'justify-content', type: 'select', defaults: 'flex-start',
                      list: [{value:'flex-start',name:'Start'},{value:'flex-end',name:'End'},{value:'center',name:'Center'},{value:'space-between',name:'Space Between'},{value:'space-around',name:'Space Around'},{value:'space-evenly',name:'Space Evenly'}] },
                    { name: 'Align Items', property: 'align-items', type: 'select', defaults: 'stretch',
                      list: [{value:'stretch',name:'Stretch'},{value:'flex-start',name:'Start'},{value:'flex-end',name:'End'},{value:'center',name:'Center'},{value:'baseline',name:'Baseline'}] },
                    { name: 'Gap', property: 'gap', type: 'text', defaults: '0' },
                    { name: 'Align Self', property: 'align-self', type: 'select', defaults: 'auto',
                      list: [{value:'auto',name:'Auto'},{value:'stretch',name:'Stretch'},{value:'flex-start',name:'Start'},{value:'flex-end',name:'End'},{value:'center',name:'Center'}] },
                ]
            }, {
                name: 'Position',
                open: false,
                buildProps: ['position', 'z-index', 'top', 'right', 'bottom', 'left'],
                properties: [
                    { name: 'Position', property: 'position', type: 'select', defaults: 'static',
                      list: [{value:'static',name:'Static'},{value:'relative',name:'Relative'},{value:'absolute',name:'Absolute'},{value:'fixed',name:'Fixed'},{value:'sticky',name:'Sticky'}] },
                    { name: 'Z-Index', property: 'z-index', type: 'integer', defaults: 0 }
                ]
            }, {
                name: 'Dimension',
                open: false,
                buildProps: ['width', 'height', 'max-width', 'min-width', 'max-height', 'min-height', 'margin', 'padding'],
                properties: [
                    { name: 'Aspect Ratio', property: 'aspect-ratio', type: 'text', defaults: 'auto' }
                ]
            }, {
                name: 'Typography',
                open: false,
                buildProps: ['font-family', 'font-size', 'font-weight', 'letter-spacing', 'color', 'line-height', 'text-align', 'text-decoration', 'text-shadow'],
                properties: [
                    { name: 'Text Transform', property: 'text-transform', type: 'select', defaults: 'none',
                      list: [{value:'none',name:'None'},{value:'uppercase',name:'UPPERCASE'},{value:'lowercase',name:'lowercase'},{value:'capitalize',name:'Capitalize'}] },
                    { name: 'White Space', property: 'white-space', type: 'select', defaults: 'normal',
                      list: [{value:'normal',name:'Normal'},{value:'nowrap',name:'No Wrap'},{value:'pre',name:'Pre'},{value:'pre-wrap',name:'Pre Wrap'}] },
                ]
            }, {
                name: 'Background',
                open: false,
                buildProps: ['background-color', 'background-image', 'background-repeat', 'background-position', 'background-size'],
                properties: [
                    { name: 'Bg Size', property: 'background-size', type: 'select', defaults: 'auto',
                      list: [{value:'auto',name:'Auto'},{value:'cover',name:'Cover'},{value:'contain',name:'Contain'}] },
                    { name: 'Bg Position', property: 'background-position', type: 'select', defaults: 'center center',
                      list: [{value:'center center',name:'Center'},{value:'top center',name:'Top'},{value:'bottom center',name:'Bottom'},{value:'left center',name:'Left'},{value:'right center',name:'Right'}] },
                    { name: 'Bg Repeat', property: 'background-repeat', type: 'select', defaults: 'repeat',
                      list: [{value:'repeat',name:'Repeat'},{value:'no-repeat',name:'No Repeat'},{value:'repeat-x',name:'Repeat X'},{value:'repeat-y',name:'Repeat Y'}] },
                ]
            }, {
                name: 'Borders & Shadows',
                open: false,
                buildProps: ['border', 'border-radius', 'box-shadow', 'outline']
            }, {
                name: 'Effects',
                open: false,
                buildProps: ['opacity', 'cursor', 'transition', 'transform'],
                properties: [
                    { name: 'Filter', property: 'filter', type: 'text', defaults: 'none' },
                    { name: 'Backdrop Filter', property: 'backdrop-filter', type: 'text', defaults: 'none' },
                    { name: 'Mix Blend', property: 'mix-blend-mode', type: 'select', defaults: 'normal',
                      list: [{value:'normal',name:'Normal'},{value:'multiply',name:'Multiply'},{value:'screen',name:'Screen'},{value:'overlay',name:'Overlay'},{value:'darken',name:'Darken'},{value:'lighten',name:'Lighten'}] },
                ]
            }],
        },
        // Disable default panels
        panels: { defaults: [] },

        canvas: {
            scripts: [
                'https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js'
            ],
            styles: [
                window.editorData.appCssUrl,
                'https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@300;400;600;700;900&display=swap'
            ]
        },
        plugins: [
            grapesjsTailwind,
            // grapesjsPluginForms removed Ã¢â‚¬â€ editorOverrides handles form/input/button/textarea
            grapesjsPresetWebpage,

            SpacingTool,
            editorOverrides,
            landingParserPlugin,
            countdownPlugin,
            SidebarContentEditing,
            CustomComponents,
            CanvasInteractionControl,
            keyboardShortcutsPlugin,
            contextMenuPlugin,
            conversionBlocksPlugin,
            aiAssistantPlugin,
            exitIntentPlugin,
            deviceVisibilityPlugin,
            advancedEditingControlsPlugin,
            editorAnimationSafeModePlugin,
            lpSliderPlugin,
            (editor, opts) => {
                console.log('--- GRAPESJS-ICONS PLUGIN INITIALIZATION ---(INTERCEPTED)');
                console.log('Received options:', opts);
                // Call the original plugin
                grapesjsIcons(editor, opts);
            }
        ],
        pluginsOpts: {
            // Provide BOTH strings to see which one it picks up
            [grapesjsIcons]: { collections: ['ri', 'mdi', 'uim', 'streamline-emojis'] },
            'grapesjs-icons': { collections: ['ri', 'mdi', 'uim', 'streamline-emojis'] },
            mo: { collections: ['ri', 'mdi', 'uim', 'streamline-emojis'] },
            [grapesjsTailwind]: {},
            [grapesjsPresetWebpage]: {
                modalImportTitle: 'Import Template',
                modalImportLabel: '<div style="margin-bottom: 10px; font-size: 13px;">Paste your HTML/CSS here</div>',
                modalImportContent: '',
                // Disable default UI that conflicts with our custom UI
                navbarOpts: false,
                countdownOpts: false,
                formsOpts: false,
                blocksBasicOpts: { flexGrid: true },
            },
            [SpacingTool]: {
                panelId: 'panel-options', // Assuming this is where we want it, or maybe a dedicated toolbar
                // Note: panel-options might be hidden by our custom logic. 
                // Let's specify a panel that exists or creates the button via Command if panelId is invalid.
                // Re-reading spacingTool.js, it uses editor.Panels.addButton.
                // In our editor.js we destroyed default panels. We should check if 'panel-options' exists.
                // Actually, line 121 removes 'options'.
                // So we might need to recreate a panel or rely on a new one.
                // Let's rely on the Command being registered and maybe manually adding a button to our DOM if need be.
                // But for now, let's pass a dummy 'options' panel hoping it gets created or we find a way.
                // Wait, we have 'panel-settings' in the DOM. Maybe we can attach it there?
                // But editor.Panels manages internal GrapesJS panels, not the DOM elements directly unless they are mounted.
                // Let's stick to registering it and later I might need to manually append a button if Panels are disabled.
            },
            [editorOverrides]: {},
            [countdownPlugin]: {},
            [SidebarContentEditing]: {},
            [CustomComponents]: {},
            [CanvasInteractionControl]: {},
            [editorAnimationSafeModePlugin]: {},
            [lpSliderPlugin]: {},
        }
    });

    const hiddenCss = document.getElementById('gjs-css');

    // Load initial content
    // FIX: Check if JSON is actually valid and has pages/styles, otherwise fall back to HTML
    const hiddenContainer = document.getElementById('gjs');
    const projectData = window.editorData.grapesJsJson;

    // Check if we have meaningful project data (at least 1 page with components, or styles)
    const hasProjectData = !window.editorData.forceHtmlMode && projectData &&
        (projectData.pages?.length > 0 || Object.keys(projectData).length > 2);

    if (hasProjectData) {
        editor.loadProjectData(projectData);
    } else if (hiddenContainer && hiddenContainer.innerHTML.trim().length > 0) {
        // Fallback to HTML if JSON is empty/invalid
        editor.setComponents(hiddenContainer.innerHTML);

        // Fallback to CSS
        if (hiddenCss && hiddenCss.innerHTML.trim().length > 0) {
            editor.setStyle(hiddenCss.innerHTML);
        }
    }

    // Safety net: if project JSON exists but contains no styles, use saved CSS.
    const applyCssFallback = () => {
        if (!hiddenCss || hiddenCss.innerHTML.trim().length === 0) return;
        const currentCss = (editor.getCss() || '').trim();
        if (!currentCss) {
            editor.setStyle(hiddenCss.innerHTML);
            console.log('[GrapesJS] Applied CSS fallback from #gjs-css');
        }
    };

    // --- LOGIC: Clean up Default UI (Post-Init) ---
    editor.on('load', () => {
        applyCssFallback();

        // Force remove right sidebar panels if they exist
        const panelsToRemove = ['views', 'views-container', 'options', 'open-tm', 'open-layers', 'open-sm', 'open-blocks'];
        panelsToRemove.forEach(id => {
            try {
                editor.Panels.removePanel(id);
            } catch (e) { }
        });

        // Force resize
        editor.trigger('change:canvasOffset');

        const runInjectedTemplateScripts = async (scripts, targetParent) => {
            const frameDoc = editor.Canvas.getDocument();
            const scriptList = Array.isArray(scripts)
                ? scripts
                : (scripts ? Array.from(scripts) : []);

            if (scriptList.length === 0) {
                return;
            }

            for (const origScript of scriptList) {
                if (!origScript || typeof origScript.getAttribute !== 'function') {
                    continue;
                }

                const script = frameDoc.createElement('script');
                const attrs = origScript.attributes ? Array.from(origScript.attributes) : [];
                attrs.forEach(attr => {
                    script.setAttribute(attr.name, attr.value);
                });

                if (origScript.textContent) {
                    script.textContent = origScript.textContent;
                }

                const type = (script.getAttribute('type') || '').toLowerCase();
                const src = script.getAttribute('src') || '';

                // Editor-safe mode for remote templates: skip heavy module scripts
                // (ThreeJS/ScrollTrigger scenes) that can hide/pin full sections in iframe.
                if (window.editorData.disableModuleScripts && type === 'module') {
                    console.log('[GrapesJS] Skipped module script in editor:', src || '[inline module]');
                    continue;
                }

                const parent = type === 'importmap' ? frameDoc.head : targetParent;

                await new Promise(resolve => {
                    script.onload = () => resolve();
                    script.onerror = () => resolve();
                    parent.appendChild(script);

                    // Inline scripts (and importmaps) do not fire onload consistently.
                    if (!script.src || type === 'importmap') {
                        resolve();
                    }
                });
            }
        };

        const canvasDoc = editor.Canvas.getDocument();
        const canvasHead = canvasDoc.head;
        const canvasBody = canvasDoc.body;
        canvasDoc.documentElement.setAttribute('data-gjs-editor-canvas', 'true');
        if (canvasDoc.defaultView) {
            canvasDoc.defaultView.__GJS_EDITOR_MODE = true;
        }

        const injectEditorSolutionFallbackCss = () => {
            if (canvasDoc.getElementById('editor-solution-fallback-css')) {
                return;
            }

            const gsapSectionSelector = '[data-gsap-section], #solution';
            const gsapItemSelector = '[data-gsap-item], .slide-solution';
            const style = canvasDoc.createElement('style');
            style.id = 'editor-solution-fallback-css';
            style.textContent = `
                :is(${gsapSectionSelector}) {
                    position: relative !important;
                    min-height: 100vh !important;
                    height: auto !important;
                    max-height: none !important;
                    overflow: visible !important;
                }
                :is(${gsapSectionSelector}) .ui-layer {
                    position: relative !important;
                    inset: auto !important;
                    height: auto !important;
                    max-height: none !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: flex-start !important;
                    gap: 16px !important;
                    pointer-events: auto !important;
                }
                :is(${gsapSectionSelector}) .ui-layer > div[class*="max-w-2xl"] {
                    position: relative !important;
                    height: auto !important;
                    min-height: 0 !important;
                }
                :is(${gsapSectionSelector}) :is(${gsapItemSelector}) {
                    position: relative !important;
                    inset: auto !important;
                    display: flex !important;
                    opacity: 1 !important;
                    visibility: visible !important;
                    transform: none !important;
                    filter: none !important;
                    pointer-events: auto !important;
                }
                :is(${gsapSectionSelector}) :is(${gsapItemSelector}) + :is(${gsapItemSelector}) {
                    margin-top: 16px !important;
                }
            `;
            canvasHead.appendChild(style);
        };

        // INJECT CUSTOM HEAD (Template styles/links). Script tags are extracted
        // and executed via DOM APIs (insertAdjacentHTML does not execute scripts).
        let templateHeadScripts = [];
        if (window.editorData.customHead) {
            // Inject CSP Meta tag to allow remote folder-based importmaps/scripts.
            const cspMeta = canvasDoc.createElement('meta');
            cspMeta.setAttribute('http-equiv', 'Content-Security-Policy');
            cspMeta.setAttribute('content', "default-src 'self' data: blob: https: http:; script-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: https: http:; style-src 'self' 'unsafe-inline' https: http:; img-src 'self' data: blob: https: http:; font-src 'self' data: https: http:; connect-src 'self' https: http: ws: wss:;");
            canvasHead.appendChild(cspMeta);

            const tempHead = document.createElement('div');
            tempHead.innerHTML = window.editorData.customHead;
            templateHeadScripts = Array.from(tempHead.querySelectorAll('script'));
            templateHeadScripts.forEach(node => node.remove());

            canvasHead.insertAdjacentHTML('beforeend', tempHead.innerHTML);
            console.log('Injected custom head styles into canvas with CSP');
        }

        // INJECT BODY/EXTRACTED SCRIPTS (Template JS - animations, interactions, etc.)
        const jsContainer = document.getElementById('gjs-js');
        let bodyScripts = [];

        if (jsContainer && jsContainer.textContent.trim().length > 0) {
            // Content was HTML-escaped via e() in blade, decode it
            const rawJs = jsContainer.textContent;

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = rawJs;
            bodyScripts = Array.from(tempDiv.querySelectorAll('script'));

            // Backward compatibility: if JS was saved as raw code (without <script> wrappers)
            if (bodyScripts.length === 0 && rawJs.trim().length > 0) {
                const fallbackScript = document.createElement('script');
                fallbackScript.textContent = rawJs;
                bodyScripts = [fallbackScript];
            }
        }

        
        runInjectedTemplateScripts(templateHeadScripts, canvasHead)
            .then(() => runInjectedTemplateScripts(bodyScripts, canvasBody))
            .then(() => {
            // Many imported templates register logic on DOMContentLoaded.
            // Scripts are injected after frame load, so fire synthetic events.
            injectEditorSolutionFallbackCss();
            canvasDoc.dispatchEvent(new Event('DOMContentLoaded', { bubbles: true }));
            canvasDoc.defaultView?.dispatchEvent(new Event('load'));

            // Fallback: force-reveal blocks that depend on JS-added .active class.
            canvasDoc.querySelectorAll('.reveal').forEach(el => el.classList.add('active'));
const totalScripts = templateHeadScripts.length + bodyScripts.length;
            if (totalScripts > 0) {
                console.log('Injected', totalScripts, 'scripts into canvas');
            }
        });
        // NOTE: Editor reveal CSS is now consolidated in custom-components.js plugin
        // (Section 3 Ã¢â‚¬â€ EDITOR-ONLY REVEAL CSS). No duplicate injection here.

        // FORCE IMAGE DOUBLE-CLICK -> OPEN ASSETS
        const body = editor.Canvas.getBody();
        if (body) {
            body.addEventListener('dblclick', (e) => {
                const el = e.target;
                if (el && (el.tagName === 'IMG' || el.getAttribute('type') === 'image')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const component = editor.getSelected();
                    if (component) {
                        editor.runCommand('open-assets', {
                            target: component,
                            types: ['image'],
                            accept: 'image/*',
                            onSelect: () => editor.Modal.close()
                        });
                    }
                }
            });
            console.log('Global Image Double-Click Listener Attached');
        }

        // AUTO-PARSE BLOCKS from Content (For Uploaded Templates)
        // If we have content but no blocks in logic, try to parse.
        // We just run it always, it handles idempotency reasonably well or we accept duplicates in "Custom" if re-run.
        // To avoid excessive duplicates on re-loads if JSON is saved, maybe check if we have blocks?
        // But BlockManager is client-side only usually.
        setTimeout(() => {
            const html = editor.getHtml();
            if (html) {
                console.log('Auto-parsing HTML to blocks...');
                editor.runCommand('landing-page:parse', { html });
            }
        }, 1000);
    });

    // Asset loading merged into the main 'load' handler above.
    // Panel cleanup is also handled in the main 'load' handler (L227-232).
    editor.on('load', async () => {
        // Load media assets from server
        try {
            const response = await fetch(`/landings/${window.editorData.landingId}/media`);
            const assets = await response.json();
            editor.AssetManager.add(assets);
        } catch (error) {
            console.error('Failed to load assets', error);
        }
    });

    // --- LOGIC: Sidebar Switching & Search ---

    const tabElements = document.getElementById('tab-elements');
    const tabGlobals = document.getElementById('tab-globals');
    // Create Media Tab if not exists in DOM, or inject it
    // The user wants a visible sidebar button/tab named "Media".
    // We can inject it into the nav where 'tab-elements' and 'tab-globals' are.

    // Inject Media Tab
    const navContainer = tabElements?.parentNode;
    let tabMedia = document.getElementById('tab-media');

    if (navContainer && !tabMedia) {
        tabMedia = document.createElement('button');
        tabMedia.id = 'tab-media';
        tabMedia.className = 'flex-1 py-4 px-1 text-center border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition-colors duration-150';
        tabMedia.innerHTML = '<span class="text-sm font-medium">Media</span>';
        navContainer.appendChild(tabMedia);
    }

    const panelBlocks = document.getElementById('panel-blocks');
    const panelSettings = document.getElementById('panel-settings');
    const panelLayers = document.getElementById('panel-layers');
    const searchBar = document.getElementById('sidebar-search');

    function showBlocks() {
        if (panelBlocks) panelBlocks.classList.remove('hidden');
        if (searchBar) searchBar.classList.remove('hidden');
        if (panelSettings) panelSettings.classList.add('hidden');
        if (panelLayers) panelLayers.classList.add('hidden');

        if (tabElements) {
            tabElements.classList.add('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabElements.classList.remove('text-gray-500', 'border-transparent');
        }
        if (tabGlobals) {
            tabGlobals.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabGlobals.classList.add('text-gray-500', 'border-transparent');
        }
        if (tabMedia) {
            tabMedia.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabMedia.classList.add('text-gray-500', 'border-transparent');
        }
    }

    function showSettings() {
        if (panelBlocks) panelBlocks.classList.add('hidden');
        if (searchBar) searchBar.classList.add('hidden');
        if (panelSettings) panelSettings.classList.remove('hidden');
        if (panelLayers) panelLayers.classList.add('hidden');

        if (tabElements) {
            tabElements.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabElements.classList.add('text-gray-500', 'border-transparent');
        }
        if (tabGlobals) {
            tabGlobals.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabGlobals.classList.add('text-gray-500', 'border-transparent');
        }
        if (tabMedia) {
            tabMedia.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabMedia.classList.add('text-gray-500', 'border-transparent');
        }
    }

    function showLayers() {
        if (panelBlocks) panelBlocks.classList.add('hidden');
        if (searchBar) searchBar.classList.add('hidden');
        if (panelSettings) panelSettings.classList.add('hidden');
        if (panelLayers) panelLayers.classList.remove('hidden');

        if (tabElements) {
            tabElements.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabElements.classList.add('text-gray-500', 'border-transparent');
        }
        if (tabGlobals) {
            tabGlobals.classList.add('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabGlobals.classList.remove('text-gray-500', 'border-transparent');
        }
        if (tabMedia) {
            tabMedia.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabMedia.classList.add('text-gray-500', 'border-transparent');
        }
    }

    function openMediaLibrary() {
        // Trigger GrapesJS Asset Manager
        editor.runCommand('open-assets');

        // Visual feedback for tab selection
        if (tabElements) {
            tabElements.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabElements.classList.add('text-gray-500', 'border-transparent');
        }
        if (tabGlobals) {
            tabGlobals.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabGlobals.classList.add('text-gray-500', 'border-transparent');
        }
        if (tabMedia) {
            tabMedia.classList.add('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabMedia.classList.remove('text-gray-500', 'border-transparent');
        }
    }

    // Event Listeners
    if (tabElements) tabElements.addEventListener('click', showBlocks);
    if (tabGlobals) tabGlobals.addEventListener('click', showLayers);
    if (tabMedia) tabMedia.addEventListener('click', openMediaLibrary);

    // Auto-switch on selection
    editor.on('component:selected', () => {
        showSettings();
    });

    // --- LOGIC: Block Search ---
    const searchInput = document.getElementById('block-search');
    if (searchInput) {
        searchInput.addEventListener('keyup', (e) => {
            const term = e.target.value.toLowerCase();
            const categories = document.querySelectorAll('.gjs-block-category');

            // If no categories found (custom rendering?), try direct blocks
            if (categories.length === 0) {
                const blocks = document.querySelectorAll('.gjs-block');
                blocks.forEach(el => {
                    const title = (el.getAttribute('title') || el.textContent).toLowerCase();
                    el.style.display = title.includes(term) ? 'flex' : 'none';
                });
                return;
            }

            // Filter Blocks within Categories
            categories.forEach(category => {
                const blocks = category.querySelectorAll('.gjs-block');
                let hasVisibleBlock = false;

                blocks.forEach(el => {
                    const title = (el.getAttribute('title') || el.textContent).toLowerCase();
                    const match = title.includes(term);

                    if (match) {
                        el.style.display = 'flex';
                        hasVisibleBlock = true;
                    } else {
                        el.style.display = 'none';
                    }
                });

                // Toggle Category Visibility
                if (hasVisibleBlock) {
                    category.style.display = 'block';
                    category.classList.add('gjs-open'); // Auto-expand category
                    const blocksCont = category.querySelector('.gjs-blocks-c');
                    if (blocksCont) blocksCont.style.display = 'block'; // Ensure container is visible
                } else {
                    category.style.display = 'none';
                    category.classList.remove('gjs-open');
                }
            });
        });
    }


    // --- LOGIC: Bottom Bar Actions ---

    // Device Switcher
    const deviceButtons = {
        'desktop': document.getElementById('btn-device-desktop'),
        'tablet': document.getElementById('btn-device-tablet'),
        'mobile': document.getElementById('btn-device-mobile')
    };

    function setActiveDevice(device) {
        editor.setDevice(device === 'desktop' ? 'Desktop' : (device === 'tablet' ? 'Tablet' : 'Mobile Portrait'));

        Object.keys(deviceButtons).forEach(key => {
            if (!deviceButtons[key]) return;
            if (key === device) {
                deviceButtons[key].classList.add('text-white', 'bg-gray-700');
                deviceButtons[key].classList.remove('text-gray-400');
            } else {
                deviceButtons[key].classList.remove('text-white', 'bg-gray-700');
                deviceButtons[key].classList.add('text-gray-400');
            }
        });
    }

    if (deviceButtons.desktop) deviceButtons.desktop.addEventListener('click', () => setActiveDevice('desktop'));
    if (deviceButtons.tablet) deviceButtons.tablet.addEventListener('click', () => setActiveDevice('tablet'));
    if (deviceButtons.mobile) deviceButtons.mobile.addEventListener('click', () => setActiveDevice('mobile'));

    // --- LOGIC: Custom Spacing Tool Toggle ---

    // Create the button
    const spacingBtn = document.createElement('button');
    spacingBtn.className = 'text-gray-400 hover:text-white p-1 mx-2 relative transition-colors'; // Match Undo/Redo style more closely
    spacingBtn.title = 'Spacing Tool';
    spacingBtn.id = 'btn-spacing-tool';
    spacingBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
        </svg>
    `;

    // Insert it before the Undo button (or somewhere visible)
    const btnUndo = document.getElementById('btn-undo');
    if (btnUndo) {
        console.log('Injecting Spacing Button before Undo');
        btnUndo.parentNode.insertBefore(spacingBtn, btnUndo);

        let spacingActive = false;
        const toggleSpacing = () => {
            if (spacingActive) {
                console.log('Disabling Spacing Tool');
                editor.stopCommand('spacing-tool:toggle');
                spacingBtn.classList.remove('text-indigo-400');
                spacingBtn.classList.add('text-gray-400');
                spacingActive = false;
            } else {
                console.log('Enabling Spacing Tool');
                editor.runCommand('spacing-tool:toggle');
                spacingBtn.classList.remove('text-gray-400');
                spacingBtn.classList.add('text-indigo-400');
                spacingActive = true;
            }
        };
        spacingBtn.addEventListener('click', toggleSpacing);
    } else {
        console.error('Could not find #btn-undo to inject Spacing Button');
    }

    if (btnUndo) btnUndo.addEventListener('click', () => editor.runCommand('core:undo'));

    const btnRedo = document.getElementById('btn-redo');
    if (btnRedo) btnRedo.addEventListener('click', () => editor.runCommand('core:redo'));

    const btnPreview = document.getElementById('btn-preview');
    if (btnPreview) btnPreview.addEventListener('click', () => editor.runCommand('core:preview'));

    const btnSave = document.getElementById('btn-save');
    if (btnSave) {
        
        
        
        
        btnSave.addEventListener('click', async () => {
            const htmlRaw = editor.getHtml();
            const html = editor.runCommand('animation-safe:prepare-html-export', { html: htmlRaw }) || htmlRaw;
            const css = editor.getCss();
            const json = editor.getProjectData();

            try {
                const originalText = btnSave.innerText;
                btnSave.innerText = 'SAVING...';
                btnSave.disabled = true;

                const response = await fetch(window.editorData.saveUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.editorData.csrfToken
                    },
                    // Keep extracted/body scripts persistent across editor saves.
                    // Scripts are injected from #gjs-js and are not edited by GrapesJS.
                    body: JSON.stringify({
                        grapesjs_json: JSON.stringify(json),
                        html: html,
                        css: css,
                        js: document.getElementById('gjs-js')?.textContent || ''
                    })
                });

                if (response.ok) {
                    window.Toast ? window.Toast.success('Saved successfully!') : console.log('Saved');
                } else {
                    window.Toast ? window.Toast.error('Failed to save.') : console.error('Failed to save');
                }

                btnSave.innerText = originalText;
                btnSave.disabled = false;

            } catch (error) {
                console.error(error);
                window.Toast ? window.Toast.error('An error occurred.') : console.error('Error');
            }
        });
    }

    
    // Auto-switch Sidebar on Element Selection
    editor.on('component:selected', (model) => {
        const editTab = document.querySelector('#rail-tab-edit');
        if (editTab && !editTab.classList.contains('active')) {
            editTab.click();
        }
        
        const nameEl = document.getElementById('selected-element-name');
        const breadEl = document.getElementById('selected-element-breadcrumbs');
        
        if (nameEl && model) {
            nameEl.textContent = model.getName() || model.get('type') || 'Element';
            
            const parents = [];
            let current = model.parent();
            while(current && current.get('type') !== 'wrapper') {
                parents.unshift(current.getName() || current.get('type'));
                current = current.parent();
            }
            if (parents.length === 0) parents.push('Body');
            
            if (breadEl) {
                breadEl.innerHTML = '<span>' + parents[0] + '</span>';
                if (parents.length > 1) {
                    breadEl.innerHTML += '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 4px; display: inline-block"><polyline points="9 18 15 12 9 6"></polyline></svg><span>...</span>';
                }
            }
        }
    });

    editor.on('component:deselected', () => {
        const nameEl = document.getElementById('selected-element-name');
        if (nameEl) nameEl.textContent = 'Select an element';
        const breadEl = document.getElementById('selected-element-breadcrumbs');
        if (breadEl) breadEl.innerHTML = '<span>Body</span>';
    });

    // Ensure render
    setTimeout(() => {
        editor.refresh();
    }, 500);
});
