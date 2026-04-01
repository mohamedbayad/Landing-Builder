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
                    classes = cmp.getClasses ? cmp.getClasses() : [];
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
                    // If height is a precise pixel value on an animated element, scrub it.
                    if (style['height'] && /px$/.test(style['height'])) {
                        delete style['height'];
                        modified = true;
                    }
                    if (style['width'] && /px$/.test(style['width'])) {
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
            
            // 1. Traverse and clean DOM state BEFORE serialization
            editor.getComponents().forEach(cmp => cleanAnimationState(cmp));

            // 2. NOW Extract clean data
            const html = editor.getHtml();
            const css = editor.getCss();
            const json = editor.getProjectData();
`;

content = content.replace(
    /btnSave\.addEventListener\('click',\s*async\s*\(\)\s*=>\s*\{\s*const components = editor\.getComponents\(\);\s*const html = editor\.getHtml\(\);\s*const css = editor\.getCss\(\);\s*const json = editor\.getProjectData\(\);/s,
    cleanerLogic
);

fs.writeFileSync(file, content);
console.log('Patched editor.js with cleanAnimationState');
