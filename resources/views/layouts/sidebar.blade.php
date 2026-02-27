<div class="flex flex-col w-64 h-screen px-5 py-8 bg-white border-r dark:bg-gray-900 dark:border-gray-800 flex-shrink-0">
    <!-- Brand -->
    <div class="flex items-center justify-center h-10 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white tracking-tight">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-1 group">
                <span class="text-indigo-600 group-hover:text-indigo-500 transition-colors">LANDING</span><span class="text-gray-900 dark:text-white">BUILDER</span>
            </a>
        </h2>
    </div>

    <!-- Navigation -->
    <div class="flex flex-col justify-between flex-1 overflow-y-auto scrollbar-hide">
        <nav class="space-y-1">
            <!-- Dashboard -->
            <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('dashboard') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="mx-3">Dashboard</span>
            </a>

            <!-- Landings -->
            <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('landings.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('landings.index') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('landings.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="mx-3">My Landings</span>
            </a>

            <!-- Templates -->
            <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('templates.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('templates.index') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('templates.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                <span class="mx-3">Templates</span>
            </a>

            <!-- Media Library -->
            <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('media.index') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('media.index') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('media.index') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="mx-3">Media Library</span>
            </a>
            
            <div class="px-4 mt-6 mb-2">
                <p class="text-xs font-semibold text-gray-400 tracking-wider uppercase">Analytics & Commerce</p>
            </div>

            <!-- Analytics (New) -->
            <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('analytics.index') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('analytics.index') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('analytics.index') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="mx-3">Analytics</span>
            </a>

            <!-- Leads (Sales) -->
            <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('leads.index') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('leads.index') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('leads.index') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="mx-3">Orders & Leads</span>
            </a>

            <!-- Forms -->
             <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('forms.index') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('forms.index') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('forms.index') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="mx-3">Form Submissions</span>
            </a>

            <!-- Visitor Recordings -->
            <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('recordings.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('recordings.index') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('recordings.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span class="mx-3">Visitor Recordings</span>
            </a>

            <div class="px-4 mt-6 mb-2">
                <p class="text-xs font-semibold text-gray-400 tracking-wider uppercase">Configuration</p>
            </div>

            <!-- Custom Domains (Coming Soon) -->
            <div class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group text-gray-400 cursor-not-allowed dark:text-gray-600">
                <svg class="w-5 h-5 flex-shrink-0 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                <div class="mx-3 flex flex-1 justify-between items-center">
                    <span>Custom Domains</span>
                    <span class="text-[9px] uppercase font-bold tracking-wider bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500 px-2 py-0.5 rounded-full ml-auto">Soon</span>
                </div>
            </div>

             <a class="flex items-center px-4 py-2.5 text-sm font-medium transition-all duration-200 transform rounded-lg group {{ request()->routeIs('settings.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-indigo-400' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200' }}" href="{{ route('settings.index') }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('settings.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="mx-3">Settings</span>
            </a>
        </nav>

        <div class="mt-auto">
             <!-- User Profile / Logout -->
            <div class="px-4 pt-6 border-t border-gray-100 dark:border-gray-700">
                 <div class="flex items-center mb-4">
                    <img class="object-cover rounded-full h-10 w-10 ring-2 ring-gray-100 dark:ring-gray-700" src="https://ui-avatars.com/api/?name={{ urlencode(optional(Auth::user())->name ?? 'Guest') }}&background=6366f1&color=fff" alt="avatar" />
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 cursor-pointer overflow-hidden truncate w-32" title="{{ optional(Auth::user())->name ?? 'Guest' }}">{{ optional(Auth::user())->name ?? 'Guest' }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Admin</p>
                    </div>
                 </div>
                 
                 <div class="flex items-center justify-between mb-4">
                     <!-- Dark Mode Toggle -->
                    <button id="theme-toggle" type="button" class="flex items-center justify-center w-full px-3 py-2 text-sm text-gray-500 transition-colors duration-200 bg-gray-50 dark:bg-gray-800 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-700 mr-2">
                        <svg id="theme-toggle-dark-icon" class="hidden w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                        <span>Theme</span>
                    </button>
                    
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="flex items-center justify-center w-full px-3 py-2 text-sm font-medium text-red-600 transition-colors duration-200 bg-red-50 rounded-lg dark:bg-gray-800 dark:text-red-400 hover:bg-red-100 dark:hover:bg-gray-700">
                             <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                             Logout
                        </button>
                    </form>
                 </div>
            </div>
        </div>
    </div>
</div>
