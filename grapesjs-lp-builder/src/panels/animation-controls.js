/**
 * Adds GrapesJS panel controls for animation preview/reset actions.
 */

const fromVarsForAnimation = (animation) => {
    switch (String(animation || '')) {
    case 'fadeInDown':
        return { opacity: 0, y: -40 };
    case 'fadeInLeft':
        return { opacity: 0, x: -40 };
    case 'fadeInRight':
        return { opacity: 0, x: 40 };
    case 'slideInLeft':
        return { opacity: 0, x: -120 };
    case 'slideInRight':
        return { opacity: 0, x: 120 };
    case 'zoomIn':
        return { opacity: 0, scale: 0.7 };
    case 'zoomOut':
        return { opacity: 0, scale: 1.25 };
    case 'bounceIn':
        return { opacity: 0, scale: 0.35 };
    case 'flipInX':
        return { opacity: 0, rotateX: 90, transformPerspective: 600 };
    case 'flipInY':
        return { opacity: 0, rotateY: 90, transformPerspective: 600 };
    case 'rotateIn':
        return { opacity: 0, rotate: -15, transformOrigin: 'center center' };
    case 'fadeInUp':
    default:
        return { opacity: 0, y: 40 };
    }
};

const parseNumber = (value, fallback, min, max) => {
    const parsed = Number.parseFloat(value);
    if (!Number.isFinite(parsed)) {
        return fallback;
    }
    return Math.min(max, Math.max(min, parsed));
};

/**
 * Register animation preview/reset buttons and commands.
 * @param {import('grapesjs').Editor} editor
 */
export default function registerAnimationControls(editor) {
    const commands = editor.Commands;

    commands.add('lp-preview-animations', {
        run(ed) {
            const frameWin = ed.Canvas.getWindow?.();
            const frameDoc = ed.Canvas.getDocument?.();
            if (!frameWin?.gsap || !frameDoc) {
                return;
            }

            const gsap = frameWin.gsap;
            const nodes = Array.from(frameDoc.querySelectorAll('[data-gjs-type="gsap-animated"]'));

            nodes.forEach((el) => {
                const attrs = el.attributes;
                const animation = attrs.getNamedItem('data-gsap-animation')?.value || 'fadeInUp';
                const duration = parseNumber(attrs.getNamedItem('data-gsap-duration')?.value, 1, 0.1, 5);
                const delay = parseNumber(attrs.getNamedItem('data-gsap-delay')?.value, 0, 0, 3);
                const ease = attrs.getNamedItem('data-gsap-ease')?.value || 'power2.out';
                const stagger = parseNumber(attrs.getNamedItem('data-gsap-stagger')?.value, 0.1, 0, 1);
                const animateChildren = String(attrs.getNamedItem('data-gsap-children')?.value || 'false').toLowerCase() === 'true';

                const targets = animateChildren && el.children.length > 0 ? Array.from(el.children) : el;
                gsap.killTweensOf([el, ...Array.from(el.children || [])]);
                gsap.set([el, ...Array.from(el.children || [])], { clearProps: 'transform,opacity,filter' });
                gsap.from(targets, {
                    ...fromVarsForAnimation(animation),
                    duration,
                    delay,
                    ease,
                    stagger: Array.isArray(targets) && targets.length > 1 ? stagger : 0,
                });
            });
        },
    });

    commands.add('lp-reset-animations', {
        run(ed) {
            const frameWin = ed.Canvas.getWindow?.();
            const frameDoc = ed.Canvas.getDocument?.();
            if (!frameWin?.gsap || !frameDoc) {
                return;
            }

            const gsap = frameWin.gsap;
            const nodes = Array.from(frameDoc.querySelectorAll('[data-gjs-type="gsap-animated"]'));
            const allTargets = [];

            nodes.forEach((el) => {
                allTargets.push(el);
                allTargets.push(...Array.from(el.children || []));
            });

            if (frameWin.ScrollTrigger?.getAll) {
                frameWin.ScrollTrigger.getAll().forEach((trigger) => trigger.kill());
            }

            gsap.globalTimeline.getChildren(true, true, true).forEach((timeline) => timeline.kill());
            gsap.killTweensOf(allTargets);
            gsap.set(allTargets, { clearProps: 'transform,opacity,filter' });
        },
    });

    const panels = editor.Panels;
    const panelId = 'options';

    if (!panels.getButton(panelId, 'lp-preview-animations')) {
        panels.addButton(panelId, {
            id: 'lp-preview-animations',
            className: 'fa fa-play',
            command: 'lp-preview-animations',
            attributes: { title: 'Preview Animations' },
        });
    }

    if (!panels.getButton(panelId, 'lp-reset-animations')) {
        panels.addButton(panelId, {
            id: 'lp-reset-animations',
            className: 'fa fa-undo',
            command: 'lp-reset-animations',
            attributes: { title: 'Reset Animations' },
        });
    }
}
