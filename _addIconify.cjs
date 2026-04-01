const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/resources/views/editor.blade.php';
let content = fs.readFileSync(file, 'utf8');

if (!content.includes('iconify-icon.min.js')) {
    content = content.replace(
        "@vite(['resources/css/app.css', 'resources/js/editor.js'])",
        "@vite(['resources/css/app.css', 'resources/js/editor.js'])\n    <script src=\"https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js\"></script>"
    );
    fs.writeFileSync(file, content);
    console.log('Added iconify to editor.blade.php');
} else {
    console.log('Iconify already present');
}
