/**
 * Export utilities for GrapesJS LP Builder templates.
 * Reads current section data attributes and generates animations.js.
 */

import { serializeSceneBuilderHelpers } from './scene-builder';

const requiredAttrsByType = {
    'gsap-animated': [
        'data-gsap-animation',
        'data-gsap-duration',
        'data-gsap-delay',
        'data-gsap-ease',
        'data-gsap-trigger',
        'data-gsap-children',
        'data-gsap-stagger',
    ],
    'threejs-scene': [
        'data-scene-type',
        'data-scene-color',
        'data-scene-bg',
        'data-scene-height',
        'data-scene-speed',
        'data-particle-count',
        'data-threejs-overlay',
        'data-wireframe',
        'data-auto-rotate',
    ],
};

const toBool = (value) => String(value || '').trim().toLowerCase() === 'true';

const toNumber = (value, fallback) => {
    const parsed = Number.parseFloat(value);
    return Number.isFinite(parsed) ? parsed : fallback;
};

const parseHtml = (html) => {
    const parser = new DOMParser();
    return parser.parseFromString('<!doctype html><html><body>' + html + '</body></html>', 'text/html');
};

const collectSections = (doc) => {
    const nodes = Array.from(doc.querySelectorAll('[data-gjs-type]'));
    const counters = new Map();

    return nodes.map((node) => {
        const type = String(node.getAttribute('data-gjs-type') || '').trim();
        const index = counters.get(type) || 0;
        counters.set(type, index + 1);

        const requiredAttrs = requiredAttrsByType[type] || [];
        const attrs = {};
        requiredAttrs.forEach((key) => {
            attrs[key] = String(node.getAttribute(key) || '');
        });

        return {
            id: String(node.getAttribute('id') || '').trim(),
            type,
            index,
            attrs,
        };
    });
};

const buildGetFromVarsSource = () => [
    'function getFromVars(animation) {',
    '    switch (String(animation || "")) {',
    '    case "fadeInDown": return { opacity: 0, y: -40 };',
    '    case "fadeInLeft": return { opacity: 0, x: -40 };',
    '    case "fadeInRight": return { opacity: 0, x: 40 };',
    '    case "slideInLeft": return { opacity: 0, x: -120 };',
    '    case "slideInRight": return { opacity: 0, x: 120 };',
    '    case "zoomIn": return { opacity: 0, scale: 0.7 };',
    '    case "zoomOut": return { opacity: 0, scale: 1.25 };',
    '    case "bounceIn": return { opacity: 0, scale: 0.35 };',
    '    case "flipInX": return { opacity: 0, rotateX: 90, transformPerspective: 600 };',
    '    case "flipInY": return { opacity: 0, rotateY: 90, transformPerspective: 600 };',
    '    case "rotateIn": return { opacity: 0, rotate: -15, transformOrigin: "center center" };',
    '    case "fadeInUp":',
    '    default: return { opacity: 0, y: 40 };',
    '    }',
    '}',
].join('\n');

/**
 * Generate readable animations.js code from parsed section metadata.
 * @param {Array<{id:string,type:string,index:number,attrs:Record<string,string>}>} sections
 * @returns {string}
 */
export function generateAnimationsJS(sections) {
    const gsapDefs = sections
        .filter((section) => section.type === 'gsap-animated')
        .map((section) => ({
            id: section.id,
            type: section.type,
            index: section.index,
            animation: section.attrs['data-gsap-animation'] || 'fadeInUp',
            duration: toNumber(section.attrs['data-gsap-duration'], 1),
            delay: toNumber(section.attrs['data-gsap-delay'], 0),
            ease: section.attrs['data-gsap-ease'] || 'power2.out',
            trigger: section.attrs['data-gsap-trigger'] || 'scroll',
            children: toBool(section.attrs['data-gsap-children']),
            stagger: toNumber(section.attrs['data-gsap-stagger'], 0.1),
        }));

    const threeDefs = sections
        .filter((section) => section.type === 'threejs-scene')
        .map((section) => ({
            id: section.id,
            type: section.type,
            index: section.index,
            sceneType: section.attrs['data-scene-type'] || 'particles',
            sceneColor: section.attrs['data-scene-color'] || '#5b8cff',
            sceneBg: section.attrs['data-scene-bg'] || 'transparent',
            sceneHeight: Number.parseInt(section.attrs['data-scene-height'], 10) || 400,
            sceneSpeed: toNumber(section.attrs['data-scene-speed'], 1),
            particleCount: Number.parseInt(section.attrs['data-particle-count'], 10) || 120,
            overlay: toBool(section.attrs['data-threejs-overlay']),
            wireframe: toBool(section.attrs['data-wireframe']),
            autoRotate: toBool(section.attrs['data-auto-rotate']),
        }));

    const hasGsap = gsapDefs.length > 0;
    const hasThree = threeDefs.length > 0;

    const parts = [];
    parts.push('/**');
    parts.push(' * Auto-generated animations runtime from GrapesJS LP Builder.');
    parts.push(' * Generated on: ' + new Date().toISOString());
    parts.push(' */');
    parts.push('');
    parts.push(buildGetFromVarsSource());
    parts.push('');
    parts.push(serializeSceneBuilderHelpers());
    parts.push('');
    parts.push('document.addEventListener("DOMContentLoaded", function () {');
    parts.push('    var gsapSections = ' + JSON.stringify(gsapDefs, null, 4) + ';');
    parts.push('    var threeSections = ' + JSON.stringify(threeDefs, null, 4) + ';');
    parts.push('');
    parts.push('    function resolveSection(def) {');
    parts.push('        if (!def) return null;');
    parts.push('        if (def.id) {');
    parts.push('            return document.getElementById(def.id);');
    parts.push('        }');
    parts.push('        var list = document.querySelectorAll("[data-gjs-type=\\"" + def.type + "\\"]");');
    parts.push('        return list[def.index] || null;');
    parts.push('    }');
    parts.push('');

    if (hasGsap) {
        parts.push('    if (window.gsap) {');
        parts.push('        if (window.ScrollTrigger && typeof window.gsap.registerPlugin === "function") {');
        parts.push('            window.gsap.registerPlugin(window.ScrollTrigger);');
        parts.push('        }');
        parts.push('');
        parts.push('        gsapSections.forEach(function (def) {');
        parts.push('            var element = resolveSection(def);');
        parts.push('            if (!element) return;');
        parts.push('');
        parts.push('            var targets = def.children ? Array.prototype.slice.call(element.children || []) : element;');
        parts.push('            var vars = Object.assign({}, getFromVars(def.animation), {');
        parts.push('                duration: def.duration,');
        parts.push('                delay: def.delay,');
        parts.push('                ease: def.ease');
        parts.push('            });');
        parts.push('');
        parts.push('            if (def.children && Array.isArray(targets) && targets.length > 1) {');
        parts.push('                vars.stagger = def.stagger;');
        parts.push('            }');
        parts.push('');
        parts.push('            if (def.trigger === "scroll" && window.ScrollTrigger) {');
        parts.push('                vars.scrollTrigger = { trigger: element, start: "top 80%", toggleActions: "play none none reset" };');
        parts.push('                window.gsap.from(targets, vars);');
        parts.push('            } else if (def.trigger === "hover") {');
        parts.push('                element.addEventListener("mouseenter", function () { window.gsap.from(targets, vars); });');
        parts.push('            } else if (def.trigger === "click") {');
        parts.push('                element.addEventListener("click", function () { window.gsap.from(targets, vars); });');
        parts.push('            } else {');
        parts.push('                window.gsap.from(targets, vars);');
        parts.push('            }');
        parts.push('        });');
        parts.push('    }');
        parts.push('');
    }

    if (hasThree) {
        parts.push('    if (window.THREE) {');
        parts.push('        threeSections.forEach(function (def) {');
        parts.push('            var element = resolveSection(def);');
        parts.push('            if (!element) return;');
        parts.push('            buildScene(element, def);');
        parts.push('        });');
        parts.push('    }');
        parts.push('');
    }

    parts.push('});');

    return parts.join('\n');
}

/**
 * Export current template assets from editor state.
 * @param {import('grapesjs').Editor} editor
 * @returns {{html:string, css:string, animationsJS:string}}
 */
export function exportTemplate(editor) {
    const html = String(editor?.getHtml?.() || '');
    const css = String(editor?.getCss?.() || '');

    const doc = parseHtml(html);
    const sections = collectSections(doc);
    const animationsJS = generateAnimationsJS(sections);

    return {
        html,
        css,
        animationsJS,
    };
}

export default exportTemplate;
