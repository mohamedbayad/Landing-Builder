const fs = require('fs');

const file = 'c:/Users/DELL/Desktop/web app/system/resources/js/editor.js';
let content = fs.readFileSync(file, 'utf8');

// 1. Add import
if (!content.includes('import editorAnimationSafeModePlugin')) {
    content = content.replace(
        "import advancedEditingControlsPlugin from './grapesjs/plugins/advanced-editing-controls';",
        "import advancedEditingControlsPlugin from './grapesjs/plugins/advanced-editing-controls';\nimport editorAnimationSafeModePlugin from './grapesjs/plugins/editor-animation-safe-mode';"
    );
}

// 2. Add to plugins array
if (!content.includes('editorAnimationSafeModePlugin,')) {
    content = content.replace(
        "advancedEditingControlsPlugin,",
        "advancedEditingControlsPlugin,\n            editorAnimationSafeModePlugin,"
    );
    content = content.replace(
        "[advancedEditingControlsPlugin]: {},",
        "[advancedEditingControlsPlugin]: {},\n            [editorAnimationSafeModePlugin]: {},"
    );
}

// 3. Strip original btn-save cleaner functions and replace with runCommand calls
const cleanerLogicRegex = /function isInsideSolutionComponent.*?let json = editor\.getProjectData\(\);/s;
if (cleanerLogicRegex.test(content)) {
    const newSaveLogic = `
            // 1. DETOX COMPONENTS MEMORY BEFORE EXTRACTING DATA
            editor.runCommand('animation-safe:sanitize-payload');
            
            // 2. Extract Data
            let html = editor.getHtml();
            html = editor.runCommand('animation-safe:detox-html', { html: html });
            
            const css = editor.getCss();
            let json = editor.getProjectData();
`;
    content = content.replace(cleanerLogicRegex, newSaveLogic);
} else {
    // If exact regex fails, log it so I can see what happened
    console.log("Regex for cleanerLogic failed to match in editor.js. Please inspect.");
}

fs.writeFileSync(file, content);
console.log('Successfully installed editorAnimationSafeModePlugin into editor.js and stripped old monolithic code.');
