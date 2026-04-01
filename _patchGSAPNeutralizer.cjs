const fs = require('fs');

const neutralizer = `
        $gsapNeutralizer = "<script>
            window.isGrapesJSEditor = true;
            Object.defineProperty(window, 'gsap', {
                configurable: true,
                get: function() { return this._gsap_mock; },
                set: function(val) {
                    this._gsap_real = val;
                    const mock = new Proxy(val, {
                        get: function(target, prop) {
                            if (['to', 'from', 'fromTo', 'registerPlugin'].includes(prop)) {
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
        </script>";

        $editorCustomHead = trim(implode("\\n", array_filter([
            $gsapNeutralizer,
            $tailwindHead,
            $landingHead,
        ])));
`;

const file = 'c:/Users/DELL/Desktop/web app/system/app/Http/Controllers/LandingPageController.php';
let content = fs.readFileSync(file, 'utf8');

// Replace the array_filter block for editorCustomHead
content = content.replace(
    /(\$editorCustomHead = trim\(implode\("\\n", array_filter\(\[)([\s\S]*?)(\]\)\)\);)/,
    function(match, p1, p2, p3) {
        if (!p2.includes('$gsapNeutralizer')) {
            return `
        $gsapNeutralizer = "<script>
            window.isGrapesJSEditor = true;
            Object.defineProperty(window, 'gsap', {
                configurable: true,
                get: function() { return this._gsap_mock; },
                set: function(val) {
                    this._gsap_real = val;
                    const mock = new Proxy(val, {
                        get: function(target, prop) {
                            if (['to', 'from', 'fromTo', 'registerPlugin'].includes(prop)) {
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
        </script>";

        $editorCustomHead = trim(implode("\\n", array_filter([
            $gsapNeutralizer,
            $tailwindHead,
            $landingHead,
        ])));`;
        }
        return match;
    }
);

fs.writeFileSync(file, content);
console.log('Injected gsapNeutralizer into LandingPageController.php');
