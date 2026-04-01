const fs = require('fs');

const file = 'c:/Users/DELL/Desktop/web app/system/resources/js/editor.js';
let content = fs.readFileSync(file, 'utf8');

// STRIP:
// const forceShowSolutionSection = () => { ... };
const extractRegex = /const forceShowSolutionSection = \(\) => {[\s\S]*?};\n/g;
content = content.replace(extractRegex, '');

// STRIP invocations:
content = content.replace(/\s*forceShowSolutionSection\(\);\s*/g, '\n');
content = content.replace(/\s*\[100, 400, 1000, 1800\]\.forEach\(delay => setTimeout\(forceShowSolutionSection, delay\)\);\s*/g, '\n');

fs.writeFileSync(file, content);
console.log('Successfully stripped forceShowSolutionSection from editor.js');

const safeModeFile = 'c:/Users/DELL/Desktop/web app/system/resources/js/grapesjs/plugins/editor-animation-safe-mode.js';
let safeContent = fs.readFileSync(safeModeFile, 'utf8');

// Add the :has selector to perfectly mimic the old parentElement logic
safeContent = safeContent.replace(
    'body[data-render-mode="editor"] #solution .slide-solution {',
    `body[data-render-mode="editor"] #solution *:has(> .slide-solution) {
                        position: relative !important;
                        height: auto !important;
                        min-height: 0 !important;
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 16px !important;
                    }
                    body[data-render-mode="editor"] #solution .slide-solution {`
);

fs.writeFileSync(safeModeFile, safeContent);
console.log('Successfully updated CSS injection for slideHost wrapper.');
