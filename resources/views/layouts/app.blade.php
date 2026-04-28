<!DOCTYPE html>
@php
    $user = auth()->user();
    // Use the user's first workspace settings, or fallbacks if not set
    $workspace = $user ? $user->workspaces()->first() : null;
    $settings = $workspace ? $workspace->settings : null;
    $direction = $settings->dashboard_direction ?? 'ltr';
    $primaryColor = $settings->dashboard_primary_color ?? '#FF7A00'; // Brand orange default
    $sidebarBg = $settings->sidebar_bg ?? null;
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Dynamic Theme Styles -->
        <style>
            :root {
                --color-primary: {{ $primaryColor }};
                @if($sidebarBg)
                --sidebar-bg: {{ $sidebarBg }};
                @endif
            }
            [dir="rtl"] {
                direction: rtl;
                text-align: right;
            }
            /* Override Tailwind brand-orange with Custom Primary if set */
            .bg-brand-orange { background-color: var(--color-primary) !important; }
            .text-brand-orange { color: var(--color-primary) !important; }
            .border-brand-orange { border-color: var(--color-primary) !important; }
            .ring-brand-orange { --tw-ring-color: var(--color-primary) !important; }
            .hover\:bg-brand-orange-600:hover { background-color: var(--color-primary) !important; filter: brightness(0.9); }
            .focus\:ring-brand-orange:focus { --tw-ring-color: var(--color-primary) !important; }
            .focus\:border-brand-orange:focus { border-color: var(--color-primary) !important; }
            .focus\:ring-brand-orange\/20:focus { --tw-ring-color: color-mix(in srgb, var(--color-primary) 20%, transparent) !important; }

            /* Legacy indigo overrides for backward compatibility */
            .bg-indigo-600 { background-color: var(--color-primary) !important; }
            .text-indigo-600 { color: var(--color-primary) !important; }
            .border-indigo-600 { border-color: var(--color-primary) !important; }
            .ring-indigo-600 { --tw-ring-color: var(--color-primary) !important; }
            .focus\:ring-indigo-500:focus { --tw-ring-color: var(--color-primary) !important; }

            @if($sidebarBg)
             .dark aside { background-color: var(--sidebar-bg) !important; }
            @endif

            /* Normalize page headings when rendered inside topbar */
            .topbar-context h1,
            .topbar-context h2,
            .topbar-context h3 {
                color: rgb(17 24 39) !important;
            }
            .dark .topbar-context h1,
            .dark .topbar-context h2,
            .dark .topbar-context h3 {
                color: #fff !important;
            }
            .topbar-context p {
                color: rgb(107 114 128) !important;
            }
            .dark .topbar-context p {
                color: rgb(156 163 175) !important;
            }

            /* Light mode sidebar readability */
            html:not(.dark) #sidebar-drawer .nav-section-label {
                color: rgb(148 163 184) !important;
            }
            html:not(.dark) #sidebar-drawer a.group {
                color: rgb(75 85 99) !important;
            }
            html:not(.dark) #sidebar-drawer a.group:hover,
            html:not(.dark) #sidebar-drawer a.group.nav-active-indicator {
                background-color: rgb(243 244 246) !important;
                color: rgb(17 24 39) !important;
            }
            html:not(.dark) #sidebar-drawer a.group svg {
                color: rgb(107 114 128) !important;
            }
            html:not(.dark) #sidebar-drawer a.group:hover svg,
            html:not(.dark) #sidebar-drawer a.group.nav-active-indicator svg {
                color: rgb(55 65 81) !important;
            }
        </style>

        <!-- Dark Mode Script -->
        <script>
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark')
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-[#0D1117]">
        <!-- Toast Container -->
        <div id="toast-container" class="pro-toast-stack"></div>

        <!-- Global Confirmation Modal -->
        @include('components.confirmation-modal')

        <div class="flex h-screen overflow-hidden">

            <!-- Sidebar Overlay (for mobile and when sidebar is collapsed on desktop) -->
            <div id="sidebar-overlay"
                 class="sidebar-overlay fixed inset-0 z-30 bg-black/50 hidden"
                 onclick="closeSidebar()">
            </div>

            <!-- Sidebar Container (collapsible on all screen sizes) -->
            <aside id="sidebar-drawer"
                   class="sidebar-drawer fixed inset-y-0 left-0 z-40 w-72 -translate-x-full flex-shrink-0">
                @include('layouts.sidebar')
            </aside>

            <!-- Main Content -->
            <div id="app-main-shell" class="flex flex-col flex-1 min-w-0 overflow-hidden">
                <!-- Topbar (Visible on all screen sizes) -->
                <div class="flex items-center justify-between bg-white dark:bg-[#0D1117] border-b border-gray-200 dark:border-white/10 px-4 py-3">
                     <div class="flex items-center gap-3 min-w-0 flex-1">
                         <!-- Sidebar Toggle Button -->
                         <button id="mobile-menu-btn"
                                 onclick="toggleSidebar()"
                                 class="text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-brand-orange">
                             <!-- Hamburger Icon (shown when sidebar is hidden) -->
                             <svg id="hamburger-icon" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                             </svg>
                             <!-- Close Icon (shown when sidebar is open) -->
                             <svg id="close-icon" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                             </svg>
                         </button>
                         @if (isset($topbar))
                             <div class="topbar-context min-w-0 flex-1">
                                 {{ $topbar }}
                             </div>
                         @elseif (isset($header))
                             <div class="topbar-context min-w-0 flex-1">
                                 {{ $header }}
                             </div>
                         @else
                             <!-- Brand (hidden on small screens, shown on md+) -->
                             <a href="{{ route('dashboard') }}" class="hidden sm:flex items-baseline gap-0.5 min-w-0">
                                 <span class="text-lg font-bold text-gray-900 dark:text-white">LANDING</span>
                                 <span class="text-lg font-bold text-brand-orange">BUILDER</span>
                             </a>
                         @endif
                     </div>
                     <div class="flex items-center gap-2">
                         <!-- Theme Toggle -->
                         <button id="theme-toggle-mobile" type="button"
                                 class="text-gray-500 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 focus:outline-none">
                             <svg id="theme-toggle-dark-icon-mobile" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                             <svg id="theme-toggle-light-icon-mobile" class="w-5 h-5 hidden" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                         </button>
                     </div>
                </div>

                <main class="flex-1 relative overflow-y-auto focus:outline-none">
                    <div class="py-6">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                            <!-- Page Header -->
                            <!-- Slot Content -->
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script>
            const DESKTOP_BREAKPOINT = 768;
            const SIDEBAR_DESKTOP_OFFSET_CLASS = 'md:ml-72';
            const MAIN_SHELL_TRANSITION_CLASSES = ['transition-[margin]', 'duration-200', 'ease-out'];
            let isDesktop = window.innerWidth >= DESKTOP_BREAKPOINT;

            function getSidebarElements() {
                return {
                    drawer: document.getElementById('sidebar-drawer'),
                    overlay: document.getElementById('sidebar-overlay'),
                    mainShell: document.getElementById('app-main-shell'),
                };
            }

            function updateSidebarIcon(isOpen) {
                const hamburgerIcon = document.getElementById('hamburger-icon');
                const closeIcon = document.getElementById('close-icon');
                if (hamburgerIcon && closeIcon) {
                    hamburgerIcon.classList.toggle('hidden', isOpen);
                    closeIcon.classList.toggle('hidden', !isOpen);
                }
            }

            function applyDesktopOffset(isOpen) {
                const { mainShell } = getSidebarElements();
                if (!mainShell) return;
                mainShell.classList.toggle(SIDEBAR_DESKTOP_OFFSET_CLASS, isDesktop && isOpen);
            }

            function setMainShellAnimation(enabled) {
                const { mainShell } = getSidebarElements();
                if (!mainShell) return;
                mainShell.classList.toggle(MAIN_SHELL_TRANSITION_CLASSES[0], enabled);
                mainShell.classList.toggle(MAIN_SHELL_TRANSITION_CLASSES[1], enabled);
                mainShell.classList.toggle(MAIN_SHELL_TRANSITION_CLASSES[2], enabled);
            }

            function syncSidebarState(isOpen, saveState = true, withAnimation = false) {
                const { drawer, overlay, mainShell } = getSidebarElements();
                if (!drawer || !overlay || !mainShell) return;

                setMainShellAnimation(withAnimation);

                drawer.classList.toggle('-translate-x-full', !isOpen);
                updateSidebarIcon(isOpen);
                applyDesktopOffset(isOpen);

                if (isDesktop) {
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                } else {
                    overlay.classList.toggle('hidden', !isOpen);
                    document.body.classList.toggle('overflow-hidden', isOpen);
                }

                if (saveState) {
                    localStorage.setItem('sidebar-open', isOpen ? 'true' : 'false');
                }
            }

            function openSidebar(saveState = true) {
                syncSidebarState(true, saveState, true);
            }

            function closeSidebar(saveState = true) {
                syncSidebarState(false, saveState, true);
            }

            function toggleSidebar() {
                const { drawer } = getSidebarElements();
                if (!drawer) return;
                const isOpen = !drawer.classList.contains('-translate-x-full');
                syncSidebarState(!isOpen, true, true);
            }

            // Initialize sidebar state based on screen size and persisted preference
            document.addEventListener('DOMContentLoaded', function() {
                isDesktop = window.innerWidth >= DESKTOP_BREAKPOINT;
                const savedState = localStorage.getItem('sidebar-open');
                const shouldOpen = savedState === null ? isDesktop : savedState === 'true';
                syncSidebarState(shouldOpen, false, false);
            });

            // Handle screen resize
            window.addEventListener('resize', function() {
                const wasDesktop = isDesktop;
                isDesktop = window.innerWidth >= DESKTOP_BREAKPOINT;

                const { drawer } = getSidebarElements();
                if (!drawer) return;

                const isOpen = !drawer.classList.contains('-translate-x-full');
                if (wasDesktop !== isDesktop) {
                    syncSidebarState(isOpen, false, false);
                    return;
                }

                applyDesktopOffset(isOpen);
            });

            // Dark Mode Toggle Logic
            var themeToggleBtn = document.getElementById('theme-toggle');
            var themeToggleMobileBtn = document.getElementById('theme-toggle-mobile');
            var darkIcon = document.getElementById('theme-toggle-dark-icon');
            var lightIcon = document.getElementById('theme-toggle-light-icon');
            var darkIconMobile = document.getElementById('theme-toggle-dark-icon-mobile');
            var lightIconMobile = document.getElementById('theme-toggle-light-icon-mobile');

            // Change the icons inside the button based on previous settings
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                lightIcon && lightIcon.classList.remove('hidden');
                lightIconMobile && lightIconMobile.classList.remove('hidden');
            } else {
                darkIcon && darkIcon.classList.remove('hidden');
                darkIconMobile && darkIconMobile.classList.remove('hidden');
            }

            function toggleTheme() {
                darkIcon && darkIcon.classList.toggle('hidden');
                lightIcon && lightIcon.classList.toggle('hidden');
                darkIconMobile && darkIconMobile.classList.toggle('hidden');
                lightIconMobile && lightIconMobile.classList.toggle('hidden');

                if (localStorage.getItem('color-theme')) {
                    if (localStorage.getItem('color-theme') === 'light') {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('color-theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('color-theme', 'light');
                    }
                } else {
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('color-theme', 'light');
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('color-theme', 'dark');
                    }
                }
            }

            if(themeToggleBtn) {
                themeToggleBtn.addEventListener('click', toggleTheme);
            }
            if(themeToggleMobileBtn) {
                themeToggleMobileBtn.addEventListener('click', toggleTheme);
            }
        </script>
        <script
            id="dashboard-ai-assistant-runtime"
            src="{{ asset('js/dashboard-ai-assistant.js') }}?v={{ filemtime(public_path('js/dashboard-ai-assistant.js')) }}"
            data-endpoint="{{ route('dashboard.assistant.chat') }}"
            data-route-name="{{ request()->route()?->getName() ?? '' }}"
            data-user-id="{{ $user?->id ?? '' }}"
            data-workspace-id="{{ $workspace?->id ?? '' }}"
            defer
        ></script>
    </body>
</html>
