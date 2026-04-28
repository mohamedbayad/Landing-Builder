<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
            {{ __('AI Landing Page Studio') }}
        </h2>
    </x-slot>

    <div class="py-4 h-full flex flex-col">
        <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 h-[calc(100vh-140px)] w-full flex gap-6">

            <!-- Left Panel: Input Configuration -->
            <div class="w-1/3 bg-white dark:bg-[#161B22] rounded-xl shadow-sm border border-gray-100 dark:border-white/[0.06] flex flex-col overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Product Details</h3>
                </div>
                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                    <form id="ai-generator-form" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Name <span class="text-red-500">*</span></label>
                            <input type="text" name="product_name" id="product_name" required
                                class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange"
                                placeholder="e.g. Alcedo Blood Pressure Monitor">
                            <p id="product_name_error" class="hidden text-xs text-red-500 mt-1">Product name is required</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Image <span class="text-xs text-gray-500">(Optional)</span></label>
                            <input type="file" name="product_image" id="product_image" accept="image/*"
                                class="block w-full text-sm text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10 rounded-lg cursor-pointer bg-gray-50 dark:bg-[#0D1117] focus:outline-none px-3 py-2">
                            <p id="product_image_error" class="hidden text-xs text-red-500 mt-1">Invalid product image file</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Description <span class="text-xs text-gray-500">(Optional)</span></label>
                            <textarea name="description" id="description" rows="4"
                                class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange"
                                placeholder="Describe the main features and benefits..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Audience <span class="text-xs text-gray-500">(Optional)</span></label>
                            <input type="text" name="audience" id="audience"
                                class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange"
                                placeholder="e.g. Seniors, Health-conscious individuals">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Offer / Price <span class="text-xs text-gray-500">(Optional)</span></label>
                            <input type="text" name="offer" id="offer"
                                class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange"
                                placeholder="e.g. $49.99 (50% Off)">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Call to Action (CTA) <span class="text-xs text-gray-500">(Optional)</span></label>
                            <input type="text" name="cta" id="cta"
                                class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange"
                                placeholder="e.g. Get Yours Now">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Language</label>
                            <select name="language" id="language"
                                class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                                <option value="English">English</option>
                                <option value="Spanish">Spanish</option>
                                <option value="French">French</option>
                            </select>
                        </div>
                    </form>
                </div>
                <!-- Bottom Action -->
                <div class="px-6 py-4 border-t border-gray-100 dark:border-white/[0.06] bg-gray-50 dark:bg-white/[0.02]">
                    <button type="button" id="btn-generate"
                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-brand-orange text-white rounded-lg text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-orange/20">
                        <svg id="spinner" class="animate-spin h-4 w-4 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Generate Landing Page
                    </button>
                    <button type="button" id="btn-publish"
                        class="mt-3 w-full hidden inline-flex justify-center items-center gap-2 px-4 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-white/8 transition-all">
                        Publish to Builder
                    </button>
                </div>
            </div>

            <!-- Right Panel: Preview -->
            <div class="w-2/3 bg-gray-50 dark:bg-[#0D1117] rounded-xl shadow-sm border border-gray-100 dark:border-white/[0.06] flex flex-col overflow-hidden relative">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06] bg-white dark:bg-[#161B22] flex justify-between items-center">
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Live Preview</span>
                    <span id="preview-status" class="text-xs text-gray-500 dark:text-gray-400">Awaiting input...</span>
                </div>

                <!-- ░░░ PROGRESS BAR ZONE ░░░ -->
                <div id="progress-zone" class="hidden px-0 bg-white dark:bg-[#161B22] border-b border-gray-100 dark:border-white/[0.06]">
                    <!-- Track -->
                    <div class="w-full h-1.5 bg-gray-200 dark:bg-white/[0.06]">
                        <div id="progress-bar"
                             class="h-full rounded-r-full bg-gradient-to-r from-brand-orange to-orange-400 transition-all duration-700 ease-out"
                             style="width: 0%">
                        </div>
                    </div>
                    <!-- Step label + % -->
                    <div class="flex items-center justify-between px-6 py-2">
                        <span id="progress-step" class="text-xs font-medium text-brand-orange flex items-center gap-1.5">
                            <svg class="animate-spin w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Initializing...
                        </span>
                        <span id="progress-pct" class="text-xs font-bold text-gray-600 dark:text-gray-400">0%</span>
                    </div>
                </div>
                <!-- ░░░ END PROGRESS BAR ZONE ░░░ -->

                <div class="flex-1 overflow-hidden relative bg-white" id="preview-wrapper">
                    <!-- Skeleton Loader (shown while pending/processing) -->
                    <div id="skeleton-overlay" class="hidden absolute inset-0 z-20 bg-white dark:bg-[#0D1117] p-8 overflow-hidden">
                        <div class="space-y-5 animate-pulse">
                            <div class="h-10 bg-gray-200 dark:bg-white/[0.06] rounded-xl w-3/4 mx-auto"></div>
                            <div class="h-6 bg-gray-100 dark:bg-white/[0.04] rounded-lg w-1/2 mx-auto"></div>
                            <div class="h-56 bg-gray-200 dark:bg-white/[0.06] rounded-2xl w-full mt-4"></div>
                            <div class="grid grid-cols-3 gap-4 mt-2">
                                <div class="h-24 bg-gray-100 dark:bg-white/[0.04] rounded-xl"></div>
                                <div class="h-24 bg-gray-100 dark:bg-white/[0.04] rounded-xl"></div>
                                <div class="h-24 bg-gray-100 dark:bg-white/[0.04] rounded-xl"></div>
                            </div>
                            <div class="h-8 bg-gray-200 dark:bg-white/[0.06] rounded-xl w-1/3 mx-auto mt-4"></div>
                            <div class="space-y-3 mt-2">
                                <div class="h-3 bg-gray-100 dark:bg-white/[0.04] rounded w-full"></div>
                                <div class="h-3 bg-gray-100 dark:bg-white/[0.04] rounded w-5/6"></div>
                                <div class="h-3 bg-gray-100 dark:bg-white/[0.04] rounded w-4/6"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Banner -->
                    <div id="error-banner" class="hidden absolute inset-0 z-20 flex items-center justify-center bg-white dark:bg-[#0D1117]">
                        <div class="text-center max-w-sm">
                            <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Generation Failed</h3>
                            <p id="error-message" class="text-sm text-gray-500 dark:text-gray-400"></p>
                            <button onclick="location.reload()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-brand-orange text-white rounded-lg text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">Try Again</button>
                        </div>
                    </div>

                    <!-- The generated iframe will go here to isolate CSS -->
                    <iframe id="preview-frame" class="w-full h-full border-0 hidden" sandbox="allow-same-origin allow-scripts"></iframe>

                    <!-- Granular Element Floating Menu -->
                    <div id="element-menu" class="absolute hidden bg-white dark:bg-[#161B22] shadow-xl border border-gray-100 dark:border-white/[0.06] rounded-xl p-1 z-50 flex flex-col gap-1 min-w-[140px] animate-in fade-in zoom-in duration-200">
                        <button id="btn-regen-element" class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-orange-50 dark:hover:bg-orange-500/10 rounded-lg transition-colors group">
                            <svg class="w-4 h-4 text-brand-orange group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Regenerate
                        </button>
                        <button id="btn-comment-element" class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 rounded-lg transition-colors group">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            Add Comment
                        </button>
                    </div>

                    <!-- Comment Dialog -->
                    <div id="comment-dialog" class="absolute hidden bg-white dark:bg-[#161B22] shadow-2xl border border-gray-100 dark:border-white/[0.06] rounded-xl p-4 z-50 w-72 animate-in fade-in slide-in-from-bottom-2 duration-300">
                        <label class="block text-xs font-semibold text-brand-orange uppercase tracking-wider mb-2">Instruction for AI</label>
                        <textarea id="element-comment" rows="3" class="w-full text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0D1117] dark:text-gray-200 focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange mb-3" placeholder="e.g. Make this more emotional and personal..."></textarea>
                        <div class="flex justify-end gap-2">
                            <button id="btn-cancel-comment" class="px-3 py-1 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Cancel</button>
                            <button id="btn-submit-comment" class="px-4 py-1.5 text-xs bg-brand-orange text-white rounded-lg hover:bg-brand-orange-600 font-semibold transition-all">Apply &amp; Regenerate</button>
                        </div>
                    </div>

                    <!-- Overlay for regeneration loader -->
                    <div id="regen-overlay" class="absolute inset-0 bg-white/70 dark:bg-[#0D1117]/70 backdrop-blur-sm hidden flex-col items-center justify-center z-40">
                        <svg class="animate-spin h-8 w-8 text-brand-orange mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200" id="regen-text">Regenerating section...</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #475569; }

        /* Highlight for selected elements in preview */
        .selected-element-highlight {
            outline: 3px solid #F97316 !important;
            outline-offset: 4px !important;
            border-radius: 4px !important;
            cursor: pointer !important;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .animate-in { animation: fadeIn 0.2s ease-out; }

        /* Progress bar shimmer */
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        #progress-bar {
            background-size: 800px 100%;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnGenerate = document.getElementById('btn-generate');
            const btnPublish = document.getElementById('btn-publish');
            const form = document.getElementById('ai-generator-form');
            const spinner = document.getElementById('spinner');
            const previewStatus = document.getElementById('preview-status');
            const previewFrame = document.getElementById('preview-frame');
            const regenOverlay = document.getElementById('regen-overlay');
            const regenText = document.getElementById('regen-text');

            const elementMenu = document.getElementById('element-menu');
            const commentDialog = document.getElementById('comment-dialog');
            const btnRegenElement = document.getElementById('btn-regen-element');
            const btnCommentElement = document.getElementById('btn-comment-element');
            const btnSubmitComment = document.getElementById('btn-submit-comment');
            const btnCancelComment = document.getElementById('btn-cancel-comment');
            const elementCommentText = document.getElementById('element-comment');

            // Selection State
            let selectedElementData = null;
            let currentLandingData = null;

            // Make the button constantly enabled to prevent JS blocking issues
            btnGenerate.disabled = false;

            btnGenerate.addEventListener('click', async function(e) {
                e.preventDefault();
                let isValid = true;

                if (document.getElementById('product_name').value.trim() === '') isValid = false;
                if(!isValid) return;

                btnGenerate.disabled = true;
                spinner.classList.remove('hidden');
                previewStatus.textContent = 'Contacting AI services...';

                // Display Progress UI immediately
                document.getElementById('progress-zone').classList.remove('hidden');
                document.getElementById('skeleton-overlay').classList.remove('hidden');
                document.getElementById('preview-frame').classList.add('hidden');
                document.getElementById('error-banner').classList.add('hidden');

                const formData = new FormData(form);

                try {
                    const response = await fetch('/ai-generator/generate', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if(!response.ok) {
                        const errData = await response.json();
                        throw new Error(errData.message || 'Generation failed');
                    }

                    const initData = await response.json();
                    const taskId = initData.task_id;

                    // Start Polling
                    pollGenerationStatus(taskId);

                } catch (error) {
                    console.error('Error generating:', error);
                    previewStatus.textContent = 'Error during generation';
                    alert('Generation Error: ' + error.message);

                    document.getElementById('progress-zone').classList.add('hidden');
                    document.getElementById('skeleton-overlay').classList.add('hidden');

                    btnGenerate.disabled = false;
                    spinner.classList.add('hidden');
                }
            });

            async function pollGenerationStatus(taskId) {
                const maxAttempts = 150; // 5 minutes at 2s intervals
                let attempts = 0;

                // Step label map
                const stepLabel = (pct) => {
                    if (pct <= 5)  return 'Initializing...';
                    if (pct <= 20) return 'Analyzing product details...';
                    if (pct <= 40) return 'Running product research...';
                    if (pct <= 55) return 'Building page context...';
                    if (pct <= 75) return 'Generating landing page HTML...';
                    if (pct <= 90) return 'Processing assets & images...';
                    if (pct < 100) return 'Finalizing...';
                    return 'Done!';
                };

                const updateProgress = (pct) => {
                    const bar = document.getElementById('progress-bar');
                    const step = document.getElementById('progress-step');
                    const pctEl = document.getElementById('progress-pct');
                    if (bar)  bar.style.width = pct + '%';
                    if (step) step.textContent = stepLabel(pct);
                    if (pctEl) pctEl.textContent = pct + '%';
                };

                const interval = setInterval(async () => {
                    attempts++;

                    try {
                        const response = await fetch(`/api/ai/generation-status/${taskId}`);
                        const data = await response.json();
                        const pct = data.progress || 0;

                        if (data.status === 'pending') {
                            previewStatus.textContent = 'Queued - waiting for worker...';
                            updateProgress(pct || 2);

                        } else if (data.status === 'processing') {
                            previewStatus.textContent = `Generating... ${pct}%`;
                            updateProgress(pct);

                        } else if (data.status === 'completed') {
                            clearInterval(interval);

                            // Animate to 100%
                            updateProgress(100);
                            previewStatus.textContent = 'Generation complete';

                            setTimeout(() => {
                                // Hide progress UI
                                document.getElementById('progress-zone').classList.add('hidden');
                                document.getElementById('skeleton-overlay').classList.add('hidden');

                                // Show iframe
                                const frame = document.getElementById('preview-frame');
                                frame.classList.remove('hidden');

                                // Render
                                currentLandingData = data.result.data;
                                renderPreview(currentLandingData);

                                btnPublish.classList.remove('hidden');
                                btnGenerate.disabled = false;
                                spinner.classList.add('hidden');
                            }, 600); // Short delay so user sees 100%

                        } else if (data.status === 'failed') {
                            clearInterval(interval);

                            document.getElementById('progress-zone').classList.add('hidden');
                            document.getElementById('skeleton-overlay').classList.add('hidden');

                            const errBanner = document.getElementById('error-banner');
                            const errMsg    = document.getElementById('error-message');
                            errMsg.textContent = data.error_message || data.error || 'Unknown AI failure.';
                            errBanner.classList.remove('hidden');

                            previewStatus.textContent = 'Generation failed';
                            btnGenerate.disabled = false;
                            spinner.classList.add('hidden');
                        }

                        if (attempts >= maxAttempts) {
                            clearInterval(interval);
                            previewStatus.textContent = 'Timed out - queue worker may be offline';
                            document.getElementById('progress-zone').classList.add('hidden');
                            document.getElementById('skeleton-overlay').classList.add('hidden');
                            const errBanner = document.getElementById('error-banner');
                            const errMsg = document.getElementById('error-message');
                            errMsg.textContent = 'Generation stayed queued too long. Start queue worker with: php artisan queue:work';
                            errBanner.classList.remove('hidden');
                            btnGenerate.disabled = false;
                            spinner.classList.add('hidden');
                        }

                    } catch (error) {
                        console.error('Polling error:', error);
                    }
                }, 2000); // ← poll every 2 seconds
            }

            btnPublish.addEventListener('click', async function() {
                if (!currentLandingData || !Array.isArray(currentLandingData.sections) || currentLandingData.sections.length === 0) {
                    alert('Nothing to publish yet. Please generate a landing page first.');
                    return;
                }

                btnPublish.disabled = true;
                btnPublish.textContent = 'Publishing...';

                try {
                    const response = await fetch('/ai-generator/publish', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: document.getElementById('product_name').value?.trim() || null,
                            status: 'published',
                            result: currentLandingData
                        })
                    });

                    const data = await response.json();
                    if (!response.ok || data.status !== 'success') {
                        throw new Error(data.message || 'Publish failed');
                    }

                    previewStatus.textContent = 'Published to My Landings';
                    alert('Published successfully. You can now edit it in the builder.');
                    window.location.href = data.edit_url || data.landings_url || '/landings';
                } catch (error) {
                    alert('Publish Error: ' + error.message);
                    btnPublish.disabled = false;
                    btnPublish.textContent = 'Publish to Builder';
                }
            });

            function renderPreview(data) {
                console.log('--- RENDERING PREVIEW ---');
                let sectionsHtml = '';
                let globalCss = '';
                let globalJs = '';
                let globalHead = '';

                let parsedSections = data.sections || [];

                if (parsedSections.length > 0) {
                    parsedSections.forEach((sec, index) => {
                        let htmlContent = sec.html_content || sec.html_css || sec.html || "";
                        let secId = sec.id || `section_${index}`;

                        // Inject granular editability metadata into the HTML content
                        // We wrap headings, buttons, and images with data attributes
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = htmlContent;

                        // 1. Tag Headings
                        tempDiv.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach((el, i) => {
                            el.setAttribute('data-editable', 'true');
                            el.setAttribute('data-element-id', `${secId}_h${i}`);
                            el.setAttribute('data-element-type', 'heading');
                        });

                        // 2. Tag Buttons
                        tempDiv.querySelectorAll('button, a.btn, .button, a.inline-flex, a.bg-indigo-600').forEach((el, i) => {
                            el.setAttribute('data-editable', 'true');
                            el.setAttribute('data-element-id', `${secId}_btn${i}`);
                            el.setAttribute('data-element-type', 'button');
                        });

                        // 3. Tag Images
                        tempDiv.querySelectorAll('img, svg').forEach((el, i) => {
                            el.setAttribute('data-editable', 'true');
                            el.setAttribute('data-element-id', `${secId}_img${i}`);
                            el.setAttribute('data-element-type', 'image');
                        });

                        // 4. Tag Paragraphs and Generic Blocks
                        tempDiv.querySelectorAll('p, li, span.badge, .badge, .feature-item, .benefit-card, blockquote').forEach((el, i) => {
                            el.setAttribute('data-editable', 'true');
                            el.setAttribute('data-element-id', `${secId}_txt${i}`);
                            el.setAttribute('data-element-type', 'text_block');
                        });

                        htmlContent = tempDiv.innerHTML;

                        // Extract CSS/JS
                        const content = sec.html_content || sec.html_css || sec;
                        if (content.css) globalCss += content.css + "\n";
                        if (content.js) globalJs += content.js + "\n";
                        if (content.custom_head) globalHead += content.custom_head + "\n";

                        sectionsHtml += `
                            <div class="ai-section hover:ring-2 hover:ring-orange-400 hover:ring-inset relative group transition-all duration-200" data-id="${secId}">
                                ${htmlContent}
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                    <button onclick="window.parent.regenerateSection('${secId}')" class="bg-brand-orange/90 backdrop-blur-sm text-white px-3 py-1.5 rounded-lg text-xs shadow-lg hover:bg-brand-orange flex items-center gap-1.5 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        Section
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                }

                let seoHtml = '';
                if (data.seo) {
                    const title = data.seo.title ? data.seo.title.replace(/"/g, '&quot;') : 'Landing Page';
                    const description = data.seo.description ? data.seo.description.replace(/"/g, '&quot;') : '';
                    seoHtml = `
                        <title>${title}</title>
                        <meta name="description" content="${description}">
                        ${data.seo.schema || ''}
                    `;
                }

                const doc = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="UTF-8">
                        ${seoHtml}
                        <script src="https://cdn.tailwindcss.com"><\/script>
                        ${globalHead}
                        <style>
                            body { margin: 0; padding: 0; }
                            [data-editable="true"]:hover {
                                outline: 2px dashed #F97316 !important;
                                outline-offset: 2px !important;
                                cursor: cell !important;
                            }
                            .selected-element {
                                outline: 3px solid #F97316 !important;
                                outline-offset: 4px !important;
                                border-radius: 4px !important;
                            }
                            ${globalCss}
                        </style>
                    </head>
                    <body>
                        ${sectionsHtml}
                        <script>
                            ${globalJs}

                            // Handle Granular Selection
                            document.body.addEventListener('click', function(e) {
                                const editable = e.target.closest('[data-editable="true"]');
                                if (editable) {
                                    e.preventDefault();
                                    e.stopPropagation();

                                    // Remove previous highlights
                                    document.querySelectorAll('.selected-element').forEach(el => el.classList.remove('selected-element'));

                                    // Highlight new
                                    editable.classList.add('selected-element');

                                    // Get element rect for positioning
                                    const rect = editable.getBoundingClientRect();

                                    // Send to parent
                                    window.parent.onElementSelected({
                                        id: editable.getAttribute('data-element-id'),
                                        type: editable.getAttribute('data-element-type'),
                                        rect: rect
                                    });
                                } else {
                                    window.parent.onElementDeselected();
                                }
                            });
                        <\/script>
                    </body>
                    </html>
                `;

                previewFrame.srcdoc = doc;
            }

            // --- Selection UI Logic ---

            window.onElementSelected = function(data) {
                selectedElementData = data;

                // Position the floating menu. The elementMenu is position: absolute inside the relative preview-wrapper.
                // data.rect gives coords relative to the iframe's visible viewport.
                // We do not need the frame's bounding rect, just the data.rect.
                const topPos = data.rect.top > 50 ? data.rect.top - 50 : 10;
                const leftPos = Math.max(10, data.rect.left);

                elementMenu.style.top = `${topPos}px`;
                elementMenu.style.left = `${leftPos}px`;

                elementMenu.classList.remove('hidden');
                commentDialog.classList.add('hidden');
            };

            window.onElementDeselected = function() {
                elementMenu.classList.add('hidden');
                commentDialog.classList.add('hidden');
            };

            btnRegenElement.addEventListener('click', async function() {
                if (!selectedElementData) return;
                await triggerElementRegeneration();
            });

            btnCommentElement.addEventListener('click', function() {
                elementMenu.classList.add('hidden');
                commentDialog.style.top = elementMenu.style.top;
                commentDialog.style.left = elementMenu.style.left;
                commentDialog.classList.remove('hidden');
                elementCommentText.focus();
            });

            btnCancelComment.addEventListener('click', () => commentDialog.classList.add('hidden'));

            btnSubmitComment.addEventListener('click', async function() {
                const comment = elementCommentText.value.trim();
                await triggerElementRegeneration(comment);
                commentDialog.classList.add('hidden');
                elementCommentText.value = '';
            });

            async function triggerElementRegeneration(comment = null) {
                regenOverlay.classList.remove('hidden');
                regenOverlay.classList.add('flex');
                regenText.textContent = `Regenerating ${selectedElementData.type}...`;

                try {
                    const response = await fetch('/ai-generator/regenerate-element', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            element_id: selectedElementData.id,
                            type: selectedElementData.type,
                            comment: comment,
                            context: currentLandingData,
                            product_name: document.getElementById('product_name').value
                        })
                    });

                    if(!response.ok) throw new Error('Regeneration failed');
                    const res = await response.json();

                    alert('Element updated successfully! Applying change...');

                    const sectionId = selectedElementData.id.split('_')[0];
                    const sectionIndex = currentLandingData.sections.findIndex(s => s.id === sectionId);
                    if (sectionIndex > -1) {
                        currentLandingData.sections[sectionIndex].html = res.data.html || currentLandingData.sections[sectionIndex].html;
                        renderPreview(currentLandingData);
                    }
                } catch (err) {
                    alert('Regeneration Error: ' + err.message);
                } finally {
                    regenOverlay.classList.remove('flex');
                    regenOverlay.classList.add('hidden');
                    elementMenu.classList.add('hidden');
                }
            }

            // Expose function for section regeneration
            window.regenerateSection = async function(sectionId) {
                regenOverlay.classList.remove('hidden');
                regenOverlay.classList.add('flex');
                regenText.textContent = `Regenerating ${sectionId} section...`;

                try {
                    const response = await fetch('/ai-generator/regenerate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            section: sectionId,
                            context: currentLandingData,
                            product_name: document.getElementById('product_name').value
                        })
                    });

                    if(!response.ok) throw new Error('Regeneration failed');
                    const responseData = await response.json();

                    const sectionIndex = currentLandingData.sections.findIndex(s => s.id === sectionId);
                    if(sectionIndex > -1) {
                        currentLandingData.sections[sectionIndex] = responseData.data;
                        renderPreview(currentLandingData);
                    }
                } catch (error) {
                    alert('Regeneration Error: ' + error.message);
                } finally {
                    regenOverlay.classList.remove('flex');
                    regenOverlay.classList.add('hidden');
                }
            };
        });
    </script>
</x-app-layout>

