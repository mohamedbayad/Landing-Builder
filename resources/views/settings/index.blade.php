<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
            {{ __('Global Settings') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'theme' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            
            <!-- Tabs Navigation -->
            <div class="flex space-x-1 rounded-xl bg-gray-200 dark:bg-gray-700 p-1 mb-8 overflow-x-auto">
                <button @click="tab = 'theme'" :class="{ 'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white': tab === 'theme', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200': tab !== 'theme' }" class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all">
                    Theme & Dashboard
                </button>
                <button @click="tab = 'payment'" :class="{ 'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white': tab === 'payment', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200': tab !== 'payment' }" class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all">
                    Payment & Currency
                </button>
                <button @click="tab = 'whatsapp'" :class="{ 'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white': tab === 'whatsapp', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200': tab !== 'whatsapp' }" class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all">
                    WhatsApp Automation
                </button>
                <button @click="tab = 'license'" :class="{ 'bg-white dark:bg-gray-600 shadow text-gray-900 dark:text-white': tab === 'license', 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200': tab !== 'license' }" class="w-full rounded-lg py-2.5 text-sm font-medium leading-5 transition-all">
                    License
                </button>
            </div>

            <!-- Content -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
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
                    <div x-show="tab === 'theme'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Theme Configuration</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Customize dashboard appearance and public page styles.</p>
                        </div>

                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="dashboard_direction" value="{{ $workspace->settings->dashboard_direction ?? 'ltr' }}"> <!-- Ensure field exists for validation trigger -->

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                                <!-- Dashboard Settings -->
                                <div class="space-y-6">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">Dashboard UI</h4>
                                    
                                    <div>
                                        <label for="dashboard_direction" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Layout Direction</label>
                                        <select name="dashboard_direction" id="dashboard_direction" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="ltr" {{ ($workspace->settings->dashboard_direction ?? 'ltr') == 'ltr' ? 'selected' : '' }}>LTR (Left to Right)</option>
                                            <option value="rtl" {{ ($workspace->settings->dashboard_direction ?? 'ltr') == 'rtl' ? 'selected' : '' }}>RTL (Right to Left)</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="dashboard_primary_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Color</label>
                                        <div class="mt-1 flex items-center space-x-2">
                                            <input type="color" name="dashboard_primary_color" id="dashboard_primary_color" value="{{ $workspace->settings->dashboard_primary_color ?? '#4f46e5' }}" class="h-10 w-20 rounded p-1 border border-gray-300">
                                            <span class="text-xs text-gray-500">Main button & highlight color</span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label for="sidebar_bg" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sidebar Background</label>
                                        <input type="color" name="sidebar_bg" value="{{ $workspace->settings->sidebar_bg ?? '#ffffff' }}" class="mt-1 h-10 w-20 rounded p-1 border border-gray-300">
                                    </div>
                                </div>

                                <!-- Landing Page Styles -->
                                <div class="space-y-6">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">Public Pages Style</h4>
                                    
                                    <div x-data="{ checkoutLayout: '{{ $workspace->settings->checkout_style ?? 'layout_1' }}' }">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Checkout Layout Preset</label>
                                        <input type="hidden" name="checkout_style" :value="checkoutLayout">
                                        
                                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                                            <!-- Layout 1: Sidebar Left + Content Right -->
                                            <label @click="checkoutLayout = 'layout_1'" 
                                                   :class="checkoutLayout === 'layout_1' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <!-- Wireframe Preview -->
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-2 mb-3 flex gap-1.5">
                                                    <div class="w-1/3 bg-gray-300 dark:bg-gray-500 rounded"></div>
                                                    <div class="flex-1 flex flex-col gap-1">
                                                        <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded w-3/4"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-gray-400 rounded"></div>
                                                        <div class="h-3 bg-indigo-400 rounded w-1/2"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Sidebar Left</span>
                                                    <div :class="checkoutLayout === 'layout_1' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_1'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Layout 2: Single Column (Centered) -->
                                            <label @click="checkoutLayout = 'layout_2'" 
                                                   :class="checkoutLayout === 'layout_2' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-2 mb-3 flex justify-center">
                                                    <div class="w-2/3 flex flex-col gap-1">
                                                        <div class="h-2 bg-gray-300 dark:bg-gray-500 rounded mx-auto w-1/2"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-gray-400 rounded"></div>
                                                        <div class="h-3 bg-indigo-400 rounded mx-auto w-3/4"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Centered</span>
                                                    <div :class="checkoutLayout === 'layout_2' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_2'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Layout 3: Split Screen (50/50) -->
                                            <label @click="checkoutLayout = 'layout_3'" 
                                                   :class="checkoutLayout === 'layout_3' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-2 mb-3 flex gap-1">
                                                    <div class="flex-1 bg-indigo-200 dark:bg-indigo-900/50 rounded flex items-center justify-center">
                                                        <div class="w-4 h-4 bg-indigo-400 rounded"></div>
                                                    </div>
                                                    <div class="flex-1 flex flex-col gap-1 p-1">
                                                        <div class="h-1.5 bg-gray-300 dark:bg-gray-500 rounded w-3/4"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-gray-400 rounded"></div>
                                                        <div class="h-2 bg-indigo-400 rounded w-full"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Split 50/50</span>
                                                    <div :class="checkoutLayout === 'layout_3' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_3'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Layout 4: Full Width with Header -->
                                            <label @click="checkoutLayout = 'layout_4'" 
                                                   :class="checkoutLayout === 'layout_4' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-1.5 mb-3 flex flex-col gap-1">
                                                    <div class="h-3 bg-indigo-300 dark:bg-indigo-800 rounded"></div>
                                                    <div class="flex-1 flex flex-col gap-1 px-2">
                                                        <div class="h-1.5 bg-gray-300 dark:bg-gray-500 rounded w-1/2 mx-auto"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-gray-400 rounded"></div>
                                                        <div class="h-2 bg-indigo-400 rounded w-1/3 mx-auto"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Full Width</span>
                                                    <div :class="checkoutLayout === 'layout_4' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="checkoutLayout === 'layout_4'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Layout 5: Minimalist (Content Only) -->
                                            <label @click="checkoutLayout = 'layout_5'" 
                                                   :class="checkoutLayout === 'layout_5' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-3 mb-3 flex flex-col justify-center gap-1.5">
                                                    <div class="h-1.5 bg-gray-300 dark:bg-gray-500 rounded w-1/2 mx-auto"></div>
                                                    <div class="h-6 bg-gray-200 dark:bg-gray-400 rounded w-4/5 mx-auto"></div>
                                                    <div class="h-2.5 bg-indigo-400 rounded w-1/3 mx-auto"></div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Minimalist</span>
                                                    <div :class="checkoutLayout === 'layout_5' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
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
                                                   :class="thankyouLayout === 'thankyou_1' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-2 mb-3 flex flex-col items-center justify-center relative overflow-hidden">
                                                    <!-- Confetti dots -->
                                                    <div class="absolute top-1 left-2 w-1 h-1 bg-yellow-400 rounded-full"></div>
                                                    <div class="absolute top-2 right-3 w-1.5 h-1.5 bg-pink-400 rounded-full"></div>
                                                    <div class="absolute top-3 left-4 w-1 h-1 bg-green-400 rounded-full"></div>
                                                    <div class="absolute bottom-3 right-2 w-1 h-1 bg-blue-400 rounded-full"></div>
                                                    <div class="absolute bottom-2 left-3 w-1.5 h-1.5 bg-purple-400 rounded-full"></div>
                                                    <!-- Checkmark -->
                                                    <div class="w-6 h-6 rounded-full bg-green-400 flex items-center justify-center mb-1">
                                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                    <div class="h-1.5 bg-gray-300 dark:bg-gray-500 rounded w-3/4"></div>
                                                    <div class="h-1 bg-gray-200 dark:bg-gray-400 rounded w-1/2 mt-1"></div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Confetti</span>
                                                    <div :class="thankyouLayout === 'thankyou_1' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_1'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Thankyou 2: Simple Card -->
                                            <label @click="thankyouLayout = 'thankyou_2'" 
                                                   :class="thankyouLayout === 'thankyou_2' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-2 mb-3 flex items-center justify-center">
                                                    <div class="w-4/5 bg-white dark:bg-gray-500 rounded-lg p-2 shadow-sm flex flex-col items-center gap-1">
                                                        <div class="w-4 h-4 rounded-full bg-green-400 flex items-center justify-center">
                                                            <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                        <div class="h-1 bg-gray-300 dark:bg-gray-400 rounded w-3/4"></div>
                                                        <div class="h-1 bg-gray-200 dark:bg-gray-300 rounded w-1/2"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Simple Card</span>
                                                    <div :class="thankyouLayout === 'thankyou_2' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_2'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Thankyou 3: Split with Summary -->
                                            <label @click="thankyouLayout = 'thankyou_3'" 
                                                   :class="thankyouLayout === 'thankyou_3' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-1.5 mb-3 flex gap-1">
                                                    <div class="flex-1 bg-green-100 dark:bg-green-900/30 rounded flex flex-col items-center justify-center p-1">
                                                        <div class="w-3 h-3 rounded-full bg-green-400 flex items-center justify-center">
                                                            <svg class="w-1.5 h-1.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                        <div class="h-1 bg-gray-300 dark:bg-gray-500 rounded w-3/4 mt-1"></div>
                                                    </div>
                                                    <div class="flex-1 flex flex-col gap-0.5 p-1">
                                                        <div class="h-1 bg-gray-300 dark:bg-gray-500 rounded w-full"></div>
                                                        <div class="h-1 bg-gray-200 dark:bg-gray-400 rounded w-3/4"></div>
                                                        <div class="h-1 bg-gray-200 dark:bg-gray-400 rounded w-full"></div>
                                                        <div class="h-1.5 bg-indigo-400 rounded w-2/3 mt-auto"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Split View</span>
                                                    <div :class="thankyouLayout === 'thankyou_3' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_3'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Thankyou 4: Full Width Banner -->
                                            <label @click="thankyouLayout = 'thankyou_4'" 
                                                   :class="thankyouLayout === 'thankyou_4' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-1.5 mb-3 flex flex-col gap-1">
                                                    <div class="h-5 bg-green-200 dark:bg-green-800 rounded flex items-center justify-center">
                                                        <div class="w-3 h-3 rounded-full bg-green-500 flex items-center justify-center">
                                                            <svg class="w-1.5 h-1.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 flex flex-col gap-0.5 px-1">
                                                        <div class="h-1 bg-gray-300 dark:bg-gray-500 rounded w-1/2 mx-auto"></div>
                                                        <div class="flex-1 bg-gray-200 dark:bg-gray-400 rounded"></div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Full Banner</span>
                                                    <div :class="thankyouLayout === 'thankyou_4' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
                                                        <svg x-show="thankyouLayout === 'thankyou_4'" class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                </div>
                                            </label>

                                            <!-- Thankyou 5: Minimalist -->
                                            <label @click="thankyouLayout = 'thankyou_5'" 
                                                   :class="thankyouLayout === 'thankyou_5' ? 'ring-2 ring-indigo-500 border-indigo-500' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                                   class="relative cursor-pointer rounded-xl border-2 bg-white dark:bg-gray-700 p-3 transition-all">
                                                <div class="aspect-[4/3] bg-gray-100 dark:bg-gray-600 rounded-lg p-3 mb-3 flex flex-col items-center justify-center gap-1">
                                                    <div class="w-5 h-5 rounded-full bg-green-400 flex items-center justify-center">
                                                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                    </div>
                                                    <div class="h-1.5 bg-gray-300 dark:bg-gray-500 rounded w-2/3"></div>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Minimalist</span>
                                                    <div :class="thankyouLayout === 'thankyou_5' ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-500'" class="w-4 h-4 rounded-full flex items-center justify-center transition-colors">
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
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Download Invoice Button</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="thankyou_show_invoice_btn" class="sr-only peer" {{ ($workspace->settings->thankyou_show_invoice_btn ?? true) ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="px-6 py-2.5 bg-indigo-600 rounded-lg text-white font-semibold hover:bg-indigo-700 shadow-lg">Save Theme Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: PAYMENT -->
                    <div x-show="tab === 'payment'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Payment Gateways & Currency</h3>
                        </div>

                         <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            @method('PUT')
                            <!-- Basic fields required for validation -->
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Default Currency</label>
                                    <select name="currency" id="currency" class="block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="USD" {{ old('currency', $workspace->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                        <option value="EUR" {{ old('currency', $workspace->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                        <option value="GBP" {{ old('currency', $workspace->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                        <option value="MAD" {{ old('currency', $workspace->currency) == 'MAD' ? 'selected' : '' }}>MAD (DH)</option>
                                    </select>
                                </div>

                                <div class="col-span-1 md:col-span-2 space-y-6">
                                     <!-- Stripe -->
                                    <div class="p-5 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-4">Stripe</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm text-gray-600 dark:text-gray-400">Publishable Key</label>
                                                <input type="text" name="stripe_publishable_key" value="{{ $workspace->stripe_publishable_key }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 dark:text-gray-400">Secret Key</label>
                                                <input type="password" name="stripe_secret_key" value="{{ $workspace->stripe_secret_key }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PayPal -->
                                     <div class="p-5 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <h4 class="font-medium text-gray-900 dark:text-white mb-4">PayPal</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm text-gray-600 dark:text-gray-400">Client ID</label>
                                                <input type="text" name="paypal_client_id" value="{{ $workspace->paypal_client_id }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-600 dark:text-gray-400">Secret</label>
                                                <input type="password" name="paypal_secret" value="{{ $workspace->paypal_secret }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="px-6 py-2.5 bg-indigo-600 rounded-lg text-white font-semibold hover:bg-indigo-700 shadow-lg">Save Payment Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: WHATSAPP -->
                    <div x-show="tab === 'whatsapp'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="whatsapp_phone_check" value="1"> <!-- Hidden input to identify this form -->

                            <div class="mb-6 flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">WhatsApp Automation</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure automated WhatsApp redirection and messages.</p>
                                </div>
                                
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="whatsapp_enabled" class="sr-only peer" {{ ($workspace->settings->whatsapp_enabled ?? false) ? 'checked' : '' }}>
                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Enable Feature</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Config -->
                                <div class="space-y-6">
                                    <div class="bg-gray-50 dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Redirection Settings</h4>
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default WhatsApp Number</label>
                                                <input type="text" name="whatsapp_phone" placeholder="e.g. 1234567890" value="{{ $workspace->settings->whatsapp_phone ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <p class="text-xs text-gray-500 mt-1">Include country code without + (e.g. 15551234567)</p>
                                            </div>

                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Auto Redirect on Thank You Page</span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="whatsapp_redirect_enabled" class="sr-only peer" {{ ($workspace->settings->whatsapp_redirect_enabled ?? false) ? 'checked' : '' }}>
                                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                                </label>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Redirect Delay (Seconds)</label>
                                                <input type="number" name="whatsapp_redirect_seconds" min="0" max="60" value="{{ $workspace->settings->whatsapp_redirect_seconds ?? 5 }}" class="mt-1 block w-24 rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Template Builder -->
                                <div class="space-y-6">
                                     <div class="bg-gray-50 dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Message Templates</h4>
                                        
                                        @php
                                            $defaultThanks = 'Hello {{ customer-name }}, your order #{{ order-id }} is confirmed. Total: {{ currency }} {{ order-total }}.';
                                            $defaultLanding = 'I want to know more about this offer.';
                                            $whatsappVars = ['{{ customer-name }}', '{{ order-id }}', '{{ order-total }}', '{{ currency }}', '{{ customer-phone }}'];
                                        @endphp
                                        
                                        <div class="mb-5">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thank You Page Message</label>
                                            <textarea name="whatsapp_template_thankyou" rows="4" class="block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Hello @{{ customer-name }}, thank you for your order!">{{ old('whatsapp_template_thankyou', $workspace->settings->whatsapp_template_thankyou ?? $defaultThanks) }}</textarea>
                                            
                                            <!-- Variable Chips -->
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="text-xs font-semibold text-gray-500 uppercase">Available Variables:</span>
                                                @foreach($whatsappVars as $var)
                                                    <button type="button" onclick="const ta = this.closest('div').previousElementSibling; ta.value += ' {{ $var }}'; ta.focus();" class="px-2 py-1 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100">{{ $var }}</button>
                                                @endforeach
                                            </div>
                                        </div>

                                         <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Landing Page Button Message</label>
                                            <textarea name="whatsapp_template_landing" rows="2" class="block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="I have a question about @{{ landing-title }}">{{ old('whatsapp_template_landing', $workspace->settings->whatsapp_template_landing ?? $defaultLanding) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                             <div class="mt-8 flex justify-end">
                                <button type="submit" class="px-6 py-2.5 bg-green-600 rounded-lg text-white font-semibold hover:bg-green-700 shadow-lg">Save WhatsApp Settings</button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: LICENSE -->
                    <div x-show="tab === 'license'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" style="display: none;">
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">License Management</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Activate your license to unlock premium features and templates.</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">License Status</h4>
                                    <p class="text-sm text-gray-500">Current status of your installation.</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ ($workspace->settings->license_status === 'active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($workspace->settings->license_status ?? 'Inactive') }}
                                </span>
                            </div>

                            <div class="mb-4">
                                @if(session('status') === 'license-activated')
                                    <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-200 dark:text-green-800" role="alert">
                                        <span class="font-medium">Success!</span> License activated successfully.
                                    </div>
                                @endif
                                
                                @if(session('status') === 'license-removed')
                                    <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">
                                        <span class="font-medium">Removed!</span> License deactivated.
                                    </div>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('settings.update') }}">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-4">
                                    <label for="license_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">License Key</label>
                                    <input type="text" name="license_key" id="license_key" value="{{ old('license_key', $workspace->settings->license_key) }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="LICENSE-XXXX-YYYY-ZZZZ">
                                    @error('license_key')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    @if(session('error'))
                                         <p class="text-red-500 text-xs mt-1">{{ session('error') }}</p>
                                    @endif
                                </div>

                                <div class="flex justify-between items-center">
                                    @if($workspace->settings->license_status === 'active')
                                        <button type="submit" name="remove_license" value="1" class="text-red-500 hover:text-red-700 text-sm font-medium focus:outline-none" onclick="return confirm('Are you sure you want to deactivate and remove this license?');">
                                            Deactivate License
                                        </button>
                                    @else
                                        <div></div> <!-- Spacer -->
                                    @endif

                                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 rounded-lg text-white font-semibold hover:bg-indigo-700 shadow-lg">
                                        {{ ($workspace->settings->license_status === 'active') ? 'Update License' : 'Activate License' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
