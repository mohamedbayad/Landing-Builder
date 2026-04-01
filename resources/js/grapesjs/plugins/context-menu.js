/**
 * Plugin: Context Menu
 *
 * Adds a right-click context menu inside the GrapesJS canvas with actions:
 *   - Select Parent
 *   - Duplicate
 *   - Copy / Paste
 *   - Move Up / Move Down
 *   - Delete
 *   - Save as Block
 */
export default function contextMenuPlugin(editor, opts = {}) {

    let menuEl = null;
    let clipboard = null;
    let targetComponent = null;

    const MENU_ITEMS = [
        { id: 'select-parent', label: '⬆ Select Parent', icon: '', action: 'selectParent' },
        { id: 'divider-1', divider: true },
        { id: 'duplicate', label: '⧉ Duplicate', shortcut: 'Ctrl+D', action: 'duplicate' },
        { id: 'copy', label: '📋 Copy', shortcut: 'Ctrl+C', action: 'copy' },
        { id: 'paste', label: '📥 Paste', shortcut: 'Ctrl+V', action: 'paste' },
        { id: 'divider-2', divider: true },
        { id: 'move-up', label: '↑ Move Up', action: 'moveUp' },
        { id: 'move-down', label: '↓ Move Down', action: 'moveDown' },
        { id: 'divider-3', divider: true },
        { id: 'save-block', label: '💾 Save as Block', action: 'saveAsBlock' },
        { id: 'divider-4', divider: true },
        { id: 'delete', label: '🗑 Delete', shortcut: 'Del', action: 'delete', danger: true },
    ];

    const createMenu = () => {
        if (menuEl) return;

        menuEl = document.createElement('div');
        menuEl.id = 'gjs-context-menu';
        menuEl.style.cssText = `
            position: fixed;
            z-index: 99999;
            display: none;
            min-width: 200px;
            background: #1e1e2e;
            border: 1px solid #3a3a4a;
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.45), 0 2px 8px rgba(0,0,0,0.25);
            padding: 4px 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            font-size: 13px;
            color: #e0e0e0;
            backdrop-filter: blur(12px);
            animation: gjsMenuFadeIn 0.12s ease-out;
        `;

        // Inject animation keyframe
        const style = document.createElement('style');
        style.textContent = `
            @keyframes gjsMenuFadeIn {
                from { opacity: 0; transform: scale(0.95) translateY(-4px); }
                to   { opacity: 1; transform: scale(1) translateY(0); }
            }
        `;
        document.head.appendChild(style);

        MENU_ITEMS.forEach(item => {
            if (item.divider) {
                const hr = document.createElement('div');
                hr.style.cssText = 'height: 1px; background: #3a3a4a; margin: 4px 8px;';
                menuEl.appendChild(hr);
                return;
            }

            const btn = document.createElement('button');
            btn.id = `gjs-ctx-${item.id}`;
            btn.type = 'button';
            btn.style.cssText = `
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                padding: 7px 14px;
                border: none;
                background: transparent;
                color: ${item.danger ? '#f87171' : '#e0e0e0'};
                cursor: pointer;
                font-size: 13px;
                text-align: left;
                transition: background 0.1s;
                font-family: inherit;
            `;

            const labelSpan = document.createElement('span');
            labelSpan.textContent = item.label;

            btn.appendChild(labelSpan);

            if (item.shortcut) {
                const shortcutSpan = document.createElement('span');
                shortcutSpan.textContent = item.shortcut;
                shortcutSpan.style.cssText = 'font-size: 11px; color: #777; margin-left: 24px;';
                btn.appendChild(shortcutSpan);
            }

            btn.addEventListener('mouseenter', () => {
                btn.style.background = item.danger ? 'rgba(248,113,113,0.12)' : 'rgba(99,102,241,0.15)';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.background = 'transparent';
            });

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                executeAction(item.action);
                hideMenu();
            });

            menuEl.appendChild(btn);
        });

        document.body.appendChild(menuEl);
    };

    const showMenu = (x, y) => {
        if (!menuEl) createMenu();

        // Update paste item state
        const pasteBtn = menuEl.querySelector('#gjs-ctx-paste');
        if (pasteBtn) {
            pasteBtn.style.opacity = clipboard ? '1' : '0.4';
            pasteBtn.style.pointerEvents = clipboard ? 'auto' : 'none';
        }

        // Update move actions state
        if (targetComponent) {
            const parent = targetComponent.parent();
            const index = targetComponent.index();
            const siblings = parent?.components?.()?.length || 0;

            const moveUpBtn = menuEl.querySelector('#gjs-ctx-move-up');
            const moveDownBtn = menuEl.querySelector('#gjs-ctx-move-down');

            if (moveUpBtn) {
                moveUpBtn.style.opacity = index > 0 ? '1' : '0.4';
                moveUpBtn.style.pointerEvents = index > 0 ? 'auto' : 'none';
            }
            if (moveDownBtn) {
                moveDownBtn.style.opacity = index < siblings - 1 ? '1' : '0.4';
                moveDownBtn.style.pointerEvents = index < siblings - 1 ? 'auto' : 'none';
            }
        }

        // Position — ensure menu stays within viewport
        menuEl.style.display = 'block';
        const menuRect = menuEl.getBoundingClientRect();
        const viewW = window.innerWidth;
        const viewH = window.innerHeight;

        let finalX = x;
        let finalY = y;

        if (x + menuRect.width > viewW - 8) finalX = viewW - menuRect.width - 8;
        if (y + menuRect.height > viewH - 8) finalY = viewH - menuRect.height - 8;
        if (finalX < 8) finalX = 8;
        if (finalY < 8) finalY = 8;

        menuEl.style.left = `${finalX}px`;
        menuEl.style.top = `${finalY}px`;
    };

    const hideMenu = () => {
        if (menuEl) menuEl.style.display = 'none';
    };

    const executeAction = (action) => {
        if (!targetComponent && action !== 'paste') return;

        switch (action) {
            case 'selectParent': {
                const parent = targetComponent.parent();
                if (parent && parent !== editor.getWrapper()) {
                    editor.select(parent);
                }
                break;
            }

            case 'duplicate': {
                const parent = targetComponent.parent();
                if (parent) {
                    const idx = targetComponent.index();
                    const cloned = targetComponent.clone();
                    parent.append(cloned, { at: idx + 1 });
                    editor.select(cloned);
                }
                break;
            }

            case 'copy': {
                clipboard = {
                    html: targetComponent.toHTML(),
                };
                if (window.Toast) window.Toast.success('Copied');
                break;
            }

            case 'paste': {
                if (!clipboard?.html) return;
                const container = targetComponent || editor.getWrapper();
                const added = container.append(clipboard.html);
                if (added?.length > 0) editor.select(added[added.length - 1]);
                break;
            }

            case 'moveUp': {
                const parent = targetComponent.parent();
                if (!parent) return;
                const idx = targetComponent.index();
                if (idx > 0) {
                    targetComponent.move(parent, { at: idx - 1 });
                    editor.select(targetComponent);
                }
                break;
            }

            case 'moveDown': {
                const parent = targetComponent.parent();
                if (!parent) return;
                const idx = targetComponent.index();
                const total = parent.components().length;
                if (idx < total - 1) {
                    targetComponent.move(parent, { at: idx + 2 });
                    editor.select(targetComponent);
                }
                break;
            }

            case 'saveAsBlock': {
                const html = targetComponent.toHTML();
                const tagName = (targetComponent.get('tagName') || 'div').toLowerCase();
                const id = targetComponent.getId?.() || `saved-${Date.now()}`;
                const label = targetComponent.getName?.() || tagName || 'Saved Block';

                editor.BlockManager.add(`saved-${Date.now()}`, {
                    label: `📦 ${label}`,
                    category: 'Saved',
                    content: html,
                    attributes: { class: 'fa fa-bookmark' },
                });

                if (window.Toast) window.Toast.success(`"${label}" saved as block`);
                break;
            }

            case 'delete': {
                const parent = targetComponent.parent();
                if (parent) {
                    targetComponent.remove();
                }
                break;
            }
        }
    };

    // --- Event Binding ---

    // Listen for right-click on the canvas iframe
    const attachContextMenu = () => {
        try {
            const canvasDoc = editor.Canvas.getDocument();
            if (!canvasDoc) return;

            canvasDoc.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // Find the GrapesJS component at this point
                const el = e.target;
                const cmp = editor.getSelected();

                // If user right-clicked on the selected component or one of its children, use it
                // Otherwise, try to find the component from the DOM element
                if (cmp) {
                    targetComponent = cmp;
                } else {
                    // Walk up from clicked element to find a GrapesJS component
                    let current = el;
                    while (current && current !== canvasDoc.body) {
                        const found = editor.Components?.getById?.(current.id);
                        if (found) {
                            targetComponent = found;
                            editor.select(found);
                            break;
                        }
                        current = current.parentElement;
                    }
                }

                if (!targetComponent) return;

                // Convert iframe coordinates to main window coordinates
                const frameEl = editor.Canvas.getFrameEl();
                const frameRect = frameEl.getBoundingClientRect();
                const x = e.clientX + frameRect.left;
                const y = e.clientY + frameRect.top;

                showMenu(x, y);
            });
        } catch (err) {
            console.warn('[GrapesJS] Context menu attach failed:', err);
        }
    };

    // Hide menu on click anywhere
    document.addEventListener('click', hideMenu);
    document.addEventListener('contextmenu', (e) => {
        // Hide when right-clicking outside canvas
        if (!menuEl?.contains(e.target)) {
            hideMenu();
        }
    });

    // Hide on scroll / canvas interaction
    editor.on('component:selected', hideMenu);
    editor.on('canvas:frame:load', attachContextMenu);
    editor.on('load', attachContextMenu);

    // Cleanup
    editor.on('destroy', () => {
        document.removeEventListener('click', hideMenu);
        if (menuEl?.parentNode) menuEl.parentNode.removeChild(menuEl);
    });

    console.log('[GrapesJS] Context Menu plugin loaded.');
}
