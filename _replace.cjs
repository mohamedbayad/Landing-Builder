const fs = require('fs');
const file = 'c:/Users/DELL/Desktop/web app/system/resources/views/editor.blade.php';
let content = fs.readFileSync(file, 'utf8');

const startMarker = '    <!-- LEFT SIDEBAR (Elementor Style) -->';
const endMarker = '    <!-- RIGHT CANVAS AREA -->';

const startIndex = content.indexOf(startMarker);
const endIndex = content.indexOf(endMarker);

if (startIndex === -1 || endIndex === -1) {
    console.error('Markers not found');
    process.exit(1);
}

const replacement = `    <!-- LEFT UI: DUAL-PANEL SYSTEM -->
    <div class="flex h-full shrink-0 z-20 shadow-2xl relative">
        
        <!-- PRIMARY RAIL (Far Left) -->
        <aside id="editor-rail" class="w-16 flex flex-col bg-[#1e1e1e] border-r border-[#333] shrink-0 h-full py-4 items-center justify-between">
            
            <div class="flex flex-col gap-4 w-full items-center">
                <!-- Back to app -->
                <a href="{{ route('landings.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl text-gray-500 hover:text-white hover:bg-[#2e2e2e] transition mb-4" title="Exit Editor">
                    <iconify-icon icon="lucide:layout-dashboard" width="20"></iconify-icon>
                </a>

                <!-- Rail Tabs -->
                <button class="rail-tab active w-10 h-10 flex flex-col items-center justify-center rounded-xl text-indigo-400 bg-[#2e2e2e] transition" data-target="panel-blocks" title="Add Elements">
                    <iconify-icon icon="lucide:plus" width="22"></iconify-icon>
                </button>
                
                <button class="rail-tab w-10 h-10 flex flex-col items-center justify-center rounded-xl text-gray-500 hover:text-white hover:bg-[#2e2e2e] transition" data-target="panel-edit" id="rail-tab-edit" title="Edit Properties">
                    <iconify-icon icon="lucide:sliders-horizontal" width="20"></iconify-icon>
                </button>
                
                <button class="rail-tab w-10 h-10 flex flex-col items-center justify-center rounded-xl text-gray-500 hover:text-white hover:bg-[#2e2e2e] transition" data-target="panel-layers" title="Layers & Structure">
                    <iconify-icon icon="lucide:layers" width="20"></iconify-icon>
                </button>

                <button class="rail-tab w-10 h-10 flex flex-col items-center justify-center rounded-xl text-amber-500 hover:text-amber-400 hover:bg-[#2e2e2e] transition mt-2" data-target="panel-ai" title="AI Assistant">
                    <iconify-icon icon="lucide:sparkles" width="20"></iconify-icon>
                </button>
            </div>

            <div class="flex flex-col gap-2 w-full items-center">
                <button class="rail-tab w-10 h-10 flex flex-col items-center justify-center rounded-xl text-gray-500 hover:text-white hover:bg-[#2e2e2e] transition" data-target="panel-settings" title="Global Settings">
                    <iconify-icon icon="lucide:settings" width="20"></iconify-icon>
                </button>
            </div>
        </aside>

        <!-- CONTEXTUAL PANEL -->
        <aside id="editor-panel" class="w-[280px] flex flex-col bg-[#252525] border-r border-[#333] shrink-0 h-full overflow-hidden transition-all duration-300">
            
            <!-- PANEL: ADD (Blocks) -->
            <div id="panel-blocks" class="context-panel flex flex-col h-full">
                <div class="px-4 py-4 border-b border-[#333] shrink-0">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Add Elements</h2>
                </div>
                <!-- Search -->
                <div class="p-3 border-b border-[#333] shrink-0">
                    <div class="relative flex items-center">
                        <iconify-icon icon="lucide:search" class="absolute left-3 text-gray-500" width="16"></iconify-icon>
                        <input type="text" id="block-search" placeholder="Search blocks..." class="w-full bg-[#1a1a1a] text-gray-300 text-sm rounded-lg border border-[#444] focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 pl-9 pr-3 py-2 placeholder-gray-600 transition outline-none">
                    </div>
                </div>
                <!-- Blocks Container -->
                <div id="blocks-container" class="flex-1 overflow-y-auto p-3 flex flex-col gap-2 relative pb-20">
                    <!-- GrapesJS Blocks Inject Here -->
                </div>
            </div>

            <!-- PANEL: EDIT (Traits, Styles, Advanced) -->
            <div id="panel-edit" class="context-panel hidden flex flex-col h-full bg-[#252525]">
                
                <!-- Selected Element Header -->
                <div class="px-4 py-3 border-b border-[#333] bg-[#2a2a2a] shrink-0 sticky top-0 z-20">
                    <div class="flex items-center gap-2 mb-1">
                        <div id="selected-element-icon" class="text-indigo-400 flex items-center justify-center bg-indigo-500/10 p-1.5 rounded-md">
                            <iconify-icon icon="lucide:box" width="16"></iconify-icon>
                        </div>
                        <h2 id="selected-element-name" class="text-sm font-bold text-gray-200 truncate">Select an element</h2>
                    </div>
                    <div id="selected-element-breadcrumbs" class="text-[10px] text-gray-500 truncate flex items-center gap-1">
                        <span>Body</span>
                    </div>
                </div>

                <!-- Sub-tabs for Edit -->
                <div class="flex border-b border-[#333] bg-[#222] shrink-0 px-2 pt-2">
                    <button class="edit-tab active flex-1 pb-2 text-xs font-semibold text-gray-300 border-b-2 border-indigo-500 transition-colors" data-target="edit-content">Content</button>
                    <button class="edit-tab flex-1 pb-2 text-xs font-semibold text-gray-500 border-b-2 border-transparent hover:text-gray-300 transition-colors" data-target="edit-style">Style</button>
                    <button class="edit-tab flex-1 pb-2 text-xs font-semibold text-gray-500 border-b-2 border-transparent hover:text-gray-300 transition-colors" data-target="edit-advanced">Advanced</button>
                </div>

                <div class="flex-1 overflow-y-auto relative p-0 pb-20">
                    <!-- Content Tab -->
                    <div id="edit-content" class="edit-pane p-4">
                        <div id="panel-traits" class="traits-container"></div>
                    </div>
                    
                    <!-- Style Tab -->
                    <div id="edit-style" class="edit-pane hidden pb-10">
                        <div id="panel-styles" class="styles-container"></div>
                    </div>
                    
                    <!-- Advanced Tab -->
                    <div id="edit-advanced" class="edit-pane hidden pb-10">
                        <div id="panel-advanced"></div> <!-- Targeted by advanced controls -->
                    </div>
                </div>
            </div>

            <!-- PANEL: LAYERS -->
            <div id="panel-layers" class="context-panel hidden flex flex-col h-full">
                <div class="px-4 py-4 border-b border-[#333] shrink-0">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Structure</h2>
                </div>
                <div id="layers-container" class="flex-1 overflow-y-auto relative pb-20 p-2">
                    <!-- GrapesJS Layers Inject Here -->
                </div>
            </div>

            <!-- PANEL: AI ASSISTANT -->
            <div id="panel-ai" class="context-panel hidden flex flex-col h-full bg-[#252525]">
                <div class="px-4 py-4 border-b border-[#333] shrink-0 flex items-center gap-2">
                    <iconify-icon icon="lucide:sparkles" width="16" class="text-amber-500"></iconify-icon>
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-amber-500">AI Assistant</h2>
                </div>
                <div id="ai-container" class="flex-1 overflow-y-auto relative p-4 pb-20">
                    <p class="text-xs text-gray-500 italic text-center mt-10">Select a text or section block to see AI suggestions.</p>
                </div>
            </div>

            <!-- PANEL: PAGE SETTINGS -->
            <div id="panel-settings" class="context-panel hidden flex flex-col h-full">
                <div class="px-4 py-4 border-b border-[#333] shrink-0">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Global Settings</h2>
                </div>
                <div class="p-4 flex flex-col gap-4">
                    <div class="p-4 bg-[#1a1a1a] border border-[#333] rounded-lg flex flex-col items-center justify-center text-center">
                        <iconify-icon icon="lucide:settings-2" width="24" class="text-gray-500 mb-2"></iconify-icon>
                        <h3 class="text-sm font-semibold text-gray-300">Page Configuration</h3>
                        <p class="text-xs text-gray-500 mt-1">SEO and global styles are managed here.</p>
                    </div>
                </div>
            </div>
            
        </aside>
    </div>
`;

content = content.substring(0, startIndex) + replacement + content.substring(endIndex);
fs.writeFileSync(file, content);
console.log('Successfully updated editor.blade.php');
