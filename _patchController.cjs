const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/app/Http/Controllers/LandingPageController.php';

let content = fs.readFileSync(file, 'utf8');

// Strip the $gsapNeutralizer assignment block
content = content.replace(/\$gsapNeutralizer\s*=\s*"<script>[\s\S]*?<\/script>";\s*/g, '');

// Strip the variable from the array_filter function call
content = content.replace(/\$gsapNeutralizer,\s*/g, '');

fs.writeFileSync(file, content);
console.log('Successfully detached $gsapNeutralizer from LandingPageController.php.');
