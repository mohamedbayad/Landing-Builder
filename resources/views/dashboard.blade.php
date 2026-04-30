<x-app-layout>
    <x-slot name="topbar">
        <div class="leading-tight">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white tracking-tight">Dashboard</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">Welcome back. Here's what's happening today.</p>
        </div>
    </x-slot>

    <div class="py-6 px-4 sm:px-6 md:px-8 space-y-6">

        @if(($isSuperAdmin ?? false) && !empty($superAdminStats))
            @php
                $adminKpis = $superAdminStats['kpis'] ?? [];
                $planMix = collect($superAdminStats['active_by_plan'] ?? []);
                $latestSubscribersList = collect($superAdminStats['latest_subscribers'] ?? []);
            @endphp
            <section class="bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-white/[0.06] flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">Super Admin Management Overview</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Revenue + subscribers + user management at a glance.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('users.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-gray-100 dark:bg-white/[0.06] text-gray-800 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-white/[0.1] transition-colors">Manage Users</a>
                        <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-gray-100 dark:bg-white/[0.06] text-gray-800 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-white/[0.1] transition-colors">Manage Subscriptions</a>
                        <a href="{{ route('plans.index') }}" class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium bg-brand-orange text-white hover:opacity-90 transition-colors">Manage Plans</a>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-emerald-200/50 dark:border-emerald-500/20 bg-emerald-50/70 dark:bg-emerald-500/5 p-4">
                        <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-400 font-semibold">Estimated MRR</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">${{ number_format((float) ($adminKpis['mrr'] ?? 0), 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-indigo-200/50 dark:border-indigo-500/20 bg-indigo-50/70 dark:bg-indigo-500/5 p-4">
                        <p class="text-xs uppercase tracking-wide text-indigo-700 dark:text-indigo-400 font-semibold">Estimated ARR</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">${{ number_format((float) ($adminKpis['arr'] ?? 0), 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-orange-200/50 dark:border-orange-500/20 bg-orange-50/70 dark:bg-orange-500/5 p-4">
                        <p class="text-xs uppercase tracking-wide text-orange-700 dark:text-orange-400 font-semibold">Booked Revenue (Estimate)</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">${{ number_format((float) ($adminKpis['booked_revenue'] ?? 0), 2) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-white/[0.06] bg-gray-50/70 dark:bg-white/[0.02] p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-600 dark:text-gray-400 font-semibold">Total Users</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format((int) ($adminKpis['total_users'] ?? 0)) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-white/[0.06] bg-gray-50/70 dark:bg-white/[0.02] p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-600 dark:text-gray-400 font-semibold">Total Subscribers</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format((int) ($adminKpis['total_subscribers'] ?? 0)) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-white/[0.06] bg-gray-50/70 dark:bg-white/[0.02] p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-600 dark:text-gray-400 font-semibold">Active Subscribers</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format((int) ($adminKpis['active_subscribers'] ?? 0)) }}</p>
                    </div>
                </div>

                <div class="px-6 pb-6 grid grid-cols-1 xl:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-gray-200 dark:border-white/[0.06] overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.06] bg-gray-50/70 dark:bg-white/[0.02]">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Active Subscriptions by Plan</h3>
                        </div>
                        <div class="p-4 space-y-3">
                            @forelse($planMix as $item)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $item['plan'] }}</span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-orange/10 text-brand-orange border border-brand-orange/20">{{ (int) $item['count'] }}</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">No active plan distribution yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-white/[0.06] overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.06] bg-gray-50/70 dark:bg-white/[0.02]">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Latest Subscribers</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                            @forelse($latestSubscribersList as $subscriberItem)
                                <div class="px-4 py-3 flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $subscriberItem['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $subscriberItem['email'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-600 dark:text-gray-300">{{ $subscriberItem['plan'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst((string) $subscriberItem['status']) }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-6">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No subscribers found yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- Stats Grid (5 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Visits Card -->
            <div class="group relative overflow-hidden bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] p-5 hover:shadow-xl hover:shadow-orange-500/5 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2.5 rounded-xl bg-gradient-to-br from-orange-500/10 to-orange-500/5 text-brand-orange border border-orange-500/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        @if($visitsChange != 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $visitsChange >= 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                            {{ $visitsChange >= 0 ? '↑' : '↓' }} {{ abs($visitsChange) }}%
                        </span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ number_format($totalVisits) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Visits</p>
                </div>
            </div>

            <!-- Leads Card -->
            <div class="group relative overflow-hidden bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] p-5 hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2.5 rounded-xl bg-gradient-to-br from-emerald-500/10 to-emerald-500/5 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        @if($leadsChange != 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $leadsChange >= 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                            {{ $leadsChange >= 0 ? '↑' : '↓' }} {{ abs($leadsChange) }}%
                        </span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ number_format($totalLeads) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Leads</p>
                </div>
            </div>

            <!-- Conversion Rate Card -->
            <div class="group relative overflow-hidden bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] p-5 hover:shadow-xl hover:shadow-purple-500/5 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2.5 rounded-xl bg-gradient-to-br from-purple-500/10 to-purple-500/5 text-purple-600 dark:text-purple-400 border border-purple-500/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $conversionRate }}%</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Conversion Rate</p>
                </div>
            </div>

            <!-- Pages Card -->
            <div class="group relative overflow-hidden bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] p-5 hover:shadow-xl hover:shadow-blue-500/5 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2.5 rounded-xl bg-gradient-to-br from-blue-500/10 to-blue-500/5 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ number_format($totalPages) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Active Pages</p>
                </div>
            </div>

            <!-- Checkouts Card -->
            <div class="group relative overflow-hidden bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] p-5 hover:shadow-xl hover:shadow-orange-500/5 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-2.5 rounded-xl bg-gradient-to-br from-orange-500/10 to-orange-500/5 text-orange-600 dark:text-orange-400 border border-orange-500/20">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        @if($checkoutsChange != 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $checkoutsChange >= 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' }}">
                            {{ $checkoutsChange >= 0 ? '↑' : '↓' }} {{ abs($checkoutsChange) }}%
                        </span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ number_format($checkoutVisits) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Checkouts</p>
                </div>
            </div>
        </div>

        <!-- Traffic & Devices Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Traffic Sources Donut Chart -->
            <div class="lg:col-span-2 bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-white/[0.06]">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Traffic Sources</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Where your visitors come from</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="relative w-52 h-52">
                            <canvas id="trafficSourcesChart"></canvas>
                        </div>
                        <div class="flex-1 grid grid-cols-2 gap-4 w-full">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/[0.02]">
                                <span class="w-3 h-3 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/30"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Direct</span>
                                <span class="ml-auto text-sm font-bold text-gray-900 dark:text-white">{{ $trafficSources['Direct'] }}%</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/[0.02]">
                                <span class="w-3 h-3 rounded-full bg-pink-500 shadow-lg shadow-pink-500/30"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Paid Ads</span>
                                <span class="ml-auto text-sm font-bold text-gray-900 dark:text-white">{{ $trafficSources['Social'] }}%</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/[0.02]">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/30"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Search</span>
                                <span class="ml-auto text-sm font-bold text-gray-900 dark:text-white">{{ $trafficSources['Search'] }}%</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/[0.02]">
                                <span class="w-3 h-3 rounded-full bg-amber-500 shadow-lg shadow-amber-500/30"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Referral</span>
                                <span class="ml-auto text-sm font-bold text-gray-900 dark:text-white">{{ $trafficSources['Referral'] }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device Distribution -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Devices</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visitor device breakdown</p>
                </div>
                <div class="p-6 space-y-6">
                    <!-- Mobile -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-orange-500/10 text-brand-orange">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Mobile</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $deviceDistribution['mobile'] }}%</span>
                        </div>
                        <div class="h-2.5 bg-gray-100 dark:bg-white/[0.06] rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-brand-orange to-orange-400 rounded-full transition-all duration-500" style="width: {{ $deviceDistribution['mobile'] }}%"></div>
                        </div>
                    </div>
                    <!-- Desktop -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-500">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Desktop</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $deviceDistribution['desktop'] }}%</span>
                        </div>
                        <div class="h-2.5 bg-gray-100 dark:bg-white/[0.06] rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full transition-all duration-500" style="width: {{ $deviceDistribution['desktop'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Traffic Overview Chart -->
        <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
            <div class="p-6 pb-0">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Traffic Overview</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visits vs Leads comparison</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <select onchange="updateParam('visits_landing_id', this.value)" class="text-sm rounded-xl border-gray-200 dark:border-white/[0.08] bg-gray-50 dark:bg-[#0D1117] text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange px-4 py-2.5 transition-all">
                            <option value="">All Landings</option>
                            @foreach($landings as $landing)
                                <option value="{{ $landing->id }}" {{ request('visits_landing_id') == $landing->id ? 'selected' : '' }}>
                                    {{ $landing->name }}
                                </option>
                            @endforeach
                        </select>

                        <select onchange="handleRangeChange('visits', this.value)" class="text-sm rounded-xl border-gray-200 dark:border-white/[0.08] bg-gray-50 dark:bg-[#0D1117] text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange px-4 py-2.5 transition-all">
                            <option value="today" {{ request('visits_range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="7d" {{ request('visits_range', '7d') == '7d' ? 'selected' : '' }}>7 Days</option>
                            <option value="30d" {{ request('visits_range') == '30d' ? 'selected' : '' }}>30 Days</option>
                            <option value="this_month" {{ request('visits_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ request('visits_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="custom" {{ request('visits_range') == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-6 pt-0">
                <div class="relative h-80 w-full">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Who's Online & Top Landings Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="online-users-section">
            <!-- Total Online Card -->
            <div class="bg-gradient-to-br from-emerald-100 via-white to-emerald-50 dark:from-emerald-500/10 dark:via-[#161B22] dark:to-[#161B22] rounded-2xl border border-emerald-200 dark:border-emerald-500/20 overflow-hidden">
                <div class="p-6 flex flex-col items-center justify-center h-full min-h-[220px] relative">
                    <div class="absolute inset-0 bg-gradient-to-t from-emerald-500/5 to-transparent"></div>
                    <div class="relative">
                        <div class="relative inline-flex mb-4">
                            <span class="flex h-3 w-3 absolute top-0 right-0 -mr-1 -mt-1">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-5xl font-bold text-gray-900 dark:text-white" id="online-users-count">{{ $onlineUsersCount }}</h3>
                        <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400/80 mt-2 uppercase tracking-wider">Live Visitors</p>
                    </div>
                </div>
            </div>

            <!-- Active Locations Breakdown -->
            <div class="lg:col-span-2 bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="p-6 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Active Locations</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Real-time visitor geography</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400 dark:text-gray-500">Refresh in <span id="refresh-countdown" class="font-medium text-gray-600 dark:text-gray-300">60</span>s</span>
                            <button onclick="fetchOnlineUsers(true)" id="manual-refresh-btn" class="p-2 rounded-xl bg-gray-50 dark:bg-white/[0.03] text-gray-400 hover:text-brand-orange hover:bg-brand-orange/5 transition-all" title="Refresh Now">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-4 overflow-y-auto max-h-72 pr-2 custom-scrollbar" id="online-users-list">
                    @if($onlineUsers->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                            <svg class="w-12 h-12 mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium">No active visitors</p>
                            <p class="text-xs mt-1 opacity-70">Waiting for visitors to connect...</p>
                        </div>
                    @else
                        <ul class="space-y-3">
                            @foreach($onlineUsers as $loc)
                                <li class="flex items-center justify-between p-4 rounded-xl bg-gray-50/50 dark:bg-white/[0.02] hover:bg-gray-100/70 dark:hover:bg-white/[0.04] transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/[0.06]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-orange-500/10 to-orange-500/5 text-brand-orange flex items-center justify-center font-bold text-sm border border-orange-500/20">
                                            {{ substr($loc->country === 'Unknown' ? 'UN' : $loc->country, 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $loc->country === 'Unknown' ? 'Unknown Country' : $loc->country }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ $loc->city === 'Unknown' ? 'Unknown City' : $loc->city }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="hidden sm:block w-24 h-1.5 bg-gray-200 dark:bg-white/[0.06] rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full" style="width: {{ ($loc->count / max($onlineUsersCount, 1)) * 100 }}%"></div>
                                        </div>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            {{ $loc->count }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Landings & Activity Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Performing Landings Table -->
            <div class="lg:col-span-2 bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="p-6 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Performing</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your best converting landing pages</p>
                        </div>
                        <a href="{{ route('landings.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-orange hover:text-brand-orange-600 transition-colors">
                            View All
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-white/[0.02]">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Page</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visits</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conv.</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                            @forelse($topLandings as $landing)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($landing->name, 28) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php($isPlatformMain = \App\Support\LandingPublicUrl::isPlatformMainLanding($landing))
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $landing->is_main ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400 border border-gray-200 dark:border-white/[0.08]' }}">
                                            {{ $landing->is_main ? ($isPlatformMain ? 'Main Landing (Platform Root)' : 'Workspace Default') : 'Secondary' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 tabular-nums">{{ number_format($landing->visits_count) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-bold {{ $landing->conversion_rate >= 5 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $landing->conversion_rate }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('landings.edit', $landing->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-orange/10 text-brand-orange hover:bg-brand-orange/20 transition-colors">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <p class="text-sm">No landing pages yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="p-6 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Latest events & actions</p>
                </div>
                <div class="p-6 pt-4 space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
                    @forelse($recentActivity as $activity)
                        <div class="flex items-start gap-4 p-3.5 rounded-xl bg-gray-50/50 dark:bg-white/[0.02] hover:bg-gray-100/50 dark:hover:bg-white/[0.04] transition-all">
                            <div class="flex-shrink-0 p-2 rounded-lg {{ $activity['type'] === 'lead' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-blue-500/10 text-blue-600 dark:text-blue-400' }}">
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
                                <p class="text-sm text-gray-900 dark:text-white leading-relaxed">{{ $activity['message'] }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">{{ $activity['time_ago'] }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 text-gray-400 dark:text-gray-500">
                            <svg class="w-10 h-10 mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm">No recent activity</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Orders & Forms Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Orders -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="p-6 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Orders</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Latest checkout completions</p>
                        </div>
                        <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-orange hover:text-brand-orange-600 transition-colors">
                            View All
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-white/[0.02]">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                            @forelse($recentOrders as $order)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $order->created_at->format('M d') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $order->customer_name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white tabular-nums">{{ $order->currency }} {{ number_format($order->amount, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            {{ $order->status === 'paid' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : '' }}
                                            {{ $order->status === 'pending' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                                            {{ $order->status === 'failed' ? 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' : '' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-sm">No orders yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Forms -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="p-6 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Form Submissions</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Latest lead captures</p>
                        </div>
                        <a href="{{ route('forms.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-orange hover:text-brand-orange-600 transition-colors">
                            View All
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50/50 dark:bg-white/[0.02]">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Landing</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                            @forelse($recentForms as $form)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $form->created_at->format('M d, H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $form->email ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $form->landing->name ?? 'Deleted' }}</span>
                                    </td>
                                </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-sm">No submissions yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Custom Date Range Modal -->
        <div id="custom-date-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeCustomModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-[#161B22] rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100 dark:border-white/[0.08]">
                    <div class="p-6 pb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white" id="modal-title">Custom Date Range</h3>
                        <div class="mt-5 grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                                <input type="date" id="custom-start" class="w-full rounded-xl border-gray-200 dark:border-white/[0.08] dark:bg-[#0D1117] dark:text-white text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange px-4 py-3 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                                <input type="date" id="custom-end" class="w-full rounded-xl border-gray-200 dark:border-white/[0.08] dark:bg-[#0D1117] dark:text-white text-sm focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange px-4 py-3 transition-all">
                            </div>
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50/50 dark:bg-white/[0.02] flex justify-end gap-3">
                        <button type="button" onclick="closeCustomModal()" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-[#0D1117] border border-gray-200 dark:border-white/[0.08] hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-all">Cancel</button>
                        <button type="button" onclick="applyCustomRange()" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-orange hover:opacity-90 transition-all shadow-lg shadow-brand-orange/20">Apply Range</button>
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

        <!-- Online Users Auto-refresh -->
        <script>
            let secondsLeft = 60;

            function resetCountdown() {
                secondsLeft = 60;
                const countdownEl = document.getElementById('refresh-countdown');
                if (countdownEl) countdownEl.textContent = secondsLeft;
            }

            function fetchOnlineUsers(manual = false) {
                if (manual) {
                    const btn = document.getElementById('manual-refresh-btn');
                    if (btn) {
                        btn.innerHTML = `<svg class="w-4 h-4 animate-spin text-brand-orange" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                        setTimeout(() => {
                            btn.innerHTML = `<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>`;
                        }, 500);
                    }
                }

                fetch('{{ route("online-users.api") }}')
                    .then(response => response.json())
                    .then(data => {
                        const countEl = document.getElementById('online-users-count');
                        if (countEl) countEl.textContent = data.count;

                        const listEl = document.getElementById('online-users-list');
                        if (listEl) {
                            if (data.locations.length === 0) {
                                listEl.innerHTML = `
                                    <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-500">
                                        <svg class="w-12 h-12 mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-medium">No active visitors</p>
                                        <p class="text-xs mt-1 opacity-70">Waiting for visitors to connect...</p>
                                    </div>
                                `;
                            } else {
                                let html = '<ul class="space-y-3">';
                                data.locations.forEach(loc => {
                                    const country = loc.country === 'Unknown' ? 'Unknown Country' : loc.country;
                                    const city = loc.city === 'Unknown' ? 'Unknown City' : loc.city;
                                    const initial = loc.country === 'Unknown' ? 'UN' : loc.country.substring(0, 2);
                                    const plural = loc.count === 1 ? 'Visitor' : 'Visitors';
                                    const maxCount = Math.max(data.count, 1);
                                    const pct = Math.round((loc.count / maxCount) * 100);

                                    html += `
                                        <li class="flex items-center justify-between p-4 rounded-xl bg-gray-50/50 dark:bg-white/[0.02] hover:bg-gray-100/70 dark:hover:bg-white/[0.04] transition-all border border-transparent hover:border-gray-100 dark:hover:border-white/[0.06]">
                                            <div class="flex items-center gap-4">
                                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-orange-500/10 to-orange-500/5 text-brand-orange flex items-center justify-center font-bold text-sm border border-orange-500/20">
                                                    ${initial}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">${country}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1.5">
                                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        ${city}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <div class="hidden sm:block w-24 h-1.5 bg-gray-200 dark:bg-white/[0.06] rounded-full overflow-hidden">
                                                    <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full" style="width: ${pct}%"></div>
                                                </div>
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    ${loc.count}
                                                </span>
                                            </div>
                                        </li>
                                    `;
                                });
                                html += '</ul>';
                                listEl.innerHTML = html;
                            }
                        }
                        resetCountdown();
                    });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const countdownEl = document.getElementById('refresh-countdown');
                if (countdownEl) {
                    setInterval(() => {
                        secondsLeft--;
                        if (secondsLeft <= 0) {
                            fetchOnlineUsers(false);
                        } else {
                            countdownEl.textContent = secondsLeft;
                        }
                    }, 1000);
                }
            });
        </script>

    </div>

    @vite(['resources/js/dashboard.js'])

</x-app-layout>
