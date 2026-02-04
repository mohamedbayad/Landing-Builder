<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            
            <!-- Stats Grid (5 Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <!-- Visits Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="p-2.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            @if($visitsChange != 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $visitsChange >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $visitsChange >= 0 ? '↑' : '↓' }} {{ abs($visitsChange) }}%
                            </span>
                            @endif
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ number_format($totalVisits) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Visits</p>
                    </div>
                </div>

                <!-- Leads Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="p-2.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            @if($leadsChange != 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $leadsChange >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $leadsChange >= 0 ? '↑' : '↓' }} {{ abs($leadsChange) }}%
                            </span>
                            @endif
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ number_format($totalLeads) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Leads</p>
                    </div>
                </div>

                <!-- Conversion Rate Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="p-2.5 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ $conversionRate }}%</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Conversion Rate</p>
                    </div>
                </div>

                <!-- Pages Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="p-2.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ number_format($totalPages) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Pages</p>
                    </div>
                </div>

                <!-- Checkouts Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-lg transition-shadow">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="p-2.5 rounded-lg bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            @if($checkoutsChange != 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $checkoutsChange >= 0 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $checkoutsChange >= 0 ? '↑' : '↓' }} {{ abs($checkoutsChange) }}%
                            </span>
                            @endif
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-3">{{ number_format($checkoutVisits) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Checkouts</p>
                    </div>
                </div>
            </div>

            <!-- Traffic Sources & Device Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Traffic Sources Donut Chart -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Traffic Sources</h3>
                        <div class="flex flex-col md:flex-row items-center gap-6">
                            <div class="relative w-48 h-48">
                                <canvas id="trafficSourcesChart"></canvas>
                            </div>
                            <div class="flex-1 grid grid-cols-2 gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Direct</span>
                                    <span class="ml-auto text-sm font-semibold text-gray-900 dark:text-white">{{ $trafficSources['Direct'] }}%</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-pink-500"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Social</span>
                                    <span class="ml-auto text-sm font-semibold text-gray-900 dark:text-white">{{ $trafficSources['Social'] }}%</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Search</span>
                                    <span class="ml-auto text-sm font-semibold text-gray-900 dark:text-white">{{ $trafficSources['Search'] }}%</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Referral</span>
                                    <span class="ml-auto text-sm font-semibold text-gray-900 dark:text-white">{{ $trafficSources['Referral'] }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Device Distribution -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Device Distribution</h3>
                        <div class="space-y-4">
                            <!-- Mobile -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mobile</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $deviceDistribution['mobile'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-indigo-500 h-2 rounded-full transition-all duration-500" style="width: {{ $deviceDistribution['mobile'] }}%"></div>
                                </div>
                            </div>
                            <!-- Desktop -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Desktop</span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $deviceDistribution['desktop'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $deviceDistribution['desktop'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Traffic Overview Chart (Multi-line) -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 mb-8">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Traffic Overview</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Visits vs Leads comparison</p>
                        </div>
                        <div class="flex gap-2">
                            <select onchange="updateParam('visits_landing_id', this.value)" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                <option value="">All Landings</option>
                                @foreach($landings as $landing)
                                    <option value="{{ $landing->id }}" {{ request('visits_landing_id') == $landing->id ? 'selected' : '' }}>
                                        {{ $landing->name }}
                                    </option>
                                @endforeach
                            </select>

                            <select onchange="handleRangeChange('visits', this.value)" class="text-xs rounded-lg border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                <option value="today" {{ request('visits_range') == 'today' ? 'selected' : '' }}>Today</option>
                                <option value="7d" {{ request('visits_range', '7d') == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="30d" {{ request('visits_range') == '30d' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="this_month" {{ request('visits_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                <option value="last_month" {{ request('visits_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                                <option value="custom" {{ request('visits_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="relative h-80 w-full">
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Performing Landings & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Top Performing Landings Table -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top Performing Landings</h3>
                            <a href="{{ route('landings.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">View All &rarr;</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase text-xs">Page Name</th>
                                        <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase text-xs">Status</th>
                                        <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase text-xs text-right">Visits</th>
                                        <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase text-xs text-right">Conv. Rate</th>
                                        <th class="px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase text-xs text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($topLandings as $landing)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ Str::limit($landing->name, 25) }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $landing->is_main ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                                                    {{ $landing->is_main ? 'Live' : 'Draft' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300 font-mono">{{ number_format($landing->visits_count) }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="font-semibold {{ $landing->conversion_rate >= 5 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">
                                                    {{ $landing->conversion_rate }}%
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('landings.edit', $landing) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-xs font-medium">Edit</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                No landing pages yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Recent Activity</h3>
                        <div class="space-y-4 max-h-80 overflow-y-auto">
                            @forelse($recentActivity as $activity)
                                <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex-shrink-0 p-2 rounded-full {{ $activity['type'] === 'lead' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' }}">
                                        @if($activity['type'] === 'lead')
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900 dark:text-white truncate">{{ $activity['message'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $activity['time_ago'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-8 w-8 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm">No recent activity</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Data Tables (Existing) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Recent Orders -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Orders</h3>
                            <a href="{{ route('leads.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">View All &rarr;</a>
                        </div>
                        <div class="overflow-hidden bg-white dark:bg-gray-800 rounded-lg">
                            <table class="min-w-full text-left text-sm whitespace-nowrap">
                                <thead class="uppercase tracking-wider border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Date</th>
                                        <th scope="col" class="px-6 py-3">Customer</th>
                                        <th scope="col" class="px-6 py-3">Amount</th>
                                        <th scope="col" class="px-6 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($recentOrders as $order)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                                {{ $order->created_at->format('M d') }}
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                {{ $order->customer_name }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-900 dark:text-white">
                                                {{ $order->currency }} {{ number_format($order->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $order->status === 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                                    {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                                    {{ $order->status === 'failed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                No recent orders found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Forms -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Forms</h3>
                            <a href="{{ route('forms.index') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">View All &rarr;</a>
                        </div>
                        <div class="overflow-hidden bg-white dark:bg-gray-800 rounded-lg">
                            <table class="min-w-full text-left text-sm whitespace-nowrap">
                                <thead class="uppercase tracking-wider border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-semibold">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Date</th>
                                        <th scope="col" class="px-6 py-3">Email</th>
                                        <th scope="col" class="px-6 py-3">Landing</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($recentForms as $form)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                                {{ $form->created_at->format('M d, H:i') }}
                                            </td>
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                                {{ $form->email ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">
                                                {{ $form->landing->name ?? 'Deleted' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                                No recent form submissions.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Date Range Modal -->
            <div id="custom-date-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeCustomModal()"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">Custom Date Range</h3>
                                    <div class="mt-4 grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                                            <input type="date" id="custom-start" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                                            <input type="date" id="custom-end" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" onclick="applyCustomRange()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">Apply</button>
                            <button type="button" onclick="closeCustomModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data for JS -->
            <script>
                window.dashboardData = {
                    chartLabels: @json($chartLabels),
                    visits: @json($visitsData),
                    leads: @json($leadsData),
                    trafficSources: @json($trafficSources),
                };

                let currentFilterType = '';

                function updateParam(key, value) {
                    const url = new URL(window.location.href);
                    if (value) {
                        url.searchParams.set(key, value);
                    } else {
                        url.searchParams.delete(key);
                    }
                    if (key.endsWith('_range') && value !== 'custom') {
                        const type = key.replace('_range', '');
                        url.searchParams.delete(type + '_start');
                        url.searchParams.delete(type + '_end');
                    }
                    window.location.href = url.toString();
                }
                
                function handleRangeChange(type, value) {
                    if (value === 'custom') {
                        currentFilterType = type;
                        openCustomModal();
                    } else {
                        updateParam(type + '_range', value);
                    }
                }

                function openCustomModal() {
                    document.getElementById('custom-date-modal').classList.remove('hidden');
                }

                function closeCustomModal() {
                    document.getElementById('custom-date-modal').classList.add('hidden');
                }

                function applyCustomRange() {
                    const start = document.getElementById('custom-start').value;
                    const end = document.getElementById('custom-end').value;

                    if (!start || !end) {
                        if (window.Toast) {
                            window.Toast.error('Please select both start and end dates.');
                        } else {
                            alert('Please select both start and end dates.');
                        }
                        return;
                    }
                    
                    const url = new URL(window.location.href);
                    url.searchParams.set(currentFilterType + '_range', 'custom');
                    url.searchParams.set(currentFilterType + '_start', start);
                    url.searchParams.set(currentFilterType + '_end', end);
                    window.location.href = url.toString();
                }
            </script>

        </div>
    </div>

    @vite(['resources/js/dashboard.js'])

</x-app-layout>
