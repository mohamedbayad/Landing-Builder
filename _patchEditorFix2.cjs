const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/resources/js/editor.js';

let content = fs.readFileSync(file, 'utf8');

const cleanerLogic = `
        btnSave.addEventListener('click', async () => {
            // Helper to clean GSAP/Animation state pollution before serialization
            function cleanAnimationState(cmp) {
                if (!cmp) return;
                
                let classes = [];
                try {
                    // getClasses() returns objects in GrapesJS. We want string names.
                    const rawClasses = cmp.getClasses ? cmp.getClasses() : [];
                    classes = rawClasses.map(c => typeof c === 'string' ? c : (c.id || (typeof c.get === 'function' ? c.get('name') : '')));
                } catch(e) {}
                
                const attrs = cmp.getAttributes ? cmp.getAttributes() : {};
                
                const isAnimated = classes.some(c => c === 'gs-reveal' || c.startsWith('animate-') || c === 'fade' || c === 'scroll') ||
                                   attrs['data-animate'] || attrs['data-gsap'] || classes.includes('pin-spacer');
                
                if (isAnimated) {
                    const style = cmp.getStyle();
                    let modified = false;
                    
                    const propsToClean = ['transform', 'translate', 'scale', 'rotate', 'opacity', 'visibility', 'translate3d'];
                    propsToClean.forEach(prop => {
                        if (style[prop] !== undefined) {
                            delete style[prop];
                            modified = true;
                        }
                    });

                    // GSAP ScrollTrigger often injects hardcoded px heights (e.g., height: 10px) that crush layouts.
                    if (style['height'] && /(px|vh|vw|%)$/.test(style['height'])) {
                        const hVal = parseFloat(style['height']);
                        // If it's suspiciously small or exactly 0... actually GSAP sometimes sets height: 10px or height: 0px or height: ... wait.
                        // Or if we just clear height because the element is GS-REVEAL, and reveals should rely on CSS stylesheet, not inline height!
                        // Deleting inline height for animated elements is extremely safe, as they rely on structure.
                        delete style['height'];
                        modified = true;
                    }
                    if (style['width'] && /(px|vw|%)$/.test(style['width'])) {
                        // GSAP ScrollTrigger also injects explicit width
                        delete style['width'];
                        modified = true;
                    }
                    
                    if (modified) {
                        cmp.setStyle(style);
                    }
                }
                
                // Recursively clean children
                if (cmp.components) {
                    cmp.components().forEach(child => cleanAnimationState(child));
                }
            }
            
            // 1. Traverse and clean VDOM state BEFORE serialization
            try {
                editor.getComponents().forEach(cmp => cleanAnimationState(cmp));
            } catch(cleanErr) {
                console.error('Error cleaning animation state:', cleanErr);
            }

            // 2. NOW Extract clean data
            const html = editor.getHtml();
            const css = editor.getCss();
            const json = editor.getProjectData();
`;

// It might have already been patched with the old syntax, let's reverse it and patch again.
content = content.replace(
    /btnSave\.addEventListener\('click',\s*async\s*\(\)\s*=>\s*\{\s*\/\/\s*Helper to clean GSAP.*?const json = editor\.getProjectData\(\);/s,
    cleanerLogic
);

fs.writeFileSync(file, content);
console.log('Repatched editor.js with correct cleanAnimationState');
