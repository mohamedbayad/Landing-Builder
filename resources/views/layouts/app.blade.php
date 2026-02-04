<!DOCTYPE html>
@php
    $user = auth()->user();
    // Use the user's first workspace settings, or fallbacks if not set
    $workspace = $user ? $user->workspaces()->first() : null;
    $settings = $workspace ? $workspace->settings : null;
    $direction = $settings->dashboard_direction ?? 'ltr';
    $primaryColor = $settings->dashboard_primary_color ?? '#4f46e5'; // Indigo-600 default
    $sidebarBg = $settings->sidebar_bg ?? null;
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
            /* Override Tailwind Indigo defaults with Custom Primary if set */
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
            // On page load or when changing themes, best to add inline in `head` to avoid FOUC
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark')
            }
        </script>
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        <!-- Toast Container -->
        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-2"></div>
        
        <!-- Global Confirmation Modal -->
        @include('components.confirmation-modal')


        <div class="flex h-screen overflow-hidden">

            <!-- Sidebar -->
            <aside class="hidden md:flex flex-shrink-0 z-20">
                @include('layouts.sidebar')
            </aside>

            <!-- Main Content -->
            <div class="flex flex-col flex-1 w-0 overflow-hidden">
                <!-- Mobile Header (Visible only on mobile) -->
                <div class="md:hidden flex items-center justify-between bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                     <span class="font-bold text-xl text-gray-800 dark:text-gray-200">Menu</span>
                     <button id="mobile-menu-btn" class="text-gray-500 hover:text-gray-600 focus:outline-none focus:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                     </button>
                </div>
                <!-- Mobile Sidebar Overlay (Optional implementation, for now standard resize) -->

                <main class="flex-1 relative overflow-y-auto focus:outline-none">
                    <div class="py-6">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
                            <!-- Page Header -->
                            @if (isset($header))
                            <header class="mb-4">
                                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                    {{ $header }}
                                </div>
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
            // Dark Mode Toggle Logic
            var themeToggleBtn = document.getElementById('theme-toggle');
            var darkIcon = document.getElementById('theme-toggle-dark-icon');
            var lightIcon = document.getElementById('theme-toggle-light-icon');

            // Change the icons inside the button based on previous settings
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                lightIcon.classList.remove('hidden');
            } else {
                darkIcon.classList.remove('hidden');
            }

            if(themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function() {
                    // toggle icons inside button
                    darkIcon.classList.toggle('hidden');
                    lightIcon.classList.toggle('hidden');

                    // if set via local storage previously
                    if (localStorage.getItem('color-theme')) {
                        if (localStorage.getItem('color-theme') === 'light') {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('color-theme', 'dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('color-theme', 'light');
                        }
                    } else {
                        // if NOT set via local storage previously
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
