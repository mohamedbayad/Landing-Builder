<x-app-layout>
    <div class="py-8 bg-gray-50 dark:bg-gray-900 min-h-screen font-sans" x-data="analyticsDashboard()">
        
        <!-- Sticky Header & Filter Bar -->
        <div class="sticky top-0 z-10 bg-gray-50/95 dark:bg-gray-900/95 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800 pb-4 pt-4 px-4 sm:px-6 lg:px-8 transition-all duration-200"
             :class="{'shadow-sm': true}">
            
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                
                <!-- Title -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                        Analytics
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Performance overview & insights
                    </p>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    
                    <!-- Landing Select -->
                    <div class="relative">
                        <select x-model="filters.landing_id" @change="fetchData()" 
                                class="appearance-none pl-3 pr-8 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-shadow shadow-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750">
                            <option value="">All Landings</option>
                            @foreach($landings as $landing)
                                <option value="{{ $landing->id }}">{{ $landing->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <!-- Date Range Select -->
                    <div class="relative">
                        <select x-model="filters.range" @change="fetchData()" 
                                class="appearance-none pl-3 pr-8 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-shadow shadow-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750">
                            <option value="today">Today</option>
                            <option value="7d">Last 7 Days</option>
                            <option value="30d">Last 30 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                        </select>
                         <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    <!-- Actions -->
                    <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="Export (Coming Soon)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </button>
                     <button @click="resetFilters()" class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" title="Refresh">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>

                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

            <!-- Loading State -->
            <div x-show="isLoading" class="flex flex-col items-center justify-center py-20 transition-opacity duration-300">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                <p class="mt-4 text-sm text-gray-500 animate-pulse">Gathering insights...</p>
            </div>

            <!-- Dashboard Content -->
            <div x-show="!isLoading" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="space-y-8"
                 style="display: none;">

                <!-- 0. Real-time Overview Module -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Left Card: Map (lg:col-span-2) -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                Active users by Country
                                <span class="relative flex h-2.5 w-2.5 ml-1">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                </span>
                            </h3>
                            <button class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </button>
                        </div>
                        
                        <div class="flex flex-col md:flex-row flex-grow gap-6">
                            <!-- Map Container -->
                            <div class="w-full md:w-2/3 min-h-[300px] relative flex items-center justify-center" id="realtime-map-container" wire:ignore>
                                <!-- Map SVG injected by D3 -->
                            </div>
                            
                            <!-- Internal Country List -->
                            <div class="w-full md:w-1/3 flex flex-col border-l border-gray-100 dark:border-gray-700 pl-0 md:pl-6">
                                <div class="flex justify-between text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700 pb-2 mb-3">
                                    <span>Country</span>
                                    <span>Active Users</span>
                                </div>
                                <div class="space-y-4 flex-grow overflow-y-auto max-h-[300px] custom-scrollbar pr-2">
                                    <template x-for="c in realtimeData.countries.slice(0, 6)" :key="c.country">
                                        <div>
                                            <div class="flex justify-between text-sm py-1">
                                                <span class="font-medium text-gray-700 dark:text-gray-200 truncate pr-2" x-text="c.country"></span>
                                                <span class="font-bold text-gray-900 dark:text-white" x-text="c.count"></span>
                                            </div>
                                            <!-- Blue bar -->
                                            <div class="w-full bg-transparent h-0.5 mt-0.5">
                                                <div class="bg-blue-600 h-0.5" :style="'width: ' + (realtimeData.countries[0] ? (c.count / realtimeData.countries[0].count * 100) : 0) + '%'"></div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="realtimeData.countries.length === 0">
                                        <div class="text-sm text-gray-400 py-4 text-center">No active users</div>
                                    </template>
                                </div>
                                <div class="pt-3 mt-auto text-right">
                                    <button class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center justify-end w-full gap-1">
                                        View countries <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Card: Ticker (lg:col-span-1) -->
                    <div class="lg:col-span-1 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col">
                        <div class="flex justify-between items-start border-b border-gray-100 dark:border-gray-700 pb-2 mb-4 relative">
                            <h3 class="text-[11px] font-bold text-gray-500 tracking-widest uppercase">
                                Active users in last 30 minutes
                            </h3>
                            <button class="text-emerald-600 bg-emerald-50 rounded-full p-0.5 absolute right-0 top-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </button>
                        </div>
                        
                        <div class="text-[2.75rem] leading-none font-semibold text-gray-900 dark:text-white tracking-tight mb-8">
                            <span x-text="realtimeData.total">0</span>
                        </div>
                        
                        <h3 class="text-[11px] font-bold text-gray-500 tracking-widest uppercase mb-4">
                            Active users per minute
                        </h3>
                        
                        <!-- Minute Bar Chart -->
                        <div class="flex items-end h-24 gap-[2px] w-full border-b border-gray-300 dark:border-gray-600 pb-0 mb-6 relative">
                            <template x-for="min in realtimeData.minutes" :key="min.time">
                                <div class="flex-1 bg-blue-100 hover:bg-blue-200 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors cursor-pointer group relative h-full flex flex-col justify-end">
                                    <div class="w-full bg-blue-600" 
                                         :style="'height: ' + (Math.max(...realtimeData.minutes.map(m=>m.count)) > 0 && min.count > 0 ? Math.max((min.count / Math.max(...realtimeData.minutes.map(m=>m.count))) * 100, 2) : 0) + '%'">
                                    </div>
                                    <!-- Simple Tooltip -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-900 text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap z-50">
                                        <span x-text="min.count"></span> users
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Top Countries List -->
                        <div class="flex justify-between text-[11px] font-bold text-gray-500 tracking-widest uppercase border-b border-gray-200 dark:border-gray-700 pb-2 mb-3">
                            <span>Top Countries</span>
                            <span>Active Users</span>
                        </div>
                        <div class="space-y-3 flex-grow overflow-y-auto max-h-[160px] custom-scrollbar">
                            <template x-for="c in realtimeData.countries.slice(0, 5)" :key="c.country">
                                <div>
                                    <div class="flex justify-between text-[13px] py-0.5 relative z-10">
                                        <span class="text-gray-600 dark:text-gray-300 truncate pr-2" x-text="c.country"></span>
                                        <span class="text-gray-900 dark:text-white text-right" x-text="c.count"></span>
                                    </div>
                                    <!-- Subtle underbar like in screenshot -->
                                    <div class="w-full h-px mt-0.5">
                                        <div class="bg-blue-600 h-full" :style="'width: ' + (realtimeData.countries[0] ? (c.count / realtimeData.countries[0].count * 100) : 0) + '%'"></div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="realtimeData.countries.length === 0">
                                <div class="text-xs text-gray-400 py-2">No data yet.</div>
                            </template>
                        </div>
                        <div class="pt-3 mt-auto text-right">
                            <button class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center justify-end w-full gap-1">
                                View real time <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 1. KPI Cards Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Sessions -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start">
                             <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sessions</p>
                                <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white" x-text="formatNumber(data.kpi.sessions)">0</h3>
                             </div>
                             <div class="p-2 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg text-indigo-600 dark:text-indigo-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                             </div>
                        </div>
                        <div class="mt-4 flex items-center text-xs">
                            <span class="text-green-500 font-medium flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                +12%
                            </span>
                            <span class="text-gray-400 ml-2">vs. previous period</span>
                        </div>
                    </div>

                    <!-- Unique Visitors -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                         <div class="flex justify-between items-start">
                             <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unique Visitors</p>
                                <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white" x-text="formatNumber(data.kpi.uniques)">0</h3>
                             </div>
                             <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                             </div>
                        </div>
                         <div class="mt-4 flex items-center text-xs">
                             <!-- Placeholder trend -->
                            <span class="text-gray-400">Unique people tracked</span>
                        </div>
                    </div>

                    <!-- Leads -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                         <div class="flex justify-between items-start">
                             <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Leads</p>
                                <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white" x-text="formatNumber(data.kpi.leads)">0</h3>
                             </div>
                             <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg text-emerald-600 dark:text-emerald-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             </div>
                        </div>
                         <div class="mt-4 flex items-center text-xs">
                            <span class="text-emerald-500 font-medium flex items-center">
                                High Value
                            </span>
                        </div>
                    </div>

                    <!-- Conversion Rate -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
                         <div class="flex justify-between items-start">
                             <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conversion Rate</p>
                                <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                                    <span x-text="data.kpi.conversion_rate">0</span><span class="text-lg text-gray-400 font-normal">%</span>
                                </h3>
                             </div>
                             <div class="p-2 bg-amber-50 dark:bg-amber-900/30 rounded-lg text-amber-600 dark:text-amber-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                             </div>
                        </div>
                        <div class="mt-4 flex items-center text-xs">
                            <span class="text-gray-400">Avg across landings</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-show="data.kpi.has_data">
                    
                    <!-- Left Column (Bigger) -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Line Chart Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Sessions Overview</h3>
                                <div class="flex items-center gap-2">
                                     <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">
                                        Docs
                                    </span>
                                </div>
                            </div>
                            <div class="relative h-72 w-full">
                                <canvas id="mainChart"></canvas>
                            </div>
                        </div>

                         <!-- Funnel Card (Mini/Horizontal) -->
                         <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Conversion Funnel</h3>
                            
                            <div class="space-y-4">
                                <!-- Steps -->
                                <template x-for="(step, index) in data.funnel" :key="step.label">
                                    <div class="relative group">
                                         <div class="flex justify-between text-sm mb-1.5 align-middle">
                                            <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 transition-colors" x-text="step.label"></span>
                                            <span class="font-bold text-gray-900 dark:text-white" x-text="formatNumber(step.value)"></span>
                                        </div>
                                        <div class="w-full bg-gray-100 rounded-full h-3 dark:bg-gray-700 overflow-hidden">
                                           <div class="h-3 rounded-full transition-all duration-1000 ease-out" 
                                                 :class="getFunnelColor(index)"
                                                 :style="'width: ' + (data.funnel[0].value > 0 ? (step.value / data.funnel[0].value * 100) : 0) + '%'">
                                           </div>
                                        </div>
                                         <div class="text-xs text-gray-400 mt-1 flex justify-between">
                                            <span x-show="index > 0">
                                                Conversion: <span class="font-medium text-gray-600 dark:text-gray-400" x-text="calculateDropoff(step.value, data.funnel[index-1]?.value) + '%'"></span>
                                            </span>
                                            <span x-show="index === 0">Starting Point</span>
                                            <span x-text="data.funnel[0].value > 0 ? Math.round(step.value / data.funnel[0].value * 100) + '% of Total' : '0%'"></span>
                                        </div>

                                        <!-- CTA Clicks Breakdown (shown under the CTA Clicks step) -->
                                        <template x-if="step.label === 'CTA Clicks' && data.clicks_breakdown && data.clicks_breakdown.length > 0">
                                            <div class="mt-3 ml-4 pl-3 border-l-2 border-indigo-200 dark:border-indigo-800 space-y-2">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Click Breakdown</span>
                                                    <span class="text-[11px] text-gray-400" x-text="data.clicks_breakdown.length + ' labels'"></span>
                                                </div>
                                                <template x-for="(item, bi) in data.clicks_breakdown" :key="item.label">
                                                    <div class="group/item">
                                                        <div class="flex justify-between items-center text-xs mb-0.5">
                                                            <span class="text-gray-600 dark:text-gray-300 font-medium truncate max-w-[160px]" x-text="item.label" :title="item.label"></span>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-gray-900 dark:text-white font-bold" x-text="item.clicks"></span>
                                                                <span class="text-gray-400 text-[10px] w-10 text-right" x-text="item.percentage + '%'"></span>
                                                            </div>
                                                        </div>
                                                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                                            <div class="h-1.5 rounded-full transition-all duration-700 ease-out" 
                                                                 :class="bi === 0 ? 'bg-indigo-500' : bi === 1 ? 'bg-blue-400' : 'bg-sky-300'"
                                                                 :style="'width: ' + item.percentage + '%'">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Top Landings Table -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top Landing Pages</h3>
                                <button class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">View All</button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Page</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Sessions</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Leads</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Conv.</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                        <template x-for="row in data.landing_performance" :key="row.name">
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer" x-text="row.name"></div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white border-l border-transparent" x-text="formatNumber(row.sessions)"></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white" x-text="row.leads"></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                     <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                                           :class="row.conversion_rate > 5 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'">
                                                        <span x-text="row.conversion_rate"></span>%
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 hidden sm:table-cell" x-text="formatDuration(row.avg_duration)"></td>
                                            </tr>
                                        </template>
                                        <template x-if="data.landing_performance.length === 0">
                                            <tr>
                                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                                    No landing stats available.
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column (Smaller) -->
                    <div class="space-y-6">
                        
                        <!-- Sources Donut -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Traffic Sources</h3>
                            <div class="relative h-56 mx-auto">
                                <canvas id="sourcesChart"></canvas>
                            </div>
                            <div class="mt-6 space-y-3">
                                <template x-for="(pct, source) in data.breakdowns.sources_pct" :key="source">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="flex items-center text-gray-600 dark:text-gray-400">
                                            <span class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: getSourceColor(source) }"></span>
                                            <span x-text="source"></span>
                                        </span>
                                        <span class="font-semibold text-gray-900 dark:text-white" x-text="pct + '%'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Devices Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Device Breakdown</h3>
                            <div class="space-y-5">
                                 <!-- Mobile -->
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            Mobile / Tablet
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="data.breakdowns.devices.mobile + '%'"></span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 dark:bg-gray-700">
                                        <div class="bg-blue-500 h-2 rounded-full" :style="'width: ' + data.breakdowns.devices.mobile + '%'"></div>
                                    </div>
                                </div>
                                
                                <!-- Desktop -->
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            Desktop
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="data.breakdowns.devices.desktop + '%'"></span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 dark:bg-gray-700">
                                        <div class="bg-emerald-500 h-2 rounded-full" :style="'width: ' + data.breakdowns.devices.desktop + '%'"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Visitor Types (Small) -->
                         <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                            <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Visitor Trend</h3>
                            <div class="flex items-center justify-between">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" x-text="data.breakdowns.visitor_types.new + '%'"></div>
                                    <div class="text-xs text-gray-500 mt-1">New</div>
                                </div>
                                <div class="h-8 w-px bg-gray-200 dark:bg-gray-700"></div>
                                 <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-700 dark:text-gray-300" x-text="data.breakdowns.visitor_types.returning + '%'"></div>
                                    <div class="text-xs text-gray-500 mt-1">Returning</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer / Empty State Message if no data -->
                 <div x-show="!data.kpi.has_data && !isLoading" class="mt-12 text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No data available</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or wait for new traffic.</p>
                </div>

            </div>
        </div>
    </div>

    <!-- D3.js & TopoJSON -->
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script src="https://unpkg.com/topojson-client@3"></script>
    <!-- Chart.js loaded via Vite/node_modules -->

    <script>
        function analyticsDashboard() {
            return {
                isLoading: false, // Init false, handled by fetchData
                filters: {
                    landing_id: '',
                    range: '30d'
                },
                data: {
                    kpi: { has_data: false, sessions: 0, uniques: 0, leads: 0, conversion_rate: 0, bounce_rate: 0, avg_duration: 0 },
                    funnel: [{label: 'Sessions', value: 0}, {label: 'CTA Clicks', value: 0}, {label: 'Form', value: 0}, {label: 'Leads', value: 0}],
                    clicks_breakdown: [],
                    landing_performance: [],
                    breakdowns: {
                        sources_pct: {},
                        devices: { mobile: 0, desktop: 0 },
                        visitor_types: { new: 0, returning: 0 },
                        top_referrers: [],
                        top_campaigns: []
                    }
                },
                realtimeData: {
                    total: 0,
                    countries: [],
                    minutes: Array.from({length: 30}, (_, i) => ({ time: i, count: 0 }))
                },
                realtimePoll: null,
                charts: {},
                mapState: { rendered: false, svg: null, worldData: null, path: null },

                init() {
                    this.fetchData();
                    this.fetchRealtime();
                    
                    // Poll Real-time data every 30s
                    this.realtimePoll = setInterval(() => {
                        this.fetchRealtime();
                    }, 30000);

                    this.initD3Map();
                },

                fetchRealtime() {
                    const params = new URLSearchParams({ landing_id: this.filters.landing_id });
                    fetch(`{{ route('analytics.realtime') }}?${params}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) return;
                            this.realtimeData = data;
                            this.updateD3Map(data.countries);
                        });
                },

                fetchData() {
                    this.isLoading = true;
                    const params = new URLSearchParams(this.filters);

                    fetch(`{{ route('analytics.data') }}?${params}`)
                        .then(res => res.json())
                        .then(data => {
                            this.data = data;
                            this.$nextTick(() => {
                                this.updateCharts(data);
                                this.isLoading = false;
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            this.isLoading = false;
                        });
                },

                 resetFilters() {
                    this.filters.landing_id = '';
                    this.filters.range = '30d';
                    this.fetchData();
                    this.fetchRealtime();
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('en-US', { notation: "compact", compactDisplay: "short" }).format(num);
                },

                formatDuration(seconds) {
                    if (seconds < 60) return Math.round(seconds) + 's';
                    const mins = Math.floor(seconds / 60);
                    return `${mins}m ${Math.round(seconds % 60)}s`;
                },
                
                getFunnelColor(index) {
                    // Gradient colors for steps
                    const classes = ['bg-indigo-500', 'bg-blue-400', 'bg-sky-400', 'bg-emerald-400'];
                    return classes[index] || 'bg-gray-400';
                },

                calculateDropoff(current, previous) {
                     if (!previous || previous === 0) return 100; // if prev is 0, this should ideally be 0 too, but logic varies
                     return Math.round((current / previous) * 100);
                },
                
                getSourceColor(source) {
                    const colors = {
                        'Direct': '#6366f1', // Indigo
                        'Social': '#ec4899', // Pink
                        'Search': '#10b981', // Emerald
                        'Referral': '#f59e0b', // Amber
                        'Paid': '#8b5cf6', // Violet
                        'Email': '#64748b' // Slate
                    };
                    return colors[source] || '#9ca3af';
                },

                updateCharts(data) {
                    // Line Chart
                    this.renderChart('mainChart', 'line', {
                        labels: data.charts.labels,
                        datasets: [
                            {
                                label: 'Sessions',
                                data: data.charts.sessions,
                                borderColor: '#6366f1',
                                backgroundColor: (context) => {
                                    const ctx = context.chart.ctx;
                                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                                    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
                                    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
                                    return gradient;
                                },
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 0,
                                pointHoverRadius: 4
                            },
                             {
                                label: 'Leads',
                                data: data.charts.leads,
                                borderColor: '#10b981', 
                                backgroundColor: 'rgba(16, 185, 129, 0.0)',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: false,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                borderDash: [5, 5]
                            }
                        ]
                    }, {
                        interaction: { intersect: false, mode: 'index' },
                        scales: {
                            y: { grid: { borderDash: [2, 4], color: '#e5e7eb', drawBorder: false }, ticks: { display: true } },
                            x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } }
                        },
                        plugins: { legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 8, usePointStyle: true } } }
                    });

                    // Sources Donut
                    this.renderChart('sourcesChart', 'doughnut', {
                        labels: Object.keys(data.breakdowns.sources),
                        datasets: [{
                            data: Object.values(data.breakdowns.sources),
                            backgroundColor: Object.keys(data.breakdowns.sources).map(k => this.getSourceColor(k)),
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    }, { 
                        cutout: '75%', 
                        plugins: { legend: { display: false } } 
                    });
                },

                renderChart(id, type, data, options = {}) {
                    const ctx = document.getElementById(id)?.getContext('2d');
                    if (!ctx) return;
                    if (this.charts[id]) this.charts[id].destroy();

                    this.charts[id] = new Chart(ctx, {
                        type: type,
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }, // Default no legend for cleaner look
                            ...options
                        }
                    });
                },

                // D3 Map Logic
                async initD3Map() {
                    if (this.mapState.rendered) return;
                    this.mapState.rendered = true;

                    const width = 800;
                    const height = 450;
                    
                    const container = d3.select("#realtime-map-container");
                    if(container.empty()) return;

                    this.mapState.svg = container
                        .append("svg")
                        .attr("viewBox", `0 0 ${width} ${height}`)
                        .style("width", "100%")
                        .style("height", "100%")
                        .style("max-height", "400px");

                    const projection = d3.geoMercator()
                        .scale(130)
                        .translate([width / 2, height / 1.5]);

                    this.mapState.path = d3.geoPath().projection(projection);

                    try {
                        const response = await fetch("https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json");
                        this.mapState.worldData = await response.json();
                        
                        this.mapState.svg.append("g")
                            .attr("class", "countries-group")
                            .selectAll("path")
                            .data(topojson.feature(this.mapState.worldData, this.mapState.worldData.objects.countries).features)
                            .enter().append("path")
                            .attr("d", this.mapState.path)
                            .attr("fill", "#e2e8f0") // Tailwind slate-200 base
                            .attr("stroke", "#ffffff")
                            .attr("stroke-width", 0.5)
                            .attr("class", "country-path");
                            
                        // If realtime data came before map loaded
                        if (this.realtimeData.countries.length > 0) {
                            this.updateD3Map(this.realtimeData.countries);
                        }
                    } catch (e) {
                        console.error("Map loading error", e);
                    }
                },

                updateD3Map(countriesData) {
                    if (!this.mapState.svg || !this.mapState.worldData) return;
                    
                    const countsDict = {};
                    let maxCount = 0;
                    countriesData.forEach(c => {
                        countsDict[c.country] = c.count;
                        if (c.count > maxCount) maxCount = c.count;
                    });

                    // Define colors
                    const emptyColor = document.documentElement.classList.contains('dark') ? '#374151' : '#e2e8f0'; // slate-200 / gray-700
                    const activeColorMin = '#93c5fd'; // blue-300
                    const activeColorMax = '#1d4ed8'; // blue-700

                    const colorScale = d3.scaleLinear()
                        .domain([1, Math.max(maxCount, 2)]) // ensure gradient handles small numbers
                        .range([activeColorMin, activeColorMax]);

                    this.mapState.svg.selectAll(".country-path")
                        .transition()
                        .duration(700)
                        .attr("fill", function(d) {
                            let name = d.properties.name;
                            // Basic mapping adjustments if Topojson name != IP-API name
                            if (name === 'United States of America') name = 'United States';
                            if (name === 'United Kingdom') name = 'United Kingdom';
                            
                            const count = countsDict[name] || 0;
                            return count > 0 ? colorScale(count) : emptyColor;
                        });
                }
            }
        }
    </script>
</x-app-layout>
