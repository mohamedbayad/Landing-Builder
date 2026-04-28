<x-app-layout>
<x-slot name="topbar">
    <div class="leading-tight">
        <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight">Visitor Recordings</h1>
        <p class="text-xs sm:text-sm text-gray-400 mt-0.5 truncate">
            Showing replays for {{ $landingPage ? $landingPage->name : 'All Landings' }}
        </p>
    </div>
</x-slot>

<div class="py-8" x-data="{ deviceFilter: 'all', convertedFilter: 'all' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Filters -->
        <div class="flex justify-end mb-6">
            <div class="flex flex-wrap gap-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <select x-model="deviceFilter" class="pl-9 pr-4 py-2 border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        <option value="all">All Devices</option>
                        <option value="desktop">Desktop</option>
                        <option value="tablet">Tablet</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>

                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </div>
                    <select x-model="convertedFilter" class="pl-9 pr-4 py-2 border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        <option value="all">All Sessions</option>
                        <option value="1">Converted Only</option>
                        <option value="0">Unconverted</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-[#161B22] rounded-xl shadow-sm border border-gray-100 dark:border-white/[0.06] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-white/[0.06]">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visitor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Environment</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Activity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                        @forelse($sessions as $session)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors" x-show="(deviceFilter === 'all' || deviceFilter === '{{ $session->device_type }}') && (convertedFilter === 'all' || convertedFilter === '{{ $session->converted ? '1' : '0' }}')">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center text-brand-orange font-bold text-xs border border-orange-100 dark:border-orange-500/20">
                                        {{ strtoupper(substr($session->visitor_id, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            #{{ substr($session->visitor_id, 0, 8) }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[150px]">
                                            @if($session->referrer)
                                                From {{ parse_url($session->referrer, PHP_URL_HOST) ?? 'External' }}
                                            @else
                                                Direct Type-in
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white">{{ $session->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $session->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-300 capitalize">
                                    @if($session->device_type === 'desktop')
                                        <svg class="flex-shrink-0 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    @elseif($session->device_type === 'tablet')
                                        <svg class="flex-shrink-0 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @else
                                        <svg class="flex-shrink-0 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @endif
                                    {{ $session->device_type }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $session->pages_count }} page{{ $session->pages_count !== 1 ? 's' : '' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $session->duration }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($session->converted)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                        <svg class="mr-1 h-2.5 w-2.5 text-emerald-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        Converted
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-400">
                                        <svg class="mr-1 h-2.5 w-2.5 text-gray-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        Browsed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('recordings.show', [$landingPage ? $landingPage->id : $session->landing_page_id, $session->session_id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-orange text-white text-xs font-semibold rounded-lg hover:bg-brand-orange-600 transition-all shadow-sm">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Play
                                    </a>

                                    <form action="{{ route('recordings.destroy', $session->session_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this recording?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">No recordings yet</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Recordings will appear here once visitors interact with your pages.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sessions->hasPages())
            <div class="bg-white dark:bg-[#161B22] px-4 py-3 border-t border-gray-100 dark:border-white/[0.06]">
                {{ $sessions->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
</x-app-layout>
