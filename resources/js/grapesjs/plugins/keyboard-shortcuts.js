/**
 * Plugin: Keyboard Shortcuts
 *
 * Adds essential keyboard shortcuts to the GrapesJS editor:
 *   Ctrl+S       → Save
 *   Ctrl+Z       → Undo (reinforces built-in)
 *   Ctrl+Y       → Redo (reinforces built-in)
 *   Ctrl+Shift+Z → Redo (alternative)
 *   Delete/Backspace → Delete selected component
 *   Ctrl+D       → Duplicate selected component
 *   Ctrl+C       → Copy selected component
 *   Ctrl+V       → Paste copied component
 *   Escape       → Deselect / close modals
 */
export default function keyboardShortcutsPlugin(editor, opts = {}) {

    let clipboard = null;

    const handleKeydown = (e) => {
        const tag = (e.target.tagName || '').toLowerCase();
        const isEditable = e.target.isContentEditable;
        const isInput = tag === 'input' || tag === 'textarea' || tag === 'select' || isEditable;

        // --- Ctrl+S → Save (always intercept, even in inputs) ---
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            e.stopPropagation();
            const saveBtn = document.getElementById('btn-save');
            if (saveBtn && !saveBtn.disabled) {
                saveBtn.click();
            }
            return;
        }

        // Skip other shortcuts if user is typing in an input/textarea
        if (isInput) return;

        const selected = editor.getSelected();

        // --- Ctrl+Z → Undo ---
        if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') {
            e.preventDefault();
            editor.runCommand('core:undo');
            return;
        }

        // --- Ctrl+Y or Ctrl+Shift+Z → Redo ---
        if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z') || (e.shiftKey && e.key === 'Z'))) {
            e.preventDefault();
            editor.runCommand('core:redo');
            return;
        }

        // --- Delete/Backspace → Remove selected ---
        if ((e.key === 'Delete' || e.key === 'Backspace') && selected) {
            e.preventDefault();
            // Don't delete the body/wrapper
            const parent = selected.parent();
            if (parent) {
                selected.remove();
            }
            return;
        }

        // --- Ctrl+D → Duplicate ---
        if ((e.ctrlKey || e.metaKey) && e.key === 'd' && selected) {
            e.preventDefault();
            const parent = selected.parent();
            if (parent) {
                const idx = selected.index();
                const cloned = selected.clone();
                parent.append(cloned, { at: idx + 1 });
                editor.select(cloned);
            }
            return;
        }

        // --- Ctrl+C → Copy ---
        if ((e.ctrlKey || e.metaKey) && e.key === 'c' && selected) {
            e.preventDefault();
            clipboard = {
                html: selected.toHTML(),
                css: editor.CodeManager?.getCode?.(selected, 'css')?.css || '',
            };
            if (window.Toast) {
                window.Toast.success('Component copied');
            }
            return;
        }

        // --- Ctrl+V → Paste ---
        if ((e.ctrlKey || e.metaKey) && e.key === 'v' && clipboard) {
            e.preventDefault();
            const target = selected || editor.getWrapper();
            if (target && clipboard.html) {
                const added = target.append(clipboard.html);
                if (added && added.length > 0) {
                    editor.select(added[added.length - 1]);
                }
            }
            return;
        }

        // --- Escape → Deselect ---
        if (e.key === 'Escape') {
            // Close modal if open
            if (editor.Modal?.isOpen?.()) {
                editor.Modal.close();
                return;
            }
            // Otherwise deselect
            editor.select(null);
            return;
        }
    };

    // Attach to the main document
    document.addEventListener('keydown', handleKeydown, true);

    // Also attach to the canvas iframe when it loads
    const attachToCanvas = () => {
        try {
            const canvasDoc = editor.Canvas.getDocument();
            if (canvasDoc) {
                canvasDoc.addEventListener('keydown', handleKeydown, true);
            }
        } catch (e) {
            // Canvas not ready yet
        }
    };

    editor.on('canvas:frame:load', attachToCanvas);
    editor.on('load', attachToCanvas);

    // Cleanup
    editor.on('destroy', () => {
        document.removeEventListener('keydown', handleKeydown, true);
        try {
            const canvasDoc = editor.Canvas.getDocument();
            if (canvasDoc) {
                canvasDoc.removeEventListener('keydown', handleKeydown, true);
            }
        } catch (e) {}
    });

    console.log('[GrapesJS] Keyboard Shortcuts plugin loaded. (Ctrl+S/Z/Y/D/C/V, Del, Esc)');
}
