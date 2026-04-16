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
             aside { background-color: var(--sidebar-bg) !important; }
            @endif
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
        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-2"></div>

        <!-- Global Confirmation Modal -->
        @include('components.confirmation-modal')

        <div class="flex h-screen overflow-hidden">

            <!-- Mobile Sidebar Overlay -->
            <div id="sidebar-overlay"
                 class="sidebar-overlay fixed inset-0 z-30 bg-black/50 hidden md:hidden"
                 onclick="closeSidebar()">
            </div>

            <!-- Sidebar Container (drawer on mobile, static on desktop) -->
            <aside id="sidebar-drawer"
                   class="sidebar-drawer fixed inset-y-0 left-0 z-40 w-72
                          -translate-x-full md:translate-x-0
                          md:static md:z-auto
                          flex-shrink-0">
                @include('layouts.sidebar')
            </aside>

            <!-- Main Content -->
            <div class="flex flex-col flex-1 w-0 overflow-hidden">
                <!-- Mobile Header (Visible only on mobile) -->
                <div class="md:hidden flex items-center justify-between bg-brand-dark dark:bg-gray-950 border-b border-white/10 px-4 py-3">
                     <a href="{{ route('dashboard') }}" class="flex items-baseline gap-0.5">
                         <span class="text-lg font-bold text-white">LANDING</span>
                         <span class="text-lg font-bold text-brand-orange">BUILDER</span>
                     </a>
                     <button id="mobile-menu-btn"
                             onclick="openSidebar()"
                             class="text-gray-300 hover:text-white p-2 rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-brand-orange">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                     </button>
                </div>

                <main class="flex-1 relative overflow-y-auto focus:outline-none">
                    <div class="py-6">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                            <!-- Page Header -->
                            @if (isset($header))
                            <header class="mb-6">
                                {{ $header }}
                            </header>
                            @endif

                            <!-- Slot Content -->
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script>
            // Sidebar Drawer Functions
            function openSidebar() {
                document.getElementById('sidebar-drawer').classList.remove('-translate-x-full');
                document.getElementById('sidebar-drawer').classList.add('translate-x-0');
                document.getElementById('sidebar-overlay').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeSidebar() {
                document.getElementById('sidebar-drawer').classList.add('-translate-x-full');
                document.getElementById('sidebar-drawer').classList.remove('translate-x-0');
                document.getElementById('sidebar-overlay').classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            // Close sidebar when resizing to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    document.getElementById('sidebar-drawer').classList.remove('-translate-x-full');
                    document.getElementById('sidebar-drawer').classList.add('translate-x-0');
                    document.getElementById('sidebar-overlay').classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                } else {
                    // On mobile, ensure sidebar is hidden unless explicitly opened
                    if (!document.getElementById('sidebar-overlay').classList.contains('hidden')) return;
                    document.getElementById('sidebar-drawer').classList.add('-translate-x-full');
                    document.getElementById('sidebar-drawer').classList.remove('translate-x-0');
                }
            });

            // Dark Mode Toggle Logic
            var themeToggleBtn = document.getElementById('theme-toggle');
            var darkIcon = document.getElementById('theme-toggle-dark-icon');
            var lightIcon = document.getElementById('theme-toggle-light-icon');

            // Change the icons inside the button based on previous settings
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                lightIcon && lightIcon.classList.remove('hidden');
            } else {
                darkIcon && darkIcon.classList.remove('hidden');
            }

            if(themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function() {
                    darkIcon && darkIcon.classList.toggle('hidden');
                    lightIcon && lightIcon.classList.toggle('hidden');

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
                });
            }
        </script>
    </body>
</html>