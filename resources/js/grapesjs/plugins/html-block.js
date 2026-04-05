import CodeMirror from 'codemirror';
import 'codemirror/lib/codemirror.css';
import 'codemirror/theme/material-darker.css';
import 'codemirror/mode/xml/xml';
import 'codemirror/mode/javascript/javascript';
import 'codemirror/mode/css/css';
import 'codemirror/mode/htmlmixed/htmlmixed';
import 'codemirror/addon/edit/closebrackets';
import 'codemirror/addon/edit/closetag';
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/addon/edit/matchtags';

/**
 * Robust HTML Block (single source of truth)
 * - One block in Block Manager
 * - One component type: html-block
 * - One command: open-html-editor
 * - 3 edit triggers: dblclick + toolbar + trait button
 */
export default function htmlBlockPlugin(editor, opts = {}) {
    if (editor.__htmlBlockPluginReady) return;
    editor.__htmlBlockPluginReady = true;

    const OPTIONS = {
        blockId: 'html-block',
        blockLabel: 'HTML',
        blockCategory: 'Basic',
        componentType: 'html-block',
        markerAttr: 'data-gjs-type',
        markerValue: 'html-block',
        sourceAttr: 'data-html-source',
        storageProp: 'htmlContent',
        ...opts,
    };

    const SCRIPT_PATTERN = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi;
    const STARTER_HTML = '<div class="custom-html-block">\n  \n</div>';

    let activeCodeMirror = null;
    let activeTextArea = null;

    const log = (...args) => console.log('[HTML BLOCK]', ...args);

    const cleanupEditor = () => {
        if (activeCodeMirror && activeTextArea) {
            activeCodeMirror.toTextArea();
        }
        activeCodeMirror = null;
        activeTextArea = null;
    };

    const sanitizeHtml = (raw) => {
        if (typeof raw !== 'string') return '';
        return raw.replace(SCRIPT_PATTERN, '').trim();
    };

    const placeholderHtml = () => `
        <div class="html-block-placeholder" aria-hidden="true">
            <div class="html-block-icon">&lt;/&gt;</div>
            <div class="html-block-title">Custom HTML</div>
            <div class="html-block-help">Double-click or use Edit HTML to add code</div>
        </div>
    `;

    const getSourceHtml = (component) => {
        const attrs = component?.getAttributes?.() || {};
        const attrHtml = attrs[OPTIONS.sourceAttr];
        const propHtml = component?.get?.(OPTIONS.storageProp);
        return String(attrHtml ?? propHtml ?? '').trim();
    };

    const setComponentHtml = (component, rawHtml) => {
        if (!component) return;

        const clean = sanitizeHtml(rawHtml);
        const next = clean || '';

        component.set(OPTIONS.storageProp, next);
        component.addAttributes({ [OPTIONS.sourceAttr]: next });
        component.components(next || placeholderHtml());

        const isEmpty = next.length === 0;
        component.addAttributes({ 'data-html-empty': isEmpty ? 'true' : 'false' });
        component.view?.render?.();

        log('save clicked', { isEmpty, length: next.length });
    };

    const createModalContent = (component) => {
        const root = document.createElement('div');
        root.innerHTML = `
            <div style="display:flex;flex-direction:column;gap:12px;width:min(1000px,92vw);max-width:100%;">
                <p style="margin:0;color:#9ca3af;font-size:12px;">
                    Edit raw HTML. Script tags are removed for safety.
                </p>
                <textarea id="html-editor-input" spellcheck="false" style="min-height:420px;"></textarea>
                <div style="display:flex;justify-content:flex-end;gap:10px;">
                    <button type="button" id="cancel-html-editor" style="padding:8px 14px;border:1px solid #475569;border-radius:8px;background:transparent;color:#e2e8f0;cursor:pointer;">Cancel</button>
                    <button type="button" id="save-html-editor" style="padding:8px 14px;border:1px solid #4f46e5;border-radius:8px;background:#4f46e5;color:#fff;cursor:pointer;font-weight:600;">Save</button>
                </div>
            </div>
        `;

        const textarea = root.querySelector('#html-editor-input');
        const cancelBtn = root.querySelector('#cancel-html-editor');
        const saveBtn = root.querySelector('#save-html-editor');
        if (!textarea || !cancelBtn || !saveBtn) return null;

        const currentHtml = getSourceHtml(component);
        textarea.value = currentHtml || STARTER_HTML;

        cancelBtn.addEventListener('click', () => {
            editor.Modal.close();
        });

        saveBtn.addEventListener('click', () => {
            const newHtml = activeCodeMirror ? activeCodeMirror.getValue() : textarea.value;
            setComponentHtml(component, newHtml);
            editor.Modal.close();
        });

        return { root, textarea };
    };

    editor.Commands.add('open-html-editor', {
        run(ed, _sender, optsRun = {}) {
            const component = optsRun.component || ed.getSelected();
            if (!component) {
                console.warn('[HTML BLOCK] No component selected');
                return;
            }

            log('open-html-editor command running', {
                id: component.getId?.(),
                type: component.get('type'),
            });

            const modal = ed.Modal;
            const ui = createModalContent(component);
            if (!ui) return;

            cleanupEditor();
            modal.setTitle('Edit HTML');
            modal.setContent(ui.root);
            modal.open();
            log('modal opened');

            requestAnimationFrame(() => {
                try {
                    activeTextArea = ui.textarea;
                    activeCodeMirror = CodeMirror.fromTextArea(ui.textarea, {
                        mode: 'htmlmixed',
                        theme: 'material-darker',
                        lineNumbers: true,
                        lineWrapping: false,
                        indentUnit: 2,
                        tabSize: 2,
                        indentWithTabs: false,
                        autoCloseTags: true,
                        autoCloseBrackets: true,
                        matchTags: { bothTags: true },
                        matchBrackets: true,
                    });
                    activeCodeMirror.setSize('100%', 'min(62vh, 560px)');
                    activeCodeMirror.focus();
                } catch (error) {
                    console.warn('[HTML BLOCK] CodeMirror fallback to textarea', error);
                    ui.textarea.focus();
                }
            });
        },
    });

    // Backward compatibility command alias
    editor.Commands.add('custom-html:open-editor', {
        run(ed, sender, optsRun = {}) {
            ed.runCommand('open-html-editor', optsRun);
        },
    });

    editor.on('modal:close', cleanupEditor);

    const componentDef = {
        isComponent(el) {
            if (!el || !el.getAttribute) return false;
            const marker = String(el.getAttribute(OPTIONS.markerAttr) || '').toLowerCase();
            if (marker === OPTIONS.markerValue) return { type: OPTIONS.componentType };
            if (el.classList?.contains('custom-html-block-root')) return { type: OPTIONS.componentType };
            return false;
        },

        model: {
            defaults: {
                name: 'HTML',
                tagName: 'div',
                selectable: true,
                draggable: true,
                droppable: true,
                editable: false,
                copyable: true,
                removable: true,
                badgable: true,
                attributes: {
                    class: 'custom-html-block-root',
                    [OPTIONS.markerAttr]: OPTIONS.markerValue,
                    [OPTIONS.sourceAttr]: '',
                    'data-html-empty': 'true',
                },
                [OPTIONS.storageProp]: '',
                components: placeholderHtml(),
                toolbar: [
                    {
                        attributes: { class: 'fa fa-code', title: 'Edit HTML' },
                        command: 'open-html-editor',
                    },
                ],
                traits: [
                    {
                        type: 'button',
                        name: 'html-block-edit',
                        text: 'Edit HTML',
                        full: true,
                        command: 'open-html-editor',
                    },
                ],
            },

            init() {
                const current = getSourceHtml(this);
                if (current) {
                    setComponentHtml(this, current);
                } else {
                    this.addAttributes({ 'data-html-empty': 'true' });
                }
            },
        },

        view: {
            events: {
                dblclick: 'onDblClick',
            },

            onRender() {
                // Capture-phase binding as hard fallback in case delegated dblclick is swallowed.
                if (this.__dblHandler) {
                    this.el.removeEventListener('dblclick', this.__dblHandler, true);
                }
                this.__dblHandler = (event) => this.onDblClick(event);
                this.el.addEventListener('dblclick', this.__dblHandler, true);
            },

            removed() {
                if (this.__dblHandler) {
                    this.el.removeEventListener('dblclick', this.__dblHandler, true);
                }
                this.__dblHandler = null;
            },

            onDblClick(event) {
                event?.preventDefault?.();
                event?.stopPropagation?.();
                log('dblclick fired', {
                    id: this.model.getId?.(),
                    type: this.model.get('type'),
                });
                this.model.em.runCommand('open-html-editor', { component: this.model });
            },
        },
    };

    editor.DomComponents.addType(OPTIONS.componentType, componentDef);

    const ensureSingleHtmlBlock = () => {
        const bm = editor.BlockManager;
        const all = bm.getAll();
        const toRemove = [];

        all.forEach((block) => {
            const id = String(block.getId?.() || block.id || '').toLowerCase();
            const rawLabel = String(block.get?.('label') ?? block.label ?? '').replace(/<[^>]*>/g, '').trim().toLowerCase();
            const isHtmlDup = id === 'custom-code' || id === 'custom-html' || id === 'builder-html' || id === OPTIONS.blockId || rawLabel === 'html';
            if (isHtmlDup) toRemove.push(id);
        });

        toRemove.forEach((id) => bm.remove(id));

        bm.add(OPTIONS.blockId, {
            label: OPTIONS.blockLabel,
            category: OPTIONS.blockCategory,
            media: `
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 18l6-6-6-6"></path>
                    <path d="M8 6l-6 6 6 6"></path>
                </svg>
            `,
            attributes: { class: 'gjs-fonts gjs-f-b1' },
            content: {
                type: OPTIONS.componentType,
                tagName: 'div',
                attributes: {
                    class: 'custom-html-block-root',
                    [OPTIONS.markerAttr]: OPTIONS.markerValue,
                    [OPTIONS.sourceAttr]: '',
                    'data-html-empty': 'true',
                },
                components: placeholderHtml(),
            },
        });
    };

    const injectStyles = () => {
        const canvasDoc = editor.Canvas.getDocument();
        if (canvasDoc && !canvasDoc.getElementById('html-block-canvas-style')) {
            const style = canvasDoc.createElement('style');
            style.id = 'html-block-canvas-style';
            style.textContent = `
                .custom-html-block-root {
                    border: 1px dashed rgba(99, 102, 241, 0.55);
                    border-radius: 10px;
                    min-height: 110px;
                    padding: 12px;
                    position: relative;
                    background: rgba(15, 23, 42, 0.02);
                }
                .custom-html-block-root .html-block-placeholder {
                    pointer-events: none;
                    display: grid;
                    place-items: center;
                    text-align: center;
                    color: #94a3b8;
                    min-height: 84px;
                }
                .custom-html-block-root .html-block-icon {
                    font: 700 18px/1 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
                    color: #6366f1;
                    margin-bottom: 6px;
                }
                .custom-html-block-root .html-block-title {
                    font: 600 12px/1.35 Inter, system-ui, sans-serif;
                    color: #e2e8f0;
                }
                .custom-html-block-root .html-block-help {
                    font: 500 11px/1.35 Inter, system-ui, sans-serif;
                    color: #94a3b8;
                    margin-top: 4px;
                }
            `;
            canvasDoc.head.appendChild(style);
        }

        if (!document.getElementById('html-block-modal-style')) {
            const style = document.createElement('style');
            style.id = 'html-block-modal-style';
            style.textContent = `
                .gjs-mdl-dialog .CodeMirror {
                    border: 1px solid #334155;
                    border-radius: 10px;
                    font-size: 13px;
                    line-height: 1.55;
                }
                .gjs-mdl-dialog .CodeMirror-gutters {
                    border-right: 1px solid rgba(148, 163, 184, 0.2);
                }
            `;
            document.head.appendChild(style);
        }
    };

    editor.on('component:add', (component) => {
        if (component.get?.('type') === OPTIONS.componentType) {
            log('dropped', {
                id: component.getId?.(),
                type: component.get('type'),
            });
        }
    });

    editor.on('component:selected', (component) => {
        if (component?.get?.('type') === OPTIONS.componentType) {
            log('selected', {
                id: component.getId?.(),
                hasCommand: !!editor.Commands.get('open-html-editor'),
            });
        }
    });

    editor.on('component:update:attributes', (component) => {
        if (component?.get?.('type') !== OPTIONS.componentType) return;
        if (!Object.prototype.hasOwnProperty.call(component.getAttributes?.() || {}, OPTIONS.sourceAttr)) return;
        if (!component.get(OPTIONS.storageProp) && getSourceHtml(component)) {
            component.set(OPTIONS.storageProp, getSourceHtml(component));
        }
    });

    ensureSingleHtmlBlock();
    injectStyles();
    editor.on('load', () => {
        ensureSingleHtmlBlock();
        injectStyles();
    });
    editor.on('canvas:frame:load', injectStyles);
}
