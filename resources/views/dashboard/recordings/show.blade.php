<x-app-layout>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/rrweb-player@1.0.0-alpha.4/dist/style.css" />
<style>
    .rr-player { background: #f9fafb; border-radius: 0.5rem; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
    .dark .rr-player { background: #1f2937; border-color: #374151; }
</style>

<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Session Replay</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Visitor <strong>{{ substr($session->visitor_id, 0, 8) }}</strong> from {{ $session->started_at->format('M d, Y') }}</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('recordings.index', $landingPage->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4 space-y-6">
                <!-- Metadata Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white capitalize flex items-center mb-4">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Details
                        </h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Device</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 capitalize font-medium flex items-center">
                                    @if($session->device_type === 'desktop')
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    @elseif($session->device_type === 'tablet')
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @else
                                        <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @endif
                                    {{ $session->device_type }} 
                                    <span class="ml-1 text-gray-400 text-xs">({{ $session->screen_width }}x{{ $session->screen_height }})</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 font-medium">{{ $session->duration }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</dt>
                                <dd class="mt-1 text-sm font-medium">
                                    @if($session->converted)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Converted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Browsed
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Referrer</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 truncate" title="{{ $session->referrer }}">{{ $session->referrer ?: 'Direct / None' }}</dd>
                            </div>
                            @if($session->utm_params)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Campaign Limits</dt>
                                <dd class="mt-2 text-xs">
                                    <ul class="space-y-1">
                                    @foreach($session->utm_params as $key => $val)
                                        <li class="flex justify-between">
                                            <span class="text-gray-500">{{ str_replace('utm_', '', $key) }}:</span>
                                            <span class="font-medium text-gray-900 dark:text-gray-200">{{ $val }}</span>
                                        </li>
                                    @endforeach
                                    </ul>
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Timeline Card -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white capitalize flex items-center mb-4">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Journey
                        </h3>
                        <div class="flow-root mt-4">
                            <ul class="-mb-8">
                                @foreach($session->pages as $index => $page)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-600" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-800 
                                                    {{ $page->page_type === 'checkout' ? 'bg-yellow-500' : ($page->page_type === 'thankyou' ? 'bg-green-500' : 'bg-indigo-500') }}">
                                                    @if($page->page_type === 'checkout')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                    @elseif($page->page_type === 'thankyou')
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    @else
                                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">
                                                        <span class="font-medium text-gray-900 dark:text-white">{{ $page->page_type }}</span> step
                                                    </p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                    <time datetime="{{ $page->entered_at }}">{{ $page->entered_at->format('H:i:s') }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Player Data -->
            <div class="w-full lg:w-3/4">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-100 dark:border-gray-700 overflow-hidden h-full">
                    <div class="p-4 sm:p-6 w-full h-full flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900">
                        <div id="player" class="w-full max-w-full flex items-center justify-center"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/lz-string@1.5.0/libs/lz-string.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/rrweb-player@1.0.0-alpha.4/dist/index.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const allEvents = [];
        
        @foreach($session->pages as $page)
            @foreach($page->events as $eventBatch)
                (function() {
                    try {
                        const rawContent = @json($eventBatch->events_compressed);
                        let parsed = null;
                        
                        if (rawContent && (rawContent.trim().startsWith('[') || rawContent.trim().startsWith('{'))) {
                            parsed = JSON.parse(rawContent);
                        } else if (rawContent) {
                            const decompressed = typeof LZString !== 'undefined' ? LZString.decompressFromBase64(rawContent) : null;
                            if (decompressed) {
                                parsed = JSON.parse(decompressed);
                            }
                        }
                        
                        if (Array.isArray(parsed)) {
                            parsed.forEach(e => allEvents.push(e));
                        }
                    } catch(err) {
                        console.error('Failed to parse event batch for page {{ $page->id }}', err);
                    }
                })();
            @endforeach
        @endforeach

        allEvents.sort((a, b) => a.timestamp - b.timestamp);

        if (allEvents.length === 0) {
            document.getElementById('player').innerHTML = '<div class="text-center p-12"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg><h3 class="mt-2 text-sm font-medium text-gray-900">No events found</h3><p class="mt-1 text-sm text-gray-500">There are no playable events recorded for this session.</p></div>';
            return;
        }

        // Calculate a responsive width
        const playerContainer = document.getElementById('player');
        let playerWidth = playerContainer.clientWidth;
        let playerHeight = (playerWidth * 9) / 16; // 16:9 aspect ratio
        
        // Ensure minimums but clamp to actual available space
        if (playerWidth > 1024) {
            playerWidth = 1024;
            playerHeight = (1024 * 9) / 16;
        }

        const player = new rrwebPlayer({
            target: playerContainer,
            props: {
                events: allEvents,
                autoPlay: true,
                width: playerWidth,
                height: playerHeight,
                skipInactive: true,
                showController: true,
            }
        });
        
        // Re-scale player on window resize for responsiveness
        window.addEventListener('resize', function() {
            setTimeout(() => {
                let newWidth = playerContainer.parentElement.clientWidth - 32;
                if (newWidth > 1024) newWidth = 1024;
                const newHeight = (newWidth * 9) / 16;
                // Basic implementation for adjusting dimensions although rrweb native resize takes extra config
                const playerEl = document.querySelector('.rr-player');
                if (playerEl) {
                    playerEl.style.transform = `scale(${newWidth/playerWidth})`;
                    playerEl.style.transformOrigin = 'top left';
                }
            }, 100);
        });
    });
</script>
</x-app-layout>
