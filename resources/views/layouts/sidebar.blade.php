<div class="flex flex-col w-72 h-screen bg-brand-dark dark:bg-[#0D1117] flex-shrink-0 overflow-hidden relative border-r border-white/5">
    <!-- Mobile Close Button -->
    <button onclick="closeSidebar()"
            class="md:hidden absolute top-4 right-3 p-1.5 rounded-md text-gray-500 hover:text-white hover:bg-white/8 transition-colors z-10">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Brand -->
    <div class="flex items-center gap-3 px-5 pt-5 pb-4 border-b border-white/[0.06]">
        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-orange/10 ring-1 ring-brand-orange/30 text-brand-orange flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <a href="{{ route('dashboard') }}" class="flex items-baseline gap-0.5 group">
            <span class="text-[15px] font-semibold text-white tracking-tight leading-none">Landing</span><span class="text-[15px] font-semibold text-brand-orange tracking-tight leading-none">Builder</span>
        </a>
    </div>

    <!-- Navigation -->
    <div class="flex flex-col justify-between flex-1 overflow-y-auto scrollbar-hide px-3 py-3">
        <nav class="space-y-0.5">

            <!-- Dashboard -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('dashboard') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('dashboard') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="ml-2.5">Dashboard</span>
            </a>

            <!-- Landings -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('landings.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('landings.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('landings.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="ml-2.5">My Landings</span>
            </a>

            <!-- Templates -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('templates.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('templates.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('templates.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                <span class="ml-2.5">Templates</span>
            </a>

            <!-- AI Generator -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('ai-generator.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('ai-generator.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('ai-generator.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span class="ml-2.5">AI Studio</span>
            </a>

            <!-- Media Library -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('media.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('media.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('media.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="ml-2.5">Media Library</span>
            </a>

            <!-- Section: Analytics & Commerce -->
            <div class="nav-section-label">Analytics &amp; Commerce</div>

            <!-- Analytics -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('analytics.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('analytics.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('analytics.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="ml-2.5">Analytics</span>
            </a>

            <!-- Who's Online -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('online-users.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('online-users.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('online-users.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                </svg>
                <span class="ml-2.5">Who's Online</span>
            </a>

            <!-- Leads -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('leads.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('leads.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('leads.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="ml-2.5">Orders &amp; Leads</span>
            </a>

            <!-- Forms -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('forms.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('forms.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('forms.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="ml-2.5">Form Submissions</span>
            </a>

            <!-- Visitor Recordings -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('recordings.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('recordings.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('recordings.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span class="ml-2.5">Visitor Recordings</span>
            </a>

            <!-- Section: Configuration -->
            <div class="nav-section-label">Configuration</div>

            <!-- Custom Domains (Coming Soon) -->
            <div class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-600 cursor-not-allowed select-none">
                <svg class="w-4 h-4 flex-shrink-0 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                <span class="ml-2.5 flex-1">Custom Domains</span>
                <span class="text-[9px] uppercase font-bold tracking-wider bg-white/5 text-gray-600 px-1.5 py-0.5 rounded">Soon</span>
            </div>

            <!-- Settings -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('settings.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('settings.index') }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('settings.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="ml-2.5">Settings</span>
            </a>
        </nav>

        <!-- User Profile / Logout -->
        <div class="mt-auto pt-3 border-t border-white/[0.06]">
            <div class="flex items-center gap-2.5 px-2 mb-3">
                <img class="object-cover rounded-full h-8 w-8 ring-1 ring-white/10"
                     src="https://ui-avatars.com/api/?name={{ urlencode(optional(Auth::user())->name ?? 'Guest') }}&background=F97316&color=fff&size=64"
                     alt="avatar" />
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-white truncate leading-tight">{{ optional(Auth::user())->name ?? 'Guest' }}</p>
                    <p class="text-[11px] text-gray-500 leading-tight">Admin</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 px-1">
                <!-- Dark Mode Toggle -->
                <button id="theme-toggle" type="button"
                        class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-400 bg-white/5 rounded-md hover:bg-white/8 hover:text-gray-200 transition-colors duration-150 focus:outline-none">
                    <svg id="theme-toggle-dark-icon" class="hidden w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    <svg id="theme-toggle-light-icon" class="hidden w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                    Theme
                </button>

                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-400/70 bg-red-500/8 rounded-md hover:bg-red-500/15 hover:text-red-400 transition-colors duration-150">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
