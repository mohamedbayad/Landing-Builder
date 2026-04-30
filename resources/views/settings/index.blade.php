<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
            {{ __('Global Settings') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: '{{ session('activeTab', 'theme') }}' }" data-settings-tabs data-initial-tab="{{ session('activeTab', 'theme') }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

            <!-- Tabs Navigation -->
            <div class="flex space-x-1 rounded-xl bg-gray-100 dark:bg-white/[0.06] p-1 mb-8 overflow-x-auto">
                <button @click="tab = 'theme'" data-settings-tab-btn="theme" :class="{ 'bg-white dark:bg-[#161B22] shadow text-gray-900 dark:text-white': tab === 'theme', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200': tab !== 'theme' }" class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all">
                    Theme & Dashboard
                </button>
                <button @click="tab = 'payment'" data-settings-tab-btn="payment" :class="{ 'bg-white dark:bg-[#161B22] shadow text-gray-900 dark:text-white': tab === 'payment', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200': tab !== 'payment' }" class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all">
                    Payment & Currency
                </button>
                <button @click="tab = 'whatsapp'" data-settings-tab-btn="whatsapp" :class="{ 'bg-white dark:bg-[#161B22] shadow text-gray-900 dark:text-white': tab === 'whatsapp', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200': tab !== 'whatsapp' }" class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all">
                    WhatsApp Automation
                </button>
                <button @click="tab = 'ai_settings'" data-settings-tab-btn="ai_settings" :class="{ 'bg-white dark:bg-[#161B22] shadow text-gray-900 dark:text-white': tab === 'ai_settings', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200': tab !== 'ai_settings' }" class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all">
                    AI Settings
                </button>
            </div>

            <!-- Content -->
            <div class="bg-white dark:bg-[#161B22] overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-white/[0.06]">
                <div class="p-8">

                    <!-- SUCCESS MESSAGE -->
                    @if (session('status') === 'settings-updated')
                        <div
                            x-data="{ show: true }"
                            x-show="show"
                            x-transition
                            x-init="setTimeout(() => show = false, 3000)"
                            class="mb-6 p-4 rounded-lg bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 flex items-center"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            {{ __('Settings Saved Successfully') }}
                        </div>
                    @endif

                    <!-- TAB: THEME -->
                    <div data-settings-tab-panel="theme" x-show="tab === 'theme'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Theme Configuration</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Customize dashboard appearance and public page styles.</p>
                        </div>

                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="dashboard_direction" value="{{ $workspace->settings->dashboard_direction ?? 'ltr' }}">

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                                <!-- Dashboard Settings -->
                                <div class="space-y-6">
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-white/[0.06] pb-2">Dashboard UI</h4>

                                    <div>
                                        <label for="dashboard_direction" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Layout Direction</label>
                                        <select name="dashboard_direction" id="dashboard_direction" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                            <option value="ltr" {{ ($workspace->settings->dashboard_direction ?? 'ltr') == 'ltr' ? 'selected' : '' }}>LTR (Left to Right)</option>
                                            <option value="rtl" {{ ($workspace->settings->dashboard_direction ?? 'ltr') == 'rtl' ? 'selected' : '' }}>RTL (Right to Left)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="workspace_public_endpoint" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Workspace Public Endpoint</label>
                                        <input
                                            type="text"
                                            name="workspace_public_endpoint"
                                            id="workspace_public_endpoint"
                                            value="{{ old('workspace_public_endpoint', $workspace->settings->workspace_public_endpoint ?? '') }}"
                                            placeholder="my-workspace"
                                            class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20"
                                        >
                                        @error('workspace_public_endpoint')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Public base URL: <code>/w/{{ old('workspace_public_endpoint', $workspace->settings->workspace_public_endpoint ?? 'my-workspace') }}</code>
                                        </p>
                                    </div>

                                    <div>
                                        <label for="dashboard_primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Color</label>
                                        <div class="mt-1 flex items-center space-x-2">
                                            <input type="color" name="dashboard_primary_color" id="dashboard_primary_color" value="{{ $workspace->settings->dashboard_primary_color ?? '#F97316' }}" class="h-10 w-20 rounded p-1 border border-gray-300 dark:border-white/[0.06]">
                                            <span class="text-xs text-gray-500">Main button & highlight color</span>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="sidebar_bg" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sidebar Background</label>
                                        <input type="color" name="sidebar_bg" value="{{ $workspace->settings->sidebar_bg ?? '#ffffff' }}" class="mt-1 h-10 w-20 rounded p-1 border border-gray-300 dark:border-white/[0.06]">
                                    </div>
                                </div>

                                <!-- Landing Page Styles -->
                                <div class="space-y-6">
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-white/[0.06] pb-2">Public Pages Style</h4>

                                    <div x-data="{ checkoutLayout: '{{ $workspace->settings->checkout_style ?? 'layout_1' }}' }">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Checkout Layout Preset</label>
                                        <input type="hidden" name="checkout_style" :value="checkoutLayout">

                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                                            <!-- Layout 1: Sidebar Left + Content Right -->
                                            <label @click="checkoutLayout = 'layout_1'"
                                                   :class="checkoutLayout === 'layout_1' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-2 mb-3 flex gap-1.5">
                                                    <div class="w-1/3 bg-gray-300 dark:bg-white/[0.12] rounded"></div>
                                                    <div class="flex-1 flex flex-col gap-1">
                                                        <div class="h-2 bg-gray-300 dark:bg-white/[0.12] rounded w-3/4"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-white/[0.06] rounded"></div>
                                                        <div class="h-3 bg-orange-400 rounded w-1/2"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Sidebar Left</span>
                                                    <div :class="checkoutLayout === 'layout_1' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_1'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Layout 2: Single Column (Centered) -->
                                            <label @click="checkoutLayout = 'layout_2'"
                                                   :class="checkoutLayout === 'layout_2' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-2 mb-3 flex justify-center">
                                                    <div class="w-2/3 flex flex-col gap-1">
                                                        <div class="h-2 bg-gray-300 dark:bg-white/[0.12] rounded mx-auto w-1/2"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-white/[0.06] rounded"></div>
                                                        <div class="h-3 bg-orange-400 rounded mx-auto w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Centered</span>
                                                    <div :class="checkoutLayout === 'layout_2' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_2'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Layout 3: Split Screen (50/50) -->
                                            <label @click="checkoutLayout = 'layout_3'"
                                                   :class="checkoutLayout === 'layout_3' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-2 mb-3 flex gap-1">
                                                    <div class="flex-1 bg-orange-100 dark:bg-orange-500/10 rounded flex items-center justify-center">
                                                        <div class="w-4 h-4 bg-orange-400 rounded"></div>
                                                    </div>
                                                    <div class="flex-1 flex flex-col gap-1 p-1">
                                                        <div class="h-1.5 bg-gray-300 dark:bg-white/[0.12] rounded w-3/4"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-white/[0.06] rounded"></div>
                                                        <div class="h-2 bg-orange-400 rounded w-full"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Split 50/50</span>
                                                    <div :class="checkoutLayout === 'layout_3' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_3'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Layout 4: Full Width with Header -->
                                            <label @click="checkoutLayout = 'layout_4'"
                                                   :class="checkoutLayout === 'layout_4' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-1.5 mb-3 flex flex-col gap-1">
                                                    <div class="h-3 bg-orange-200 dark:bg-orange-500/20 rounded"></div>
                                                    <div class="flex-1 flex flex-col gap-1 px-2">
                                                        <div class="h-1.5 bg-gray-300 dark:bg-white/[0.12] rounded w-1/2 mx-auto"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-white/[0.06] rounded"></div>
                                                        <div class="h-2 bg-orange-400 rounded w-1/3 mx-auto"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Full Width</span>
                                                    <div :class="checkoutLayout === 'layout_4' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_4'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Layout 5: Minimalist (Content Only) -->
                                            <label @click="checkoutLayout = 'layout_5'"
                                                   :class="checkoutLayout === 'layout_5' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-3 mb-3 flex flex-col justify-center gap-1.5">
                                                    <div class="h-1.5 bg-gray-300 dark:bg-white/[0.12] rounded w-1/2 mx-auto"></div>
                                                    <div class="h-6 bg-gray-200 dark:bg-white/[0.06] rounded w-4/5 mx-auto"></div>
                                                    <div class="h-2.5 bg-orange-400 rounded w-1/3 mx-auto"></div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Minimalist</span>
                                                    <div :class="checkoutLayout === 'layout_5' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_5'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div x-data="{ thankyouLayout: '{{ $workspace->settings->thankyou_style ?? 'thankyou_1' }}' }">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Thank You Page Preset</label>
                                        <input type="hidden" name="thankyou_style" :value="thankyouLayout">

                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                                            <!-- Thankyou 1: Confetti Celebration -->
                                            <label @click="thankyouLayout = 'thankyou_1'"
                                                   :class="thankyouLayout === 'thankyou_1' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-2 mb-3 flex flex-col items-center justify-center relative overflow-hidden">
                                                    <div class="absolute top-1 left-2 w-1 h-1 bg-yellow-400 rounded-full"></div>
                                                    <div class="absolute top-2 right-3 w-1.5 h-1.5 bg-pink-400 rounded-full"></div>
                                                    <div class="absolute top-3 left-4 w-1 h-1 bg-green-400 rounded-full"></div>
                                                    <div class="absolute bottom-3 right-2 w-1 h-1 bg-blue-400 rounded-full"></div>
                                                    <div class="absolute bottom-2 left-3 w-1.5 h-1.5 bg-purple-400 rounded-full"></div>
                                                    <div class="w-6 h-6 rounded-full bg-green-400 flex items-center justify-center mb-1">
                                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                    <div class="h-1.5 bg-gray-300 dark:bg-white/[0.12] rounded w-3/4"></div>
                                                    <div class="h-1 bg-gray-200 dark:bg-white/[0.06] rounded w-1/2 mt-1"></div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Confetti</span>
                                                    <div :class="thankyouLayout === 'thankyou_1' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_1'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Thankyou 2: Simple Card -->
                                            <label @click="thankyouLayout = 'thankyou_2'"
                                                   :class="thankyouLayout === 'thankyou_2' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-2 mb-3 flex items-center justify-center">
                                                    <div class="w-4/5 bg-white dark:bg-white/[0.06] rounded-lg p-2 shadow-sm flex flex-col items-center gap-1">
                                                        <div class="w-4 h-4 rounded-full bg-green-400 flex items-center justify-center">
                                                            <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                        <div class="h-1 bg-gray-300 dark:bg-white/[0.12] rounded w-3/4"></div>
                                                        <div class="h-1 bg-gray-200 dark:bg-white/[0.06] rounded w-1/2"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Simple Card</span>
                                                    <div :class="thankyouLayout === 'thankyou_2' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_2'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Thankyou 3: Split with Summary -->
                                            <label @click="thankyouLayout = 'thankyou_3'"
                                                   :class="thankyouLayout === 'thankyou_3' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-1.5 mb-3 flex gap-1">
                                                    <div class="flex-1 bg-green-100 dark:bg-green-900/30 rounded flex flex-col items-center justify-center p-1">
                                                        <div class="w-3 h-3 rounded-full bg-green-400 flex items-center justify-center">
                                                            <svg class="w-1.5 h-1.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                        <div class="h-1 bg-gray-300 dark:bg-white/[0.12] rounded w-3/4 mt-1"></div>
                                                    </div>
                                                    <div class="flex-1 flex flex-col gap-0.5 p-1">
                                                        <div class="h-1 bg-gray-300 dark:bg-white/[0.12] rounded w-full"></div>
                                                        <div class="h-1 bg-gray-200 dark:bg-white/[0.06] rounded w-3/4"></div>
                                                        <div class="h-1 bg-gray-200 dark:bg-white/[0.06] rounded w-full"></div>
                                                        <div class="h-1.5 bg-orange-400 rounded w-2/3 mt-auto"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Split View</span>
                                                    <div :class="thankyouLayout === 'thankyou_3' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_3'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Thankyou 4: Full Width Banner -->
                                            <label @click="thankyouLayout = 'thankyou_4'"
                                                   :class="thankyouLayout === 'thankyou_4' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-1.5 mb-3 flex flex-col gap-1">
                                                    <div class="h-5 bg-green-200 dark:bg-green-800 rounded flex items-center justify-center">
                                                        <div class="w-3 h-3 rounded-full bg-green-500 flex items-center justify-center">
                                                            <svg class="w-1.5 h-1.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 flex flex-col gap-0.5 px-1">
                                                        <div class="h-1 bg-gray-300 dark:bg-white/[0.12] rounded w-1/2 mx-auto"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-white/[0.06] rounded"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Full Banner</span>
                                                    <div :class="thankyouLayout === 'thankyou_4' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_4'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Thankyou 5: Minimalist -->
                                            <label @click="thankyouLayout = 'thankyou_5'"
                                                   :class="thankyouLayout === 'thankyou_5' ? 'ring-2 ring-brand-orange border-brand-orange' : 'border-gray-200 dark:border-white/[0.06] hover:border-gray-300 dark:hover:border-white/[0.12]'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-[#0D1117] p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-white/[0.06] rounded-lg p-3 mb-3 flex flex-col items-center justify-center gap-1">
                                                    <div class="w-5 h-5 rounded-full bg-green-400 flex items-center justify-center">
                                                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                    <div class="h-1.5 bg-gray-300 dark:bg-white/[0.12] rounded w-2/3"></div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Minimalist</span>
                                                    <div :class="thankyouLayout === 'thankyou_5' ? 'bg-brand-orange' : 'bg-gray-200 dark:bg-white/[0.12]'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_5'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Order Summary Table</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="thankyou_show_summary" class="sr-only peer" {{ ($workspace->settings->thankyou_show_summary ?? true) ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-orange/20 dark:peer-focus:ring-brand-orange/30 rounded-full peer dark:bg-white/[0.12] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-white/[0.06] peer-checked:bg-brand-orange"></div>
                                        </label>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Download Invoice Button</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="thankyou_show_invoice_btn" class="sr-only peer" {{ ($workspace->settings->thankyou_show_invoice_btn ?? true) ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-orange/20 dark:peer-focus:ring-brand-orange/30 rounded-full peer dark:bg-white/[0.12] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-white/[0.06] peer-checked:bg-brand-orange"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="px-6 py-2.5 bg-brand-orange rounded-lg text-white text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">Save Theme Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: PAYMENT -->
                    <div data-settings-tab-panel="payment" x-show="tab === 'payment'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Payment Gateways & Currency</h3>
                        </div>

                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Default Currency</label>
                                    <select name="currency" id="currency" class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                        <option value="USD" {{ old('currency', $workspace->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        <option value="EUR" {{ old('currency', $workspace->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                        <option value="GBP" {{ old('currency', $workspace->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                        <option value="MAD" {{ old('currency', $workspace->currency) == 'MAD' ? 'selected' : '' }}>MAD (DH)</option>
                                    </select>
                                </div>

                                <div class="col-span-1 md:col-span-2 space-y-6">
                                    <!-- Stripe -->
                                    <div class="p-5 bg-gray-50 dark:bg-white/[0.02] rounded-xl border border-gray-200 dark:border-white/[0.06]">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Stripe</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm text-gray-600 dark:text-gray-400">Publishable Key</label>
                                                <input type="text" name="stripe_publishable_key" value="{{ $workspace->stripe_publishable_key }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white focus:border-brand-orange focus:ring-brand-orange/20">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 dark:text-gray-400">Secret Key</label>
                                                <input type="password" name="stripe_secret_key" value="{{ $workspace->stripe_secret_key }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white focus:border-brand-orange focus:ring-brand-orange/20">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PayPal -->
                                    <div class="p-5 bg-gray-50 dark:bg-white/[0.02] rounded-xl border border-gray-200 dark:border-white/[0.06]">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4">PayPal</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm text-gray-600 dark:text-gray-400">Client ID</label>
                                                <input type="text" name="paypal_client_id" value="{{ $workspace->paypal_client_id }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white focus:border-brand-orange focus:ring-brand-orange/20">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 dark:text-gray-400">Secret</label>
                                                <input type="password" name="paypal_secret" value="{{ $workspace->paypal_secret }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white focus:border-brand-orange focus:ring-brand-orange/20">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="px-6 py-2.5 bg-brand-orange rounded-lg text-white text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">Save Payment Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: WHATSAPP -->
                    <div data-settings-tab-panel="whatsapp" x-show="tab === 'whatsapp'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="whatsapp_phone_check" value="1">

                            <div class="mb-6 flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">WhatsApp Automation</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure automated WhatsApp redirection and messages.</p>
                                </div>

                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="whatsapp_enabled" class="sr-only peer" {{ ($workspace->settings->whatsapp_enabled ?? false) ? 'checked' : '' }}>
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-white/[0.12] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-white/[0.06] peer-checked:bg-green-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Enable Feature</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Config -->
                                <div class="space-y-6">
                                    <div class="bg-gray-50 dark:bg-white/[0.02] p-5 rounded-xl border border-gray-200 dark:border-white/[0.06]">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Redirection Settings</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default WhatsApp Number</label>
                                                <input type="text" name="whatsapp_phone" placeholder="e.g. 1234567890" value="{{ $workspace->settings->whatsapp_phone ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white focus:border-brand-orange focus:ring-brand-orange/20">
                                                <p class="text-xs text-gray-500 mt-1">Include country code without + (e.g. 15551234567)</p>
                                            </div>

                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Auto Redirect on Thank You Page</span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="whatsapp_redirect_enabled" class="sr-only peer" {{ ($workspace->settings->whatsapp_redirect_enabled ?? false) ? 'checked' : '' }}>
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer dark:bg-white/[0.12] peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                                </label>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Redirect Delay (Seconds)</label>
                                                <input type="number" name="whatsapp_redirect_seconds" min="0" max="60" value="{{ $workspace->settings->whatsapp_redirect_seconds ?? 5 }}" class="mt-1 block w-24 rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white focus:border-brand-orange focus:ring-brand-orange/20">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Template Builder -->
                                <div class="space-y-6">
                                    <div class="bg-gray-50 dark:bg-white/[0.02] p-5 rounded-xl border border-gray-200 dark:border-white/[0.06]">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Message Templates</h4>

                                        @php
                                            $defaultThanks = 'Hello {{ customer-name }}, your order #{{ order-id }} is confirmed. Total: {{ currency }} {{ order-total }}.';
                                            $defaultLanding = 'I want to know more about this offer.';
                                            $whatsappVars = ['{{ customer-name }}', '{{ order-id }}', '{{ order-total }}', '{{ currency }}', '{{ customer-phone }}'];
                                        @endphp

                                        <div class="mb-5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thank You Page Message</label>
                                            <textarea name="whatsapp_template_thankyou" rows="4" class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white focus:border-brand-orange focus:ring-brand-orange/20" placeholder="Hello @{{ customer-name }}, thank you for your order!">{{ old('whatsapp_template_thankyou', $workspace->settings->whatsapp_template_thankyou ?? $defaultThanks) }}</textarea>

                                            <!-- Variable Chips -->
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="text-xs font-semibold text-gray-500 uppercase">Available Variables:</span>
                                                @foreach($whatsappVars as $var)
                                                    <button type="button" onclick="const ta = this.closest('div').previousElementSibling; ta.value += ' {{ $var }}'; ta.focus();" class="px-2 py-1 bg-white dark:bg-white/[0.06] border border-gray-300 dark:border-white/[0.06] rounded text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/[0.10]">{{ $var }}</button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Landing Page Button Message</label>
                                            <textarea name="whatsapp_template_landing" rows="2" class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white focus:border-brand-orange focus:ring-brand-orange/20" placeholder="I have a question about @{{ landing-title }}">{{ old('whatsapp_template_landing', $workspace->settings->whatsapp_template_landing ?? $defaultLanding) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="px-6 py-2.5 bg-green-600 rounded-lg text-white text-sm font-semibold hover:bg-green-700 transition-all shadow-sm">Save WhatsApp Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: AI SETTINGS -->
                    <div data-settings-tab-panel="ai_settings" x-show="tab === 'ai_settings'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                        <div class="mb-6 flex justify-between items-end">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">AI Configuration</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure multiple AI providers and assign specific models to specific tasks.</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-white/[0.02] rounded-xl border border-gray-200 dark:border-white/[0.06] p-6 mb-8"
                             x-data="{
                                ctaType: '{{ old('chatbot_custom_cta_type', $workspace->settings->chatbot_custom_cta_type ?? 'form') }}',
                                targetPlaceholder() {
                                    if (this.ctaType === 'whatsapp') return 'Example: +212600000000';
                                    if (this.ctaType === 'instagram') return 'Example: https://instagram.com/yourbrand';
                                    if (this.ctaType === 'custom_phone') return 'Example: +212600000000';
                                    if (this.ctaType === 'custom_link') return 'Example: https://your-offer-link.com';
                                    return 'No target needed for form CTA';
                                }
                             }">
                            <form method="POST" action="{{ route('settings.update') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="chatbot_cta_settings_check" value="1">

                                <div class="flex items-start justify-between gap-6">
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">Landing Chatbot CTA (Subscriber)</h4>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure CTA behavior shown by chatbot only when visitor is conversion-ready.</p>
                                    </div>

                                    <label class="relative inline-flex items-center cursor-pointer mt-1">
                                        <input type="checkbox" name="chatbot_custom_cta_enabled" class="sr-only peer" {{ ($workspace->settings->chatbot_custom_cta_enabled ?? false) ? 'checked' : '' }}>
                                        <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-orange/20 dark:peer-focus:ring-brand-orange/30 rounded-full peer dark:bg-white/[0.12] peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-white/[0.06] peer-checked:bg-brand-orange"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Enable</span>
                                    </label>
                                </div>

                                <div class="mt-5">
                                    <label for="chatbot_custom_cta_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Custom CTA button text
                                    </label>
                                    <input
                                        type="text"
                                        id="chatbot_custom_cta_text"
                                        name="chatbot_custom_cta_text"
                                        maxlength="120"
                                        value="{{ old('chatbot_custom_cta_text', $workspace->settings->chatbot_custom_cta_text ?? '') }}"
                                        placeholder="Example: Reserve your free strategy call now"
                                        class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20"
                                    >
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        If empty, chatbot uses automatic CTA text for the selected action.
                                    </p>
                                </div>

                                <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label for="chatbot_custom_cta_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            CTA action type
                                        </label>
                                        <select id="chatbot_custom_cta_type" name="chatbot_custom_cta_type" x-model="ctaType" class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                            <option value="form">Form in LP</option>
                                            <option value="whatsapp">WhatsApp</option>
                                            <option value="instagram">Instagram</option>
                                            <option value="custom_link">Custom URL</option>
                                            <option value="custom_phone">Custom Phone</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="chatbot_custom_cta_landing_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Apply CTA on
                                        </label>
                                        <select id="chatbot_custom_cta_landing_id" name="chatbot_custom_cta_landing_id" class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                            <option value="">All landing pages</option>
                                            @foreach(($workspaceLandings ?? collect()) as $landingItem)
                                                <option value="{{ $landingItem->id }}" {{ (string) old('chatbot_custom_cta_landing_id', $workspace->settings->chatbot_custom_cta_landing_id ?? '') === (string) $landingItem->id ? 'selected' : '' }}>
                                                    {{ $landingItem->name }} ({{ $landingItem->slug }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <label for="chatbot_custom_cta_target" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        CTA target (URL or phone number)
                                    </label>
                                    <input
                                        type="text"
                                        id="chatbot_custom_cta_target"
                                        name="chatbot_custom_cta_target"
                                        maxlength="255"
                                        value="{{ old('chatbot_custom_cta_target', $workspace->settings->chatbot_custom_cta_target ?? '') }}"
                                        :placeholder="targetPlaceholder()"
                                        class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20"
                                    >
                                    @error('chatbot_custom_cta_target')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Form type does not need target. WhatsApp/Phone expects a number. Instagram/Custom URL expects a valid link.
                                    </p>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="px-6 py-2.5 bg-brand-orange rounded-lg text-white text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">
                                        Save Chatbot CTA Settings
                                    </button>
                                </div>
                            </form>
                        </div>

                        @php
                            $canManageTech = (bool) (auth()->user()?->hasPermission('tech.manage'));
                        @endphp
                        @if($canManageTech)
                        <!-- Add New Provider Form -->
                        @php
                            $providerOptions = \App\Support\AI\ProviderRegistry::optionsForSettingsForm();
                        @endphp
                        <div class="bg-gray-50 dark:bg-white/[0.02] rounded-xl border border-gray-200 dark:border-white/[0.06] p-6 mb-8"
                             x-data='{
                                openForm: false,
                                provider: "{{ array_key_first($providerOptions) }}",
                                baseUrl: "",
                                providerMeta: @json($providerOptions),
                                syncProvider(nextProvider) {
                                    const previousProvider = this.provider;
                                    const previousSuggested = this.providerMeta?.[previousProvider]?.default_base_url ?? "";
                                    const shouldAutofillNext = Boolean(this.providerMeta?.[nextProvider]?.autofill_base_url);
                                    const nextSuggested = this.providerMeta?.[nextProvider]?.default_base_url ?? "";

                                    this.provider = nextProvider;

                                    if (shouldAutofillNext && (this.baseUrl === "" || this.baseUrl === previousSuggested)) {
                                        this.baseUrl = nextSuggested;
                                    }

                                    if (!shouldAutofillNext && this.baseUrl === previousSuggested) {
                                        this.baseUrl = "";
                                    }
                                },
                                apiKeyPlaceholder() {
                                    return this.providerMeta?.[this.provider]?.api_key_placeholder ?? "sk-...";
                                },
                                baseUrlPlaceholder() {
                                    return this.providerMeta?.[this.provider]?.base_url_placeholder ?? "https://...";
                                },
                                helperNote() {
                                    return this.providerMeta?.[this.provider]?.helper_note ?? "";
                                }
                             }'
                             x-init='syncProvider(provider)'>
                            <button @click="openForm = !openForm" type="button" class="flex items-center text-sm font-semibold text-brand-orange hover:text-brand-orange-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add New AI Provider
                            </button>

                            <div x-show="openForm" style="display: none;" class="mt-6 pt-6 border-t border-gray-200 dark:border-white/[0.06]">
                                <form method="POST" action="{{ route('settings.ai.providers.store') }}">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Internal Name <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" required placeholder="e.g., My Team OpenAI Key" class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Provider Type <span class="text-red-500">*</span></label>
                                            <select name="provider" x-model="provider" @change="syncProvider($event.target.value)" required class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                                @foreach($providerOptions as $providerKey => $providerOption)
                                                    <option value="{{ $providerKey }}">{{ $providerOption['label'] }}</option>
                                                @endforeach
                                            </select>
                                            <p x-show="helperNote()" x-text="helperNote()" class="mt-2 text-xs text-gray-500 dark:text-gray-400"></p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API Key <span x-show="providerMeta?.[provider]?.requires_api_key" class="text-red-500">*</span></label>
                                            <input type="password" name="api_key" :required="Boolean(providerMeta?.[provider]?.requires_api_key)" :placeholder="apiKeyPlaceholder()" class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                            <p x-show="provider === 'openrouter'" class="mt-2 text-xs text-gray-500 dark:text-gray-400">Example: sk-or-v1-...</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom Base URL (Optional)</label>
                                            <input type="url" name="base_url" x-model="baseUrl" :placeholder="baseUrlPlaceholder()" class="block w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.06] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                            <p x-show="provider === 'openrouter'" class="mt-2 text-xs text-gray-500 dark:text-gray-400">Suggested base URL: https://openrouter.ai/api/v1</p>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button type="button" @click="openForm = false" class="mr-3 px-4 py-2 text-sm text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Cancel</button>
                                        <button type="submit" class="px-4 py-2 bg-brand-orange text-white rounded-lg hover:bg-brand-orange-600 text-sm font-semibold transition-all shadow-sm">Save Provider</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- List of Providers and Models -->
                        <div class="space-y-8">
                            @php
                                $providers = \App\Models\AiProvider::where('workspace_id', $workspace->id)->with('models')->get();
                                $providerLabels = \App\Support\AI\ProviderRegistry::labels();
                                $assignments = $workspace->settings->ai_role_assignments ?? [];
                                $roles = [
                                    'text_generation' => ['label' => 'Text Generation (Copy, Ideas)', 'json_key' => 'text_generation'],
                                    'image_generation' => ['label' => 'Image Generation (DALL-E, etc)', 'json_key' => 'image_generation'],
                                    'vision' => ['label' => 'Vision (Image Analysis)', 'json_key' => 'vision_analysis'],
                                    'embeddings' => ['label' => 'Embeddings (Search)', 'json_key' => 'embeddings'],
                                    'audio' => ['label' => 'Audio (TTS, Whisper)', 'json_key' => 'audio']
                                ];
                            @endphp

                            @if($providers->isEmpty())
                                <div class="text-center py-10 bg-white dark:bg-[#161B22] rounded-xl border border-gray-200 dark:border-white/[0.06]">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No AI Providers configured</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding a provider (like OpenAI, OpenRouter, or Anthropic).</p>
                                </div>
                            @else
                                <form method="POST" action="{{ route('settings.ai.models.roles.update') }}">
                                    @csrf

                                    @foreach($providers as $provider)
                                        <div class="bg-white dark:bg-[#161B22] border border-gray-200 dark:border-white/[0.06] rounded-xl overflow-hidden mb-6">
                                            <!-- Provider Header -->
                                            <div class="px-6 py-4 bg-gray-50 dark:bg-white/[0.02] flex items-center justify-between border-b border-gray-200 dark:border-white/[0.06]">
                                                <div>
                                                    <h4 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
                                                        {{ $provider->name }}
                                                        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                                                            {{ $providerLabels[$provider->provider] ?? ucfirst($provider->provider) }}
                                                        </span>
                                                    </h4>
                                                    <p class="text-xs text-gray-500 mt-1 font-mono hover:text-gray-700 cursor-help" title="API Key ending in {{ substr($provider->api_key, -4) }}">
                                                        sk-••••••••••••{{ substr($provider->api_key, -4) }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center space-x-3">
                                                    <!-- Load Models Button -->
                                                    <button type="button" onclick="event.preventDefault(); document.getElementById('load-models-{{ $provider->id }}').submit();" class="text-sm bg-gray-100 hover:bg-gray-200 dark:bg-white/[0.06] dark:hover:bg-white/[0.10] text-gray-800 dark:text-white px-3 py-1.5 rounded-lg transition-colors text-sm font-medium">
                                                        Load / Refresh Models
                                                    </button>

                                                    <!-- Delete Provider -->
                                                    <button type="button" onclick="if(confirm('Are you sure you want to delete this provider?')) { document.getElementById('delete-provider-{{ $provider->id }}').submit(); }" class="text-red-500 hover:text-red-700 p-1">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Models Table -->
                                            <div class="p-0">
                                                @if($provider->models->isEmpty())
                                                    <div class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                                        No models loaded yet. Click "Load / Refresh Models" to fetch them from the provider.
                                                    </div>
                                                @else
                                                    <div class="overflow-x-auto">
                                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                                                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                                                <tr>
                                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model Name</th>
                                                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Capabilities</th>
                                                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assign Role</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-200 dark:divide-white/[0.06]">
                                                                @foreach($provider->models->sortBy('name') as $model)
                                                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                                            {{ $model->name }}
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                                                            <div class="flex justify-center space-x-1">
                                                                                @if($model->supports_text_generation)<span title="Text" class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-white/[0.06]">T</span>@endif
                                                                                @if($model->supports_image_generation)<span title="Image" class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-white/[0.06]">Img</span>@endif
                                                                                @if($model->supports_vision)<span title="Vision" class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-white/[0.06]">👁️</span>@endif
                                                                                @if($model->supports_embeddings)<span title="Embeddings" class="px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-white/[0.06]">Emb</span>@endif
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                                            <div class="flex flex-col items-end space-y-2">
                                                                                @foreach($roles as $roleKey => $roleData)
                                                                                    @php
                                                                                        $supportsField = 'supports_' . $roleKey;
                                                                                        $jsonKey = $roleData['json_key'];
                                                                                        $isChecked = false;

                                                                                        if (isset($assignments[$jsonKey])) {
                                                                                            $isChecked = ($assignments[$jsonKey]['model'] === $model->name && ($assignments[$jsonKey]['provider_id'] ?? null) == $model->ai_provider_id);
                                                                                        } else {
                                                                                            $isDefaultField = 'is_default_' . $roleKey;
                                                                                            $isChecked = $model->$isDefaultField;
                                                                                        }
                                                                                    @endphp

                                                                                    @if($model->$supportsField)
                                                                                        <label class="inline-flex items-center cursor-pointer">
                                                                                            <span class="mr-3 text-xs text-gray-600 dark:text-gray-400">{{ $roleData['label'] }}</span>
                                                                                            <input type="radio" name="roles[{{ $roleKey }}]" value="{{ $model->id }}" class="form-radio h-4 w-4 text-brand-orange border-gray-300 focus:ring-brand-orange/20" {{ $isChecked ? 'checked' : '' }}>
                                                                                        </label>
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="mt-8 flex justify-end">
                                        <button type="submit" class="px-6 py-2.5 bg-brand-orange rounded-lg text-white text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">Save Role Assignments</button>
                                    </div>

                                </form>
                            @endif
                        </div>

                        <!-- Hidden forms for loading/deleting providers -->
                        @foreach($providers as $provider)
                            <form id="load-models-{{ $provider->id }}" action="{{ route('settings.ai.providers.load-models', $provider->id) }}" method="POST" class="hidden">
                                @csrf
                            </form>
                            <form id="delete-provider-{{ $provider->id }}" action="{{ route('settings.ai.providers.destroy', $provider->id) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endforeach
                        @else
                        <div class="bg-white dark:bg-[#161B22] border border-gray-200 dark:border-white/[0.06] rounded-xl p-5">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                AI provider integration and model role assignment are managed by admin/super-admin accounts.
                            </p>
                        </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.querySelector('[data-settings-tabs]');
            if (!root) return;

            const buttons = Array.from(root.querySelectorAll('[data-settings-tab-btn]'));
            const panels = Array.from(root.querySelectorAll('[data-settings-tab-panel]'));
            if (!buttons.length || !panels.length) return;

            const knownTabs = new Set(buttons.map((btn) => btn.dataset.settingsTabBtn));
            const requestedTab = root.getAttribute('data-initial-tab') || 'theme';
            const defaultTab = knownTabs.has(requestedTab) ? requestedTab : 'theme';

            const activateTab = (tabName) => {
                panels.forEach((panel) => {
                    const isActive = panel.dataset.settingsTabPanel === tabName;
                    panel.style.display = isActive ? '' : 'none';
                });

                buttons.forEach((btn) => {
                    const isActive = btn.dataset.settingsTabBtn === tabName;
                    btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });
            };

            buttons.forEach((btn) => {
                btn.addEventListener('click', function () {
                    const tabName = btn.dataset.settingsTabBtn;
                    if (!tabName) return;
                    activateTab(tabName);
                });
            });

            activateTab(defaultTab);
        });
    </script>
</x-app-layout>
