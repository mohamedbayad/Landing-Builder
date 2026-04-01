/**
 * Plugin: Sidebar Content Editing
 *
 * Adds sidebar editing controls for:
 * - Text components (Text mode + Code mode + formatting toolbar)
 * - Image components (preview + URL/alt/title + media picker)
 */
export default function sidebarContentEditingPlugin(editor, opts = {}) {
    const targetSelector = opts.targetSelector || '#panel-traits';

    const textTags = new Set([
        'p', 'span', 'a', 'button', 'label', 'small', 'strong', 'em', 'b', 'i',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'blockquote'
    ]);

    let panelRoot = null;

    // Text UI
    let textSectionEl = null;
    let statusEl = null;
    let helperEl = null;
    let modeTextBtn = null;
    let modeCodeBtn = null;
    let richEditorEl = null;
    let codeEditorEl = null;
    let selectChildBtn = null;
    let toolbarButtons = [];

    // Image UI
    let imageStatusEl = null;
    let imageSectionEl = null;
    let imagePreviewEl = null;
    let imageSrcInputEl = null;
    let imageAltInputEl = null;
    let imageTitleInputEl = null;
    let imageOpenMediaBtn = null;
    let selectImageChildBtn = null;

    // State
    let selectedComponent = null;
    let editableTextComponent = null;
    let editableImageComponent = null;
    let isApplying = false;
    let currentMode = 'text';

    const debounce = (fn, delay = 80) => {
        let t = null;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    };

    const getType = (cmp) => (cmp?.get('type') || '').toLowerCase();
    const getTag = (cmp) => (cmp?.get('tagName') || '').toLowerCase();

    const isTextLike = (cmp) => {
        if (!cmp) return false;
        const type = getType(cmp);
        const tag = getTag(cmp);

        if (type === 'text' || type === 'textnode') return true;
        if (textTags.has(tag)) return true;

        const content = cmp.get('content');
        return typeof content === 'string' && content.trim().length > 0;
    };

    const isImageLike = (cmp) => {
        if (!cmp) return false;
        const type = getType(cmp);
        const tag = getTag(cmp);

        return type === 'image' || tag === 'img';
    };

    const hasChildren = (cmp) => {
        const children = cmp?.components?.();
        return Boolean(children && children.length > 0);
    };

    const findFirstDescendant = (cmp, predicate) => {
        if (!cmp || !cmp.components) return null;

        const children = cmp.components();
        for (let i = 0; i < children.length; i++) {
            const child = children.at(i);
            if (!child) continue;

            if (predicate(child)) {
                return child;
            }

            const nested = findFirstDescendant(child, predicate);
            if (nested) return nested;
        }

        return null;
    };

    const getEditableHtml = (cmp) => {
        if (!cmp) return '';

        const type = getType(cmp);

        if (type === 'textnode') {
            return cmp.get('content') || '';
        }

        if (hasChildren(cmp)) {
            const children = cmp.components();
            let html = '';
            children.forEach(child => {
                html += child.toHTML ? child.toHTML() : (child.get('content') || '');
            });
            if (html.trim().length > 0) {
                return html;
            }
        }

        const content = cmp.get('content');
        if (typeof content === 'string') {
            return content;
        }

        const el = cmp.view?.el;
        if (el && typeof el.innerHTML === 'string') {
            return el.innerHTML;
        }

        return '';
    };

    const getCodeHtml = (cmp) => {
        if (!cmp) return '';

        if (typeof cmp.toHTML === 'function') {
            return cmp.toHTML();
        }

        const tag = getTag(cmp) || 'div';
        const attrs = cmp.getAttributes?.() || {};
        const attrText = Object.entries(attrs)
            .map(([name, value]) => (value === '' ? name : `${name}="${String(value).replace(/"/g, '&quot;')}"`))
            .join(' ');

        const openTag = attrText ? `<${tag} ${attrText}>` : `<${tag}>`;
        return `${openTag}${getEditableHtml(cmp)}</${tag}>`;
    };

    const parseSingleRootHtml = (raw) => {
        if (typeof raw !== 'string') return null;
        const source = raw.trim();
        if (!source) return null;

        const parser = new DOMParser();
        const doc = parser.parseFromString(source, 'text/html');
        const body = doc?.body;

        if (!body) return null;

        const elements = Array.from(body.children || []);
        if (elements.length !== 1) {
            return null;
        }

        const root = elements[0];
        const attributes = {};

        Array.from(root.attributes || []).forEach(attr => {
            if (attr.name.startsWith('data-gjs-')) return;
            attributes[attr.name] = attr.value;
        });

        return {
            tagName: root.tagName.toLowerCase(),
            attributes,
            innerHtml: root.innerHTML,
        };
    };

    const applyEditableHtml = (cmp, nextValue) => {
        if (!cmp) return;

        const type = getType(cmp);

        if (type === 'textnode') {
            cmp.set('content', nextValue);
            cmp.view?.render?.();
            return;
        }

        if (hasChildren(cmp) || textTags.has(getTag(cmp)) || type === 'text') {
            try {
                cmp.components(nextValue);
            } catch (e) {
                cmp.set('content', nextValue);
            }
            cmp.view?.render?.();
            return;
        }

        cmp.set('content', nextValue);
        cmp.view?.render?.();
    };

    const applyCodeHtml = (cmp, rawCode) => {
        const parsed = parseSingleRootHtml(rawCode);

        if (!parsed) {
            applyEditableHtml(cmp, rawCode);
            return;
        }

        const currentTag = getTag(cmp);
        if (parsed.tagName && parsed.tagName !== currentTag) {
            cmp.set('tagName', parsed.tagName);
        }

        if (typeof cmp.setAttributes === 'function') {
            cmp.setAttributes(parsed.attributes);
        } else if (typeof cmp.addAttributes === 'function') {
            cmp.addAttributes(parsed.attributes);
        }

        applyEditableHtml(cmp, parsed.innerHtml);
    };

    const normalizeRichHtml = (html) => {
        if (typeof html !== 'string') return '';
        const trimmed = html.trim();

        if (trimmed === '<br>' || trimmed === '<div><br></div>' || trimmed === '<p><br></p>') {
            return '';
        }

        return html;
    };

    const getRichHtmlValue = () => normalizeRichHtml(richEditorEl?.innerHTML || '');

    const getImageState = (cmp) => {
        if (!cmp) return { src: '', alt: '', title: '' };

        const attrs = cmp.getAttributes?.() || {};

        return {
            src: String(attrs.src ?? cmp.get('src') ?? ''),
            alt: String(attrs.alt ?? ''),
            title: String(attrs.title ?? ''),
        };
    };

    const applyImageState = (cmp, patch) => {
        if (!cmp) return;

        const nextAttrs = {
            ...(cmp.getAttributes?.() || {}),
        };

        if (patch.src !== undefined) nextAttrs.src = patch.src;
        if (patch.alt !== undefined) nextAttrs.alt = patch.alt;
        if (patch.title !== undefined) nextAttrs.title = patch.title;

        if (typeof cmp.setAttributes === 'function') {
            cmp.setAttributes(nextAttrs);
        } else if (typeof cmp.addAttributes === 'function') {
            cmp.addAttributes(nextAttrs);
        }

        if (patch.src !== undefined && typeof cmp.set === 'function') {
            cmp.set('src', patch.src);
        }

        cmp.view?.render?.();
    };

    const updateImagePreview = (src) => {
        if (!imagePreviewEl) return;

        const value = (src || '').trim();
        if (!value) {
            imagePreviewEl.style.display = 'none';
            imagePreviewEl.removeAttribute('src');
            return;
        }

        imagePreviewEl.style.display = 'block';
        imagePreviewEl.src = value;
    };

    const syncCodeFromRich = () => {
        if (!codeEditorEl || !editableTextComponent) return;

        isApplying = true;
        applyEditableHtml(editableTextComponent, getRichHtmlValue());
        codeEditorEl.value = getCodeHtml(editableTextComponent);
        isApplying = false;
    };

    const syncRichFromCode = () => {
        if (!codeEditorEl || !richEditorEl || !editableTextComponent) return;

        isApplying = true;
        applyCodeHtml(editableTextComponent, codeEditorEl.value || '');
        richEditorEl.innerHTML = getEditableHtml(editableTextComponent);
        isApplying = false;
    };

    const updateModeButtonsUI = () => {
        if (!modeTextBtn || !modeCodeBtn) return;

        if (currentMode === 'text') {
            modeTextBtn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-500');
            modeTextBtn.classList.remove('text-gray-300', 'border-gray-600');

            modeCodeBtn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-500');
            modeCodeBtn.classList.add('text-gray-300', 'border-gray-600');
        } else {
            modeCodeBtn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-500');
            modeCodeBtn.classList.remove('text-gray-300', 'border-gray-600');

            modeTextBtn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-500');
            modeTextBtn.classList.add('text-gray-300', 'border-gray-600');
        }
    };

    const updateModeVisibility = () => {
        if (!richEditorEl || !codeEditorEl) return;

        const isTextMode = currentMode === 'text';
        richEditorEl.style.display = isTextMode ? 'block' : 'none';
        codeEditorEl.style.display = isTextMode ? 'none' : 'block';

        if (helperEl) {
            helperEl.textContent = isTextMode
                ? 'Text mode: visual editing + formatting (bold/italic/...).'
                : 'Code mode: edit raw HTML tags and classes directly.';
        }
    };

    const setMode = (mode, { sync = true } = {}) => {
        if (mode !== 'text' && mode !== 'code') return;

        if (mode === currentMode) {
            updateModeButtonsUI();
            updateModeVisibility();
            return;
        }

        if (sync && editableTextComponent) {
            if (currentMode === 'text') {
                syncCodeFromRich();
            } else {
                syncRichFromCode();
                codeEditorEl.value = getCodeHtml(editableTextComponent);
            }
        }

        currentMode = mode;
        updateModeButtonsUI();
        updateModeVisibility();

        if (editableTextComponent) {
            if (currentMode === 'text') {
                richEditorEl.innerHTML = getEditableHtml(editableTextComponent);
            } else {
                codeEditorEl.value = getCodeHtml(editableTextComponent);
            }
        }
    };

    const setTextInputsEnabled = (enabled) => {
        if (richEditorEl) {
            richEditorEl.setAttribute('contenteditable', enabled ? 'true' : 'false');
            richEditorEl.classList.toggle('opacity-60', !enabled);
        }

        if (codeEditorEl) {
            codeEditorEl.disabled = !enabled;
            codeEditorEl.classList.toggle('opacity-60', !enabled);
        }

        if (modeTextBtn) modeTextBtn.disabled = !enabled;
        if (modeCodeBtn) modeCodeBtn.disabled = !enabled;

        toolbarButtons.forEach(btn => {
            btn.disabled = !enabled;
            btn.classList.toggle('opacity-60', !enabled);
        });
    };

    const setImageInputsEnabled = (enabled) => {
        if (imageSrcInputEl) imageSrcInputEl.disabled = !enabled;
        if (imageAltInputEl) imageAltInputEl.disabled = !enabled;
        if (imageTitleInputEl) imageTitleInputEl.disabled = !enabled;
        if (imageOpenMediaBtn) imageOpenMediaBtn.disabled = !enabled;

        if (imageSectionEl) {
            imageSectionEl.classList.toggle('opacity-70', !enabled);
        }
    };

    const showOnlySection = (section) => {
        if (textSectionEl) {
            textSectionEl.style.display = section === 'text' ? 'block' : 'none';
        }

        if (imageSectionEl) {
            imageSectionEl.style.display = section === 'image' ? 'block' : 'none';
        }
    };

    const setTextState = ({
        enabled,
        message,
        htmlValue = '',
        helper = '',
        showSelectChild = false,
    }) => {
        if (!panelRoot) return;

        statusEl.textContent = message || '';

        if (richEditorEl) {
            richEditorEl.innerHTML = htmlValue || '';
        }
        if (codeEditorEl) {
            codeEditorEl.value = editableTextComponent ? getCodeHtml(editableTextComponent) : '';
        }

        if (helperEl && helper) {
            helperEl.textContent = helper;
        }

        if (selectChildBtn) {
            selectChildBtn.style.display = showSelectChild ? 'inline-flex' : 'none';
        }

        setTextInputsEnabled(enabled);
        updateModeButtonsUI();
        updateModeVisibility();
    };

    const setImageState = ({
        enabled,
        message,
        value = { src: '', alt: '', title: '' },
        showSelectChild = false,
    }) => {
        if (!panelRoot) return;

        if (imageStatusEl) {
            imageStatusEl.textContent = message || '';
        }

        if (imageSrcInputEl) imageSrcInputEl.value = value.src || '';
        if (imageAltInputEl) imageAltInputEl.value = value.alt || '';
        if (imageTitleInputEl) imageTitleInputEl.value = value.title || '';

        updateImagePreview(value.src || '');

        if (selectImageChildBtn) {
            selectImageChildBtn.style.display = showSelectChild ? 'inline-flex' : 'none';
        }

        setImageInputsEnabled(enabled);
    };

    const syncFromSelection = () => {
        if (!panelRoot || !panelRoot.isConnected) {
            mountPanel();
        }

        selectedComponent = editor.getSelected();
        editableTextComponent = null;
        editableImageComponent = null;

        if (!selectedComponent) {
            showOnlySection('none');

            setTextState({
                enabled: false,
                message: 'Select a text element to edit.',
                helper: 'Choose a heading/paragraph/button text component first.'
            });

            setImageState({
                enabled: false,
                message: 'Select an image to edit.',
            });
            return;
        }

        if (isTextLike(selectedComponent)) {
            showOnlySection('text');

            editableTextComponent = selectedComponent;

            setTextState({
                enabled: true,
                message: 'Editing selected text component',
                htmlValue: getEditableHtml(editableTextComponent),
                helper: currentMode === 'text'
                    ? 'Text mode: visual editing + formatting (bold/italic/...).'
                    : 'Code mode: edit raw HTML tags and classes directly.'
            });
            setImageState({
                enabled: false,
                message: 'Select an image to edit.',
            });
            return;
        }

        if (isImageLike(selectedComponent)) {
            showOnlySection('image');

            editableImageComponent = selectedComponent;

            setImageState({
                enabled: true,
                message: 'Editing selected image',
                value: getImageState(editableImageComponent),
            });

            setTextState({
                enabled: false,
                message: 'Select a text element to edit.',
                helper: 'Choose a heading/paragraph/button text component first.'
            });
            return;
        }

        showOnlySection('none');

        setTextState({
            enabled: false,
            message: 'Selected component is not text.',
            helper: 'Select a text element to edit its content.'
        });
        setImageState({
            enabled: false,
            message: 'Selected component is not an image.',
        });
    };

    const applyRichChange = debounce(() => {
        if (!editableTextComponent || !richEditorEl || currentMode !== 'text') return;

        const next = getRichHtmlValue();
        const current = getEditableHtml(editableTextComponent);

        if (next === current) return;

        isApplying = true;
        applyEditableHtml(editableTextComponent, next);
        if (codeEditorEl) {
            codeEditorEl.value = getCodeHtml(editableTextComponent);
        }
        isApplying = false;
    }, 80);

    const applyCodeChange = debounce(() => {
        if (!editableTextComponent || !codeEditorEl || currentMode !== 'code') return;

        const next = codeEditorEl.value;
        const current = getCodeHtml(editableTextComponent);

        if (next === current) return;

        isApplying = true;
        applyCodeHtml(editableTextComponent, next);
        if (richEditorEl) {
            richEditorEl.innerHTML = getEditableHtml(editableTextComponent);
        }
        codeEditorEl.value = getCodeHtml(editableTextComponent);
        isApplying = false;
    }, 80);

    const applyImageChange = debounce(() => {
        if (!editableImageComponent || !imageSrcInputEl || !imageAltInputEl || !imageTitleInputEl) return;

        const next = {
            src: imageSrcInputEl.value.trim(),
            alt: imageAltInputEl.value,
            title: imageTitleInputEl.value,
        };

        const current = getImageState(editableImageComponent);
        if (next.src === current.src && next.alt === current.alt && next.title === current.title) {
            return;
        }

        isApplying = true;
        applyImageState(editableImageComponent, next);
        updateImagePreview(next.src);
        isApplying = false;
    }, 80);

    const wrapCodeSelection = (before, after) => {
        if (!codeEditorEl || codeEditorEl.disabled) return;

        const start = codeEditorEl.selectionStart || 0;
        const end = codeEditorEl.selectionEnd || 0;
        const value = codeEditorEl.value || '';
        const selected = value.slice(start, end) || 'text';

        const next = value.slice(0, start) + before + selected + after + value.slice(end);
        codeEditorEl.value = next;

        const cursorStart = start + before.length;
        const cursorEnd = cursorStart + selected.length;
        codeEditorEl.focus();
        codeEditorEl.setSelectionRange(cursorStart, cursorEnd);

        applyCodeChange();
    };

    const onToolbarAction = (action) => {
        if (!editableTextComponent) return;

        if (currentMode === 'text') {
            if (!richEditorEl) return;
            richEditorEl.focus();

            if (action === 'link') {
                const url = window.prompt('Enter link URL (https://...)');
                if (!url) return;
                document.execCommand('createLink', false, url);
            } else {
                document.execCommand(action, false, null);
            }

            applyRichChange();
            return;
        }

        const wrappers = {
            bold: ['<strong>', '</strong>'],
            italic: ['<em>', '</em>'],
            underline: ['<u>', '</u>'],
            strikeThrough: ['<s>', '</s>'],
        };

        if (action === 'link') {
            const url = window.prompt('Enter link URL (https://...)');
            if (!url) return;
            wrapCodeSelection(`<a href="${url}">`, '</a>');
            return;
        }

        const pair = wrappers[action];
        if (pair) {
            wrapCodeSelection(pair[0], pair[1]);
        }
    };

    const openMediaPicker = () => {
        if (!editableImageComponent) return;

        editor.select(editableImageComponent);
        editor.runCommand('open-assets', {
            target: editableImageComponent,
            types: ['image'],
            accept: 'image/*',
        });
    };

    const mountPanel = () => {
        if (panelRoot && panelRoot.isConnected) return;

        const target = document.querySelector(targetSelector);
        if (!target) {
            console.warn('[GrapesJS] Sidebar Content Editing: target panel not found:', targetSelector);
            return;
        }

        const existing = document.getElementById('gjs-sidebar-content-editor');
        if (existing) {
            existing.remove();
        }

        panelRoot = document.createElement('div');
        panelRoot.id = 'gjs-sidebar-content-editor';
        panelRoot.className = 'p-3 border-b border-gray-700 bg-[#2e2e2e]';

        panelRoot.innerHTML = `
            <div id="gjs-sidebar-text-section">
                <div class="text-[11px] uppercase tracking-wide font-bold text-gray-300 mb-2">Text Content</div>
                <div id="gjs-sidebar-content-status" class="text-[11px] text-gray-400 mb-2"></div>

                <div class="flex items-center gap-2 mb-2">
                    <button id="gjs-mode-text" type="button" class="text-[11px] px-2 py-1 rounded border text-gray-300 border-gray-600">Text</button>
                    <button id="gjs-mode-code" type="button" class="text-[11px] px-2 py-1 rounded border text-gray-300 border-gray-600">Code</button>
                </div>

                <div class="flex flex-wrap items-center gap-1 mb-2">
                    <button id="gjs-fmt-bold" type="button" class="text-[11px] px-2 py-1 rounded border border-gray-600 text-gray-200">B</button>
                    <button id="gjs-fmt-italic" type="button" class="text-[11px] px-2 py-1 rounded border border-gray-600 text-gray-200"><em>I</em></button>
                    <button id="gjs-fmt-underline" type="button" class="text-[11px] px-2 py-1 rounded border border-gray-600 text-gray-200"><u>U</u></button>
                    <button id="gjs-fmt-strike" type="button" class="text-[11px] px-2 py-1 rounded border border-gray-600 text-gray-200"><s>S</s></button>
                    <button id="gjs-fmt-link" type="button" class="text-[11px] px-2 py-1 rounded border border-gray-600 text-gray-200">Link</button>
                </div>

                <div id="gjs-sidebar-rich-editor"
                    class="w-full min-h-[120px] rounded border border-gray-600 bg-[#1f1f1f] text-gray-100 text-xs p-2 focus:outline-none"
                    contenteditable="false"
                    spellcheck="false"></div>

                <textarea id="gjs-sidebar-code-editor"
                    class="w-full min-h-[120px] rounded border border-gray-600 bg-[#1f1f1f] text-gray-100 text-xs p-2 focus:outline-none focus:border-indigo-500 font-mono"
                    placeholder="Edit raw HTML here..."
                    style="display:none"
                    disabled
                ></textarea>

                <div class="mt-2 flex items-center gap-2">
                    <button id="gjs-sidebar-select-child" type="button"
                        class="hidden text-[11px] px-2 py-1 rounded border border-gray-500 text-gray-200 hover:border-indigo-500 hover:text-white">
                        Select Child
                    </button>
                    <div id="gjs-sidebar-content-helper" class="text-[10px] text-gray-500"></div>
                </div>
            </div>

            <div id="gjs-sidebar-image-section" class="mt-4 pt-3 border-t border-gray-700">
                <div class="text-[11px] uppercase tracking-wide font-bold text-gray-300 mb-2">Image</div>
                <div id="gjs-sidebar-image-status" class="text-[11px] text-gray-400 mb-2"></div>

                <img id="gjs-sidebar-image-preview"
                     alt="Image preview"
                     class="w-full h-auto rounded border border-gray-600 mb-2 object-contain bg-[#1f1f1f]"
                     style="max-height:140px; display:none;" />

                <label class="text-[10px] text-gray-400 uppercase tracking-wide">Source URL</label>
                <input id="gjs-sidebar-image-src" type="text"
                    class="w-full mt-1 mb-2 rounded border border-gray-600 bg-[#1f1f1f] text-gray-100 text-xs p-2 focus:outline-none focus:border-indigo-500"
                    placeholder="https://... or /storage/..." />

                <button id="gjs-sidebar-image-open-media" type="button"
                    class="w-full mb-2 text-[11px] px-2 py-2 rounded border border-gray-500 text-gray-200 hover:border-indigo-500 hover:text-white">
                    Choose From Media Library
                </button>

                <label class="text-[10px] text-gray-400 uppercase tracking-wide">Alt Text</label>
                <input id="gjs-sidebar-image-alt" type="text"
                    class="w-full mt-1 mb-2 rounded border border-gray-600 bg-[#1f1f1f] text-gray-100 text-xs p-2 focus:outline-none focus:border-indigo-500"
                    placeholder="Image description for SEO/accessibility" />

                <label class="text-[10px] text-gray-400 uppercase tracking-wide">Title</label>
                <input id="gjs-sidebar-image-title" type="text"
                    class="w-full mt-1 mb-2 rounded border border-gray-600 bg-[#1f1f1f] text-gray-100 text-xs p-2 focus:outline-none focus:border-indigo-500"
                    placeholder="Optional image title" />

                <button id="gjs-sidebar-select-image-child" type="button"
                    class="hidden text-[11px] px-2 py-1 rounded border border-gray-500 text-gray-200 hover:border-indigo-500 hover:text-white">
                    Select Image Child
                </button>
            </div>
        `;

        target.prepend(panelRoot);

        // Text refs
        textSectionEl = panelRoot.querySelector('#gjs-sidebar-text-section');
        statusEl = panelRoot.querySelector('#gjs-sidebar-content-status');
        helperEl = panelRoot.querySelector('#gjs-sidebar-content-helper');
        modeTextBtn = panelRoot.querySelector('#gjs-mode-text');
        modeCodeBtn = panelRoot.querySelector('#gjs-mode-code');
        richEditorEl = panelRoot.querySelector('#gjs-sidebar-rich-editor');
        codeEditorEl = panelRoot.querySelector('#gjs-sidebar-code-editor');
        selectChildBtn = panelRoot.querySelector('#gjs-sidebar-select-child');

        // Image refs
        imageSectionEl = panelRoot.querySelector('#gjs-sidebar-image-section');
        imageStatusEl = panelRoot.querySelector('#gjs-sidebar-image-status');
        imagePreviewEl = panelRoot.querySelector('#gjs-sidebar-image-preview');
        imageSrcInputEl = panelRoot.querySelector('#gjs-sidebar-image-src');
        imageAltInputEl = panelRoot.querySelector('#gjs-sidebar-image-alt');
        imageTitleInputEl = panelRoot.querySelector('#gjs-sidebar-image-title');
        imageOpenMediaBtn = panelRoot.querySelector('#gjs-sidebar-image-open-media');
        selectImageChildBtn = panelRoot.querySelector('#gjs-sidebar-select-image-child');

        const formatConfig = [
            ['#gjs-fmt-bold', 'bold'],
            ['#gjs-fmt-italic', 'italic'],
            ['#gjs-fmt-underline', 'underline'],
            ['#gjs-fmt-strike', 'strikeThrough'],
            ['#gjs-fmt-link', 'link'],
        ];

        toolbarButtons = formatConfig
            .map(([selector]) => panelRoot.querySelector(selector))
            .filter(Boolean);

        formatConfig.forEach(([selector, action]) => {
            const btn = panelRoot.querySelector(selector);
            if (!btn) return;
            btn.addEventListener('click', () => onToolbarAction(action));
        });

        modeTextBtn.addEventListener('click', () => setMode('text'));
        modeCodeBtn.addEventListener('click', () => setMode('code'));

        richEditorEl.addEventListener('input', applyRichChange);
        codeEditorEl.addEventListener('input', applyCodeChange);

        imageSrcInputEl.addEventListener('input', () => {
            updateImagePreview(imageSrcInputEl.value);
            applyImageChange();
        });
        imageAltInputEl.addEventListener('input', applyImageChange);
        imageTitleInputEl.addEventListener('input', applyImageChange);

        imageOpenMediaBtn.addEventListener('click', openMediaPicker);

        selectChildBtn.addEventListener('click', () => {
            if (editableTextComponent) {
                editor.select(editableTextComponent);
            }
        });

        selectImageChildBtn.addEventListener('click', () => {
            if (editableImageComponent) {
                editor.select(editableImageComponent);
            }
        });

        setTextState({
            enabled: false,
            message: 'Select a text element to edit.',
            helper: 'Choose a heading/paragraph/button text component first.'
        });

        setImageState({
            enabled: false,
            message: 'Select an image to edit.',
        });

        showOnlySection('none');
    };

    editor.on('load', () => {
        mountPanel();
        syncFromSelection();
    });

    editor.on('component:selected', () => {
        syncFromSelection();
    });

    editor.on('component:deselected', () => {
        syncFromSelection();
    });

    editor.on('component:update', (cmp) => {
        if (isApplying) return;

        if (cmp === editableTextComponent) {
            const latestInner = getEditableHtml(editableTextComponent);
            const latestCode = getCodeHtml(editableTextComponent);

            if (richEditorEl && getRichHtmlValue() !== latestInner) {
                richEditorEl.innerHTML = latestInner;
            }

            if (codeEditorEl && codeEditorEl.value !== latestCode) {
                codeEditorEl.value = latestCode;
            }
        }

        if (cmp === editableImageComponent) {
            const latestImage = getImageState(editableImageComponent);

            if (imageSrcInputEl && imageSrcInputEl.value !== latestImage.src) {
                imageSrcInputEl.value = latestImage.src;
            }
            if (imageAltInputEl && imageAltInputEl.value !== latestImage.alt) {
                imageAltInputEl.value = latestImage.alt;
            }
            if (imageTitleInputEl && imageTitleInputEl.value !== latestImage.title) {
                imageTitleInputEl.value = latestImage.title;
            }

            updateImagePreview(latestImage.src);
        }
    });

    editor.on('trait:custom', () => {
        if (!panelRoot || !panelRoot.isConnected) {
            mountPanel();
            syncFromSelection();
        }
    });

    console.log('[GrapesJS] Sidebar Content Editing plugin loaded.');
}
