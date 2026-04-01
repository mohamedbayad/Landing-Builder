/**
 * Plugin: Advanced Editing Controls
 *
 * Fills all missing element editing gaps:
 *   1. HTML Tag Changer — convert tags while preserving content/classes/attributes
 *   2. Attributes Manager — add/edit/remove ANY attribute (data-*, aria-*, custom)
 *   3. Semantic / SEO Controls — heading hierarchy, roles, aria labels
 *
 * Renders as a collapsible "Advanced" section in the settings panel.
 */
export default function advancedEditingControlsPlugin(editor, opts = {}) {

    // ═══════════════════════════════════════════════════════════════
    //  1. HTML TAG CHANGER
    // ═══════════════════════════════════════════════════════════════

    const TAG_GROUPS = {
        'Headings': ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        'Text': ['p', 'span', 'blockquote', 'pre', 'label'],
        'Structure': ['div', 'section', 'article', 'aside', 'header', 'footer', 'nav', 'main'],
        'Interactive': ['a', 'button'],
        'Lists': ['ul', 'ol', 'li'],
        'Media': ['figure', 'figcaption'],
    };

    // Flat list for validation
    const ALL_ALLOWED_TAGS = Object.values(TAG_GROUPS).flat();

    // Some conversions are invalid
    const INVALID_CONVERSIONS = {
        'img': true,      // Can't change img to anything (self-closing)
        'input': true,
        'br': true,
        'hr': true,
        'video': true,
        'audio': true,
        'iframe': true,
    };

    function changeTag(component, newTag) {
        if (!component || !newTag) return;
        newTag = newTag.toLowerCase();
        const oldTag = (component.get('tagName') || 'div').toLowerCase();

        if (oldTag === newTag) return;
        if (INVALID_CONVERSIONS[oldTag]) return;
        if (!ALL_ALLOWED_TAGS.includes(newTag)) return;

        // Preserve everything: content, classes, attributes, styles, children
        const attrs = { ...component.getAttributes() };
        const classes = component.getClasses();
        const innerHtml = component.toHTML();

        // Change the tag
        component.set('tagName', newTag);

        // Re-apply classes (tag change can reset them)
        if (classes.length > 0) {
            component.setClass(classes);
        }

        // Update the component name for the layers panel
        const tagNames = {
            'h1': 'Heading 1', 'h2': 'Heading 2', 'h3': 'Heading 3',
            'h4': 'Heading 4', 'h5': 'Heading 5', 'h6': 'Heading 6',
            'p': 'Paragraph', 'section': 'Section', 'article': 'Article',
            'aside': 'Aside', 'header': 'Header', 'footer': 'Footer',
            'nav': 'Navigation', 'main': 'Main', 'button': 'Button',
            'a': 'Link', 'span': 'Span', 'div': 'Box',
            'blockquote': 'Blockquote', 'figure': 'Figure',
            'figcaption': 'Caption', 'ul': 'Unordered List',
            'ol': 'Ordered List', 'li': 'List Item',
        };
        component.set('custom-name', tagNames[newTag] || newTag.toUpperCase());

        console.log(`[AdvancedEdit] Tag changed: <${oldTag}> → <${newTag}>`);
    }

    // ═══════════════════════════════════════════════════════════════
    //  2. ATTRIBUTES MANAGER
    // ═══════════════════════════════════════════════════════════════

    function getCustomAttributes(component) {
        if (!component) return {};
        const attrs = component.getAttributes();
        // Filter out GrapesJS internal attributes
        const internal = new Set(['id', 'class', 'style', 'data-gjs-type', 'draggable']);
        const result = {};
        for (const [key, val] of Object.entries(attrs)) {
            if (!key.startsWith('data-gjs') && !internal.has(key)) {
                result[key] = val;
            }
        }
        return result;
    }

    function setAttribute(component, name, value) {
        if (!component || !name) return;
        name = name.trim().toLowerCase();

        // Validate name
        if (!/^[a-z][a-z0-9_-]*$/i.test(name) && !name.startsWith('data-') && !name.startsWith('aria-')) {
            console.warn(`[AdvancedEdit] Invalid attribute name: ${name}`);
            return;
        }

        component.addAttributes({ [name]: value });
    }

    function removeAttribute(component, name) {
        if (!component || !name) return;
        component.removeAttributes(name);
    }

    // ═══════════════════════════════════════════════════════════════
    //  3. ADVANCED PANEL UI
    // ═══════════════════════════════════════════════════════════════

    let advancedPanelEl = null;

    function createAdvancedPanel() {
        if (advancedPanelEl) return advancedPanelEl;

        advancedPanelEl = document.createElement('div');
        advancedPanelEl.id = 'advanced-editing-panel';
        advancedPanelEl.innerHTML = '';

        return advancedPanelEl;
    }

    function renderAdvancedPanel(component) {
        const panel = createAdvancedPanel();
        if (!component) {
            panel.innerHTML = '<div style="padding: 16px; color: #777; font-size: 13px;">Select an element to see advanced controls.</div>';
            return;
        }

        const currentTag = (component.get('tagName') || 'div').toLowerCase();
        const isChangeable = !INVALID_CONVERSIONS[currentTag];
        const customAttrs = getCustomAttributes(component);
        const classes = component.getClasses();

        panel.innerHTML = `
            <style>
                #advanced-editing-panel {
                    font-family: -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
                    color: #ddd;
                    font-size: 13px;
                }
                .adv-section {
                    border-bottom: 1px solid #444;
                }
                .adv-section-header {
                    display: flex; align-items: center; justify-content: space-between;
                    padding: 10px 16px;
                    background: #2a2a2a;
                    cursor: pointer;
                    user-select: none;
                    font-size: 11px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    color: #aaa;
                }
                .adv-section-header:hover { color: #fff; }
                .adv-section-body { padding: 12px 16px; }
                .adv-row { margin-bottom: 10px; }
                .adv-label {
                    display: block;
                    font-size: 11px;
                    font-weight: 600;
                    color: #999;
                    margin-bottom: 4px;
                    text-transform: uppercase;
                    letter-spacing: 0.03em;
                }
                .adv-select, .adv-input {
                    width: 100%;
                    background: #1a1a2e;
                    border: 1px solid #444;
                    border-radius: 6px;
                    padding: 7px 10px;
                    color: #e0e0e0;
                    font-size: 13px;
                    outline: none;
                    box-sizing: border-box;
                }
                .adv-select:focus, .adv-input:focus { border-color: #6366f1; }
                .adv-tag-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 4px;
                    margin-top: 6px;
                }
                .adv-tag-btn {
                    padding: 4px 0;
                    border: 1px solid #444;
                    border-radius: 4px;
                    background: transparent;
                    color: #ccc;
                    font-size: 11px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.15s;
                    font-family: inherit;
                }
                .adv-tag-btn:hover { border-color: #6366f1; color: #fff; background: rgba(99,102,241,0.1); }
                .adv-tag-btn.active { border-color: #6366f1; background: #6366f1; color: #fff; }
                .adv-attr-row {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    margin-bottom: 6px;
                }
                .adv-attr-row input {
                    flex: 1;
                    background: #1a1a2e;
                    border: 1px solid #444;
                    border-radius: 4px;
                    padding: 5px 8px;
                    color: #e0e0e0;
                    font-size: 12px;
                    outline: none;
                    font-family: monospace;
                }
                .adv-attr-row input:focus { border-color: #6366f1; }
                .adv-attr-del {
                    background: none; border: none; color: #f87171; cursor: pointer;
                    font-size: 16px; padding: 2px 4px; line-height: 1;
                }
                .adv-attr-del:hover { color: #ff4444; }
                .adv-add-btn {
                    display: inline-flex; align-items: center; gap: 4px;
                    padding: 5px 12px;
                    border: 1px dashed #555;
                    border-radius: 6px;
                    background: transparent;
                    color: #888;
                    font-size: 12px;
                    cursor: pointer;
                    margin-top: 4px;
                    transition: all 0.15s;
                    font-family: inherit;
                }
                .adv-add-btn:hover { border-color: #6366f1; color: #6366f1; }
                .adv-class-tags {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 4px;
                    margin-bottom: 8px;
                }
                .adv-class-tag {
                    display: inline-flex; align-items: center; gap: 4px;
                    background: #333; border: 1px solid #555;
                    border-radius: 4px; padding: 3px 8px;
                    font-size: 11px; color: #ccc;
                    font-family: monospace;
                }
                .adv-class-tag .remove-class {
                    background: none; border: none; color: #888;
                    cursor: pointer; font-size: 14px; padding: 0; line-height: 1;
                }
                .adv-class-tag .remove-class:hover { color: #f87171; }
                .adv-current-tag {
                    display: inline-block;
                    background: #6366f1;
                    color: white;
                    padding: 2px 10px;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: 700;
                    font-family: monospace;
                    margin-bottom: 8px;
                }
            </style>

            <!-- TAG CHANGER -->
            ${isChangeable ? `
            <div class="adv-section">
                <div class="adv-section-header" data-toggle="tag-section">
                    <div style="display: flex; align-items: center; gap: 6px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg><span>HTML Tag</span></div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="adv-section-body" id="tag-section">
                    <div class="adv-row">
                        <span class="adv-current-tag">&lt;${currentTag}&gt;</span>
                    </div>
                    ${Object.entries(TAG_GROUPS).map(([group, tags]) => `
                        <div class="adv-row">
                            <label class="adv-label">${group}</label>
                            <div class="adv-tag-grid">
                                ${tags.map(tag => `
                                    <button class="adv-tag-btn ${tag === currentTag ? 'active' : ''}"
                                            data-tag="${tag}">${tag}</button>
                                `).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}

            <!-- ATTRIBUTES MANAGER -->
            <div class="adv-section">
                <div class="adv-section-header" data-toggle="attrs-section">
                    <div style="display: flex; align-items: center; gap: 6px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg><span>Custom Attributes</span></div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="adv-section-body" id="attrs-section">
                    <div id="attrs-list">
                        ${Object.entries(customAttrs).map(([key, val]) => `
                            <div class="adv-attr-row" data-attr-name="${key}">
                                <input type="text" value="${key}" class="attr-key" readonly style="flex: 0.6; opacity: 0.7;" />
                                <input type="text" value="${String(val).replace(/"/g, '&quot;')}" class="attr-val" placeholder="value" />
                                <button class="adv-attr-del" title="Remove">×</button>
                            </div>
                        `).join('')}
                    </div>
                    <button class="adv-add-btn" id="add-attr-btn">+ Add Attribute</button>
                </div>
            </div>

            <!-- CLASS MANAGER -->
            <div class="adv-section">
                <div class="adv-section-header" data-toggle="class-section">
                    <div style="display: flex; align-items: center; gap: 6px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5"></circle><circle cx="17.5" cy="10.5" r=".5"></circle><circle cx="8.5" cy="7.5" r=".5"></circle><circle cx="6.5" cy="12.5" r=".5"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path></svg><span>CSS Classes</span></div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="adv-section-body" id="class-section">
                    <div class="adv-class-tags" id="class-tags">
                        ${classes.map(cls => `
                            <span class="adv-class-tag">
                                ${cls}
                                <button class="remove-class" data-class="${cls}">×</button>
                            </span>
                        `).join('')}
                        ${classes.length === 0 ? '<span style="color: #666; font-size: 12px;">No classes</span>' : ''}
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <input type="text" id="add-class-input" class="adv-input" placeholder="Add class (e.g. text-center)" style="flex: 1;" />
                        <button class="adv-add-btn" id="add-class-btn" style="margin-top: 0;">+</button>
                    </div>
                    <div style="margin-top: 8px;">
                        <label class="adv-label">Quick Classes</label>
                        <div class="adv-tag-grid" style="grid-template-columns: repeat(3, 1fr);">
                            <button class="adv-tag-btn quick-class" data-cls="text-center">text-center</button>
                            <button class="adv-tag-btn quick-class" data-cls="font-bold">font-bold</button>
                            <button class="adv-tag-btn quick-class" data-cls="hidden">hidden</button>
                            <button class="adv-tag-btn quick-class" data-cls="flex">flex</button>
                            <button class="adv-tag-btn quick-class" data-cls="grid">grid</button>
                            <button class="adv-tag-btn quick-class" data-cls="mx-auto">mx-auto</button>
                            <button class="adv-tag-btn quick-class" data-cls="relative">relative</button>
                            <button class="adv-tag-btn quick-class" data-cls="overflow-hidden">overflow-hidden</button>
                            <button class="adv-tag-btn quick-class" data-cls="rounded-xl">rounded-xl</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEMANTIC / ACCESSIBILITY -->
            <div class="adv-section">
                <div class="adv-section-header" data-toggle="seo-section">
                    <div style="display: flex; align-items: center; gap: 6px;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"></path><path d="M17 3h2a2 2 0 0 1 2 2v2"></path><path d="M21 17v2a2 2 0 0 1-2 2h-2"></path><path d="M7 21H5a2 2 0 0 1-2-2v-2"></path><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><path d="M9 9h.01"></path><path d="M15 9h.01"></path></svg><span>Accessibility / SEO</span></div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="adv-section-body" id="seo-section">
                    <div class="adv-row">
                        <label class="adv-label">ARIA Role</label>
                        <select class="adv-select" id="aria-role">
                            <option value="">None</option>
                            <option value="banner" ${customAttrs['role'] === 'banner' ? 'selected' : ''}>banner</option>
                            <option value="navigation" ${customAttrs['role'] === 'navigation' ? 'selected' : ''}>navigation</option>
                            <option value="main" ${customAttrs['role'] === 'main' ? 'selected' : ''}>main</option>
                            <option value="complementary" ${customAttrs['role'] === 'complementary' ? 'selected' : ''}>complementary</option>
                            <option value="contentinfo" ${customAttrs['role'] === 'contentinfo' ? 'selected' : ''}>contentinfo</option>
                            <option value="region" ${customAttrs['role'] === 'region' ? 'selected' : ''}>region</option>
                            <option value="alert" ${customAttrs['role'] === 'alert' ? 'selected' : ''}>alert</option>
                            <option value="dialog" ${customAttrs['role'] === 'dialog' ? 'selected' : ''}>dialog</option>
                            <option value="button" ${customAttrs['role'] === 'button' ? 'selected' : ''}>button</option>
                            <option value="img" ${customAttrs['role'] === 'img' ? 'selected' : ''}>img</option>
                            <option value="list" ${customAttrs['role'] === 'list' ? 'selected' : ''}>list</option>
                            <option value="listitem" ${customAttrs['role'] === 'listitem' ? 'selected' : ''}>listitem</option>
                            <option value="presentation" ${customAttrs['role'] === 'presentation' ? 'selected' : ''}>presentation</option>
                        </select>
                    </div>
                    <div class="adv-row">
                        <label class="adv-label">ARIA Label</label>
                        <input type="text" class="adv-input" id="aria-label-input" placeholder="Accessible label..." value="${(customAttrs['aria-label'] || '').replace(/"/g, '&quot;')}" />
                    </div>
                    <div class="adv-row">
                        <label class="adv-label">data-section (analytics)</label>
                        <input type="text" class="adv-input" id="data-section-input" placeholder="e.g. hero, features, cta..." value="${(customAttrs['data-section'] || '').replace(/"/g, '&quot;')}" />
                    </div>
                    <div class="adv-row">
                        <label class="adv-label">data-track (CTA tracking)</label>
                        <input type="text" class="adv-input" id="data-track-input" placeholder="e.g. cta_hero_buy_now" value="${(customAttrs['data-track'] || '').replace(/"/g, '&quot;')}" />
                    </div>
                </div>
            </div>
        `;

        // ─── EVENT BINDING ───────────────────────────────────────

        // Section toggles
        panel.querySelectorAll('.adv-section-header').forEach(header => {
            header.addEventListener('click', () => {
                const targetId = header.getAttribute('data-toggle');
                const body = panel.querySelector(`#${targetId}`);
                if (body) {
                    body.style.display = body.style.display === 'none' ? 'block' : 'none';
                }
            });
        });

        // Tag change buttons
        panel.querySelectorAll('[data-tag]').forEach(btn => {
            btn.addEventListener('click', () => {
                changeTag(component, btn.dataset.tag);
                renderAdvancedPanel(editor.getSelected());
            });
        });

        // Attribute value changes
        panel.querySelectorAll('.adv-attr-row .attr-val').forEach(input => {
            input.addEventListener('change', () => {
                const row = input.closest('.adv-attr-row');
                const key = row.dataset.attrName;
                setAttribute(component, key, input.value);
            });
        });

        // Attribute delete
        panel.querySelectorAll('.adv-attr-del').forEach(btn => {
            btn.addEventListener('click', () => {
                const row = btn.closest('.adv-attr-row');
                const key = row.dataset.attrName;
                removeAttribute(component, key);
                renderAdvancedPanel(editor.getSelected());
            });
        });

        // Add attribute
        panel.querySelector('#add-attr-btn')?.addEventListener('click', () => {
            const list = panel.querySelector('#attrs-list');
            const row = document.createElement('div');
            row.className = 'adv-attr-row';
            row.innerHTML = `
                <input type="text" class="attr-key new-key" placeholder="name" style="flex: 0.6;" />
                <input type="text" class="attr-val new-val" placeholder="value" />
                <button class="adv-add-btn" style="margin-top: 0; padding: 4px 8px;">✓</button>
            `;

            const confirmBtn = row.querySelector('.adv-add-btn');
            confirmBtn.addEventListener('click', () => {
                const key = row.querySelector('.new-key').value.trim();
                const val = row.querySelector('.new-val').value;
                if (key) {
                    setAttribute(component, key, val);
                    renderAdvancedPanel(editor.getSelected());
                }
            });

            list.appendChild(row);
            row.querySelector('.new-key').focus();
        });

        // Class management
        panel.querySelectorAll('.remove-class').forEach(btn => {
            btn.addEventListener('click', () => {
                const cls = btn.dataset.class;
                component.removeClass(cls);
                renderAdvancedPanel(editor.getSelected());
            });
        });

        const addClassInput = panel.querySelector('#add-class-input');
        const addClassBtn = panel.querySelector('#add-class-btn');

        const doAddClass = () => {
            const val = addClassInput?.value?.trim();
            if (val) {
                // Support space-separated classes
                val.split(/\s+/).forEach(cls => {
                    if (cls) component.addClass(cls);
                });
                renderAdvancedPanel(editor.getSelected());
            }
        };

        addClassBtn?.addEventListener('click', doAddClass);
        addClassInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); doAddClass(); }
        });

        // Quick class buttons
        panel.querySelectorAll('.quick-class').forEach(btn => {
            btn.addEventListener('click', () => {
                const cls = btn.dataset.cls;
                if (component.getClasses().includes(cls)) {
                    component.removeClass(cls);
                } else {
                    component.addClass(cls);
                }
                renderAdvancedPanel(editor.getSelected());
            });
        });

        // ARIA Role
        panel.querySelector('#aria-role')?.addEventListener('change', (e) => {
            const val = e.target.value;
            if (val) {
                setAttribute(component, 'role', val);
            } else {
                removeAttribute(component, 'role');
            }
        });

        // ARIA Label
        panel.querySelector('#aria-label-input')?.addEventListener('change', (e) => {
            const val = e.target.value.trim();
            if (val) {
                setAttribute(component, 'aria-label', val);
            } else {
                removeAttribute(component, 'aria-label');
            }
        });

        // data-section
        panel.querySelector('#data-section-input')?.addEventListener('change', (e) => {
            const val = e.target.value.trim();
            if (val) {
                setAttribute(component, 'data-section', val);
            } else {
                removeAttribute(component, 'data-section');
            }
        });

        // data-track
        panel.querySelector('#data-track-input')?.addEventListener('change', (e) => {
            const val = e.target.value.trim();
            if (val) {
                setAttribute(component, 'data-track', val);
            } else {
                removeAttribute(component, 'data-track');
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    //  MOUNT INTO SETTINGS PANEL
    // ═══════════════════════════════════════════════════════════════

    function mountPanel() {
        const advancedContainer = document.getElementById('panel-advanced');
        if (!advancedContainer) {
            setTimeout(mountPanel, 200);
            return;
        }

        const panel = createAdvancedPanel();
        panel.style.background = 'transparent';
        panel.style.padding = '0'; // Let the container handle padding if needed
        
        // Remove border-bottom from last section so it looks cleaner
        const styleBlock = document.createElement('style');
        styleBlock.innerHTML = `.adv-section:last-child { border-bottom: none; }`;
        panel.appendChild(styleBlock);

        advancedContainer.appendChild(panel);
    }

    // Mount when DOM is ready
    mountPanel();

    // Update panel when component is selected
    editor.on('component:selected', (cmp) => {
        renderAdvancedPanel(cmp);
    });

    editor.on('component:deselected', () => {
        renderAdvancedPanel(null);
    });

    console.log('[GrapesJS] Advanced Editing Controls loaded. (Tag changer, Attributes manager, Class manager, Accessibility/SEO)');
}
