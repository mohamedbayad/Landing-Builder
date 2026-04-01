const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/resources/js/editor.js';
let content = fs.readFileSync(file, 'utf8');

content = content.replace(
    /appendTo:\s*'#panel-blocks',/g,
    "appendTo: '#blocks-container',"
);

content = content.replace(
    /appendTo:\s*'#panel-layers',/g,
    "appendTo: '#layers-container',"
);

const scriptAdd = `
    // Auto-switch Sidebar on Element Selection
    editor.on('component:selected', (model) => {
        const editTab = document.querySelector('#rail-tab-edit');
        if (editTab && !editTab.classList.contains('active')) {
            editTab.click();
        }
        
        const nameEl = document.getElementById('selected-element-name');
        const breadEl = document.getElementById('selected-element-breadcrumbs');
        
        if (nameEl && model) {
            nameEl.textContent = model.getName() || model.get('type') || 'Element';
            
            const parents = [];
            let current = model.parent();
            while(current && current.get('type') !== 'wrapper') {
                parents.unshift(current.getName() || current.get('type'));
                current = current.parent();
            }
            if (parents.length === 0) parents.push('Body');
            
            if (breadEl) {
                breadEl.innerHTML = '<span>' + parents[0] + '</span>';
                if (parents.length > 1) {
                    breadEl.innerHTML += '<iconify-icon icon="lucide:chevron-right" width="10"></iconify-icon><span>...</span>';
                }
            }
        }
    });

    editor.on('component:deselected', () => {
        const nameEl = document.getElementById('selected-element-name');
        if (nameEl) nameEl.textContent = 'Select an element';
        const breadEl = document.getElementById('selected-element-breadcrumbs');
        if (breadEl) breadEl.innerHTML = '<span>Body</span>';
    });

    // Ensure render`;

content = content.replace(/\/\/ Ensure render/g, scriptAdd);

fs.writeFileSync(file, content);
console.log('Successfully updated editor.js');
