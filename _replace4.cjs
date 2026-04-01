const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/resources/js/grapesjs/plugins/advanced-editing-controls.js';
let content = fs.readFileSync(file, 'utf8');

// Replace Emojis with Iconify Lucide icons
content = content.replace(
    /<span>🏷 HTML Tag<\/span>/g,
    '<div style="display: flex; align-items: center; gap: 6px;"><iconify-icon icon="lucide:code-2"></iconify-icon><span>HTML Tag</span></div>'
);

content = content.replace(
    /<span>⚙ Custom Attributes<\/span>/g,
    '<div style="display: flex; align-items: center; gap: 6px;"><iconify-icon icon="lucide:sliders"></iconify-icon><span>Custom Attributes</span></div>'
);

content = content.replace(
    /<span>🎨 CSS Classes<\/span>/g,
    '<div style="display: flex; align-items: center; gap: 6px;"><iconify-icon icon="lucide:palette"></iconify-icon><span>CSS Classes</span></div>'
);

content = content.replace(
    /<span>♿ Accessibility \/ SEO<\/span>/g,
    '<div style="display: flex; align-items: center; gap: 6px;"><iconify-icon icon="lucide:scan-face"></iconify-icon><span>Accessibility / SEO</span></div>'
);

content = content.replace(
    /<span style="font-size: 9px;">▼<\/span>/g,
    '<iconify-icon icon="lucide:chevron-down" width="14"></iconify-icon>'
);

// Update Mounting Logic
const oldMountLogic = `    function mountPanel() {
        const settingsPanel = document.getElementById('panel-settings');
        if (!settingsPanel) return;

        // Create the "Advanced" section header
        const headerEl = document.createElement('div');
        headerEl.className = 'px-4 py-3 bg-[#3a3a3a] border-b border-gray-700 border-t sticky top-0 z-10 flex justify-between items-center cursor-pointer';
        headerEl.innerHTML = \`
            <span class="font-bold text-xs uppercase text-gray-300">Advanced</span>
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        \`;

        const panel = createAdvancedPanel();
        panel.style.background = '#2e2e2e';

        let isExpanded = true;
        headerEl.addEventListener('click', () => {
            isExpanded = !isExpanded;
            panel.style.display = isExpanded ? 'block' : 'none';
        });

        settingsPanel.appendChild(headerEl);
        settingsPanel.appendChild(panel);
    }

    // Mount when DOM is ready
    const tryMount = () => {
        if (document.getElementById('panel-settings')) {
            mountPanel();
        } else {
            setTimeout(tryMount, 200);
        }
    };
    tryMount();`;

const newMountLogic = `    function mountPanel() {
        const advancedContainer = document.getElementById('panel-advanced');
        if (!advancedContainer) {
            setTimeout(mountPanel, 200);
            return;
        }

        const panel = createAdvancedPanel();
        panel.style.background = 'transparent';
        panel.style.padding = '0'; // Let the container handle padding if needed
        
        // Remove border-bottom from last section so it looks cleaner
        const styleBlock = document.createElement('style');
        styleBlock.innerHTML = \`.adv-section:last-child { border-bottom: none; }\`;
        panel.appendChild(styleBlock);

        advancedContainer.appendChild(panel);
    }

    // Mount when DOM is ready
    mountPanel();`;

if (content.includes('function mountPanel() {')) {
    const startIndex = content.indexOf('    function mountPanel() {');
    const endIndex = content.indexOf('tryMount();') + 'tryMount();'.length;
    
    if(startIndex !== -1 && endIndex !== -1) {
         content = content.substring(0, startIndex) + newMountLogic + content.substring(endIndex);
    }
}

// Adjust styling for matching the new UI (#2a2a2a and transparent)
content = content.replace(/background: #3a3a3a;/g, 'background: #2a2a2a;');
content = content.replace(/#advanced-editing-panel \{[\s\S]*?\}/, `#advanced-editing-panel {
                    font-family: -apple-system, BlinkMacSystemFont, system-ui, sans-serif;
                    color: #ddd;
                    font-size: 13px;
                }`);

fs.writeFileSync(file, content);
console.log('Successfully completed polishing and replaced emojis with SVG icons in advanced-editing-controls.js');
