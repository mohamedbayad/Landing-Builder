<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            <!-- Left brand panel (hidden on mobile) -->
            <div class="hidden lg:flex lg:w-[45%] bg-[#0D1117] relative overflow-hidden flex-col justify-between p-12">
                <!-- Subtle grid overlay -->
                <div class="absolute inset-0 opacity-[0.03]"
                     style="background-image: linear-gradient(rgba(255,255,255,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.5) 1px, transparent 1px); background-size: 32px 32px;"></div>

                <!-- Gradient orbs -->
                <div class="absolute top-0 right-0 w-80 h-80 bg-brand-orange/15 rounded-full blur-3xl -translate-y-1/3 translate-x-1/3 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-brand-orange/8 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>

                <!-- Brand logo -->
                <div class="relative z-10">
                    <a href="/" class="flex items-center gap-2 group">
                        <div class="w-7 h-7 rounded-lg bg-brand-orange/15 ring-1 ring-brand-orange/30 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="text-[15px] font-semibold text-white">Landing<span class="text-brand-orange">Builder</span></span>
                    </a>
                </div>

                <!-- Tagline -->
                <div class="relative z-10 max-w-sm">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-orange/10 border border-brand-orange/20 text-brand-orange text-xs font-medium mb-6">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-orange animate-pulse-slow"></span>
                        AI-Powered Builder
                    </div>
                    <h1 class="text-3xl font-bold text-white leading-tight mb-4 tracking-tight">
                        Build stunning landing pages in minutes
                    </h1>
                    <p class="text-gray-500 text-base leading-relaxed">
                        Drag-and-drop builder with AI-powered content generation and real-time analytics.
                    </p>

                    <!-- Feature list -->
                    <ul class="mt-8 space-y-3">
                        @foreach(['Visual drag-and-drop editor', 'AI content generation', 'Built-in analytics & heatmaps', 'Form capture & lead management'] as $feature)
                        <li class="flex items-center gap-2.5 text-sm text-gray-400">
                            <svg class="w-4 h-4 text-brand-orange flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Footer -->
                <div class="relative z-10 text-xs text-gray-600">
                    &copy; {{ date('Y') }} LandingBuilder. All rights reserved.
                </div>
            </div>

            <!-- Right form panel -->
            <div class="w-full lg:w-[55%] flex flex-col justify-center items-center px-6 py-12 bg-white dark:bg-[#0D1117]">
                <!-- Mobile logo -->
                <div class="lg:hidden mb-8">
                    <a href="/" class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-brand-orange/10 ring-1 ring-brand-orange/30 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="text-[15px] font-semibold text-gray-900 dark:text-white">Landing<span class="text-brand-orange">Builder</span></span>
                    </a>
                </div>

                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
