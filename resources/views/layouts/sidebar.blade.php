<div class="flex flex-col w-72 h-screen bg-white dark:bg-[#0D1117] flex-shrink-0 overflow-hidden relative border-r border-gray-200 dark:border-white/5">
    <!-- Mobile Close Button -->
    <button onclick="closeSidebar()"
            class="md:hidden absolute top-4 right-3 p-1.5 rounded-md text-gray-500 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/8 transition-colors z-10">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Brand -->
    <div class="flex items-center gap-3 px-5 pt-5 pb-4 border-b border-gray-200 dark:border-white/[0.06]">
        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand-orange/10 ring-1 ring-brand-orange/30 text-brand-orange flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
        <a href="{{ route('dashboard') }}" class="flex items-baseline gap-0.5 group">
            <span class="text-[15px] font-semibold text-gray-900 dark:text-white tracking-tight leading-none">Landing</span><span class="text-[15px] font-semibold text-brand-orange tracking-tight leading-none">Builder</span>
        </a>
    </div>

    <!-- Navigation -->
    <div class="flex flex-col justify-between flex-1 overflow-y-auto scrollbar-hide px-3 py-3">
        <nav class="space-y-0.5">

            <!-- Dashboard -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('dashboard') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('dashboard') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span class="ml-2.5">Dashboard</span>
            </a>

            <!-- Landing Builder: Pages -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('landings.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('landings.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('landings.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0v.243a2.25 2.25 0 01-.659 1.591l-1.591 1.591" />
                </svg>
                <span class="ml-2.5">Pages</span>
            </a>

            <!-- Landing Builder: Templates -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('templates.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('templates.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('templates.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-8.25zM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-2.25z" />
                </svg>
                <span class="ml-2.5">Templates</span>
            </a>

            @if(auth()->user()->hasPermission('templates.upload') && auth()->user()->hasPermission('tech.manage'))
                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('templates.create') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('templates.create') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('templates.create') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="ml-2.5">Add Template</span>
                </a>
            @endif

            <!-- AI Generator -->
            @if(auth()->user()->hasPermission('tech.manage'))
                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('ai-generator.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('ai-generator.index') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('ai-generator.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                    </svg>
                    <span class="ml-2.5">AI Studio</span>
                </a>
            @endif

            <!-- Media Library -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('media.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('media.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('media.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <span class="ml-2.5">Media Library</span>
            </a>

            <!-- Section: Analytics & Commerce -->
            <div class="nav-section-label">Analytics &amp; Commerce</div>

            <!-- Analytics -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('analytics.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('analytics.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('analytics.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
                <span class="ml-2.5">Analytics</span>
            </a>

            <!-- Who's Online -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('online-users.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('online-users.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('online-users.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 01-3.6 9.75m6.633-4.596a18.666 18.666 0 01-2.485 5.33" />
                </svg>
                <span class="ml-2.5">Who's Online</span>
            </a>

            <!-- Leads -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('leads.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('leads.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('leads.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <span class="ml-2.5">Orders &amp; Leads</span>
            </a>

            <!-- Forms -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('forms.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('forms.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('forms.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                </svg>
                <span class="ml-2.5">Form Submissions</span>
            </a>

            <!-- Global Workflow Builder -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('workflow-builder.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}"
               href="{{ route('workflow-builder.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('workflow-builder.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                <span class="ml-2.5">Workflow Builder</span>
            </a>

            @php($emailAutomationOpen = request()->routeIs('email-automation.*'))
            <details class="group mt-1" {{ $emailAutomationOpen ? 'open' : '' }}>
                <summary class="list-none flex items-center px-3 py-2 text-sm font-medium rounded-md cursor-pointer transition-all duration-150 {{ $emailAutomationOpen ? 'bg-gray-100 text-gray-900 dark:bg-white/[0.08] dark:text-white nav-active-indicator' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/[0.05] hover:text-gray-900 dark:hover:text-gray-100' }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ $emailAutomationOpen ? 'text-brand-orange' : 'text-gray-500 dark:text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <span class="ml-2.5 flex-1">Email Automation</span>
                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-500 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>

                <div class="mt-1 ml-3 pl-2 border-l border-gray-200 dark:border-white/[0.06] space-y-0.5">
                    <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('email-automation.automations.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}"
                       href="{{ route('email-automation.automations.index') }}">
                        <span class="ml-2.5">Automations</span>
                    </a>
                    <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('email-automation.templates.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}"
                       href="{{ route('email-automation.templates.index') }}">
                        <span class="ml-2.5">Templates</span>
                    </a>
                    <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('email-automation.contacts.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}"
                       href="{{ route('email-automation.contacts.index') }}">
                        <span class="ml-2.5">Contacts</span>
                    </a>
                    <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('email-automation.activity.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}"
                       href="{{ route('email-automation.activity.index') }}">
                        <span class="ml-2.5">Activity</span>
                    </a>
                    <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('email-automation.analytics.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}"
                       href="{{ route('email-automation.analytics.index') }}">
                        <span class="ml-2.5">Analytics</span>
                    </a>
                    <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('email-automation.settings.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}"
                       href="{{ route('email-automation.settings.index') }}">
                        <span class="ml-2.5">Email Settings</span>
                    </a>
                </div>
            </details>

            <!-- Visitor Recordings -->
            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('recordings.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('recordings.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('recordings.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <span class="ml-2.5">Visitor Recordings</span>
            </a>

            <!-- Section: Configuration -->
            <div class="nav-section-label">Configuration</div>

            @if(auth()->user()->hasPermission('users.view'))
                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('users.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('users.index') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('users.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    <span class="ml-2.5">Users</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('roles.view'))
                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('roles-permissions.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('roles-permissions.index') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('roles-permissions.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <span class="ml-2.5">Roles &amp; Permissions</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('plans.view'))
                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('plans.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('plans.index') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('plans.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                    <span class="ml-2.5">Plans</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('subscriptions.view'))
                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('subscriptions.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('subscriptions.index') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('subscriptions.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                    </svg>
                    <span class="ml-2.5">Subscriptions</span>
                </a>

                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('subscriptions.invoices.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}"
                   href="{{ route('subscriptions.invoices.index') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('subscriptions.invoices.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span class="ml-2.5">Subscription Invoices</span>
                </a>
            @endif

            @if(auth()->user()->hasPermission('custom_domains.manage'))
                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('domains.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('domains.index') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('domains.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                    </svg>
                    <span class="ml-2.5">Custom Domains</span>
                </a>
            @endif

            <!-- Settings -->
            @if(auth()->user()->hasPermission('tech.manage'))
                <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('settings.plugins.*') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('settings.plugins.index') }}">
                    <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('settings.plugins.*') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.4.959.4v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
                    </svg>
                    <span class="ml-2.5">Plugins</span>
                </a>
            @endif

            <a class="flex items-center px-3 py-2 text-sm font-medium transition-all duration-150 rounded-md group {{ request()->routeIs('settings.index') ? 'bg-white/[0.08] text-white nav-active-indicator' : 'text-gray-400 hover:bg-white/[0.05] hover:text-gray-100' }}" href="{{ route('settings.index') }}">
                <svg class="w-[18px] h-[18px] flex-shrink-0 {{ request()->routeIs('settings.index') ? 'text-brand-orange' : 'text-gray-500 group-hover:text-gray-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="ml-2.5">Settings</span>
            </a>
        </nav>

        <!-- User Profile / Logout -->
        <div class="mt-auto pt-3 border-t border-gray-200 dark:border-white/[0.06]">
            <div class="flex items-center gap-2.5 px-2 mb-3">
                <img class="object-cover rounded-full h-8 w-8 ring-1 ring-gray-200 dark:ring-white/10"
                     src="https://ui-avatars.com/api/?name={{ urlencode(optional(Auth::user())->name ?? 'Guest') }}&background=F97316&color=fff&size=64"
                     alt="avatar" />
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-gray-900 dark:text-white truncate leading-tight">{{ optional(Auth::user())->name ?? 'Guest' }}</p>
                    <p class="text-[11px] text-gray-500 leading-tight">{{ optional(optional(Auth::user())->roles->first())->name ?? 'Subscriber' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-1.5 px-1">
                <!-- Dark Mode Toggle -->
                <button id="theme-toggle" type="button"
                        class="flex-1 flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-white/5 rounded-md hover:bg-gray-200 dark:hover:bg-white/8 hover:text-gray-900 dark:hover:text-gray-200 transition-colors duration-150 focus:outline-none">
                    <svg id="theme-toggle-dark-icon" class="hidden w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    <svg id="theme-toggle-light-icon" class="hidden w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 100 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                    Theme
                </button>

                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400/70 bg-red-50 dark:bg-red-500/8 rounded-md hover:bg-red-100 dark:hover:bg-red-500/15 hover:text-red-700 dark:hover:text-red-400 transition-colors duration-150">
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
