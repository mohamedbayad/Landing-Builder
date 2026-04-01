const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/resources/js/editor.js';

let content = fs.readFileSync(file, 'utf8');

const cleanerLogic = `
        btnSave.addEventListener('click', async () => {
            // Helper 1: Clean GSAP/Animation state pollution from components model
            function cleanAnimationState(cmp) {
                if (!cmp) return;
                
                let classes = [];
                try {
                    const rawClasses = cmp.getClasses ? cmp.getClasses() : [];
                    classes = rawClasses.map(c => typeof c === 'string' ? c : (c.id || (typeof c.get === 'function' ? c.get('name') : '')));
                } catch(e) {}
                
                const attrs = cmp.getAttributes ? cmp.getAttributes() : {};
                const id = cmp.getId ? cmp.getId() : (attrs.id || '');
                
                // 1. UNSPOOL SCROLLTRIGGER WRAPPER COMPONENTS MEMORY
                if (classes.includes('pin-spacer')) {
                    console.log('Unspooling runtime .pin-spacer component inside VDOM');
                    const parent = cmp.parent();
                    if (parent) {
                        const index = cmp.index();
                        const children = cmp.components().models ? [...cmp.components().models] : [];
                        
                        // Remove the polluted wrapper
                        cmp.remove();
                        
                        // Re-insert children precisely where the wrapper was
                        children.forEach((child, i) => {
                            parent.components().add(child, { at: index + i });
                        });
                        
                        // Run cleanup on the newly extracted children instead
                        children.forEach(child => cleanAnimationState(child));
                        return; // Stop processing this deleted wrapper
                    }
                }
                
                // 2. SCRUB RUNTIME STYLES ON ANIMATED SECTIONS (e.g., #solution or marked sections)
                const isAnimatedSection = id === 'solution' || attrs['data-animated-section'] === 'true' || attrs['data-animated-section'] === true;
                const isAnimatedElement = classes.some(c => c === 'gs-reveal' || c.startsWith('animate-') || c === 'fade' || c === 'scroll') ||
                                          attrs['data-animate'] || attrs['data-gsap'];
                
                if (isAnimatedSection || isAnimatedElement) {
                    const style = cmp.getStyle();
                    let modified = false;
                    
                    const propsToClean = ['transform', 'translate', 'scale', 'rotate', 'opacity', 'visibility', 'translate3d'];
                    
                    // Specific to pinned sections: GSAP enforces these during runtime pinning
                    if (isAnimatedSection) {
                        propsToClean.push('height', 'max-height', 'width', 'max-width', 'inset', 'top', 'left', 'bottom', 'right', 'position', 'margin');
                    }

                    propsToClean.forEach(prop => {
                        if (style[prop] !== undefined) {
                            delete style[prop];
                            modified = true;
                        }
                    });

                    // For standard animated elements, aggressively kill runtime pixel heights
                    if (!isAnimatedSection && style['height'] && /(px|vh|vw|%)$/.test(style['height'])) {
                        delete style['height'];
                        modified = true;
                    }
                    if (!isAnimatedSection && style['width'] && /(px|vw|%)$/.test(style['width'])) {
                        delete style['width'];
                        modified = true;
                    }
                    
                    if (modified) {
                        cmp.setStyle(style);
                    }
                }
                
                if (cmp.components) {
                    // clone array to avoid mutation issues if children remove themselves
                    const children = cmp.components().models ? [...cmp.components().models] : [];
                    children.forEach(child => cleanAnimationState(child));
                }
            }
            
            // Helper 2: Final HTML structure detox (removes ScrollTrigger wrappers and leftover tags)
            function detoxHTMLString(rawHtml) {
                try {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = rawHtml;
                    
                    const animatedElements = tempDiv.querySelectorAll('#solution, [data-animated-section="true"], .gs-reveal, [data-animate], [data-gsap], .animate-on-scroll, .fade');
                    animatedElements.forEach(el => {
                        const isSection = el.id === 'solution' || el.getAttribute('data-animated-section') === 'true';
                        
                        if (el.style) {
                            el.style.removeProperty('transform');
                            el.style.removeProperty('translate');
                            el.style.removeProperty('scale');
                            el.style.removeProperty('rotate');
                            el.style.removeProperty('opacity');
                            el.style.removeProperty('visibility');
                            
                            if (isSection) {
                                el.style.removeProperty('height');
                                el.style.removeProperty('max-height');
                                el.style.removeProperty('width');
                                el.style.removeProperty('max-width');
                                el.style.removeProperty('inset');
                                el.style.removeProperty('top');
                                el.style.removeProperty('bottom');
                                el.style.removeProperty('left');
                                el.style.removeProperty('right');
                                el.style.removeProperty('position');
                                el.style.removeProperty('margin');
                            } else {
                                if (el.style.height && /(px|vh|vw|%)$/.test(el.style.height)) el.style.removeProperty('height');
                                if (el.style.width && /(px|vw|%)$/.test(el.style.width)) el.style.removeProperty('width');
                            }
                            
                            if (!el.getAttribute('style') || el.getAttribute('style').trim() === '') {
                                el.removeAttribute('style');
                            }
                        }
                    });
                    
                    // CRITICAL: ScrollTrigger wraps pinned elements in <div class="pin-spacer"> that breaks layouts.
                    // This un-wraps them, keeping the original child intact but destroying the wrapper.
                    const pinSpacers = tempDiv.querySelectorAll('.pin-spacer');
                    pinSpacers.forEach(spacer => {
                        const parent = spacer.parentNode;
                        while (spacer.firstChild) {
                            parent.insertBefore(spacer.firstChild, spacer);
                        }
                        parent.removeChild(spacer);
                    });
                    
                    return tempDiv.innerHTML;
                } catch(e) {
                    console.error('HTML Detox failed, using raw HTML', e);
                    return rawHtml;
                }
            }
            
            // 1. Traverse and clean VDOM state BEFORE serialization
            try {
                // Must clone root children array so mutations don't crash the iterator
                const rootComponents = editor.getWrapper().components().models ? [...editor.getWrapper().components().models] : [];
                rootComponents.forEach(cmp => cleanAnimationState(cmp));
            } catch(cleanErr) {
                console.error('Error cleaning animation state:', cleanErr);
            }

            // 2. Extract and physically detox the final HTML output
            let html = editor.getHtml();
            html = detoxHTMLString(html);
            
            // 3. Extract CSS and JSON natively
            const css = editor.getCss();
            let json = editor.getProjectData();
            
            // Note: Since we cleaned the components in step 1, the json output natively
            // contains the scrubbed component models. No double-wrap is saved!
`;

content = content.replace(
    /btnSave\.addEventListener\('click',\s*async\s*\(\)\s*=>\s*\{\s*\/\/\s*Helper 1.*?let json = editor\.getProjectData\(\);\s*\/\/ Note:.*?\!/s,
    cleanerLogic
);

fs.writeFileSync(file, content);
console.log('Successfully updated cleanAnimationState with unspool logic');
