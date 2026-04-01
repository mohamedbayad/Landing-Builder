const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/app/Http/Controllers/PublicLandingController.php';
let content = fs.readFileSync(file, 'utf8');

content = content.replace(/\\n\}/g, '}');

fs.writeFileSync(file, content);
console.log('Fixed \\n} at EOF');
