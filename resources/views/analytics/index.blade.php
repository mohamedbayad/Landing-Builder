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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                    landing_performance: [],
                    breakdowns: {
                        sources_pct: {},
                        devices: { mobile: 0, desktop: 0 },
                        visitor_types: { new: 0, returning: 0 },
                        top_referrers: [],
                        top_campaigns: []
                    }
                },
                charts: {},

                init() {
                    this.fetchData();
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
                }
            }
        }
    </script>
</x-app-layout>
