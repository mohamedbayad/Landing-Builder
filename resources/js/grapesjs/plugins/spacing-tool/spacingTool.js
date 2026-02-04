
export default function spacingTool(editor, opts = {}) {
    console.log('Spacing Tool Plugin Initialized');
    let isEnabled = false;
    let overlay = null;
    let dragData = null;
    let selectedComponent = null;

    // CSS for the overlay (Injected directly to ensure it works inside iframe)
    const overlayCss = `
        .gjs-spacing-overlay {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1000;
        }
        .gjs-spacing-handle {
            position: absolute;
            width: 10px;
            height: 10px;
            background-color: #3b82f6;
            border: 1px solid #fff;
            border-radius: 50%;
            pointer-events: auto;
            cursor: grab;
            transform: translate(-50%, -50%);
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
            z-index: 1001;
            transition: transform 0.1s;
        }
        .gjs-spacing-handle:hover {
            transform: translate(-50%, -50%) scale(1.2);
            background-color: #2563eb;
        }
        .gjs-spacing-handle:active {
            cursor: grabbing;
            background-color: #1d4ed8;
        }
        .gjs-handle-margin-top, .gjs-handle-padding-top { cursor: ns-resize; }
        .gjs-handle-margin-bottom, .gjs-handle-padding-bottom { cursor: ns-resize; }
        .gjs-handle-margin-left, .gjs-handle-padding-left { cursor: ew-resize; }
        .gjs-handle-margin-right, .gjs-handle-padding-right { cursor: ew-resize; }
        .gjs-spacing-handle.gjs-handle-margin-top { background-color: #f59e0b; }
        .gjs-spacing-handle.gjs-handle-margin-right { background-color: #f59e0b; }
        .gjs-spacing-handle.gjs-handle-margin-bottom { background-color: #f59e0b; }
        .gjs-spacing-handle.gjs-handle-margin-left { background-color: #f59e0b; }
        .gjs-spacing-value-label {
            position: absolute;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-family: sans-serif;
            pointer-events: none;
            white-space: nowrap;
            transform: translate(-50%, -130%);
            z-index: 1002;
            display: none;
        }
        .gjs-spacing-handle:hover .gjs-spacing-value-label,
        .gjs-spacing-handle:active .gjs-spacing-value-label {
            display: block;
        }
    `;

    // Configuration
    const config = {
        paddingColor: '#3b82f6', // blue
        marginColor: '#f59e0b',  // amber
        ...opts
    };

    // Helper: Parse px value
    const getStyleNum = (comp, prop) => {
        const style = comp.getStyle();
        const val = style[prop];
        // Handle "10px", "5%", "auto", or undefined
        if (!val || val === 'auto') return 0;
        return parseFloat(val) || 0;
    };

    // Helper: Set style px
    const setStyleNum = (comp, prop, val) => {
        // Ensure non-negative
        const safeVal = Math.max(0, val);
        comp.addStyle({ [prop]: `${safeVal}px` });
    };

    // Create Overlay Elements
    const createOverlay = () => {
        const canvas = editor.Canvas;
        const frameEl = canvas.getFrameEl();
        const doc = frameEl.contentDocument;
        if (!doc) return;

        // Container
        const container = doc.createElement('div');
        container.className = 'gjs-spacing-overlay';
        doc.body.appendChild(container);

        // Inject CSS
        const styleId = 'gjs-spacing-tool-style';
        if (!doc.getElementById(styleId)) {
            const styleEl = doc.createElement('style');
            styleEl.id = styleId;
            styleEl.innerHTML = overlayCss;
            doc.head.appendChild(styleEl);
        }

        // Handles Definition
        // type: 'padding' | 'margin'
        // side: 'top' | 'right' | 'bottom' | 'left'
        const handles = [
            { type: 'padding', side: 'top' },
            { type: 'padding', side: 'right' },
            { type: 'padding', side: 'bottom' },
            { type: 'padding', side: 'left' },
            { type: 'margin', side: 'top' },
            { type: 'margin', side: 'right' },
            { type: 'margin', side: 'bottom' },
            { type: 'margin', side: 'left' }
        ];

        handles.forEach(h => {
            const el = doc.createElement('div');
            el.className = `gjs-spacing-handle gjs-handle-${h.type}-${h.side}`;
            el.dataset.type = h.type;
            el.dataset.side = h.side;

            // Tooltip label
            const label = doc.createElement('div');
            label.className = 'gjs-spacing-value-label';
            label.innerText = '0px';
            el.appendChild(label);

            // Events
            el.addEventListener('mousedown', (e) => onDragStart(e, h));
            // Prevent default drag
            el.addEventListener('dragstart', (e) => e.preventDefault());

            container.appendChild(el);
        });

        overlay = container;
        updateOverlayPosition();
    };

    const removeOverlay = () => {
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
        overlay = null;
    };

    // Update positions of handles based on selected component
    const updateOverlayPosition = () => {
        if (!isEnabled || !selectedComponent || !overlay) return;

        const el = selectedComponent.getEl();
        if (!el) return;

        const rect = el.getBoundingClientRect();
        const doc = editor.Canvas.getFrameEl().contentDocument;
        const handles = overlay.querySelectorAll('.gjs-spacing-handle');

        // Scroll offsets inside iframe
        const scrollTop = doc.documentElement.scrollTop || doc.body.scrollTop;
        const scrollLeft = doc.documentElement.scrollLeft || doc.body.scrollLeft;

        // We position handles based on rect + specific offsets
        // Padding: Inside edges
        // Margin: Outside edges (approximate, usually center of the margin area or just outside border)

        // Simple positioning strategy:
        // Padding Top: Center X, Top Edge + Padding Top / 2 (or just Top Edge + 10px)
        // Margin Top: Center X, Top Edge - 10px

        // Refined:
        // Padding handles sit *inside* the border box. 
        // Margin handles sit *outside* the border box.

        handlePositioning(handles, rect, scrollTop, scrollLeft);
    };

    const handlePositioning = (handles, rect, scrollTop, scrollLeft) => {
        handles.forEach(handle => {
            const type = handle.dataset.type;
            const side = handle.dataset.side;
            const label = handle.querySelector('.gjs-spacing-value-label');

            let top = 0;
            let left = 0;

            const OFFSET = 12; // Distance from edge

            // Base coordinates (Top-Left of element in document)
            const elTop = rect.top + scrollTop;
            const elLeft = rect.left + scrollLeft;
            const elRight = elLeft + rect.width;
            const elBottom = elTop + rect.height;
            const centerX = elLeft + (rect.width / 2);
            const centerY = elTop + (rect.height / 2);

            if (type === 'padding') {
                if (side === 'top') {
                    top = elTop + OFFSET;
                    left = centerX;
                } else if (side === 'bottom') {
                    top = elBottom - OFFSET;
                    left = centerX;
                } else if (side === 'left') {
                    top = centerY;
                    left = elLeft + OFFSET;
                } else if (side === 'right') {
                    top = centerY;
                    left = elRight - OFFSET;
                }
            } else if (type === 'margin') {
                if (side === 'top') {
                    top = elTop - OFFSET;
                    left = centerX;
                } else if (side === 'bottom') {
                    top = elBottom + OFFSET;
                    left = centerX;
                } else if (side === 'left') {
                    top = centerY;
                    left = elLeft - OFFSET;
                } else if (side === 'right') {
                    top = centerY;
                    left = elRight + OFFSET;
                }
            }

            handle.style.top = `${top}px`;
            handle.style.left = `${left}px`;

            // Update Label Value
            const prop = `${type}-${side}`; // e.g. padding-top
            const val = getStyleNum(selectedComponent, prop);
            if (label) label.innerText = `${Math.round(val)}px`;
        });
    };

    // Drag Logic
    const onDragStart = (e, handleDef) => {
        if (!selectedComponent) return;
        e.stopPropagation(); // Stop GrapesJS from selecting something else
        e.preventDefault();

        const prop = `${handleDef.type}-${handleDef.side}`;
        const startVal = getStyleNum(selectedComponent, prop);

        dragData = {
            ...handleDef,
            prop,
            startVal,
            startX: e.clientX,
            startY: e.clientY
        };

        const frameDoc = editor.Canvas.getFrameEl().contentDocument;
        frameDoc.addEventListener('mousemove', onDragMove);
        frameDoc.addEventListener('mouseup', onDragEnd);
        document.addEventListener('mouseup', onDragEnd); // Catch release outside iframe
    };

    const onDragMove = (e) => {
        if (!dragData || !selectedComponent) return;
        e.preventDefault();
        e.stopPropagation();

        const dx = e.clientX - dragData.startX;
        const dy = e.clientY - dragData.startY;

        let delta = 0;

        // Calculate delta based on side
        // Moving right (dx > 0) increases Right / decreases Left?
        // Let's standardise: Pulling OUT increases value. Pushing IN decreases.

        // Padding: Pulling IN (towards center) increases padding? No, standard resizing logic usually implies:
        // Top Handle: Drag Up (negative dy) = Increase Padding? Or Drag Down (positive dy) = Increase Padding?
        // Visual Logic: Padding pushes content inward.
        // Let's use simple coordinate logic:
        // Top: -dy (Up increases)
        // Bottom: dy (Down increases)
        // Left: -dx (Left increases)
        // Right: dx (Right increases)

        // Wait, for MARGIN (Outside):
        // Top: -dy (Up increases margin, pushing element down/away)
        // Bottom: dy (Down increases margin)
        // Left: -dx (Left increases)
        // Right: dx (Right increases)

        // For PADDING (Inside):
        // Top: dy (Down increases padding top, pushing content down)
        // Bottom: -dy (Up increases padding bottom)
        // Left: dx (Right increases padding left)
        // Right: -dx (Left increases padding right)

        // Actually, let's keep it intuitive:
        // Dragging handle AWAY from element center increases value.
        // Top Handle (y < centerY): Drag Up (-dy) increases.
        // Bottom Handle (y > centerY): Drag Down (+dy) increases.
        // Left Handle (x < centerX): Drag Left (-dx) increases.
        // Right Handle (x > centerX): Drag Right (+dx) increases.

        // This applies SAME for both Margin and Padding with the handle placement I chose.
        // (Padding handles are inside, Margin outside, but relative direction is same)

        const { side } = dragData;

        if (side === 'top') delta = -dy;
        if (side === 'bottom') delta = dy;
        if (side === 'left') delta = -dx;
        if (side === 'right') delta = dx;

        // Shift Modifier (Precision)
        if (e.shiftKey) delta *= 0.25;

        // Alt Modifier (Step 4px) - Optional
        // if (e.altKey) delta = Math.round(delta / 4) * 4;

        const newVal = Math.max(0, dragData.startVal + delta);

        setStyleNum(selectedComponent, dragData.prop, newVal);

        // Force update overlay so handle follows the new style (especially margin)
        // Note: component style update triggers 'component:styleUpdate' which we listen to?
        // Or we just update manually here to be smooth.
        updateOverlayPosition();
    };

    const onDragEnd = (e) => {
        if (!dragData) return;
        dragData = null;

        const frameDoc = editor.Canvas.getFrameEl().contentDocument;
        frameDoc.removeEventListener('mousemove', onDragMove);
        frameDoc.removeEventListener('mouseup', onDragEnd);
        document.removeEventListener('mouseup', onDragEnd);
    };

    // Toggle Logic
    const enable = () => {
        console.log('Spacing Tool: Enabled');
        isEnabled = true;
        selectedComponent = editor.getSelected();
        if (selectedComponent) {
            createOverlay();
        } else {
            console.log('Spacing Tool: No component selected to show overlay');
        }
    };

    const disable = () => {
        console.log('Spacing Tool: Disabled');
        isEnabled = false;
        removeOverlay();
    };

    // Commands
    editor.Commands.add('spacing-tool:toggle', {
        run(editor) {
            enable();
        },
        stop(editor) {
            disable();
        }
    });

    // Listeners
    editor.on('component:selected', (comp) => {
        selectedComponent = comp;
        if (isEnabled) {
            removeOverlay(); // Re-create for new element
            createOverlay();
        }
    });

    editor.on('component:deselected', () => {
        selectedComponent = null;
        if (isEnabled) {
            removeOverlay();
        }
    });

    editor.on('component:styleUpdate', () => {
        if (isEnabled && selectedComponent) {
            // Use rAF to throttle updates if needed
            requestAnimationFrame(updateOverlayPosition);
        }
    });

    // Scroll/Resize listener on iframe window
    editor.on('load', () => {
        const win = editor.Canvas.getWindow();
        win.addEventListener('scroll', updateOverlayPosition);
        win.addEventListener('resize', updateOverlayPosition);
    });

    // Add Button to Panel
    const panelId = opts.panelId || 'pnael-options'; // Default to options panel if set? No, let's use 'options' usually.
    // Actually, in our editor.js we destroyed default panels. We have specific divs.
    // We should add a button to our custom toolbar or just use the Command.
    // The requirement says "Add a toggle button in the GrapesJS panel".
    // Since we have a custom UI (Tailwind), we probably need to inject a button into our specific DOM panel or use the API if we configured panels.
    // In editor.js we saw we cleared defaults.
    // Let's assume we register the command, and in editor.js we manually add the button to our custom sidebar if needed, 
    // OR we use the Panels API if we re-enabled them. 
    // BUT the user prompt 7.1 "pluginsOpts" suggests we might use Panels API.
    // Let's implement generic Panel button addition if panel exists.

    if (opts.panelId) {
        editor.Panels.addButton(opts.panelId, {
            id: opts.buttonId || 'spacing-tool-btn',
            className: 'fa fa-arrows-alt', // Example icon
            command: 'spacing-tool:toggle',
            active: false,
            togglable: true,
            attributes: { title: 'Spacing Tool' }
        });
    }
}
