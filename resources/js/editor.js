import grapesjs from 'grapesjs';
import 'grapesjs/dist/css/grapes.min.css';
import grapesjsTailwind from 'grapesjs-tailwind';
import grapesjsPresetWebpage from 'grapesjs-preset-webpage';
import grapesjsPluginForms from 'grapesjs-plugin-forms';
import SpacingTool from './grapesjs/plugins/spacing-tool';
import editorOverrides from './editor-overrides';
import landingParserPlugin from './grapesjs/landing-parser-plugin';

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
            appendTo: '#panel-blocks',
        },
        layerManager: {
            appendTo: '#panel-layers',
        },
        traitManager: {
            appendTo: '#panel-traits',
        },
        styleManager: {
            appendTo: '#panel-styles',
            sectors: [{
                name: 'General',
                open: false,
                buildProps: ['float', 'display']
            }, {
                name: 'Positioning & Layering',
                open: false,
                buildProps: ['position', 'z-index', 'top', 'right', 'bottom', 'left'],
                properties: [
                    {
                        name: 'Position',
                        property: 'position',
                        type: 'select',
                        defaults: 'static',
                        list: [
                            { value: 'static', name: 'Static' },
                            { value: 'relative', name: 'Relative' },
                            { value: 'absolute', name: 'Absolute' },
                            { value: 'fixed', name: 'Fixed' },
                            { value: 'sticky', name: 'Sticky' },
                        ],
                    },
                    {
                        name: 'Z-Index (Layer)',
                        property: 'z-index',
                        type: 'integer',
                        defaults: 0,
                    }
                ]
            }, {
                name: 'Dimension',
                open: false,
                buildProps: ['width', 'height', 'max-width', 'min-height', 'margin', 'padding']
            }, {
                name: 'Typography',
                open: false,
                buildProps: ['font-family', 'font-size', 'font-weight', 'letter-spacing', 'color', 'line-height', 'text-align', 'text-decoration', 'text-shadow']
            }, {
                name: 'Decorations',
                open: false,
                buildProps: ['background-color', 'border', 'border-radius', 'box-shadow']
            }, {
                name: 'Extra',
                open: false,
                buildProps: ['opacity', 'cursor', 'transition', 'perspective', 'transform']
            }],
        },
        // Disable default panels
        panels: { defaults: [] },

        canvas: {
            scripts: [],
            styles: [
                window.editorData.appCssUrl,
                'https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@300;400;600;700;900&display=swap'
            ]
        },
        plugins: [
            grapesjsTailwind,
            grapesjsPresetWebpage,
            grapesjsPluginForms,
            SpacingTool,
            editorOverrides,
            landingParserPlugin
        ],
        pluginsOpts: {
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
            [grapesjsPluginForms]: {
                // Add any specific options here if needed, for now default is fine
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
            [editorOverrides]: {}
        }
    });

    // Load initial content
    // FIX: Check if JSON is actually valid and has pages/styles, otherwise fall back to HTML
    const hiddenContainer = document.getElementById('gjs');
    const projectData = window.editorData.grapesJsJson;

    // Check if we have meaningful project data (at least 1 page with components, or styles)
    const hasProjectData = projectData &&
        (projectData.pages?.length > 0 || Object.keys(projectData).length > 2);

    if (hasProjectData) {
        editor.loadProjectData(projectData);
    } else if (hiddenContainer && hiddenContainer.innerHTML.trim().length > 0) {
        // Fallback to HTML if JSON is empty/invalid
        editor.setComponents(hiddenContainer.innerHTML);
    }

    // --- LOGIC: Clean up Default UI (Post-Init) ---
    editor.on('load', () => {
        // Force remove right sidebar panels if they exist
        const panelsToRemove = ['views', 'views-container', 'options', 'open-tm', 'open-layers', 'open-sm', 'open-blocks'];
        panelsToRemove.forEach(id => {
            try {
                editor.Panels.removePanel(id);
            } catch (e) { }
        });

        // Force resize
        editor.trigger('change:canvasOffset');

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

    editor.on('load', () => {
        // Remove default panels if they were added by presets
        const panels = editor.Panels;
        ['views-container', 'options', 'defaults'].forEach(id => {
            if (panels.getPanel(id)) panels.removePanel(id);
        });
    });

    // --- LOGIC: Sidebar Switching & Search ---

    const tabElements = document.getElementById('tab-elements');
    const tabGlobals = document.getElementById('tab-globals');

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
            tabElements.classList.remove('text-gray-500');
        }
    }

    function showSettings() {
        if (panelBlocks) panelBlocks.classList.add('hidden');
        if (searchBar) searchBar.classList.add('hidden');
        if (panelSettings) panelSettings.classList.remove('hidden');
        if (panelLayers) panelLayers.classList.add('hidden');

        if (tabElements) {
            tabElements.classList.remove('text-gray-200', 'border-b-2', 'border-indigo-500');
            tabElements.classList.add('text-gray-500');
        }
    }

    function showLayers() {
        if (panelBlocks) panelBlocks.classList.add('hidden');
        if (searchBar) searchBar.classList.add('hidden');
        if (panelSettings) panelSettings.classList.add('hidden');
        if (panelLayers) panelLayers.classList.remove('hidden');
    }

    // Event Listeners
    if (tabElements) tabElements.addEventListener('click', showBlocks);
    if (tabGlobals) tabGlobals.addEventListener('click', showLayers);

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
    spacingBtn.className = 'text-gray-400 hover:text-white p-1 mx-2 relative'; // Match Undo/Redo style more closely
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
            const components = editor.getComponents();
            const html = editor.getHtml();
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
                    body: JSON.stringify({
                        grapesjs_json: JSON.stringify(json),
                        html: html,
                        css: css
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

    // Ensure render
    setTimeout(() => {
        editor.refresh();
    }, 500);
});
