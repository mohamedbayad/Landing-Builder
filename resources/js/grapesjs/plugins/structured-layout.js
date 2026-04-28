/**
 * Structured Layout Plugin
 *
 * Introduces a predictable page hierarchy:
 * Section -> Row -> Column -> Element
 *
 * Goals:
 * - Provide dedicated structural blocks
 * - Keep root clean by auto-wrapping root-level element drops
 * - Add visual cues for structured containers in canvas
 */
export default function structuredLayoutPlugin(editor) {
    const { Components, BlockManager } = editor;

    const TYPE_SECTION = 'lb-section';
    const TYPE_ROW = 'lb-row';
    const TYPE_COLUMN = 'lb-column';
    const ROLE_ATTR = 'data-lb-role';

    let internalMove = false;
    let hasShownWrapHint = false;
    let isHydrating = true;

    const isWrapper = (cmp) => cmp && cmp.get && cmp.get('type') === 'wrapper';
    const isSectionLike = (cmp) => {
        if (!cmp || !cmp.get) return false;
        const type = cmp.get('type');
        if (type === TYPE_SECTION) return true;

        const attrs = cmp.getAttributes?.() || {};
        if (attrs[ROLE_ATTR] === 'section') return true;

        const tag = String(cmp.get('tagName') || '').toLowerCase();
        return tag === 'section';
    };

    const createStructuredSection = () => ({
        type: TYPE_SECTION,
        components: [
            {
                type: TYPE_ROW,
                components: [
                    {
                        type: TYPE_COLUMN,
                    },
                ],
            },
        ],
    });

    const getFirstColumn = (sectionCmp) => {
        const row = sectionCmp?.components?.()?.at?.(0);
        return row?.components?.()?.at?.(0);
    };

    const getFirstRow = (sectionCmp) => sectionCmp?.components?.()?.at?.(0);

    const isSkippableRootComponent = (cmp) => {
        if (!cmp || !cmp.get) return true;

        const type = String(cmp.get('type') || '').toLowerCase();
        const tag = String(cmp.get('tagName') || '').toLowerCase();

        const nonVisualTypes = new Set(['wrapper', 'textnode', 'comment', 'script', 'svg', 'svg-in']);
        const nonVisualTags = new Set(['script', 'style', 'link', 'meta']);

        return nonVisualTypes.has(type) || nonVisualTags.has(tag);
    };

    Components.addType(TYPE_SECTION, {
        isComponent: (el) => {
            if (el?.getAttribute?.(ROLE_ATTR) === 'section') {
                return { type: TYPE_SECTION };
            }

            return false;
        },
        model: {
            defaults: {
                name: 'Section',
                tagName: 'section',
                attributes: {
                    [ROLE_ATTR]: 'section',
                    class: 'lb-section',
                },
                draggable: '[data-gjs-type="wrapper"]',
                droppable: `[data-gjs-type="${TYPE_ROW}"]`,
                stylable: true,
                classes: ['lb-section'],
            },
        },
    });

    Components.addType(TYPE_ROW, {
        isComponent: (el) => {
            if (el?.getAttribute?.(ROLE_ATTR) === 'row') {
                return { type: TYPE_ROW };
            }

            return false;
        },
        model: {
            defaults: {
                name: 'Row',
                tagName: 'div',
                attributes: {
                    [ROLE_ATTR]: 'row',
                    class: 'lb-row',
                },
                draggable: `[data-gjs-type="${TYPE_SECTION}"]`,
                droppable: `[data-gjs-type="${TYPE_COLUMN}"]`,
                stylable: true,
                classes: ['lb-row'],
                style: {
                    display: 'flex',
                    'flex-wrap': 'wrap',
                    gap: '20px',
                    'align-items': 'stretch',
                },
            },
        },
    });

    Components.addType(TYPE_COLUMN, {
        isComponent: (el) => {
            if (el?.getAttribute?.(ROLE_ATTR) === 'column') {
                return { type: TYPE_COLUMN };
            }

            return false;
        },
        model: {
            defaults: {
                name: 'Column',
                tagName: 'div',
                attributes: {
                    [ROLE_ATTR]: 'column',
                    class: 'lb-column',
                },
                draggable: `[data-gjs-type="${TYPE_ROW}"]`,
                droppable: true,
                stylable: true,
                classes: ['lb-column'],
                style: {
                    'min-height': '80px',
                    flex: '1 1 0%',
                },
            },
        },
    });

    const addBlock = (id, config) => {
        if (BlockManager.get(id)) {
            BlockManager.remove(id);
        }
        BlockManager.add(id, config);
    };

    addBlock('tw-layout-section', {
        label: 'Section',
        category: 'Layout Structure',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="2" y="2" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"></rect>
            <line x1="2" y1="8" x2="22" y2="8" stroke="currentColor" stroke-width="1"></line>
        </svg>`,
        content: {
            type: TYPE_SECTION,
            attributes: {
                [ROLE_ATTR]: 'section',
                class: 'lb-section py-12 px-6 min-h-[200px]',
            },
            components: [
                {
                    type: TYPE_ROW,
                    attributes: {
                        [ROLE_ATTR]: 'row',
                        class: 'lb-row mx-auto w-full max-w-7xl',
                    },
                    components: [
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column w-full p-6',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">Section content here</p>' }],
                        },
                    ],
                },
            ],
        },
    });

    addBlock('tw-layout-1-column', {
        label: '1 Column',
        category: 'Layout Structure',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="4" y="4" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"></rect>
        </svg>`,
        content: {
            type: TYPE_SECTION,
            attributes: {
                [ROLE_ATTR]: 'section',
                class: 'lb-section py-6 px-4',
            },
            components: [
                {
                    type: TYPE_ROW,
                    attributes: {
                        [ROLE_ATTR]: 'row',
                        class: 'lb-row flex flex-wrap w-full',
                    },
                    components: [
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column w-full p-6',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">Single column content</p>' }],
                        },
                    ],
                },
            ],
        },
    });

    addBlock('tw-layout-2-columns', {
        label: '2 Columns',
        category: 'Layout Structure',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="2" y="4" width="9" height="16" fill="none" stroke="currentColor" stroke-width="2"></rect>
            <rect x="13" y="4" width="9" height="16" fill="none" stroke="currentColor" stroke-width="2"></rect>
        </svg>`,
        content: {
            type: TYPE_SECTION,
            attributes: {
                [ROLE_ATTR]: 'section',
                class: 'lb-section py-6 px-4',
            },
            components: [
                {
                    type: TYPE_ROW,
                    attributes: {
                        [ROLE_ATTR]: 'row',
                        class: 'lb-row flex flex-wrap w-full gap-4',
                    },
                    components: [
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column flex-1 min-w-[250px] p-6 bg-gray-50',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">Column 1</p>' }],
                        },
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column flex-1 min-w-[250px] p-6 bg-gray-50',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">Column 2</p>' }],
                        },
                    ],
                },
            ],
        },
    });

    addBlock('tw-layout-3-columns', {
        label: '3 Columns',
        category: 'Layout Structure',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="2" y="4" width="5" height="16" fill="none" stroke="currentColor" stroke-width="1.5"></rect>
            <rect x="9" y="4" width="5" height="16" fill="none" stroke="currentColor" stroke-width="1.5"></rect>
            <rect x="16" y="4" width="5" height="16" fill="none" stroke="currentColor" stroke-width="1.5"></rect>
        </svg>`,
        content: {
            type: TYPE_SECTION,
            attributes: {
                [ROLE_ATTR]: 'section',
                class: 'lb-section py-6 px-4',
            },
            components: [
                {
                    type: TYPE_ROW,
                    attributes: {
                        [ROLE_ATTR]: 'row',
                        class: 'lb-row flex flex-wrap w-full gap-4',
                    },
                    components: [
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column flex-1 min-w-[200px] p-6 bg-gray-50',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">Column 1</p>' }],
                        },
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column flex-1 min-w-[200px] p-6 bg-gray-50',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">Column 2</p>' }],
                        },
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column flex-1 min-w-[200px] p-6 bg-gray-50',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">Column 3</p>' }],
                        },
                    ],
                },
            ],
        },
    });

    addBlock('tw-layout-3-7-columns', {
        label: '3/7 Columns',
        category: 'Layout Structure',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="2" y="4" width="6" height="16" fill="none" stroke="currentColor" stroke-width="2"></rect>
            <rect x="10" y="4" width="12" height="16" fill="none" stroke="currentColor" stroke-width="2"></rect>
        </svg>`,
        content: {
            type: TYPE_SECTION,
            attributes: {
                [ROLE_ATTR]: 'section',
                class: 'lb-section py-6 px-4',
            },
            components: [
                {
                    type: TYPE_ROW,
                    attributes: {
                        [ROLE_ATTR]: 'row',
                        class: 'lb-row flex flex-wrap w-full gap-4',
                    },
                    components: [
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column w-full p-6 bg-gray-50 md:basis-[30%] md:flex-none',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">30% Column</p>' }],
                        },
                        {
                            type: TYPE_COLUMN,
                            attributes: {
                                [ROLE_ATTR]: 'column',
                                class: 'lb-column w-full p-6 bg-gray-50 md:basis-[70%] md:flex-none',
                            },
                            components: [{ type: 'text', content: '<p class="text-gray-700">70% Column</p>' }],
                        },
                    ],
                },
            ],
        },
    });

    addBlock('tw-layout-row', {
        label: 'Row',
        category: 'Layout Structure',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <rect x="2" y="8" width="20" height="8" fill="none" stroke="currentColor" stroke-width="2"></rect>
            <line x1="2" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="1" stroke-dasharray="2,2"></line>
        </svg>`,
        content: {
            type: TYPE_ROW,
            attributes: {
                [ROLE_ATTR]: 'row',
                class: 'lb-row flex flex-wrap w-full gap-4 p-4 border-2 border-dashed border-gray-300 min-h-[100px]',
            },
            components: [
                {
                    type: TYPE_COLUMN,
                    attributes: {
                        [ROLE_ATTR]: 'column',
                        class: 'lb-column w-full p-6',
                    },
                    components: [{ type: 'text', content: '<p class="text-gray-700">Drop columns here</p>' }],
                },
            ],
        },
    });

    addBlock('tw-layout-divider', {
        label: 'Divider',
        category: 'Layout Structure',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <line x1="2" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="2"></line>
        </svg>`,
        content: '<hr class="my-8 border-t-2 border-gray-200" />',
    });

    addBlock('tw-layout-spacer', {
        label: 'Spacer',
        category: 'Layout Structure',
        media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
            <line x1="12" y1="2" x2="12" y2="22" stroke="currentColor" stroke-width="2" stroke-dasharray="4,4"></line>
            <path d="M12 2l-2 3h4z" fill="currentColor"></path>
            <path d="M12 22l-2-3h4z" fill="currentColor"></path>
        </svg>`,
        content: '<div class="h-16"></div>',
    });

    const ensureBlocksPanelStyles = () => {
        if (document.getElementById('funnel-tailwind-blocks-style')) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'funnel-tailwind-blocks-style';
        style.textContent = `
            .gjs-block {
                min-height: 102px;
                padding: 10px;
                border-radius: 8px;
                border: 1px solid var(--gjs-main-dark-color);
                background: rgba(255, 255, 255, 0.04);
                color: var(--gjs-font-color);
                transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
            }
            .gjs-block:hover {
                background: rgba(255, 255, 255, 0.08);
                border-color: var(--gjs-color-highlight);
                transform: translateY(-1px);
            }
            .gjs-block-label {
                font-size: 12px;
                font-weight: 600;
                color: var(--gjs-font-color);
                margin-top: 10px;
                line-height: 1.25;
                text-align: center;
            }
            .gjs-block svg {
                width: 100%;
                height: 48px;
                max-width: 64px;
                margin: 0 auto 2px;
                color: var(--gjs-secondary-light-color);
            }
            .gjs-block svg [stroke] {
                stroke: currentColor;
            }
            .gjs-block svg [fill="currentColor"] {
                fill: currentColor;
            }
            .gjs-block-category {
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid var(--gjs-main-dark-color);
                margin-bottom: 8px;
            }
            .gjs-block-category .gjs-title {
                background-color: var(--gjs-primary-color) !important;
                color: var(--gjs-font-color) !important;
                font-weight: 600;
                padding: 10px 12px 10px 18px;
                border-radius: 0;
                border-bottom: 1px solid var(--gjs-main-dark-color);
            }
            .gjs-block-category.gjs-open .gjs-title {
                background-color: var(--gjs-primary-color) !important;
            }
            .gjs-block-category .gjs-caret-icon {
                color: var(--gjs-font-color);
            }
        `;

        document.head.appendChild(style);
    };

    ensureBlocksPanelStyles();

    const injectCanvasStyles = () => {
        const frameDoc = editor.Canvas.getDocument();
        if (!frameDoc || frameDoc.getElementById('lb-structured-layout-style')) return;

        const style = frameDoc.createElement('style');
        style.id = 'lb-structured-layout-style';
        style.textContent = `
            [data-lb-role="section"] {
                position: relative;
                outline: 1px dashed rgba(59, 130, 246, 0.32);
                outline-offset: -1px;
            }
            [data-lb-role="row"] {
                position: relative;
                outline: 1px dashed rgba(16, 185, 129, 0.28);
                outline-offset: -1px;
            }
            [data-lb-role="column"] {
                position: relative;
                min-height: 80px;
                outline: 1px dashed rgba(249, 115, 22, 0.32);
                outline-offset: -1px;
            }
            [data-lb-role="section"]::before,
            [data-lb-role="row"]::before,
            [data-lb-role="column"]::before {
                position: absolute;
                top: 6px;
                right: 8px;
                z-index: 2;
                padding: 2px 6px;
                border-radius: 999px;
                font-family: Inter, sans-serif;
                font-size: 10px;
                line-height: 1;
                letter-spacing: 0.02em;
                pointer-events: none;
                color: #0f172a;
                background: rgba(255, 255, 255, 0.72);
            }
            [data-lb-role="section"]::before { content: "Section"; }
            [data-lb-role="row"]::before { content: "Row"; }
            [data-lb-role="column"]::before { content: "Column"; }

            .gjs-placeholder {
                border: 2px dashed #3b82f6 !important;
                background: rgba(59, 130, 246, 0.14) !important;
                box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5) inset !important;
            }
        `;

        frameDoc.head.appendChild(style);
    };

    editor.on('canvas:frame:load', injectCanvasStyles);
    editor.on('load', () => {
        isHydrating = false;
        injectCanvasStyles();
    });

    editor.on('component:add', (component) => {
        if (internalMove) return;
        if (isHydrating) return;

        const parent = component?.parent?.();
        if (!isWrapper(parent)) return;
        if (isSkippableRootComponent(component)) return;
        if (isSectionLike(component)) return;

        internalMove = true;

        try {
            const insertAt = component.index();
            const section = parent.components().add(createStructuredSection(), { at: insertAt })[0];
            const placeholderRow = getFirstRow(section);
            const placeholderColumn = getFirstColumn(section);

            if (component.get('type') === TYPE_ROW && placeholderRow) {
                component.move(section, { at: 0 });
                placeholderRow.remove();
            } else if (component.get('type') === TYPE_COLUMN && placeholderRow) {
                component.move(placeholderRow, { at: 0 });

                if (placeholderColumn && placeholderColumn.components().length === 0) {
                    placeholderColumn.remove();
                }
            } else if (placeholderColumn) {
                component.move(placeholderColumn, { at: 0 });
            }

            if (!hasShownWrapHint) {
                hasShownWrapHint = true;
                window.Toast?.success?.('Element wrapped into Section > Row > Column automatically.');
            }
        } finally {
            internalMove = false;
        }
    });

    console.log('[GrapesJS] Structured Layout plugin loaded.');
}
