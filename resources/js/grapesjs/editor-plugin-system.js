import countdownPlugin from './countdown-plugin';
import conversionBlocksPlugin from './plugins/conversion-blocks';
import deviceVisibilityPlugin from './plugins/device-visibility';
import exitIntentPlugin from './plugins/exit-intent';
import htmlBlockPlugin from './plugins/html-block';
import keyboardShortcutsPlugin from './plugins/keyboard-shortcuts';
import lpSliderPlugin from './plugins/lp-slider';
import structuredLayoutPlugin from './plugins/structured-layout';
import advancedStyleManagerPlugin from './plugins/advanced-style-manager';
import backgroundPlugin from './plugins/background';
import tailwindCardsBlocksPlugin from './plugins/tailwind-cards-blocks';
import tailwindAutocompletePlugin from './plugins/tailwind-autocomplete';
import installLpBuilderWorkspacePlugin from './plugins/lp-builder';

const SYSTEM_KEY = '__funnel_builder_plugin_system';
const SYSTEM_VERSION = '1.0.0';
const WORKSPACE_PLUGIN_TAILWIND = 'tailwind-css';
const WORKSPACE_PLUGIN_TAILWIND_AUTOCOMPLETE = 'tailwind-classes-autocomplete';
const WORKSPACE_PLUGIN_MATERIAL_ICONS = 'google-material-icons';
const WORKSPACE_PLUGIN_TAILWIND_CARDS = 'tailwind-cards';
const WORKSPACE_PLUGIN_LP_BUILDER = 'grapesjs-lp-builder';
const MODE_EDITOR = 'editor';
const MODE_PREVIEW = 'preview';
const MODE_PUBLISHED = 'published';
const VALID_MODES = new Set([MODE_EDITOR, MODE_PREVIEW, MODE_PUBLISHED]);
const TAILWIND_SCRIPT_ID = 'funnel-editor-tailwind-cdn';
const TAILWIND_CONFIG_ID = 'funnel-editor-tailwind-config';
const TAILWIND_HELPER_STYLE_ID = 'funnel-editor-tailwind-helper-style';
const TAILWIND_CARDS_CANVAS_STYLE_ID = 'funnel-editor-tailwind-cards-canvas-style';
const MATERIAL_ICONS_LINK_ID = 'funnel-editor-material-icons-font';
const MATERIAL_ICONS_HELPER_STYLE_ID = 'funnel-editor-material-icons-style';
const DEFAULT_STYLE_PROPS = [
    'margin',
    'padding',
    'color',
    'background',
    'border',
    'border-radius',
    'box-shadow',
    'typography',
];

const DEFAULT_PLUGIN_DEFINITION = {
    icon: 'component',
    renderStrategy: {
        editor: 'native',
        preview: 'native',
        published: 'native',
    },
    exportStrategy: 'html-css-project',
    editableProps: [],
    styleProps: DEFAULT_STYLE_PROPS,
    actions: [],
    allowedChildren: [],
    settingsSchema: {},
    defaultContent: {},
    defaultStyle: {},
    phase: 3,
    implementationStatus: 'planned',
    ai: {
        safe: true,
        allowedAttributes: ['class', 'style', 'data-*', 'aria-*'],
    },
};

const definePlugin = (definition) => ({
    ...DEFAULT_PLUGIN_DEFINITION,
    ...definition,
    settingsSchema: definition.settingsSchema || {},
    defaultContent: definition.defaultContent || {},
    defaultStyle: definition.defaultStyle || {},
    ai: {
        ...DEFAULT_PLUGIN_DEFINITION.ai,
        ...(definition.ai || {}),
    },
});

const EDITOR_PLUGIN_DEFINITIONS = [
    definePlugin({
        id: 'layout-structure',
        category: 'layout-structure',
        label: 'Layout Structure',
        icon: 'layout',
        componentTypes: ['lb-section', 'lb-row', 'lb-column', 'spacer', 'divider'],
        editableProps: ['width', 'spacing', 'alignment', 'stacking', 'device_visibility'],
        actions: ['drag', 'drop', 'reorder', 'nest'],
        allowedChildren: ['*'],
        settingsSchema: {
            width: 'string',
            spacing: 'object',
            alignment: 'string',
            stackOnMobile: 'boolean',
        },
        defaultContent: {
            type: 'lb-section',
        },
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'layout-structure',
    }),
    definePlugin({
        id: 'text-content',
        category: 'content',
        label: 'Text Content',
        icon: 'text',
        componentTypes: ['text', 'textnode', 'heading', 'paragraph', 'list'],
        editableProps: ['text', 'typography', 'alignment', 'spacing', 'responsive_size'],
        actions: ['inline-edit', 'format'],
        allowedChildren: ['*'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'text-content',
    }),
    definePlugin({
        id: 'button-cta',
        category: 'conversion',
        label: 'Button / CTA',
        icon: 'button',
        componentTypes: ['button', 'link', 'funnel-cta'],
        editableProps: ['label', 'size', 'alignment', 'full_width', 'icon', 'action'],
        actions: ['open_url', 'scroll_to_section', 'open_popup', 'next_step', 'submit_form', 'open_checkout', 'whatsapp', 'custom_action'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'button-cta',
    }),
    definePlugin({
        id: 'image',
        category: 'media',
        label: 'Image',
        icon: 'image',
        componentTypes: ['image'],
        editableProps: ['src', 'alt', 'caption', 'size', 'fit', 'radius', 'lazyload', 'device_visibility'],
        actions: ['replace_asset', 'open_asset_manager'],
        phase: 1,
        implementationStatus: 'native',
    }),
    definePlugin({
        id: 'video',
        category: 'media',
        label: 'Video',
        icon: 'video',
        componentTypes: ['video', 'iframe'],
        editableProps: ['source', 'autoplay', 'muted', 'poster', 'aspect_ratio', 'lazyload', 'popup_mode'],
        actions: ['replace_source'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'form',
        category: 'conversion',
        label: 'Form',
        icon: 'form',
        componentTypes: ['funnel-form', 'form', 'input', 'textarea', 'select', 'checkbox', 'radio'],
        editableProps: ['fields', 'validation', 'success_message', 'redirect_url', 'tags', 'webhook', 'automation_trigger'],
        actions: ['submit_form', 'send_webhook', 'trigger_automation'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'form',
    }),
    definePlugin({
        id: 'multi-step-form',
        category: 'conversion',
        label: 'Multi-Step Form',
        icon: 'steps',
        componentTypes: ['multi-step-form', 'step', 'progress'],
        editableProps: ['steps', 'conditions', 'progress_style', 'validation'],
        actions: ['next_step', 'previous_step', 'submit'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'countdown-timer',
        category: 'interaction',
        label: 'Countdown Timer',
        icon: 'timer',
        componentTypes: ['countdown-timer'],
        editableProps: ['mode', 'end_date', 'duration', 'timezone', 'expired_message', 'expire_redirect'],
        actions: ['expire_redirect', 'toggle_expired_state'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'countdown-timer',
    }),
    definePlugin({
        id: 'testimonials-social-proof',
        category: 'conversion',
        label: 'Testimonials / Social Proof',
        icon: 'quote',
        componentTypes: ['testimonial-card', 'testimonial-slider', 'rating-stars'],
        editableProps: ['name', 'title', 'avatar', 'rating', 'review', 'layout'],
        actions: ['reorder_cards', 'toggle_blur_identity'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'conversion-blocks',
    }),
    definePlugin({
        id: 'faq-accordion',
        category: 'content',
        label: 'FAQ / Accordion',
        icon: 'faq',
        componentTypes: ['details', 'summary', 'faq-item'],
        editableProps: ['question', 'answer', 'icon', 'default_open', 'spacing'],
        actions: ['expand', 'collapse'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'conversion-blocks',
    }),
    definePlugin({
        id: 'pricing-offer-box',
        category: 'conversion',
        label: 'Pricing / Offer Box',
        icon: 'pricing',
        componentTypes: ['pricing-card', 'offer-box'],
        editableProps: ['title', 'subtitle', 'price', 'old_price', 'benefits', 'badge', 'cta', 'guarantee_note'],
        actions: ['select_plan', 'open_checkout'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'conversion-blocks',
    }),
    definePlugin({
        id: 'product-highlight',
        category: 'conversion',
        label: 'Product Highlight',
        icon: 'product',
        componentTypes: ['product-highlight'],
        editableProps: ['image', 'title', 'description', 'features', 'layout', 'cta'],
        actions: ['open_checkout', 'scroll_to_offer'],
        phase: 2,
        implementationStatus: 'native',
        runtimeKey: 'conversion-blocks',
    }),
    definePlugin({
        id: 'popup',
        category: 'interaction',
        label: 'Popup',
        icon: 'popup',
        componentTypes: ['exit-intent-popup', 'modal', 'overlay'],
        editableProps: ['trigger', 'delay', 'animation', 'overlay', 'close_behavior', 'content'],
        actions: ['open_popup', 'close_popup'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'popup',
    }),
    definePlugin({
        id: 'slider-carousel',
        category: 'interaction',
        label: 'Slider / Carousel',
        icon: 'slider',
        componentTypes: ['lp-slider'],
        editableProps: ['slides', 'autoplay', 'loop', 'speed', 'arrows', 'dots', 'swipe', 'spacing'],
        actions: ['next_slide', 'previous_slide', 'go_to_slide'],
        phase: 2,
        implementationStatus: 'native',
        runtimeKey: 'slider-carousel',
    }),
    definePlugin({
        id: 'tabs',
        category: 'interaction',
        label: 'Tabs',
        icon: 'tabs',
        componentTypes: ['tabs', 'tab', 'tab-panel'],
        editableProps: ['labels', 'active_tab', 'layout'],
        actions: ['switch_tab'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'icon-badge-trust',
        category: 'conversion',
        label: 'Icon / Badge / Trust',
        icon: 'badge',
        componentTypes: ['icon', 'trust-badge', 'trust-bar'],
        editableProps: ['icon', 'label', 'variant', 'layout'],
        actions: ['set_badge_type'],
        phase: 2,
        implementationStatus: 'native',
        runtimeKey: 'conversion-blocks',
    }),
    definePlugin({
        id: 'checkout',
        category: 'commerce',
        label: 'Checkout',
        icon: 'checkout',
        componentTypes: ['checkout-form', 'order-summary', 'billing-fields'],
        editableProps: ['product_selection', 'billing_fields', 'payment_methods', 'coupon_field', 'trust_note'],
        actions: ['open_checkout', 'apply_coupon'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'order-bump',
        category: 'commerce',
        label: 'Order Bump',
        icon: 'order-bump',
        componentTypes: ['order-bump'],
        editableProps: ['title', 'price_delta', 'description', 'image', 'selected_by_default'],
        actions: ['toggle_offer'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'upsell-downsell',
        category: 'commerce',
        label: 'Upsell / Downsell',
        icon: 'upsell',
        componentTypes: ['upsell-offer', 'downsell-offer'],
        editableProps: ['title', 'price', 'accept_cta', 'reject_cta', 'urgency'],
        actions: ['accept_offer', 'reject_offer'],
        phase: 3,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'booking-calendar',
        category: 'conversion',
        label: 'Booking / Calendar',
        icon: 'calendar',
        componentTypes: ['booking-widget'],
        editableProps: ['timezone', 'confirmation_action', 'redirect_url'],
        actions: ['open_calendar', 'submit_booking'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'progress-bar',
        category: 'interaction',
        label: 'Progress Bar',
        icon: 'progress',
        componentTypes: ['progress-bar'],
        editableProps: ['percentage', 'step_count', 'label', 'variant'],
        actions: ['set_progress'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'navigation-anchor',
        category: 'navigation',
        label: 'Navigation / Anchor',
        icon: 'anchor',
        componentTypes: ['anchor-link', 'top-nav'],
        editableProps: ['target_id', 'sticky_behavior'],
        actions: ['scroll_to_anchor'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'sticky-bar',
        category: 'conversion',
        label: 'Sticky Bar',
        icon: 'sticky',
        componentTypes: ['sticky-bar', 'sticky-countdown', 'sticky-offer'],
        editableProps: ['position', 'device_visibility', 'content', 'style'],
        actions: ['show', 'hide'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'html-embed',
        category: 'utility',
        label: 'HTML Embed',
        icon: 'code',
        componentTypes: ['custom-html'],
        editableProps: ['html', 'iframe', 'sandbox_policy', 'warning_level'],
        actions: ['sanitize_html', 'preview_embed'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'html-embed',
        ai: {
            safe: false,
        },
    }),
    definePlugin({
        id: 'custom-code',
        category: 'utility',
        label: 'Custom Code',
        icon: 'code',
        componentTypes: ['custom-code'],
        editableProps: ['custom_css', 'custom_js', 'head_code', 'body_end_code'],
        actions: ['inject_code', 'toggle_isolation'],
        phase: 2,
        implementationStatus: 'planned',
        ai: {
            safe: false,
        },
    }),
    definePlugin({
        id: 'seo-page-meta',
        category: 'seo',
        label: 'SEO / Page Meta',
        icon: 'seo',
        componentTypes: ['page-meta'],
        editableProps: ['title', 'meta_description', 'og_title', 'og_description', 'og_image', 'favicon', 'canonical', 'schema', 'indexing'],
        actions: ['update_meta'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'seo-page-meta',
    }),
    definePlugin({
        id: 'visibility-conditional-display',
        category: 'responsive',
        label: 'Visibility / Conditional Display',
        icon: 'visibility',
        componentTypes: ['conditional-wrapper'],
        editableProps: ['device_visibility', 'rules', 'query_params'],
        actions: ['show_if', 'hide_if'],
        phase: 3,
        implementationStatus: 'native',
        runtimeKey: 'visibility-conditional-display',
    }),
    definePlugin({
        id: 'saved-blocks-reusable-sections',
        category: 'template',
        label: 'Saved Blocks / Reusable Sections',
        icon: 'blocks',
        componentTypes: ['saved-block'],
        editableProps: ['name', 'global', 'source'],
        actions: ['save_block', 'insert_block', 'update_source'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'saved-blocks-reusable-sections',
    }),
    definePlugin({
        id: 'template',
        category: 'template',
        label: 'Template',
        icon: 'template',
        componentTypes: ['page-template', 'section-template'],
        editableProps: ['category', 'preview'],
        actions: ['insert_template'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'template',
    }),
    definePlugin({
        id: 'asset-manager',
        category: 'utility',
        label: 'Asset Manager',
        icon: 'asset',
        componentTypes: ['asset'],
        editableProps: ['source', 'alt', 'folder', 'usage'],
        actions: ['upload_asset', 'replace_asset', 'delete_asset'],
        phase: 1,
        implementationStatus: 'native',
    }),
    definePlugin({
        id: 'responsive-editor',
        category: 'responsive',
        label: 'Responsive Editor',
        icon: 'responsive',
        componentTypes: ['responsive-controls'],
        editableProps: ['desktop', 'tablet', 'mobile', 'stacking'],
        actions: ['switch_device', 'set_device_style'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'responsive-editor',
    }),
    definePlugin({
        id: 'history-undo-redo',
        category: 'utility',
        label: 'History / Undo Redo',
        icon: 'history',
        componentTypes: ['history'],
        editableProps: ['autosave', 'snapshot_retention'],
        actions: ['undo', 'redo', 'restore_snapshot'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'history-undo-redo',
    }),
    definePlugin({
        id: 'layers-structure-panel',
        category: 'utility',
        label: 'Layer / Structure Panel',
        icon: 'layers',
        componentTypes: ['layers'],
        editableProps: ['tree', 'rename', 'lock', 'hide'],
        actions: ['select_from_tree', 'drag_reorder'],
        phase: 1,
        implementationStatus: 'native',
    }),
    definePlugin({
        id: 'style-manager',
        category: 'utility',
        label: 'Style Manager',
        icon: 'style',
        componentTypes: ['style-manager'],
        editableProps: ['colors', 'typography', 'spacing', 'backgrounds', 'borders', 'effects'],
        actions: ['apply_style'],
        phase: 1,
        implementationStatus: 'native',
    }),
    definePlugin({
        id: 'traits-settings-panel',
        category: 'utility',
        label: 'Traits / Settings Panel',
        icon: 'settings',
        componentTypes: ['traits-panel'],
        editableProps: ['plugin_traits', 'validation'],
        actions: ['edit_traits'],
        phase: 1,
        implementationStatus: 'native',
        runtimeKey: 'traits-settings-panel',
    }),
    definePlugin({
        id: 'global-design-system',
        category: 'utility',
        label: 'Global Design System',
        icon: 'design-system',
        componentTypes: ['theme-token'],
        editableProps: ['global_colors', 'global_fonts', 'button_presets', 'spacing_tokens'],
        actions: ['apply_theme_token'],
        phase: 2,
        implementationStatus: 'planned',
    }),
    definePlugin({
        id: 'ai-safe-component-registry',
        category: 'ai',
        label: 'AI-Safe Component Registry',
        icon: 'ai',
        componentTypes: ['ai-registry'],
        editableProps: ['allowed_components', 'allowed_attributes', 'structure_rules'],
        actions: ['validate_ai_structure'],
        phase: 3,
        implementationStatus: 'native',
    }),
];

const STRUCTURAL_RULES = {
    'lb-section': {
        allowedChildTypes: ['lb-row'],
    },
    'lb-row': {
        allowedChildTypes: ['lb-column'],
    },
};

const RUNTIME_PLUGINS = {
    'layout-structure': (editor) => {
        structuredLayoutPlugin(editor);
    },
    'text-content': (editor) => {
        const blockManager = editor.BlockManager;
        const upsertTextBlock = (id, config) => {
            if (blockManager.get(id)) {
                blockManager.remove(id);
            }
            blockManager.add(id, config);
        };

        upsertTextBlock('funnel-heading', {
            label: 'Heading',
            category: 'Text',
            media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
                <text x="12" y="16" text-anchor="middle" font-size="14" font-weight="bold" fill="currentColor">H1</text>
            </svg>`,
            content: '<h1 class="text-4xl font-bold text-gray-900" data-editor-plugin="text-content">Insert your heading text here</h1>',
        });

        upsertTextBlock('funnel-paragraph', {
            label: 'Paragraph',
            category: 'Text',
            media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
                <line x1="4" y1="6" x2="20" y2="6" stroke="currentColor" stroke-width="2"></line>
                <line x1="4" y1="10" x2="20" y2="10" stroke="currentColor" stroke-width="2"></line>
                <line x1="4" y1="14" x2="16" y2="14" stroke="currentColor" stroke-width="2"></line>
                <line x1="4" y1="18" x2="18" y2="18" stroke="currentColor" stroke-width="2"></line>
            </svg>`,
            content: '<p class="text-base leading-relaxed text-gray-700" data-editor-plugin="text-content">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',
        });

        upsertTextBlock('funnel-list', {
            label: 'Bullet List',
            category: 'Text',
            media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
                <circle cx="5" cy="6" r="2" fill="currentColor"></circle>
                <line x1="10" y1="6" x2="20" y2="6" stroke="currentColor" stroke-width="2"></line>
                <circle cx="5" cy="12" r="2" fill="currentColor"></circle>
                <line x1="10" y1="12" x2="20" y2="12" stroke="currentColor" stroke-width="2"></line>
                <circle cx="5" cy="18" r="2" fill="currentColor"></circle>
                <line x1="10" y1="18" x2="20" y2="18" stroke="currentColor" stroke-width="2"></line>
            </svg>`,
            content: `
                <ul class="list-disc list-inside space-y-2 text-gray-700" data-editor-plugin="text-content">
                    <li class="text-base">First item in the list</li>
                    <li class="text-base">Second item in the list</li>
                    <li class="text-base">Third item in the list</li>
                </ul>
            `,
        });

        upsertTextBlock('funnel-quote', {
            label: 'Quote',
            category: 'Text',
            media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
                <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z" fill="currentColor"></path>
            </svg>`,
            content: `
                <blockquote class="border-l-4 border-blue-500 bg-gray-50 py-2 pl-4 italic text-gray-700" data-editor-plugin="text-content">
                    <p class="text-lg">"This is an inspiring quote that will motivate your readers."</p>
                    <footer class="mt-2 text-sm text-gray-500">- Author Name</footer>
                </blockquote>
            `,
        });
    },
    'button-cta': (editor) => {
        if (!editor.DomComponents.getType('funnel-cta')) {
            const defaultType = editor.DomComponents.getType('link');
            const defaultModel = defaultType?.model;

            editor.DomComponents.addType('funnel-cta', {
                isComponent: (el) => {
                    if (el?.getAttribute?.('data-editor-plugin') === 'button-cta') {
                        return { type: 'funnel-cta' };
                    }

                    return false;
                },
                extend: 'link',
                model: {
                    defaults: {
                        ...(defaultModel?.prototype?.defaults || {}),
                        tagName: 'a',
                        name: 'CTA Button',
                        attributes: {
                            href: '#',
                            class: 'funnel-cta-btn inline-block rounded-lg bg-blue-600 px-8 py-3 font-semibold text-white transition-colors duration-300 hover:bg-blue-700',
                            'data-editor-plugin': 'button-cta',
                            'data-cta-action': 'open_url',
                            'data-cta-target': '#',
                        },
                        traits: [
                            { type: 'text', name: 'href', label: 'URL' },
                            {
                                type: 'select',
                                name: 'data-cta-action',
                                label: 'Action',
                                options: [
                                    { id: 'open_url', name: 'Open URL' },
                                    { id: 'scroll_to_section', name: 'Scroll To Section' },
                                    { id: 'open_popup', name: 'Open Popup' },
                                    { id: 'next_step', name: 'Go To Next Step' },
                                    { id: 'submit_form', name: 'Submit Form' },
                                    { id: 'open_checkout', name: 'Open Checkout' },
                                    { id: 'whatsapp', name: 'WhatsApp' },
                                    { id: 'custom_action', name: 'Custom Action ID' },
                                ],
                            },
                            { type: 'text', name: 'data-cta-target', label: 'Action Target' },
                            {
                                type: 'checkbox',
                                name: 'data-cta-full-width',
                                label: 'Full Width',
                                valueTrue: 'true',
                                valueFalse: 'false',
                            },
                        ],
                    },
                },
            });
        }

        const blockManager = editor.BlockManager;
        if (blockManager.get('funnel-cta')) {
            blockManager.remove('funnel-cta');
        }
        blockManager.add('funnel-cta', {
            label: 'CTA Button',
            category: 'Conversion',
            media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
                <rect x="4" y="8" width="16" height="8" rx="4" fill="currentColor"></rect>
                <text x="12" y="14" text-anchor="middle" font-size="8" fill="white" font-weight="bold">CTA</text>
            </svg>`,
            content: {
                type: 'funnel-cta',
                content: 'Click Me',
            },
        });
    },
    form: (editor) => {
        const formTraitDefinitions = [
            { type: 'text', name: 'action', label: 'Action URL', placeholder: '/forms/process' },
            {
                type: 'select',
                name: 'method',
                label: 'Method',
                options: [
                    { id: 'post', name: 'POST' },
                    { id: 'get', name: 'GET' },
                ],
            },
            { type: 'text', name: 'data-form-name', label: 'Form Name' },
            { type: 'text', name: 'data-success-message', label: 'Success Message' },
            { type: 'text', name: 'data-redirect-url', label: 'Redirect URL' },
            { type: 'text', name: 'data-lead-source', label: 'Lead Source' },
            { type: 'text', name: 'data-webhook', label: 'Webhook URL' },
            { type: 'text', name: 'data-automation-trigger', label: 'Automation Trigger' },
        ];

        const ensureFormTraits = (component) => {
            if (!component) {
                return;
            }

            const tagName = String(component.get?.('tagName') || '').toLowerCase();
            const type = String(component.get?.('type') || '').toLowerCase();
            if (tagName !== 'form' && type !== 'funnel-form') {
                return;
            }

            if (typeof component.getTraits !== 'function' || typeof component.addTrait !== 'function') {
                return;
            }

            const existingNames = component
                .getTraits()
                .map((trait) => (trait?.get ? trait.get('name') : trait?.name))
                .filter(Boolean);

            const missingTraits = formTraitDefinitions.filter((trait) => !existingNames.includes(trait.name));
            if (missingTraits.length > 0) {
                component.addTrait(missingTraits, { at: 0 });
            }
        };

        if (!editor.DomComponents.getType('funnel-form')) {
            editor.DomComponents.addType('funnel-form', {
                isComponent: (el) => {
                    if (el?.getAttribute?.('data-editor-plugin') === 'form') {
                        return { type: 'funnel-form' };
                    }

                    return false;
                },
                model: {
                    defaults: {
                        tagName: 'form',
                        name: 'Funnel Form',
                        draggable: true,
                        droppable: true,
                        attributes: {
                            class: 'funnel-form w-full max-w-lg mx-auto rounded-lg bg-white p-8 shadow-md',
                            method: 'post',
                            action: '#',
                            'data-editor-plugin': 'form',
                            'data-form-name': 'Lead Form',
                            'data-success-message': 'Thanks. We received your details.',
                            'data-redirect-url': '',
                            'data-lead-source': 'funnel_builder',
                            'data-webhook': '',
                            'data-automation-trigger': '',
                        },
                        components: [
                            {
                                type: 'default',
                                tagName: 'div',
                                attributes: { class: 'mb-4' },
                                components: `
                                    <label class="mb-2 block text-sm font-bold text-gray-700">Full Name</label>
                                    <input type="text" name="name" placeholder="John Doe" required class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none" />
                                `,
                            },
                            {
                                type: 'default',
                                tagName: 'div',
                                attributes: { class: 'mb-4' },
                                components: `
                                    <label class="mb-2 block text-sm font-bold text-gray-700">Email Address</label>
                                    <input type="email" name="email" placeholder="john@example.com" required class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none" />
                                `,
                            },
                            {
                                type: 'default',
                                tagName: 'div',
                                attributes: { class: 'mb-6' },
                                components: `
                                    <label class="mb-2 block text-sm font-bold text-gray-700">Message</label>
                                    <textarea name="message" placeholder="Your message here..." class="h-32 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none"></textarea>
                                `,
                            },
                            {
                                type: 'default',
                                tagName: 'button',
                                content: 'Submit Form',
                                attributes: {
                                    type: 'submit',
                                    class: 'w-full rounded-lg bg-blue-600 px-4 py-3 font-bold text-white transition-colors duration-300 hover:bg-blue-700',
                                    'data-editor-plugin': 'button-cta',
                                    'data-cta-action': 'submit_form',
                                },
                            },
                        ],
                        traits: formTraitDefinitions,
                    },
                },
            });
        }

        const blockManager = editor.BlockManager;
        if (blockManager.get('funnel-form')) {
            blockManager.remove('funnel-form');
        }
        blockManager.add('funnel-form', {
            label: 'Lead Form',
            category: 'Conversion',
            media: `<svg viewBox="0 0 24 24" style="width:100%;height:100%;">
                <rect x="3" y="4" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"></rect>
                <line x1="6" y1="8" x2="18" y2="8" stroke="currentColor" stroke-width="1.5"></line>
                <line x1="6" y1="12" x2="18" y2="12" stroke="currentColor" stroke-width="1.5"></line>
                <rect x="6" y="15" width="12" height="3" rx="1" fill="currentColor"></rect>
            </svg>`,
            content: {
                type: 'funnel-form',
            },
        });

        editor.on('component:selected', ensureFormTraits);
    },
    'countdown-timer': (editor) => {
        countdownPlugin(editor);
    },
    'conversion-blocks': (editor) => {
        conversionBlocksPlugin(editor);
    },
    popup: (editor) => {
        exitIntentPlugin(editor);
    },
    'slider-carousel': (editor) => {
        lpSliderPlugin(editor, {
            blockCategory: 'Interaction',
        });
    },
    'visibility-conditional-display': (editor) => {
        deviceVisibilityPlugin(editor);
    },
    'responsive-editor': (editor) => {
        const deviceManager = editor.DeviceManager;
        const hasDesktop = !!deviceManager.get('Desktop');
        const hasTablet = !!deviceManager.get('Tablet');
        const hasMobile = !!deviceManager.get('Mobile');

        if (!hasDesktop) {
            deviceManager.add({
                id: 'Desktop',
                name: 'Desktop',
                width: '',
            });
        }
        if (!hasTablet) {
            deviceManager.add({
                id: 'Tablet',
                name: 'Tablet',
                width: '768px',
                widthMedia: '992px',
            });
        }
        if (!hasMobile) {
            deviceManager.add({
                id: 'Mobile',
                name: 'Mobile',
                width: '375px',
                widthMedia: '767px',
            });
        }
    },
    'history-undo-redo': (editor) => {
        keyboardShortcutsPlugin(editor);
    },
    'html-embed': (editor) => {
        htmlBlockPlugin(editor);
    },
    'traits-settings-panel': (editor) => {
        editor.on('component:selected', (component) => {
            if (!component || !component.getAttributes) {
                return;
            }

            const attrs = component.getAttributes();
            if (!attrs['data-editor-plugin']) {
                const inferred = inferPluginId(component);
                if (inferred) {
                    component.addAttributes({
                        'data-editor-plugin': inferred,
                    });
                }
            }
        });
    },
    'seo-page-meta': (editor, options = {}) => {
        const command = 'funnel-seo:open';
        if (!editor.Commands.get(command)) {
            editor.Commands.add(command, {
                run(ed) {
                    const modal = ed.Modal;
                    const current = ed.getModel().get('funnelSeoMeta') || options.initialSeoMeta || {};
                    const root = document.createElement('div');
                    root.innerHTML = `
                        <div style="padding:16px;display:grid;gap:10px;font-family:Inter,Segoe UI,sans-serif;">
                            <label style="display:grid;gap:4px;">
                                <span>Page Title</span>
                                <input data-seo-field="title" value="${escapeHtml(current.title || '')}" style="padding:8px;border-radius:6px;border:1px solid #334155;background:#0f172a;color:#e2e8f0;" />
                            </label>
                            <label style="display:grid;gap:4px;">
                                <span>Meta Description</span>
                                <textarea data-seo-field="description" style="min-height:100px;padding:8px;border-radius:6px;border:1px solid #334155;background:#0f172a;color:#e2e8f0;">${escapeHtml(current.description || '')}</textarea>
                            </label>
                            <label style="display:grid;gap:4px;">
                                <span>OG Title</span>
                                <input data-seo-field="ogTitle" value="${escapeHtml(current.ogTitle || '')}" style="padding:8px;border-radius:6px;border:1px solid #334155;background:#0f172a;color:#e2e8f0;" />
                            </label>
                            <label style="display:grid;gap:4px;">
                                <span>OG Description</span>
                                <textarea data-seo-field="ogDescription" style="min-height:80px;padding:8px;border-radius:6px;border:1px solid #334155;background:#0f172a;color:#e2e8f0;">${escapeHtml(current.ogDescription || '')}</textarea>
                            </label>
                            <label style="display:grid;gap:4px;">
                                <span>Canonical URL</span>
                                <input data-seo-field="canonical" value="${escapeHtml(current.canonical || '')}" style="padding:8px;border-radius:6px;border:1px solid #334155;background:#0f172a;color:#e2e8f0;" />
                            </label>
                            <div style="display:flex;justify-content:flex-end;gap:8px;">
                                <button type="button" data-seo-action="cancel" style="padding:8px 12px;border-radius:6px;border:1px solid #334155;background:transparent;color:#e2e8f0;cursor:pointer;">Cancel</button>
                                <button type="button" data-seo-action="save" style="padding:8px 12px;border-radius:6px;border:1px solid #4f46e5;background:#4f46e5;color:#fff;cursor:pointer;">Save SEO</button>
                            </div>
                        </div>
                    `;

                    root.querySelector('[data-seo-action="cancel"]')?.addEventListener('click', () => modal.close());
                    root.querySelector('[data-seo-action="save"]')?.addEventListener('click', () => {
                        const payload = {
                            title: root.querySelector('[data-seo-field="title"]')?.value?.trim() || '',
                            description: root.querySelector('[data-seo-field="description"]')?.value?.trim() || '',
                            ogTitle: root.querySelector('[data-seo-field="ogTitle"]')?.value?.trim() || '',
                            ogDescription: root.querySelector('[data-seo-field="ogDescription"]')?.value?.trim() || '',
                            canonical: root.querySelector('[data-seo-field="canonical"]')?.value?.trim() || '',
                        };
                        ed.getModel().set('funnelSeoMeta', payload);
                        modal.close();
                    });

                    modal.setTitle('SEO Metadata').setContent(root).open();
                },
            });
        }
    },
    'saved-blocks-reusable-sections': (editor, options = {}) => {
        const storageKey = buildSavedBlocksStorageKey(options);
        const commandSave = 'funnel-saved-blocks:save-selected';
        const commandInsert = 'funnel-saved-blocks:insert-block';

        const refreshSavedBlocks = () => {
            const blocks = readSavedBlocks(storageKey);
            blocks.forEach((item) => {
                const blockId = `saved-block-${item.id}`;
                if (editor.BlockManager.get(blockId)) {
                    return;
                }

                editor.BlockManager.add(blockId, {
                    label: `Saved: ${item.name}`,
                    category: 'Saved Sections',
                    content: item.content,
                });
            });
        };

        if (!editor.Commands.get(commandSave)) {
            editor.Commands.add(commandSave, {
                run(ed) {
                    const selected = ed.getSelected();
                    if (!selected) {
                        if (window.Toast?.error) {
                            window.Toast.error('Select a section to save first.');
                        }
                        return;
                    }

                    const name = window.prompt('Saved block name');
                    if (!name || !name.trim()) {
                        return;
                    }

                    const blocks = readSavedBlocks(storageKey);
                    blocks.push({
                        id: `sb-${Date.now()}`,
                        name: name.trim(),
                        content: selected.toHTML(),
                    });
                    writeSavedBlocks(storageKey, blocks);
                    refreshSavedBlocks();
                    if (window.Toast?.success) {
                        window.Toast.success('Section saved to Saved Blocks.');
                    }
                },
            });
        }

        if (!editor.Commands.get(commandInsert)) {
            editor.Commands.add(commandInsert, {
                run(ed, _sender, opts = {}) {
                    const blocks = readSavedBlocks(storageKey);
                    const block = blocks.find((entry) => entry.id === opts.id);
                    if (!block) {
                        return;
                    }

                    ed.getWrapper().append(block.content);
                },
            });
        }

        refreshSavedBlocks();
    },
    template: (editor) => {
        const command = 'funnel-template:insert';
        if (!editor.Commands.get(command)) {
            editor.Commands.add(command, {
                run(ed, _sender, opts = {}) {
                    const templates = {
                        'hero-proof-cta': `
                            <section data-editor-plugin="layout-structure" class="py-20 px-6">
                                <div class="max-w-4xl mx-auto text-center">
                                    <h1 data-editor-plugin="text-content" class="text-4xl font-bold">Clear Headline That Speaks To The Main Outcome</h1>
                                    <p data-editor-plugin="text-content" class="mt-4 text-lg">Short value proposition explaining the core transformation.</p>
                                    <a data-editor-plugin="button-cta" data-cta-action="open_checkout" href="#" class="inline-flex mt-8 px-6 py-3 rounded-lg font-semibold">Claim Offer</a>
                                </div>
                            </section>
                            <section data-editor-plugin="testimonials-social-proof" class="py-16 px-6">
                                <div class="max-w-5xl mx-auto grid gap-6 md:grid-cols-3">
                                    <article class="p-6 border rounded-lg"><p>"This solved the exact problem."</p><p class="mt-3 font-semibold">Customer Name</p></article>
                                    <article class="p-6 border rounded-lg"><p>"Fast setup and real results."</p><p class="mt-3 font-semibold">Customer Name</p></article>
                                    <article class="p-6 border rounded-lg"><p>"Would buy again immediately."</p><p class="mt-3 font-semibold">Customer Name</p></article>
                                </div>
                            </section>
                        `,
                    };

                    const templateId = opts.templateId || 'hero-proof-cta';
                    const html = templates[templateId];
                    if (html) {
                        ed.getWrapper().append(html);
                    }
                },
            });
        }
    },
};

const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

const buildSavedBlocksStorageKey = (options = {}) => {
    const landingId = options.landingId ? String(options.landingId) : 'unknown-landing';
    const pageId = options.pageId ? String(options.pageId) : 'unknown-page';
    return `funnel-editor:saved-blocks:${landingId}:${pageId}`;
};

const readSavedBlocks = (key) => {
    try {
        const parsed = JSON.parse(localStorage.getItem(key) || '[]');
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

const writeSavedBlocks = (key, blocks) => {
    localStorage.setItem(key, JSON.stringify(blocks));
};

const normalizeMode = (mode) => {
    const normalized = String(mode || '').trim().toLowerCase();
    return VALID_MODES.has(normalized) ? normalized : MODE_EDITOR;
};

const parseJsonSafe = (value, fallback = null) => {
    if (value == null || value === '') {
        return fallback;
    }

    if (typeof value === 'object') {
        return value;
    }

    try {
        return JSON.parse(String(value));
    } catch {
        return fallback;
    }
};

const boolSetting = (value, fallback = false) => {
    if (typeof value === 'boolean') {
        return value;
    }

    if (value == null || value === '') {
        return fallback;
    }

    const normalized = String(value).trim().toLowerCase();
    if (['1', 'true', 'yes', 'on'].includes(normalized)) {
        return true;
    }

    if (['0', 'false', 'no', 'off'].includes(normalized)) {
        return false;
    }

    return fallback;
};

const toListMap = (definitions) => {
    const list = definitions.map((plugin) => Object.freeze({ ...plugin }));
    const byId = new Map(list.map((plugin) => [plugin.id, plugin]));
    return { list, byId };
};

const inferPluginId = (component) => {
    if (!component || typeof component.get !== 'function') {
        return null;
    }

    const type = String(component.get('type') || '').toLowerCase();
    const tag = String(component.get('tagName') || '').toLowerCase();
    const attrs = component.getAttributes?.() || {};
    const className = String(attrs.class || '').toLowerCase();

    if (type === 'lb-section' || type === 'lb-row' || type === 'lb-column') return 'layout-structure';
    if (type === 'funnel-cta') return 'button-cta';
    if (type === 'funnel-form') return 'form';
    if (type === 'countdown-timer' || className.includes('countertimer')) return 'countdown-timer';
    if (type === 'lp-slider' || attrs['data-component'] === 'builder-slider') return 'slider-carousel';
    if (type === 'custom-html') return 'html-embed';
    if (type === 'image' || tag === 'img') return 'image';
    if (tag === 'form') return 'form';
    if (tag === 'video' || tag === 'iframe') return 'video';
    if (tag === 'button' || (tag === 'a' && className.includes('cta'))) return 'button-cta';
    if (tag === 'details' || tag === 'summary') return 'faq-accordion';
    if (className.includes('testimonial') || className.includes('rating')) return 'testimonials-social-proof';
    if (className.includes('pricing') || className.includes('offer')) return 'pricing-offer-box';
    if (className.includes('trust') || className.includes('badge')) return 'icon-badge-trust';
    if (tag === 'h1' || tag === 'h2' || tag === 'h3' || tag === 'h4' || tag === 'h5' || tag === 'h6' || tag === 'p' || tag === 'li') return 'text-content';
    if (attrs['data-visibility']) return 'visibility-conditional-display';
    return null;
};

const installMetadataBridge = (editor, registry) => {
    const applyMetadata = (component) => {
        if (!component || typeof component.getAttributes !== 'function') {
            return;
        }

        const attributes = component.getAttributes() || {};
        if (attributes['data-editor-plugin']) {
            return;
        }

        const inferred = inferPluginId(component);
        if (!inferred || !registry.byId.has(inferred)) {
            return;
        }

        component.addAttributes({
            'data-editor-plugin': inferred,
            'data-plugin-version': SYSTEM_VERSION,
        });
    };

    editor.on('component:add', applyMetadata);
    editor.on('component:update:attributes', applyMetadata);
    editor.on('component:selected', applyMetadata);
};

const validateEditorTree = (editor, registry) => {
    const errors = [];
    const warnings = [];

    const wrapper = editor.getWrapper?.();
    if (!wrapper) {
        return {
            valid: false,
            errors: ['Editor wrapper is not available.'],
            warnings,
        };
    }

    const walk = (component, parent = null) => {
        if (!component || typeof component.get !== 'function') {
            return;
        }

        const attrs = component.getAttributes?.() || {};
        const pluginId = attrs['data-editor-plugin'] || inferPluginId(component);
        const type = String(component.get('type') || '');

        if (pluginId && !registry.byId.has(pluginId)) {
            warnings.push(`Unknown plugin id "${pluginId}" on component type "${type || 'unknown'}".`);
        }

        if (parent) {
            const parentType = String(parent.get('type') || '');
            const rule = STRUCTURAL_RULES[parentType];
            if (rule && Array.isArray(rule.allowedChildTypes) && !rule.allowedChildTypes.includes('*')) {
                if (!rule.allowedChildTypes.includes(type)) {
                    errors.push(`Invalid nesting: "${type}" is not allowed inside "${parentType}".`);
                }
            }
        }

        const childrenCollection = component.components?.();
        const children = childrenCollection?.models || [];
        children.forEach((child) => walk(child, component));
    };

    walk(wrapper);

    return {
        valid: errors.length === 0,
        errors,
        warnings,
    };
};

const serializeEditorProject = (editor, registry, mode) => {
    const projectData = editor.getProjectData();
    const validation = validateEditorTree(editor, registry);

    const pluginMeta = {
        version: SYSTEM_VERSION,
        mode,
        savedAt: new Date().toISOString(),
        plugins: registry.list.map((plugin) => ({
            id: plugin.id,
            phase: plugin.phase,
            status: plugin.implementationStatus,
        })),
        validation,
        seoMeta: editor.getModel().get('funnelSeoMeta') || {},
    };

    return {
        projectData: {
            ...projectData,
            [SYSTEM_KEY]: pluginMeta,
        },
        validation,
    };
};

const parseStoredProjectData = (value) => {
    if (!value) return null;

    let parsed = value;
    if (typeof value === 'string') {
        try {
            parsed = JSON.parse(value);
        } catch {
            return null;
        }
    }

    if (!parsed || typeof parsed !== 'object') {
        return null;
    }

    if (parsed.projectData && typeof parsed.projectData === 'object') {
        return parsed.projectData;
    }

    if (parsed.project && typeof parsed.project === 'object') {
        return parsed.project;
    }

    return parsed;
};

const getAiSafeComponentRegistry = (registry) => registry.list
    .filter((plugin) => plugin.ai?.safe !== false)
    .map((plugin) => ({
        id: plugin.id,
        category: plugin.category,
        label: plugin.label,
        componentTypes: plugin.componentTypes || [],
        allowedAttributes: plugin.ai?.allowedAttributes || [],
        allowedChildren: plugin.allowedChildren || [],
    }));

const installRuntimePlugins = (editor, registry, options) => {
    // Install Style Manager sectors and Background plugin early so that
    // the SM sidebar is fully populated before other plugins render.
    advancedStyleManagerPlugin(editor);
    backgroundPlugin(editor);

    const installedRuntimeKeys = new Set();

    registry.list.forEach((plugin) => {
        const runtimeKey = plugin.runtimeKey || plugin.id;
        if (installedRuntimeKeys.has(runtimeKey)) {
            return;
        }

        const runtime = RUNTIME_PLUGINS[runtimeKey];
        if (typeof runtime !== 'function') {
            return;
        }

        runtime(editor, options);
        installedRuntimeKeys.add(runtimeKey);
    });
};

const installTailwindWorkspacePlugin = (editor, settings = {}) => {
    const useRuntime = boolSetting(settings.use_cdn, true);
    if (!useRuntime) {
        return;
    }

    const rawConfig = parseJsonSafe(settings.config_json, null);
    const normalizedConfig = rawConfig && typeof rawConfig === 'object' ? rawConfig : null;
    const runtimeSrc = String(settings.runtime_src || '/js/tailwind.js').trim() || '/js/tailwind.js';
    const enableCdnFallback = boolSetting(settings.fallback_cdn, true);

    const ensureTailwindInCanvas = () => {
        const frameDoc = editor.Canvas.getDocument?.();
        if (!frameDoc?.head) {
            return;
        }

        if (normalizedConfig && !frameDoc.getElementById(TAILWIND_CONFIG_ID)) {
            const configScript = frameDoc.createElement('script');
            configScript.id = TAILWIND_CONFIG_ID;
            configScript.textContent = `window.tailwind = window.tailwind || {}; window.tailwind.config = ${JSON.stringify(normalizedConfig)};`;
            frameDoc.head.appendChild(configScript);
        }

        if (!frameDoc.getElementById(TAILWIND_SCRIPT_ID)) {
            const script = frameDoc.createElement('script');
            script.id = TAILWIND_SCRIPT_ID;
            script.src = runtimeSrc;
            script.async = true;

            if (enableCdnFallback) {
                script.onerror = () => {
                    if (frameDoc.getElementById(`${TAILWIND_SCRIPT_ID}-cdn`)) {
                        return;
                    }
                    const cdnScript = frameDoc.createElement('script');
                    cdnScript.id = `${TAILWIND_SCRIPT_ID}-cdn`;
                    cdnScript.src = 'https://cdn.tailwindcss.com';
                    cdnScript.async = true;
                    frameDoc.head.appendChild(cdnScript);
                };
            }
            frameDoc.head.appendChild(script);
        }

        if (!frameDoc.getElementById(TAILWIND_HELPER_STYLE_ID)) {
            const style = frameDoc.createElement('style');
            style.id = TAILWIND_HELPER_STYLE_ID;
            style.textContent = `
                [data-gjs-type="wrapper"] {
                    min-height: 100vh;
                }
            `;
            frameDoc.head.appendChild(style);
        }
    };

    editor.on('load', ensureTailwindInCanvas);
    editor.on('canvas:frame:load', ensureTailwindInCanvas);
    ensureTailwindInCanvas();
};

const installTailwindAutocompleteWorkspacePlugin = (editor, settings = {}) => {
    const enabled = boolSetting(settings.enabled, true);
    if (!enabled) {
        return;
    }

    const minChars = Number.parseInt(String(settings.min_chars ?? ''), 10);
    const maxSuggestions = Number.parseInt(String(settings.max_suggestions ?? ''), 10);

    tailwindAutocompletePlugin(editor, {
        minChars: Number.isFinite(minChars) ? minChars : 2,
        maxSuggestions: Number.isFinite(maxSuggestions) ? maxSuggestions : 15,
    });
};

const installTailwindCardsWorkspacePlugin = (editor, settings = {}) => {
    if (editor.__tailwindCardsWorkspacePluginReady) {
        return;
    }
    editor.__tailwindCardsWorkspacePluginReady = true;

    const enabled = boolSetting(settings.enabled, true);
    if (!enabled) {
        return;
    }

    // Register card blocks only when this workspace plugin is active.
    tailwindCardsBlocksPlugin(editor);

    const loadBuilderCssInCanvas = boolSetting(settings.load_builder_css, true);
    if (!loadBuilderCssInCanvas) {
        return;
    }

    const ensureBuilderCssInCanvas = () => {
        const frameDoc = editor.Canvas.getDocument?.();
        if (!frameDoc?.head) {
            return;
        }

        if (frameDoc.getElementById(TAILWIND_CARDS_CANVAS_STYLE_ID)) {
            return;
        }

        const hostCssHref = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
            .map((link) => link.getAttribute('href'))
            .find((href) => typeof href === 'string' && (
                href.includes('/build/assets/app-')
                || href.includes('/resources/css/app.css')
            ));

        const fallbackCssHref = String(window?.editorData?.builderCssUrl || '').trim();
        const candidateHref = hostCssHref || fallbackCssHref;

        if (!candidateHref) {
            return;
        }

        const link = frameDoc.createElement('link');
        link.id = TAILWIND_CARDS_CANVAS_STYLE_ID;
        link.rel = 'stylesheet';
        link.href = candidateHref;
        frameDoc.head.appendChild(link);
    };

    editor.on('load', ensureBuilderCssInCanvas);
    editor.on('canvas:frame:load', ensureBuilderCssInCanvas);
    ensureBuilderCssInCanvas();
};

const resolveMaterialIconsFontUrl = (variant) => {
    const key = String(variant || '').trim().toLowerCase();
    const variantMap = {
        material_symbols_outlined: 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0',
        material_symbols_rounded: 'https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0',
        material_symbols_sharp: 'https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@24,400,0,0',
        material_icons: 'https://fonts.googleapis.com/icon?family=Material+Icons',
        material_icons_outlined: 'https://fonts.googleapis.com/icon?family=Material+Icons+Outlined',
        material_icons_round: 'https://fonts.googleapis.com/icon?family=Material+Icons+Round',
        material_icons_sharp: 'https://fonts.googleapis.com/icon?family=Material+Icons+Sharp',
    };

    return variantMap[key] || variantMap.material_symbols_outlined;
};

const resolveMaterialIconsClass = (variant) => {
    const key = String(variant || '').trim().toLowerCase();
    const classMap = {
        material_symbols_outlined: 'material-symbols-outlined',
        material_symbols_rounded: 'material-symbols-rounded',
        material_symbols_sharp: 'material-symbols-sharp',
        material_icons: 'material-icons',
        material_icons_outlined: 'material-icons-outlined',
        material_icons_round: 'material-icons-round',
        material_icons_sharp: 'material-icons-sharp',
    };

    return classMap[key] || classMap.material_symbols_outlined;
};

const MATERIAL_ICON_PICKER_COMMAND = 'material-icons:open-picker';
const MATERIAL_ICON_CLASS_VALUES = [
    'material-symbols-outlined',
    'material-symbols-rounded',
    'material-symbols-sharp',
    'material-icons',
    'material-icons-outlined',
    'material-icons-round',
    'material-icons-sharp',
];
const MATERIAL_ICON_INSTANCE_CLASS_PREFIX = 'mi-icon-';

const MATERIAL_ICON_CATALOG = [
    'home', 'menu', 'search', 'close', 'check', 'done', 'add', 'remove', 'edit', 'delete',
    'favorite', 'star', 'info', 'warning', 'error', 'help', 'visibility', 'visibility_off', 'lock', 'lock_open',
    'person', 'group', 'mail', 'call', 'chat', 'send', 'notifications', 'campaign', 'public', 'language',
    'shopping_cart', 'shopping_bag', 'store', 'payments', 'credit_card', 'local_shipping', 'redeem', 'sell', 'attach_money', 'price_check',
    'schedule', 'calendar_today', 'event', 'access_time', 'timer', 'hourglass_empty', 'alarm', 'date_range', 'today', 'update',
    'arrow_back', 'arrow_forward', 'arrow_upward', 'arrow_downward', 'expand_more', 'expand_less', 'chevron_left', 'chevron_right', 'north_east', 'south_west',
    'play_arrow', 'pause', 'stop', 'skip_next', 'skip_previous', 'volume_up', 'volume_off', 'mic', 'videocam', 'camera_alt',
    'image', 'photo_library', 'slideshow', 'ondemand_video', 'movie', 'music_note', 'headphones', 'podcasts', 'radio', 'live_tv',
    'settings', 'build', 'tune', 'analytics', 'insights', 'dashboard', 'bolt', 'auto_fix_high', 'code', 'terminal',
    'thumb_up', 'thumb_down', 'verified', 'security', 'shield', 'workspace_premium', 'emoji_events', 'workspace', 'layers', 'widgets',
];

const detectMaterialIconClass = (className, fallback) => {
    const tokens = String(className || '').split(/\s+/).filter(Boolean);
    const found = tokens.find((token) => MATERIAL_ICON_CLASS_VALUES.includes(token));
    return found || fallback;
};

const tokenizeClassName = (className) => String(className || '').split(/\s+/).filter(Boolean);

const resolveMaterialIconInstanceClass = (className, fallbackSeed) => {
    const tokens = tokenizeClassName(className);
    const existing = tokens.find((token) => token.startsWith(MATERIAL_ICON_INSTANCE_CLASS_PREFIX));
    if (existing) {
        return existing;
    }

    const safeSeed = String(fallbackSeed || '')
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9_-]/g, '');

    if (safeSeed) {
        return `${MATERIAL_ICON_INSTANCE_CLASS_PREFIX}${safeSeed}`;
    }

    return `${MATERIAL_ICON_INSTANCE_CLASS_PREFIX}${Math.random().toString(36).slice(2, 10)}`;
};

const buildMaterialIconClassName = (className, variantClass, instanceSeed) => {
    const resolvedVariant = detectMaterialIconClass(variantClass, MATERIAL_ICON_CLASS_VALUES[0]);
    const instanceClass = resolveMaterialIconInstanceClass(className, instanceSeed);
    const tokens = tokenizeClassName(className)
        .filter((token) => token !== instanceClass)
        .filter((token) => !MATERIAL_ICON_CLASS_VALUES.includes(token))
        .filter(Boolean);

    return [instanceClass, ...tokens, resolvedVariant].join(' ');
};


const ensureMaterialIconsInDocument = (doc, fontHref) => {
    if (!doc?.head) {
        return;
    }

    const existingLink = doc.getElementById(MATERIAL_ICONS_LINK_ID);
    if (!existingLink) {
        const link = doc.createElement('link');
        link.id = MATERIAL_ICONS_LINK_ID;
        link.rel = 'stylesheet';
        link.href = fontHref;
        doc.head.appendChild(link);
    } else if (existingLink.getAttribute('href') !== fontHref) {
        existingLink.setAttribute('href', fontHref);
    }

    if (!doc.getElementById(MATERIAL_ICONS_HELPER_STYLE_ID)) {
        const style = doc.createElement('style');
        style.id = MATERIAL_ICONS_HELPER_STYLE_ID;
        style.textContent = `
            .material-icons,
            .material-icons-outlined,
            .material-icons-round,
            .material-icons-sharp,
            .material-symbols-outlined,
            .material-symbols-rounded,
            .material-symbols-sharp {
                font-size: 24px;
                line-height: 1;
                vertical-align: middle;
            }
        `;
        doc.head.appendChild(style);
    }
};

const installMaterialIconsWorkspacePlugin = (editor, settings = {}) => {
    if (editor.__materialIconsWorkspacePluginReady) {
        return;
    }

    editor.__materialIconsWorkspacePluginReady = true;
    const useCdn = boolSetting(settings.use_cdn, true);
    if (!useCdn) {
        return;
    }

    const variant = String(settings.variant || 'material_symbols_outlined').trim().toLowerCase();
    const fontHref = resolveMaterialIconsFontUrl(variant);
    const iconClass = resolveMaterialIconsClass(variant);

    const ensureMaterialIconsInCanvas = () => {
        const frameDoc = editor.Canvas.getDocument?.();
        ensureMaterialIconsInDocument(document, fontHref);
        ensureMaterialIconsInDocument(frameDoc, fontHref);
    };

    if (!editor.Commands.get(MATERIAL_ICON_PICKER_COMMAND)) {
        editor.Commands.add(MATERIAL_ICON_PICKER_COMMAND, {
            run(ed, _sender, opts = {}) {
                const target = opts.component || ed.getSelected();
                if (!target) {
                    return;
                }

                const type = String(target.get?.('type') || '');
                if (type !== 'material-icon') {
                    return;
                }

                const attrs = target.getAttributes?.() || {};
                const initialIcon = String(attrs['data-icon-name'] || '').trim() || 'home';
                let selectedIcon = initialIcon;
                let selectedClass = detectMaterialIconClass(attrs['data-icon-variant'], detectMaterialIconClass(attrs.class, iconClass));
                const allIcons = Array.from(new Set([...MATERIAL_ICON_CATALOG, initialIcon])).sort();

                const modal = ed.Modal;
                const root = document.createElement('div');
                root.style.cssText = 'display:flex;flex-direction:column;gap:10px;min-width:min(1080px,95vw);max-width:95vw;max-height:80vh;padding:8px;background:#3f3f42;color:#e5e7eb;border-radius:2px;';

                root.innerHTML = `
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;background:#2f2f31;border:1px solid #4b5563;padding:6px 8px;">
                        <input data-mi-search type="text" placeholder="Search" value="${escapeHtml(initialIcon)}" style="flex:1;min-width:260px;height:28px;padding:0 10px;border:1px solid #4b5563;background:#252529;color:#e5e7eb;font-size:12px;" />
                        <label style="font-size:11px;opacity:.85;">Fill</label>
                        <select style="height:24px;border:1px solid #4b5563;background:#252529;color:#d1d5db;font-size:11px;padding:0 6px;">
                            <option>0</option>
                        </select>
                        <label style="font-size:11px;opacity:.85;">Weight</label>
                        <select style="height:24px;border:1px solid #4b5563;background:#252529;color:#d1d5db;font-size:11px;padding:0 6px;">
                            <option>400</option>
                        </select>
                        <label style="font-size:11px;opacity:.85;">Grade</label>
                        <select style="height:24px;border:1px solid #4b5563;background:#252529;color:#d1d5db;font-size:11px;padding:0 6px;">
                            <option>0</option>
                        </select>
                        <label style="font-size:11px;opacity:.85;">Optical Size</label>
                        <select style="height:24px;border:1px solid #4b5563;background:#252529;color:#d1d5db;font-size:11px;padding:0 6px;">
                            <option>48</option>
                        </select>
                        <select data-mi-style style="height:28px;min-width:175px;padding:0 8px;border:1px solid #4b5563;background:#252529;color:#e5e7eb;font-size:12px;">
                            ${MATERIAL_ICON_CLASS_VALUES.map((item) => `<option value="${item}" ${item === selectedClass ? 'selected' : ''}>${item}</option>`).join('')}
                        </select>
                    </div>
                    <div data-mi-preview style="display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid #4b5563;background:#2f2f31;">
                        <span data-mi-preview-icon class="${selectedClass}" style="font-size:24px;line-height:1;color:#f3f4f6;">${escapeHtml(selectedIcon)}</span>
                        <code data-mi-preview-label style="font-size:11px;padding:1px 6px;background:#1f2937;border:1px solid #4b5563;color:#d1d5db;">${escapeHtml(selectedIcon)}</code>
                    </div>
                    <div data-mi-grid style="display:grid;grid-template-columns:repeat(auto-fill,minmax(88px,1fr));gap:6px;overflow:auto;max-height:52vh;padding:2px;border:1px solid #4b5563;background:#343437;"></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding-top:4px;">
                        <small style="opacity:.7;font-size:11px;">Tip: double-click any card to apply instantly.</small>
                        <div style="display:flex;gap:8px;">
                            <button type="button" data-mi-cancel style="height:30px;padding:0 12px;border:1px solid #6b7280;background:#2b2b2e;color:#e5e7eb;cursor:pointer;">Cancel</button>
                            <button type="button" data-mi-apply style="height:30px;padding:0 12px;border:1px solid #2563eb;background:#2563eb;color:#fff;cursor:pointer;">Apply</button>
                        </div>
                    </div>
                `;

                const searchInput = root.querySelector('[data-mi-search]');
                const styleSelect = root.querySelector('[data-mi-style]');
                const grid = root.querySelector('[data-mi-grid]');
                const previewIcon = root.querySelector('[data-mi-preview-icon]');
                const previewLabel = root.querySelector('[data-mi-preview-label]');

                const applyToComponent = () => {
                    const targetAttrs = target.getAttributes?.() || {};
                    const mergedClassName = buildMaterialIconClassName(
                        targetAttrs.class,
                        selectedClass,
                        target.getId?.() || target.cid,
                    );
                    target.addAttributes({
                        class: mergedClassName,
                        'data-icon-variant': selectedClass,
                        'data-icon-name': selectedIcon,
                    });
                    target.components(selectedIcon);
                    ed.select(target);
                };

                const updatePreview = () => {
                    previewIcon.className = selectedClass;
                    previewIcon.textContent = selectedIcon;
                    previewLabel.textContent = selectedIcon;
                };

                const renderGrid = () => {
                    const term = String(searchInput.value || '').trim().toLowerCase();
                    const filtered = allIcons.filter((iconName) => iconName.toLowerCase().includes(term)).slice(0, 240);
                    grid.innerHTML = filtered.map((iconName) => {
                        const active = iconName === selectedIcon;
                        const borderColor = active ? '#60a5fa' : '#52525b';
                        const bgColor = active ? '#1f2937' : '#3f3f46';
                        return `
                            <button type="button" data-mi-icon="${escapeHtml(iconName)}" style="text-align:left;padding:6px;border:1px solid ${borderColor};background:${bgColor};display:grid;gap:5px;cursor:pointer;min-height:62px;">
                                <span class="${selectedClass}" style="font-size:20px;line-height:1;color:#f5f5f5;">${escapeHtml(iconName)}</span>
                                <span style="font-size:10px;line-height:1.2;word-break:break-word;color:#d4d4d8;">${escapeHtml(iconName)}</span>
                            </button>
                        `;
                    }).join('') || '<div style="grid-column:1/-1;padding:14px;border:1px dashed #6b7280;text-align:center;opacity:.8;color:#d4d4d8;">No icons found for this search.</div>';
                };

                searchInput.addEventListener('input', renderGrid);
                styleSelect.addEventListener('change', () => {
                    selectedClass = detectMaterialIconClass(styleSelect.value, iconClass);
                    updatePreview();
                    renderGrid();
                });

                grid.addEventListener('click', (event) => {
                    const btn = event.target.closest('[data-mi-icon]');
                    if (!btn) return;
                    selectedIcon = btn.getAttribute('data-mi-icon') || 'home';
                    updatePreview();
                    renderGrid();
                });

                grid.addEventListener('dblclick', (event) => {
                    const btn = event.target.closest('[data-mi-icon]');
                    if (!btn) return;
                    selectedIcon = btn.getAttribute('data-mi-icon') || 'home';
                    updatePreview();
                    applyToComponent();
                    modal.close();
                });

                root.querySelector('[data-mi-cancel]')?.addEventListener('click', () => modal.close());
                root.querySelector('[data-mi-apply]')?.addEventListener('click', () => {
                    applyToComponent();
                    modal.close();
                });

                updatePreview();
                renderGrid();
                modal.setTitle('Choose Material Icon');
                modal.setContent(root);
                modal.open();
                requestAnimationFrame(() => {
                    const dialog = document.querySelector('.gjs-mdl-dialog');
                    if (dialog instanceof HTMLElement) {
                        dialog.style.width = '94vw';
                        dialog.style.maxWidth = '1140px';
                    }
                });
                searchInput.focus();
                searchInput.select();
            },
        });
    }

    if (!editor.DomComponents.getType('material-icon')) {
        editor.DomComponents.addType('material-icon', {
            isComponent: (el) => {
                const className = String(el?.getAttribute?.('class') || '');
                if (/(material-icons|material-symbols)/.test(className)) {
                    return { type: 'material-icon' };
                }
                return false;
            },
            model: {
                defaults: {
                    tagName: 'span',
                    name: 'Material Icon',
                    draggable: true,
                    droppable: false,
                    attributes: {
                        class: iconClass,
                        'data-editor-plugin': 'icon-badge-trust',
                        'data-icon-name': 'home',
                        'data-icon-variant': iconClass,
                        'aria-hidden': 'true',
                    },
                    components: 'home',
                    traits: [
                        {
                            type: 'button',
                            name: 'open-material-icon-picker',
                            text: 'Choose Icon',
                            full: true,
                            command: () => editor.runCommand(MATERIAL_ICON_PICKER_COMMAND, { component: editor.getSelected() }),
                        },
                        {
                            type: 'text',
                            name: 'data-icon-name',
                            label: 'Icon Name',
                        },
                        {
                            type: 'select',
                            name: 'data-icon-variant',
                            label: 'Style',
                            options: [
                                { id: 'material-symbols-outlined', name: 'Symbols Outlined' },
                                { id: 'material-symbols-rounded', name: 'Symbols Rounded' },
                                { id: 'material-symbols-sharp', name: 'Symbols Sharp' },
                                { id: 'material-icons', name: 'Icons Filled' },
                                { id: 'material-icons-outlined', name: 'Icons Outlined' },
                                { id: 'material-icons-round', name: 'Icons Round' },
                                { id: 'material-icons-sharp', name: 'Icons Sharp' },
                            ],
                        },
                    ],
                },
                init() {
                    this.on('change:attributes:data-icon-name', () => {
                        const glyph = String(this.getAttributes()['data-icon-name'] || '').trim() || 'home';
                        this.components(glyph);
                    });

                    const syncIconVariantAttributes = () => {
                        if (this.__materialIconSyncingAttrs) {
                            return;
                        }

                        const attrs = this.getAttributes() || {};
                        const resolvedVariant = detectMaterialIconClass(
                            attrs['data-icon-variant'],
                            detectMaterialIconClass(attrs.class, iconClass),
                        );
                        const mergedClassName = buildMaterialIconClassName(
                            attrs.class,
                            resolvedVariant,
                            this.getId?.() || this.cid,
                        );

                        const nextAttrs = {};
                        if (attrs.class !== mergedClassName) {
                            nextAttrs.class = mergedClassName;
                        }
                        if (attrs['data-icon-variant'] !== resolvedVariant) {
                            nextAttrs['data-icon-variant'] = resolvedVariant;
                        }

                        if (Object.keys(nextAttrs).length > 0) {
                            this.__materialIconSyncingAttrs = true;
                            this.addAttributes(nextAttrs);
                            this.__materialIconSyncingAttrs = false;
                        }
                    };

                    this.on('change:attributes:class', syncIconVariantAttributes);
                    this.on('change:attributes:data-icon-variant', syncIconVariantAttributes);
                    syncIconVariantAttributes();
                },
            },
            view: {
                events: {
                    dblclick: 'onDblClick',
                },
                onRender() {
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
                    this.model.em.runCommand(MATERIAL_ICON_PICKER_COMMAND, { component: this.model });
                },
            },
        });
    }

    if (!editor.BlockManager.get('material-icon')) {
        editor.BlockManager.add('material-icon', {
            label: 'Material Icon',
            category: 'Media',
            content: {
                type: 'material-icon',
            },
        });
    }

    editor.on('load', ensureMaterialIconsInCanvas);
    editor.on('canvas:frame:load', ensureMaterialIconsInCanvas);
    ensureMaterialIconsInCanvas();
};

const applyWorkspacePlugins = (editor, options = {}) => {
    const workspacePlugins = Array.isArray(options.workspacePlugins) ? options.workspacePlugins : [];
    const workspacePluginMap = new Map(
        workspacePlugins
            .map((plugin) => [String(plugin?.slug || '').trim().toLowerCase(), plugin])
            .filter(([slug]) => slug),
    );

    const hasTailwindWorkspacePlugin = workspacePluginMap.has(WORKSPACE_PLUGIN_TAILWIND);
    const hasTailwindAutocompleteWorkspacePlugin = workspacePluginMap.has(WORKSPACE_PLUGIN_TAILWIND_AUTOCOMPLETE);
    const hasTailwindCardsWorkspacePlugin = workspacePluginMap.has(WORKSPACE_PLUGIN_TAILWIND_CARDS);

    // Autocomplete is useful only if Tailwind CSS is actually loaded in the canvas.
    // If users enable autocomplete alone, ensure Tailwind runtime is still available.
    if (hasTailwindAutocompleteWorkspacePlugin && !hasTailwindWorkspacePlugin) {
        installTailwindWorkspacePlugin(editor, { use_cdn: '1' });
    }

    // Cards block templates depend on Tailwind runtime utilities in editor canvas.
    if (hasTailwindCardsWorkspacePlugin && !hasTailwindWorkspacePlugin) {
        installTailwindWorkspacePlugin(editor, { use_cdn: '1' });
    }

    workspacePlugins.forEach((plugin) => {
        const slug = String(plugin?.slug || '').trim().toLowerCase();
        if (!slug) {
            return;
        }

        if (slug === WORKSPACE_PLUGIN_TAILWIND) {
            installTailwindWorkspacePlugin(editor, plugin?.settings || {});
            return;
        }

        if (slug === WORKSPACE_PLUGIN_TAILWIND_AUTOCOMPLETE) {
            installTailwindAutocompleteWorkspacePlugin(editor, plugin?.settings || {});
            return;
        }

        if (slug === WORKSPACE_PLUGIN_MATERIAL_ICONS) {
            installMaterialIconsWorkspacePlugin(editor, plugin?.settings || {});
            return;
        }

        if (slug === WORKSPACE_PLUGIN_TAILWIND_CARDS) {
            installTailwindCardsWorkspacePlugin(editor, plugin?.settings || {});
            return;
        }

        if (slug === WORKSPACE_PLUGIN_LP_BUILDER) {
            installLpBuilderWorkspacePlugin(editor, plugin?.settings || {});
        }
    });
};

const exposeGlobalRegistry = (registry, mode) => {
    window.funnelEditorPluginRegistry = {
        version: SYSTEM_VERSION,
        mode,
        plugins: registry.list,
        aiSafeRegistry: getAiSafeComponentRegistry(registry),
    };
};

export const initFunnelEditorPluginSystem = (editor, options = {}) => {
    const mode = normalizeMode(options.mode);
    const registry = toListMap(EDITOR_PLUGIN_DEFINITIONS);

    installRuntimePlugins(editor, registry, options);
    applyWorkspacePlugins(editor, options);
    installMetadataBridge(editor, registry);
    exposeGlobalRegistry(registry, mode);

    const api = {
        version: SYSTEM_VERSION,
        mode,
        plugins: registry.list,
        getById: (id) => registry.byId.get(id) || null,
        validate: () => validateEditorTree(editor, registry),
        serialize: () => serializeEditorProject(editor, registry, mode),
        parseStoredProjectData,
        getAiSafeRegistry: () => getAiSafeComponentRegistry(registry),
    };

    editor.__funnelPluginSystem = api;
    return api;
};

export {
    MODE_EDITOR,
    MODE_PREVIEW,
    MODE_PUBLISHED,
    SYSTEM_KEY,
    SYSTEM_VERSION,
    parseStoredProjectData,
};
