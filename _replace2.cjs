const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/resources/views/editor.blade.php';
let content = fs.readFileSync(file, 'utf8');

const startMarker = '        // Simple UI Toggle Logic (Bridge between Blade & GrapesJS)';
const endMarker = '    </script>';

const startIndex = content.indexOf(startMarker);
const endIndex = content.indexOf(endMarker);

if (startIndex === -1 || endIndex === -1) {
    console.error('Markers not found');
    process.exit(1);
}

const replacement = `        // UI Toggling Logic
        document.addEventListener('DOMContentLoaded', () => {
            const railTabs = document.querySelectorAll('.rail-tab');
            const contextPanels = document.querySelectorAll('.context-panel');
            const editTabs = document.querySelectorAll('.edit-tab');
            const editPanes = document.querySelectorAll('.edit-pane');

            // Rail Navigation
            railTabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    const current = e.currentTarget;
                    current.blur();
                    
                    // Update active state
                    railTabs.forEach(t => {
                        t.classList.remove('active', 'text-indigo-400', 'bg-[#2e2e2e]');
                        if(t !== current && !t.classList.contains('text-amber-500')) {
                            t.classList.add('text-gray-500');
                        }
                    });
                    
                    if (!current.classList.contains('text-amber-500')) {
                        current.classList.add('active', 'text-indigo-400', 'bg-[#2e2e2e]');
                        current.classList.remove('text-gray-500');
                    } else {
                        current.classList.add('active', 'bg-[#2e2e2e]');
                    }

                    // Show target panel
                    const targetId = current.getAttribute('data-target');
                    contextPanels.forEach(panel => {
                        panel.classList.add('hidden');
                        if (panel.id === targetId) {
                            panel.classList.remove('hidden');
                        }
                    });
                });
            });

            // Edit Sub-tabs (Content, Style, Advanced)
            editTabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    editTabs.forEach(t => {
                        t.classList.remove('active', 'text-gray-300', 'border-indigo-500');
                        t.classList.add('text-gray-500', 'border-transparent');
                    });
                    e.currentTarget.classList.add('active', 'text-gray-300', 'border-indigo-500');
                    e.currentTarget.classList.remove('text-gray-500', 'border-transparent');

                    const targetId = e.currentTarget.getAttribute('data-target');
                    editPanes.forEach(pane => {
                        pane.classList.add('hidden');
                        if (pane.id === targetId) {
                            pane.classList.remove('hidden');
                        }
                    });
                });
            });
        });
        
        window.switchToSettings = () => {
             document.querySelector('[data-target="panel-settings"]')?.click();
        };
        window.switchToEdit = () => {
             document.querySelector('#rail-tab-edit')?.click();
        };
`;

content = content.substring(0, startIndex) + replacement + content.substring(endIndex);
fs.writeFileSync(file, content);
console.log('Successfully updated script logic');
