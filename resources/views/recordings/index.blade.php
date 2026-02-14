<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
            {{ __('Visitor Recordings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            
            <!-- Main Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-6">
                    
                    <!-- Toolbar -->
                    <div class="flex flex-col lg:flex-row gap-4 mb-6">
                        <!-- Filters -->
                        <div class="flex flex-wrap gap-2 flex-1">
                            <!-- Landing Filter -->
                            <select id="landingFilter" onchange="applyFilter('landing_id', this.value)"
                                class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">All Landing Pages</option>
                                @foreach($landings as $landing)
                                    <option value="{{ $landing->id }}" {{ request('landing_id') == $landing->id ? 'selected' : '' }}>{{ $landing->name }}</option>
                                @endforeach
                            </select>

                            <!-- Date From -->
                            <input type="date" id="dateFrom" value="{{ request('date_from') }}" onchange="applyFilter('date_from', this.value)"
                                class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

                            <!-- Date To -->
                            <input type="date" id="dateTo" value="{{ request('date_to') }}" onchange="applyFilter('date_to', this.value)"
                                class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white py-2.5 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        
                        <!-- Info Badge -->
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ $recordings->total() }} recording(s)</span>
                        </div>
                    </div>
                    
                    @if($recordings->isEmpty())
                        <div class="text-center py-20 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-600">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No recordings yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Visitor sessions will appear here once they interact with your landing pages.
                            </p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Landing Page</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Duration</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($recordings as $recording)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $recording->created_at->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $recording->created_at->format('H:i') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-900 dark:text-white">{{ $recording->landingPage->landing->name ?? 'Unknown' }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $recording->location ?? 'Unknown' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                                                    {{ $recording->formatted_duration }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <!-- Play Button -->
                                                    <button onclick="playRecording({{ $recording->id }})" class="inline-flex items-center gap-1.5 px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors" title="Play Recording">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M8 5v14l11-7z"/>
                                                        </svg>
                                                        Play
                                                    </button>

                                                    <!-- Delete Button -->
                                                    <form action="{{ route('recordings.destroy', $recording) }}" method="POST" class="inline" onsubmit="event.preventDefault(); window.confirmAction('Are you sure you want to delete this recording?', this);">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Show</span>
                                <select onchange="applyFilter('per_page', this.value)" class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white py-1.5 px-3">
                                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="text-sm text-gray-500 dark:text-gray-400">per page</span>
                            </div>
                            {{ $recordings->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Player Modal -->
    <div id="playerModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/90 transition-opacity" onclick="closePlayerModal()"></div>
            <div class="relative bg-slate-900 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden border border-slate-700">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 bg-slate-800 border-b border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-600 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white" id="playerTitle">Session Replay</h3>
                            <p class="text-sm text-slate-400" id="playerMeta">Loading...</p>
                        </div>
                    </div>
                    <button onclick="closePlayerModal()" class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Player Container -->
                <div class="p-6 bg-slate-900 overflow-y-auto">
                    <div id="playerContainer" class="flex items-center justify-center min-h-[400px]">
                        <div class="text-center">
                            <svg class="animate-spin h-10 w-10 text-indigo-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-slate-400">Loading session data...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- rrweb Player CSS & JS from CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/rrweb-player@latest/dist/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/rrweb-player@latest/dist/index.js"></script>
    
    <!-- Custom styles for rrweb player mouse cursor -->
    <style>
        /* Ensure mouse cursor is visible and on top */
        .replayer-mouse {
            z-index: 999999 !important;
            display: block !important;
            pointer-events: none !important;
            position: absolute !important;
        }
        
        /* Ensure mouse tail/trail is visible */
        .replayer-mouse-tail {
            z-index: 999998 !important;
            display: block !important;
            pointer-events: none !important;
        }
        
        /* Make sure the replay wrapper allows mouse to show */
        .replayer-wrapper {
            overflow: visible !important;
        }
        
        /* Ensure iframe doesn't clip the mouse */
        .rr-player iframe {
            overflow: visible !important;
        }
    </style>

    <script>
        // Common stylesheets to inject into replay for icons/fonts
        const INJECT_STYLESHEETS = [
            // Google Fonts
            'https://fonts.googleapis.com/css2?family=Anton&family=Inter:wght@300;400;600;700;900&display=swap',
            // FontAwesome 6
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
            // Bootstrap Icons (if used)
            'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
        ];

        // CSS rules to inject directly into the replay iframe
        // These rules hide "null" text that appears when icon fonts don't load
        // AND hide elements that captured "null" from JavaScript rendering
        const INJECT_CSS_RULES = `
            /* CRITICAL: Hide 'null' text in icon elements */
            /* FontAwesome icons - hide text content, show only ::before */
            .fa, .fas, .far, .fab, .fal, .fad, .fat,
            [class*="fa-"],
            i[class*="fa"] {
                font-size: 0 !important;
                color: transparent !important;
            }
            .fa::before, .fas::before, .far::before, .fab::before, 
            .fal::before, .fad::before, .fat::before,
            [class*="fa-"]::before,
            i[class*="fa"]::before {
                font-size: 1rem !important;
                color: inherit !important;
                font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "FontAwesome" !important;
            }
            
            /* Bootstrap Icons */
            .bi, [class*="bi-"] {
                font-size: 0 !important;
                color: transparent !important;
            }
            .bi::before, [class*="bi-"]::before {
                font-size: 1rem !important;
                color: inherit !important;
                font-family: "bootstrap-icons" !important;
            }
            
            /* Generic icon class patterns */
            .icon, [class*="icon-"], [class*="-icon"] {
                font-size: 0 !important;
            }
            .icon::before, [class*="icon-"]::before, [class*="-icon"]::before {
                font-size: 1rem !important;
            }
            
            /* AGGRESSIVE: Hide elements that literally contain only "null" text */
            /* This targets JavaScript-rendered null values */
            
            /* Common widget containers that might show null */
            [class*="widget"], [class*="chat"], [class*="messenger"],
            [class*="popup"], [class*="modal"], [class*="overlay"],
            [class*="floating"], [class*="fixed"] {
                /* Don't hide entirely, but make sure icons work */
            }
            
            /* Target specific patterns from screenshot - navigation/header areas */
            nav *, header *, [class*="nav"] *, [class*="header"] *,
            [class*="menu"] *, [class*="toolbar"] * {
                /* Ensure proper font rendering */
            }
            
            /* Hide all <i> elements that don't have ::before content */
            i:empty {
                display: none !important;
            }
            
            /* SVG inline icons (shouldn't show null, but safety) */
            .svg-inline--fa {
                display: inline-block !important;
            }
            
            /* Crisp chat widget and similar floating buttons */
            [data-crisp], [class*="crisp"], 
            [id*="crisp"], [class*="intercom"],
            [id*="intercom"], [class*="drift"],
            [class*="hubspot"], [class*="tawk"] {
                visibility: hidden !important;
            }
            
            /* HIDE ONLY THE NAVIGATION BAR - NOT THE HERO SECTION */
            /* Be specific to avoid hiding hero content */
            nav:not([class*="hero"]):not([class*="main"]):not([class*="content"]),
            [class*="navbar"]:not([class*="hero"]),
            [class*="nav-bar"]:not([class*="hero"]),
            [class*="navigation"]:not([class*="hero"]):not([class*="main"]),
            [class*="top-bar"],
            [class*="topbar"] {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                max-height: 0 !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            /* Keep hero sections visible */
            [class*="hero"], [id*="hero"],
            section, main, article {
                display: block !important;
                visibility: visible !important;
            }
            
            /* Alternative: If you want to keep header but make it dark */
            /* 
            header, nav, [class*="header"], [class*="navbar"] {
                background: #0f172a !important;
                border: none !important;
            }
            */
            
            /* Ensure body uses Inter font */
            body {
                font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
            }
            
            /* Hide broken images that show "null" - more aggressive */
            img[src="null"], 
            img[src="undefined"], 
            img[src=""], 
            img:not([src]),
            img[src^="data:null"],
            img[alt="null"],
            img[alt="undefined"] {
                display: none !important;
                visibility: hidden !important;
                width: 0 !important;
                height: 0 !important;
            }
            
            /* Hide broken image icons */
            img:-moz-broken,
            img:-moz-loading {
                visibility: hidden !important;
            }
            
            /* Hide any element that only contains "null" text */
            *:empty {
                /* Keep empty elements but ensure they don't show null */
            }
        `;

        // Filter functions
        function applyFilter(key, value) {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
            url.searchParams.delete('page'); // Reset pagination
            window.location.href = url.toString();
        }

        // Player Modal functions
        function openPlayerModal() {
            document.getElementById('playerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePlayerModal() {
            document.getElementById('playerModal').classList.add('hidden');
            document.body.style.overflow = '';
            // Clear player container
            document.getElementById('playerContainer').innerHTML = `
                <div class="text-center">
                    <svg class="animate-spin h-10 w-10 text-indigo-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-slate-400">Loading session data...</p>
                </div>
            `;
        }

        // Inject styles into iframe with retry logic
        function injectStylesIntoIframe(iframe, retryCount = 0) {
            const maxRetries = 10;
            
            try {
                if (!iframe.contentDocument || !iframe.contentDocument.head) {
                    if (retryCount < maxRetries) {
                        setTimeout(() => injectStylesIntoIframe(iframe, retryCount + 1), 200);
                    }
                    return;
                }
                
                const head = iframe.contentDocument.head;
                
                // Check if already injected
                if (head.querySelector('[data-rrweb-injected]')) {
                    return;
                }
                
                // Inject stylesheet links
                INJECT_STYLESHEETS.forEach(href => {
                    const link = iframe.contentDocument.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = href;
                    link.crossOrigin = 'anonymous';
                    link.setAttribute('data-rrweb-injected', 'true');
                    head.appendChild(link);
                });
                
                // Inject inline CSS rules (these apply immediately)
                const style = iframe.contentDocument.createElement('style');
                style.textContent = INJECT_CSS_RULES;
                style.setAttribute('data-rrweb-injected', 'true');
                head.appendChild(style);
                
                // AGGRESSIVE FIX: Remove text nodes that contain "null"
                cleanupNullText(iframe.contentDocument.body);
                
                // Set up MutationObserver to clean null text as rrweb updates the DOM
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach(mutation => {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                cleanupNullText(node);
                            } else if (node.nodeType === Node.TEXT_NODE && isNullText(node.textContent)) {
                                node.textContent = '';
                            }
                        });
                    });
                });
                
                observer.observe(iframe.contentDocument.body, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
                
                console.log('[Player] Styles injected into replay iframe');
                
            } catch (e) {
                // Cross-origin error or iframe not ready
                if (retryCount < maxRetries) {
                    setTimeout(() => injectStylesIntoIframe(iframe, retryCount + 1), 200);
                }
            }
        }
        
        // Check if text is just "null" repeated (from broken JS rendering)
        function isNullText(text) {
            if (!text) return false;
            const cleaned = text.trim().toLowerCase();
            // Match patterns like "null", "nullnull", "nullnullnull", etc.
            return /^(null\s*)+$/.test(cleaned) || cleaned === 'null';
        }
        
        // Remove text nodes containing only "null" from the DOM
        function cleanupNullText(element) {
            if (!element) return;
            
            const walker = document.createTreeWalker(
                element,
                NodeFilter.SHOW_TEXT,
                null,
                false
            );
            
            const nodesToClean = [];
            let node;
            while (node = walker.nextNode()) {
                if (isNullText(node.textContent)) {
                    nodesToClean.push(node);
                }
            }
            
            nodesToClean.forEach(node => {
                node.textContent = '';
            });
            
            if (nodesToClean.length > 0) {
                console.log('[Player] Cleaned', nodesToClean.length, '"null" text nodes');
            }
            
            // Also hide broken images
            const images = element.querySelectorAll('img');
            images.forEach(img => {
                const src = img.getAttribute('src');
                const alt = img.getAttribute('alt');
                if (!src || src === 'null' || src === 'undefined' || src === '' ||
                    alt === 'null' || alt === 'undefined') {
                    img.style.display = 'none';
                }
            });
        }

        async function playRecording(id) {
            openPlayerModal();
            
            try {
                const response = await fetch(`/dashboard/recordings/${id}/events`);
                if (!response.ok) throw new Error('Failed to load recording');
                
                const data = await response.json();
                
                // Update modal header
                document.getElementById('playerTitle').textContent = `Session Replay - ${data.landing_name}`;
                document.getElementById('playerMeta').textContent = `${data.created_at} • Duration: ${Math.floor(data.duration / 60)}m ${data.duration % 60}s`;
                
                // Clear container and initialize player
                const container = document.getElementById('playerContainer');
                container.innerHTML = '';
                
                if (!data.events || data.events.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-slate-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-slate-400">No events recorded for this session</p>
                        </div>
                    `;
                    return;
                }
                
                // Initialize rrweb player with enhanced configuration
                const player = new rrwebPlayer({
                    target: container,
                    props: {
                        events: data.events,
                        width: 900,
                        height: 500,
                        autoPlay: true,
                        showController: true,
                        speedOption: [1, 2, 4, 8],
                        // Inject CSS rules directly into rrweb's rendering
                        insertStyleRules: [INJECT_CSS_RULES],
                        // Skip inactive periods for faster replay
                        skipInactive: true,
                        // Show warning for incomplete sessions
                        showWarning: true,
                        // Mouse tail for better tracking visualization
                        mouseTail: {
                            duration: 500,
                            strokeStyle: '#6366f1',
                            lineWidth: 3,
                        },
                    },
                });

                // Inject stylesheets with retry logic
                setTimeout(() => {
                    const iframe = container.querySelector('iframe');
                    if (iframe) {
                        injectStylesIntoIframe(iframe);
                    }
                }, 100);
                
                
            } catch (error) {
                console.error('Error loading recording:', error);
                document.getElementById('playerContainer').innerHTML = `
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-slate-400">Error loading recording. Please try again.</p>
                    </div>
                `;
            }
        }

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closePlayerModal();
            }
        });
    </script>
</x-app-layout>
