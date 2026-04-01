<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editor - {{ $landing->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/editor.js'])
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <style>
        body, html { height: 100%; margin: 0; overflow: hidden; }
        /* Scrollbar styling for sidebar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #2d2d2d; }
        ::-webkit-scrollbar-thumb { background: #555; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #777; }
        
        .gjs-cv-canvas {
            width: 100%;
            height: 100%;
            top: 0;
        }
        /* Hide default GrapesJS panels that conflict with our custom UI */
        .gjs-pn-panels, .gjs-pn-views-container, .gjs-pn-views, .gjs-pn-commands {
            display: none !important;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-300 font-sans h-screen flex overflow-hidden">

    <!-- LEFT UI: DUAL-PANEL SYSTEM -->
    <div class="flex h-full shrink-0 z-20 shadow-2xl relative">
        
        <!-- PRIMARY RAIL (Far Left) -->
        <aside id="editor-rail" class="w-16 flex flex-col bg-[#1e1e1e] border-r border-[#333] shrink-0 h-full py-4 items-center justify-between">
            
            <div class="flex flex-col gap-4 w-full items-center">
                <!-- Back to app -->
                <a href="{{ route('landings.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl text-gray-500 hover:text-white hover:bg-[#2e2e2e] transition mb-4" title="Exit Editor">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                </a>

                <!-- Rail Tabs -->
                <button class="rail-tab active w-10 h-10 flex flex-col items-center justify-center rounded-xl text-indigo-400 bg-[#2e2e2e] transition" data-target="panel-blocks" title="Add Elements">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
                
                <button class="rail-tab w-10 h-10 flex flex-col items-center justify-center rounded-xl text-gray-500 hover:text-white hover:bg-[#2e2e2e] transition" data-target="panel-edit" id="rail-tab-edit" title="Edit Properties">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="4" x2="14" y2="4"></line><line x1="10" y1="4" x2="3" y2="4"></line><line x1="21" y1="12" x2="12" y2="12"></line><line x1="8" y1="12" x2="3" y2="12"></line><line x1="21" y1="20" x2="16" y2="20"></line><line x1="12" y1="20" x2="3" y2="20"></line><line x1="14" y1="2" x2="14" y2="6"></line><line x1="8" y1="10" x2="8" y2="14"></line><line x1="16" y1="18" x2="16" y2="22"></line></svg>
                </button>
                
                <button class="rail-tab w-10 h-10 flex flex-col items-center justify-center rounded-xl text-gray-500 hover:text-white hover:bg-[#2e2e2e] transition" data-target="panel-layers" title="Layers & Structure">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                </button>

                <button class="rail-tab w-10 h-10 flex flex-col items-center justify-center rounded-xl text-amber-500 hover:text-amber-400 hover:bg-[#2e2e2e] transition mt-2" data-target="panel-ai" title="AI Assistant">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path><path d="M5 3v4"></path><path d="M19 17v4"></path><path d="M3 5h4"></path><path d="M17 19h4"></path></svg>
                </button>
            </div>

            <div class="flex flex-col gap-2 w-full items-center">
                <button class="rail-tab w-10 h-10 flex flex-col items-center justify-center rounded-xl text-gray-500 hover:text-white hover:bg-[#2e2e2e] transition" data-target="panel-settings" title="Global Settings">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="absolute left-3 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path><path d="M5 3v4"></path><path d="M19 17v4"></path><path d="M3 5h4"></path><path d="M17 19h4"></path></svg>
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" class="text-gray-500 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9"></path><path d="M14 17H5"></path><circle cx="17" cy="17" r="3"></circle><circle cx="7" cy="7" r="3"></circle></svg>
                        <h3 class="text-sm font-semibold text-gray-300">Page Configuration</h3>
                        <p class="text-xs text-gray-500 mt-1">SEO and global styles are managed here.</p>
                    </div>
                </div>
            </div>
            
        </aside>
    </div>
    <!-- RIGHT CANVAS AREA -->
    <main class="flex-1 flex flex-col h-full relative bg-[#1e1e1e]">
        
        <!-- Canvas -->
        <div class="flex-1 relative overflow-hidden" id="gjs-canvas-frame">
            <!-- GrapesJS Canvas mounts here -->
        </div>

        <!-- Bottom Action Bar -->
        <div class="h-10 bg-[#2e2e2e] border-t border-gray-800 flex items-center justify-between px-4 shrink-0 z-30">
             <div class="flex items-center gap-4">
                 <button class="text-gray-400 hover:text-white" title="Settings" onclick="switchToSettings()">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                 </button>
                 <button class="text-gray-400 hover:text-white" title="Navigator" onclick="toggleNavigator()">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                 </button>
             </div>
             
             <div class="flex items-center gap-4">
                 <!-- Device Switcher -->
                 <div class="flex items-center bg-[#1f1f1f] rounded border border-gray-700 p-0.5">
                     <button id="btn-device-desktop" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors active">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                     </button>
                     <button id="btn-device-tablet" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                     </button>
                     <button id="btn-device-mobile" class="p-1.5 text-gray-400 hover:text-white hover:bg-gray-700 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                     </button>
                 </div>

                 <div class="h-4 w-px bg-gray-700"></div>

                 <button id="btn-undo" class="text-gray-400 hover:text-white p-1">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                 </button>
                 <button id="btn-redo" class="text-gray-400 hover:text-white p-1">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path></svg>
                 </button>
                 <button id="btn-preview" class="text-gray-400 hover:text-indigo-400 p-1 mx-2" title="Preview">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                 </button>
                 <button id="btn-save" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold uppercase px-6 py-1.5 rounded transition-colors shadow-md">
                     Update
                 </button>
             </div>
        </div>
    </main>

    <div id="gjs" style="display:none">
        {!! $page->html !!}
    </div>
    <div id="gjs-css" style="display:none">{!! $page->css !!}</div>
    <div id="gjs-js" style="display:none">{!! e($page->js ?? '') !!}</div>

    <script>
        window.editorData = {
            landingId: {{ $landing->id }},
            pageId: {{ $page->id }},
            saveUrl: "{{ route('landings.pages.update', [$landing, $page]) }}",
            csrfToken: "{{ csrf_token() }}",
            grapesJsJson: {!! $page->grapesjs_json ?? 'null' !!},
            forceHtmlMode: {!! !empty($forceHtmlMode) ? 'true' : 'false' !!},
            disableModuleScripts: {!! !empty($disableModuleScripts) ? 'true' : 'false' !!},
            appCssUrl: "{{ Vite::asset('resources/css/app.css') }}",
            customHead: {!! json_encode($editorCustomHead ?? '', JSON_HEX_TAG) !!}
        };
        
        // UI Toggling Logic
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
    </script>
</body>
</html>
