<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Editor - {{ $landing->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/editor.js'])
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

    <!-- LEFT SIDEBAR (Elementor Style) -->
    <aside id="editor-sidebar" class="w-[300px] flex flex-col bg-[#2e2e2e] border-r border-gray-800 shrink-0 z-20 h-full shadow-xl">
        
        <!-- Header / Tabs -->
        <div class="flex items-center justify-between px-4 h-12 bg-[#2e2e2e] border-b border-gray-700 shadow-sm shrink-0">
             <div class="flex items-center gap-4">
                 <button id="tab-elements" class="sidebar-tab active text-gray-200 hover:text-white border-b-2 border-indigo-500 pb-3 mt-3 font-semibold text-xs uppercase tracking-wider">
                     <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                 </button>
                 <button id="tab-globals" class="sidebar-tab text-gray-500 hover:text-white pb-3 mt-3 font-semibold text-xs uppercase tracking-wider">
                     <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                 </button>
             </div>
             <a href="{{ route('landings.index') }}" class="text-gray-500 hover:text-white">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
             </a>
        </div>

        <!-- Search (Visible only in Elements tab) -->
        <div id="sidebar-search" class="p-4 border-b border-gray-700 bg-[#2e2e2e]">
            <input type="text" id="block-search" placeholder="Search widgets..." class="w-full bg-[#1f1f1f] text-gray-200 text-sm rounded border border-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-3 py-2 placeholder-gray-500">
        </div>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto relative">
            
            <!-- PANEL: BLOCKS (Default) -->
            <div id="panel-blocks" class="p-4 flex flex-col gap-2 pb-20">
                <!-- Blocks injected here -->
            </div>


            <!-- PANEL: SETTINGS (Traits + Styles) -->
            <div id="panel-settings" class="hidden">
                 <!-- Traits -->
                 <div class="px-4 py-3 bg-[#3a3a3a] border-b border-gray-700 sticky top-0 z-10 flex justify-between items-center cursor-pointer" onclick="toggleSection('traits-container')">
                    <span class="font-bold text-xs uppercase text-gray-300">Content / Attributes</span>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                 </div>
                 <div id="panel-traits" class="p-4 bg-[#2e2e2e] traits-container"></div>

                 <!-- Styles -->
                 <div class="px-4 py-3 bg-[#3a3a3a] border-b border-gray-700 border-t sticky top-0 z-10 flex justify-between items-center cursor-pointer" onclick="toggleSection('styles-container')">
                     <span class="font-bold text-xs uppercase text-gray-300">Style</span>
                     <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                 </div>
                 <div id="panel-styles" class="styles-container"></div>
            </div>

            <!-- PANEL: LAYERS -->
            <div id="panel-layers" class="hidden h-full">
            </div>

        </div>

    </aside>

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

    <script>
        window.editorData = {
            landingId: {{ $landing->id }},
            pageId: {{ $page->id }},
            saveUrl: "{{ route('landings.pages.update', [$landing, $page]) }}",
            csrfToken: "{{ csrf_token() }}",
            grapesJsJson: {!! $page->grapesjs_json ?? 'null' !!},
            appCssUrl: "{{ Vite::asset('resources/css/app.css') }}"
        };
        
        // Simple UI Toggle Logic (Bridge between Blade & GrapesJS)
        window.toggleSection = (cls) => {
            // No-op, managed by GrapesJS open state usually, but good placeholder
        };
        
        window.switchToSettings = () => {
             // Logic to show settings panel
        };
    </script>
</body>
</html>
