<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 dark:text-white leading-tight tracking-tight">
                    Who's Online
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Real-time visitor activity & geographic breakdown</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    Auto-refresh in <span id="refresh-countdown" class="font-mono font-bold text-indigo-600 dark:text-indigo-400">60</span>s
                </span>
                <button onclick="refreshNow()" id="manual-refresh-btn" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm" title="Refresh Now">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

            {{-- Loading Overlay --}}
            <div id="loading-overlay" class="hidden fixed inset-0 z-40 bg-gray-900/10 dark:bg-gray-900/30 backdrop-blur-sm flex items-center justify-center">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6 flex items-center gap-3 border border-gray-200 dark:border-gray-700">
                    <svg class="w-5 h-5 animate-spin text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Refreshing data...</span>
                </div>
            </div>

            {{-- Top Row: Total Online + Chart --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

                {{-- Total Online Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="p-8 flex flex-col items-center justify-center h-full min-h-[280px] relative">
                        {{-- Background Gradient --}}
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-50/80 to-transparent dark:from-emerald-900/10 dark:to-transparent"></div>

                        <div class="relative z-10 text-center">
                            {{-- Animated Ping Indicator --}}
                            <div class="relative inline-flex mb-6">
                                <span class="flex h-4 w-4 absolute -top-1 -right-1">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-white dark:border-gray-800"></span>
                                </span>
                                <div class="p-5 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 shadow-sm">
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>

                            <h3 class="text-6xl font-extrabold text-gray-900 dark:text-white tracking-tight" id="total-online-count">
                                {{ $totalOnline }}
                            </h3>
                            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mt-3 uppercase tracking-widest">
                                Live Now
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2" id="last-updated-text">
                                Updated: {{ \Carbon\Carbon::parse($lastUpdated)->format('H:i:s') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ApexChart Donut (Top 5 Locations) --}}
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Top Locations</h3>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                                Top 5
                            </span>
                        </div>
                        <div id="donut-chart" class="flex items-center justify-center" style="min-height: 240px;"></div>
                    </div>
                </div>

            </div>

            {{-- Location Breakdown Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Location Breakdown</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400" id="location-count-badge">
                            {{ $locations->count() }} {{ Str::plural('location', $locations->count()) }}
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700" id="locations-table">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Country</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">City</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visitors</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/3">Distribution</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700" id="locations-tbody">
                            @forelse($locations as $loc)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-100 dark:border-indigo-800/50 flex-shrink-0">
                                                {{ substr($loc->country === 'Unknown' ? 'UN' : $loc->country, 0, 2) }}
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $loc->country === 'Unknown' ? 'Unknown Country' : $loc->country }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $loc->city === 'Unknown' ? 'Unknown City' : $loc->city }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            {{ $loc->count }} {{ Str::plural('visitor', $loc->count) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-700" style="width: {{ round(($loc->count / max($totalOnline, 1)) * 100) }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 w-10 text-right">{{ round(($loc->count / max($totalOnline, 1)) * 100) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No active visitors right now</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Waiting for visitors to connect...</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @vite(['resources/js/online-users.js'])

    {{-- ApexCharts & Auto-refresh Script --}}
    <script>
        // ---- Initial Data ----
        let currentData = {
            total: {{ $totalOnline }},
            locations: @json($locations),
            last_updated: "{{ $lastUpdated }}"
        };

        let chart = null;
        let secondsLeft = 60;

        // ---- ApexChart ----
        function renderDonutChart(locations) {
            const top5 = locations.slice(0, 5);
            const labels = top5.map(l => `${l.country === 'Unknown' ? 'Unknown' : l.country} - ${l.city === 'Unknown' ? 'Unknown' : l.city}`);
            const series = top5.map(l => l.count);

            const isDark = document.documentElement.classList.contains('dark');

            const chartOptions = {
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'inherit',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                    },
                },
                series: series.length > 0 ? series : [1],
                labels: labels.length > 0 ? labels : ['No visitors'],
                colors: series.length > 0
                    ? ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6']
                    : ['#e5e7eb'],
                stroke: {
                    width: 2,
                    colors: [isDark ? '#1f2937' : '#ffffff']
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'right',
                    fontSize: '13px',
                    fontWeight: 500,
                    labels: {
                        colors: isDark ? '#d1d5db' : '#374151'
                    },
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 3,
                    },
                    itemMargin: {
                        vertical: 4
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    color: isDark ? '#e5e7eb' : '#111827'
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 700,
                                    color: isDark ? '#e5e7eb' : '#111827',
                                    formatter: (val) => val + ' visitors'
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Total Online',
                                    fontSize: '12px',
                                    fontWeight: 600,
                                    color: isDark ? '#9ca3af' : '#6b7280',
                                    formatter: () => currentData.total
                                }
                            }
                        }
                    }
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: (val) => val + ' visitors'
                    }
                },
                responsive: [{
                    breakpoint: 640,
                    options: {
                        chart: { height: 300 },
                        legend: { position: 'bottom' }
                    }
                }]
            };

            if (chart) {
                chart.updateOptions(chartOptions, true, true);
            } else {
                chart = new ApexCharts(document.querySelector('#donut-chart'), chartOptions);
                chart.render();
            }
        }

        // ---- Table Rendering ----
        function renderTable(locations, total) {
            const tbody = document.getElementById('locations-tbody');
            const badge = document.getElementById('location-count-badge');

            if (!tbody) return;

            badge.textContent = `${locations.length} ${locations.length === 1 ? 'location' : 'locations'}`;

            if (locations.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No active visitors right now</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Waiting for visitors to connect...</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            locations.forEach(loc => {
                const country = loc.country === 'Unknown' ? 'Unknown Country' : loc.country;
                const city = loc.city === 'Unknown' ? 'Unknown City' : loc.city;
                const initial = loc.country === 'Unknown' ? 'UN' : loc.country.substring(0, 2);
                const maxTotal = Math.max(total, 1);
                const pct = Math.round((loc.count / maxTotal) * 100);
                const plural = loc.count === 1 ? 'visitor' : 'visitors';

                html += `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs border border-indigo-100 dark:border-indigo-800/50 flex-shrink-0">
                                    ${initial}
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">${country}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                ${city}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                ${loc.count} ${plural}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-700" style="width: ${pct}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 w-10 text-right">${pct}%</span>
                            </div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        // ---- Auto-Refresh ----
        function fetchStats(showLoading = false) {
            if (showLoading) {
                document.getElementById('loading-overlay').classList.remove('hidden');
            }

            fetch("{{ route('online-users.api') }}")
                .then(res => res.json())
                .then(data => {
                    currentData = data;

                    // Update total count
                    const totalEl = document.getElementById('total-online-count');
                    if (totalEl) totalEl.textContent = data.total;

                    // Update time
                    const timeEl = document.getElementById('last-updated-text');
                    if (timeEl) {
                        const dt = new Date(data.last_updated);
                        timeEl.textContent = 'Updated: ' + dt.toLocaleTimeString();
                    }

                    // Update chart
                    renderDonutChart(data.locations);

                    // Update table
                    renderTable(data.locations, data.total);

                    // Reset countdown
                    secondsLeft = 60;
                    document.getElementById('refresh-countdown').textContent = secondsLeft;
                })
                .catch(err => console.error('Refresh failed:', err))
                .finally(() => {
                    document.getElementById('loading-overlay').classList.add('hidden');
                });
        }

        // ---- Countdown Timer ----
        setInterval(() => {
            secondsLeft--;
            const el = document.getElementById('refresh-countdown');
            if (el) el.textContent = Math.max(secondsLeft, 0);
            if (secondsLeft <= 0) {
                fetchStats(false);
            }
        }, 1000);

        // ---- Manual Refresh (global) ----
        window.refreshNow = function() {
            fetchStats(true);
        };

        // ---- Init ----
        document.addEventListener('DOMContentLoaded', () => {
            renderDonutChart(currentData.locations);
        });
    </script>
</x-app-layout>
