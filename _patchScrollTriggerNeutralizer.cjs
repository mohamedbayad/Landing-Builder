const fs = require('fs');

const file = 'c:/Users/DELL/Desktop/web app/system/app/Http/Controllers/LandingPageController.php';
let content = fs.readFileSync(file, 'utf8');

// The new block to replace the old $gsapNeutralizer block
const newNeutralizer = `        $gsapNeutralizer = "<script>
            window.isGrapesJSEditor = true;
            // 1. NEUTRALIZE GSAP
            Object.defineProperty(window, 'gsap', {
                configurable: true,
                get: function() { return this._gsap_mock; },
                set: function(val) {
                    this._gsap_real = val;
                    const mock = new Proxy(val, {
                        get: function(target, prop) {
                            if (['to', 'from', 'fromTo', 'registerPlugin', 'timeline', 'set', 'matchMedia'].includes(prop)) {
                                return function() { return target; };
                            }
                            if (prop === 'utils') {
                                return new Proxy(target.utils, {
                                    get: function(uT, uP) {
                                        if (uP === 'toArray') return (q) => Array.from(document.querySelectorAll(q));
                                        return Reflect.get(uT, uP);
                                    }
                                });
                            }
                            return Reflect.get(target, prop);
                        }
                    });
                    this._gsap_mock = mock;
                }
            });
            
            // 2. NEUTRALIZE SCROLLTRIGGER (PREVENTS .pin-spacer CORRUPTION)
            Object.defineProperty(window, 'ScrollTrigger', {
                configurable: true,
                get: function() { return this._st_mock; },
                set: function(val) {
                    this._st_real = val;
                    const mock = new Proxy(val, {
                        get: function(target, prop) {
                            if (['create', 'batch', 'matchMedia', 'refresh', 'kill', 'update', 'clearScrollMemory'].includes(prop)) {
                                return function() { return target; };
                            }
                            return Reflect.get(target, prop);
                        }
                    });
                    this._st_mock = mock;
                }
            });
        </script>";`;

// Find the old gsapNeutralizer definition block and replace it
content = content.replace(
    /\$gsapNeutralizer\s*=\s*"<script>.*?<\/script>";/s,
    newNeutralizer
);

fs.writeFileSync(file, content);
console.log('Successfully injected ScrollTrigger neutralizer into LandingPageController.php');
