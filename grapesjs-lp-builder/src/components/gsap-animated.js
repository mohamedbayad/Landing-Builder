/**
 * GrapesJS component type for GSAP-animated sections.
 * Includes traits, live preview rendering, and trigger-aware behavior.
 */

import {
    collapseDynamicSection,
    isDynamicSectionCollapsed,
    upsertSectionBadge,
} from '../utils/badge-injector';

const GSAP_ANIMATIONS = [
    'fadeInUp',
    'fadeInDown',
    'fadeInLeft',
    'fadeInRight',
    'slideInLeft',
    'slideInRight',
    'zoomIn',
    'zoomOut',
    'bounceIn',
    'flipInX',
    'flipInY',
    'rotateIn',
];

const GSAP_EASES = [
    'none',
    'power1.out',
    'power2.out',
    'power3.out',
    'power4.out',
    'back.out(1.7)',
    'elastic.out(1,0.3)',
    'bounce.out',
    'circ.out',
    'expo.out',
];

const GSAP_TRIGGERS = ['scroll', 'load', 'hover', 'click'];

const clamp = (value, min, max, fallback) => {
    const parsed = Number.parseFloat(value);
    if (!Number.isFinite(parsed)) {
        return fallback;
    }
    return Math.min(max, Math.max(min, parsed));
};

const toBool = (value) => String(value).trim().toLowerCase() === 'true';

const debounce = (fn, waitMs) => {
    let timer = 0;
    return function debounced(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), waitMs);
    };
};

/**
 * Return GSAP from vars for known animation presets.
 * @param {string} animation
 * @returns {object}
 */
export function getFromVars(animation) {
    const preset = String(animation || '').trim();

    switch (preset) {
    case 'fadeInDown':
        return { opacity: 0, y: -40 };
    case 'fadeInLeft':
        return { opacity: 0, x: -40 };
    case 'fadeInRight':
        return { opacity: 0, x: 40 };
    case 'slideInLeft':
        return { x: -120, opacity: 0 };
    case 'slideInRight':
        return { x: 120, opacity: 0 };
    case 'zoomIn':
        return { scale: 0.7, opacity: 0 };
    case 'zoomOut':
        return { scale: 1.25, opacity: 0 };
    case 'bounceIn':
        return { scale: 0.35, opacity: 0 };
    case 'flipInX':
        return { rotateX: 90, opacity: 0, transformPerspective: 600 };
    case 'flipInY':
        return { rotateY: 90, opacity: 0, transformPerspective: 600 };
    case 'rotateIn':
        return { rotate: -15, opacity: 0, transformOrigin: 'center center' };
    case 'fadeInUp':
    default:
        return { opacity: 0, y: 40 };
    }
}

const optionsFromList = (items) => items.map((item) => ({ id: item, name: item }));

const cleanupPreview = (el, win) => {
    if (!el) {
        return;
    }

    if (el.__lpGsapPreviewCleanup) {
        el.__lpGsapPreviewCleanup();
    }

    if (win?.gsap) {
        const targets = [el, ...Array.from(el.children || []).filter((child) => (
            !child.hasAttribute('data-lp-badge') && !child.hasAttribute('data-lp-overlay')
        ))];
        win.gsap.killTweensOf(targets);
        win.gsap.set(targets, { clearProps: 'transform,opacity,filter' });
    }
};

const runGsapPreview = (model, el) => {
    if (!el || !model) {
        return false;
    }

    const win = el.ownerDocument?.defaultView;
    const gsap = win?.gsap;
    if (!gsap) {
        return false;
    }

    cleanupPreview(el, win);

    const attrs = model.getAttributes() || {};
    const animation = attrs['data-gsap-animation'] || 'fadeInUp';
    const duration = clamp(attrs['data-gsap-duration'], 0.1, 5, 1);
    const delay = clamp(attrs['data-gsap-delay'], 0, 3, 0);
    const ease = attrs['data-gsap-ease'] || 'power2.out';
    const trigger = attrs['data-gsap-trigger'] || 'scroll';
    const animateChildren = toBool(attrs['data-gsap-children']);
    const stagger = clamp(attrs['data-gsap-stagger'], 0, 1, 0.1);

    const editableChildren = Array.from(el.children || []).filter((child) => (
        !child.hasAttribute('data-lp-badge') && !child.hasAttribute('data-lp-overlay')
    ));
    const targets = animateChildren && editableChildren.length > 0 ? editableChildren : el;
    const vars = {
        ...getFromVars(animation),
        duration,
        delay,
        ease,
    };

    if (Array.isArray(targets) && targets.length > 1 && animateChildren) {
        vars.stagger = stagger;
    }

    const play = () => {
        if (win?.gsap) {
            win.gsap.killTweensOf(targets);
            win.gsap.from(targets, vars);
        }
    };

    const listeners = [];
    const addListener = (eventName) => {
        const handler = () => play();
        el.addEventListener(eventName, handler);
        listeners.push([eventName, handler]);
    };

    if (trigger === 'scroll' && win.ScrollTrigger) {
        gsap.from(targets, {
            ...vars,
            scrollTrigger: {
                trigger: el,
                start: 'top 80%',
                toggleActions: 'play none none reset',
            },
        });
    } else if (trigger === 'hover') {
        addListener('mouseenter');
        play();
    } else if (trigger === 'click') {
        addListener('click');
        play();
    } else {
        play();
    }

    el.__lpGsapPreviewCleanup = () => {
        listeners.forEach(([eventName, handler]) => el.removeEventListener(eventName, handler));
    };

    return true;
};

/**
 * Register gsap-animated component type.
 * @param {import('grapesjs').Editor} editor
 */
export default function registerGsapAnimated(editor) {
    const domComponents = editor.DomComponents;

    domComponents.addType('gsap-animated', {
        isComponent(el) {
            if (!el || typeof el.getAttribute !== 'function') {
                return false;
            }

            if (el.getAttribute('data-gjs-type') === 'gsap-animated') {
                return { type: 'gsap-animated' };
            }

            return false;
        },

        model: {
            defaults: {
                name: 'GSAP Section',
                draggable: true,
                droppable: true,
                style: { 'min-height': '80px', position: 'relative' },
                traits: [
                    {
                        type: 'select',
                        name: 'data-gsap-animation',
                        label: 'Animation',
                        options: optionsFromList(GSAP_ANIMATIONS),
                    },
                    {
                        type: 'number',
                        name: 'data-gsap-duration',
                        label: 'Duration (s)',
                        min: 0.1,
                        max: 5,
                        step: 0.1,
                    },
                    {
                        type: 'number',
                        name: 'data-gsap-delay',
                        label: 'Delay (s)',
                        min: 0,
                        max: 3,
                        step: 0.1,
                    },
                    {
                        type: 'select',
                        name: 'data-gsap-ease',
                        label: 'Easing',
                        options: optionsFromList(GSAP_EASES),
                    },
                    {
                        type: 'select',
                        name: 'data-gsap-trigger',
                        label: 'Trigger',
                        options: optionsFromList(GSAP_TRIGGERS),
                    },
                    {
                        type: 'checkbox',
                        name: 'data-gsap-children',
                        label: 'Animate children',
                    },
                    {
                        type: 'number',
                        name: 'data-gsap-stagger',
                        label: 'Stagger (s)',
                        min: 0,
                        max: 1,
                        step: 0.05,
                    },
                ],
                attributes: {
                    'data-gsap-animation': 'fadeInUp',
                    'data-gsap-duration': '1',
                    'data-gsap-delay': '0',
                    'data-gsap-ease': 'power2.out',
                    'data-gsap-trigger': 'scroll',
                    'data-gsap-children': 'false',
                    'data-gsap-stagger': '0.1',
                },
            },
        },

        view: {
            init() {
                this.debouncedPreview = debounce(() => this.renderPreview(), 120);
                this.onLpSectionExpanded = ({ cid } = {}) => {
                    if (cid === this.model.cid) {
                        this.renderPreview();
                    }
                };
                editor.on('lp:section:expanded', this.onLpSectionExpanded);
                this.listenTo(this.model, 'change:attributes', this.debouncedPreview);
                this.listenTo(this.model, 'change:attributes', () => {
                    if (isDynamicSectionCollapsed(this.el)) {
                        collapseDynamicSection(this.model, this.el, editor);
                    } else {
                        upsertSectionBadge(this.model, this.el, editor);
                    }
                });
            },

            onRender() {
                collapseDynamicSection(this.model, this.el, editor);
                this.renderPreview();
            },

            removed() {
                if (this.onLpSectionExpanded) {
                    editor.off('lp:section:expanded', this.onLpSectionExpanded);
                }
                cleanupPreview(this.el, this.el?.ownerDocument?.defaultView);
            },

            renderPreview() {
                const el = this.el;
                if (!el) {
                    return;
                }

                upsertSectionBadge(this.model, el, editor);
                if (isDynamicSectionCollapsed(el)) {
                    cleanupPreview(el, el.ownerDocument?.defaultView);
                    return;
                }

                const didRun = runGsapPreview(this.model, el);
                if (didRun) {
                    return;
                }

                const doc = el.ownerDocument;
                if (!doc || el.__lpGsapWaitReadyBound) {
                    return;
                }

                el.__lpGsapWaitReadyBound = true;
                const onceReady = () => {
                    el.__lpGsapWaitReadyBound = false;
                    runGsapPreview(this.model, el);
                    doc.removeEventListener('lp:ready', onceReady);
                };

                doc.addEventListener('lp:ready', onceReady, { once: true });
            },
        },
    });
}
