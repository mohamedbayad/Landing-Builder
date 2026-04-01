const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/resources/js/grapesjs/plugins/canvas-interaction-control.js';

let content = fs.readFileSync(file, 'utf8');

// The user was aggressive with height: 0, opacity: 0 resets in canvas-interaction-control.
// Editor-Animation-Safe plugin handles safe mode, so canvas-interaction-control should strictly handle display: none / hidden.
// We strip the height / max-height scrubbing from canvas interaction to prevent it colliding with animation boundaries.

content = content.replace(
    /\s*if\s*\([^\{]*el\.style\.height\s*===\s*'0'[^\{]*\)\s*\{\s*el\.style\.removeProperty\('height'\);\s*el\.style\.removeProperty\('max-height'\);\s*el\.style\.removeProperty\('overflow'\);\s*el\.style\.opacity\s*=\s*'1';\s*el\.style\.visibility\s*=\s*'visible';\s*\}/g,
    ""
);

// also in mutation observer
content = content.replace(
    /\s*if\s*\([^\{]*target\.style\.height\s*===\s*'0'[^\{]*\)\s*\{\s*target\.style\.removeProperty\('height'\);\s*target\.style\.removeProperty\('max-height'\);\s*target\.style\.removeProperty\('overflow'\);\s*\}/g,
    ""
);

fs.writeFileSync(file, content);
console.log('Successfully detached aggressive height resets from canvas-interaction-control.js');
