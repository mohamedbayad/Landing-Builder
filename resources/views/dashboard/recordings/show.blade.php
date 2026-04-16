<x-app-layout>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/rrweb-player@1.0.0-alpha.4/dist/style.css" />
<style>
    .rr-player { background: #f9fafb; border-radius: 0.75rem; border: 1px solid #e5e7eb; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .dark .rr-player { background: #161B22; border-color: rgba(255,255,255,0.06); }
</style>

<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Session Replay</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Visitor <strong class="text-gray-700 dark:text-gray-300">{{ substr($session->visitor_id, 0, 8) }}</strong> &mdash; {{ $session->started_at->format('M d, Y') }}</p>
            </div>
            <a href="{{ route('recordings.index', $landingPage->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-white/8 transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4 space-y-5">
                <!-- Metadata Card -->
                <div class="bg-white dark:bg-[#161B22] rounded-xl shadow-sm border border-gray-100 dark:border-white/[0.06]">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Session Details
                        </h3>
                    </div>
                    <div class="p-5">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Device</dt>
                                <dd class="text-sm text-gray-900 dark:text-white capitalize font-medium flex items-center gap-1.5">
                                    @if($session->device_type === 'desktop')
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    @elseif($session->device_type === 'tablet')
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @endif
                                    {{ $session->device_type }}
                                    <span class="text-gray-400 dark:text-gray-500 text-xs font-normal">({{ $session->screen_width }}x{{ $session->screen_height }})</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Duration</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $session->duration }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Status</dt>
                                <dd class="mt-0.5">
                                    @if($session->converted)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            Converted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-400">
                                            Browsed
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">Referrer</dt>
                                <dd class="text-sm text-gray-700 dark:text-gray-300 truncate" title="{{ $session->referrer }}">{{ $session->referrer ?: 'Direct / None' }}</dd>
                            </div>
                            @if($session->utm_params)
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Campaign</dt>
                                <dd>
                                    <ul class="space-y-1">
                                    @foreach($session->utm_params as $key => $val)
                                        <li class="flex justify-between text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">{{ str_replace('utm_', '', $key) }}:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $val }}</span>
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
                <div class="bg-white dark:bg-[#161B22] rounded-xl shadow-sm border border-gray-100 dark:border-white/[0.06]">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Journey
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($session->pages as $index => $page)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-white/[0.06]" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-[#161B22]
                                                    {{ $page->page_type === 'checkout' ? 'bg-yellow-500' : ($page->page_type === 'thankyou' ? 'bg-emerald-500' : 'bg-brand-orange') }}">
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
                                                <div class="text-right text-xs whitespace-nowrap text-gray-400 dark:text-gray-500">
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

            <!-- Player Panel -->
            <div class="w-full lg:w-3/4">
                <div class="bg-white dark:bg-[#161B22] rounded-xl shadow-sm border border-gray-100 dark:border-white/[0.06] overflow-hidden h-full">
                    <div class="p-5 w-full h-full flex flex-col items-center justify-center bg-gray-50 dark:bg-[#0D1117]">
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
            document.getElementById('player').innerHTML = '<div class="text-center p-12"><svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg><h3 class="text-sm font-medium text-gray-900 dark:text-white">No events found</h3><p class="mt-1 text-sm text-gray-500">There are no playable events recorded for this session.</p></div>';
            return;
        }

        const playerContainer = document.getElementById('player');
        let playerWidth = playerContainer.clientWidth;
        let playerHeight = (playerWidth * 9) / 16;

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

        window.addEventListener('resize', function() {
            setTimeout(() => {
                let newWidth = playerContainer.parentElement.clientWidth - 32;
                if (newWidth > 1024) newWidth = 1024;
                const newHeight = (newWidth * 9) / 16;
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
