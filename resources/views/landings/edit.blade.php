<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
                {{ __('Edit Landing Page') }}
            </h2>
            <a href="{{ route('landings.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                &larr; Back to Landings
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'general' }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <!-- Tabs Header -->
                <div class="border-b border-gray-100 dark:border-gray-700">
                    <nav class="flex overflow-x-auto" aria-label="Tabs">
                        <button @click="tab = 'general'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'general', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'general' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            General
                        </button>

                        <button @click="tab = 'seo'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'seo', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'seo' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            SEO & Metadata
                        </button>
                        <button @click="tab = 'scripts'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'scripts', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'scripts' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            Scripts & Pixels
                        </button>
                        <button @click="tab = 'payments'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'payments', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'payments' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            Payment Settings
                        </button>
                        <button @click="tab = 'cart'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'cart', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'cart' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            Shopping Cart
                        </button>
                        <button @click="tab = 'countdown'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'countdown', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'countdown' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            Countdown Timer
                        </button>
                    </nav>
                </div>

                <div class="p-8">
                    <form action="{{ route('landings.update', $landing) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- General Tab -->
                        <div x-show="tab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Landing Page Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $landing->name) }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" required>
                                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL Slug</label>
                                    <div class="flex rounded-lg shadow-sm">
                                        <span class="inline-flex items-center px-4 rounded-l-lg border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-sm">
                                            {{ config('app.url') }}/
                                        </span>
                                        <input type="text" name="slug" id="slug" value="{{ old('slug', $landing->slug) }}" class="flex-1 min-w-0 block w-full px-4 py-2.5 rounded-none rounded-r-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The public URL where your landing page will be accessible.</p>
                                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>



                        <!-- SEO Tab -->
                        <div x-show="tab === 'seo'" x-cloak style="display: none;">
                            <div class="space-y-6">
                                <div>
                                    <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                                    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $landing->settings->meta_title ?? '') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" placeholder="Page Title | Brand">
                                </div>

                                <div>
                                    <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                                    <textarea name="meta_description" id="meta_description" rows="4" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Brief description for search engines...">{{ old('meta_description', $landing->settings->meta_description ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Scripts Tab -->
                        <div x-show="tab === 'scripts'" x-cloak style="display: none;">
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="fb_pixel_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facebook Pixel ID</label>
                                        <input type="text" name="fb_pixel_id" id="fb_pixel_id" value="{{ old('fb_pixel_id', $landing->settings->fb_pixel_id ?? '') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" placeholder="1234567890">
                                    </div>
                                    <div>
                                        <label for="ga_measurement_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">GA Measurement ID</label>
                                        <input type="text" name="ga_measurement_id" id="ga_measurement_id" value="{{ old('ga_measurement_id', $landing->settings->ga_measurement_id ?? '') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" placeholder="G-XXXXXXXXXX">
                                    </div>
                                </div>

                                <div>
                                    <label for="custom_head_scripts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Head Scripts</label>
                                    <textarea name="custom_head_scripts" id="custom_head_scripts" rows="6" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs font-mono" placeholder="<script>...</script>">{{ old('custom_head_scripts', $landing->settings->custom_head_scripts ?? '') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Injected before the closing &lt;/head&gt; tag.</p>
                                </div>

                                <div>
                                    <label for="custom_body_scripts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Body Scripts</label>
                                    <textarea name="custom_body_scripts" id="custom_body_scripts" rows="6" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs font-mono" placeholder="<script>...</script>">{{ old('custom_body_scripts', $landing->settings->custom_body_scripts ?? '') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Injected before the closing &lt;/body&gt; tag.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div x-show="tab === 'payments'" x-cloak style="display: none;">
                            <div class="space-y-8">

                                <!-- Payment Methods -->
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Accepted Payment Methods</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ old('enable_card', $landing->settings->enable_card ?? true) ? 'border-indigo-200 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-800' : 'border-gray-200 dark:border-gray-700' }}">
                                            <div class="flex items-center h-5">
                                                <input id="enable_card" name="enable_card" type="checkbox" value="1" {{ old('enable_card', $landing->settings->enable_card ?? true) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="block font-medium text-gray-900 dark:text-white">Credit Card</span>
                                                <span class="block text-gray-500 dark:text-gray-400 text-xs mt-1">Via Stripe</span>
                                            </div>
                                        </label>

                                        <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ old('enable_paypal', $landing->settings->enable_paypal ?? true) ? 'border-indigo-200 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-800' : 'border-gray-200 dark:border-gray-700' }}">
                                            <div class="flex items-center h-5">
                                                <input id="enable_paypal" name="enable_paypal" type="checkbox" value="1" {{ old('enable_paypal', $landing->settings->enable_paypal ?? true) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="block font-medium text-gray-900 dark:text-white">PayPal</span>
                                                <span class="block text-gray-500 dark:text-gray-400 text-xs mt-1">Express Checkout</span>
                                            </div>
                                        </label>

                                        <label class="relative flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ old('enable_cod', $landing->settings->enable_cod ?? false) ? 'border-indigo-200 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-800' : 'border-gray-200 dark:border-gray-700' }}">
                                            <div class="flex items-center h-5">
                                                <input id="enable_cod" name="enable_cod" type="checkbox" value="1" {{ old('enable_cod', $landing->settings->enable_cod ?? false) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="block font-medium text-gray-900 dark:text-white">COD</span>
                                                <span class="block text-gray-500 dark:text-gray-400 text-xs mt-1">Cash on Delivery</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- API Overrides -->
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">API Key Overrides (Optional)</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Leave blank to use the global workspace keys from Settings.</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-4">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Stripe</h4>
                                            <input type="text" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $landing->settings->stripe_publishable_key ?? '') }}" placeholder="Publishable Key" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2">
                                            <input type="password" name="stripe_secret_key" value="{{ old('stripe_secret_key', $landing->settings->stripe_secret_key ?? '') }}" placeholder="Secret Key" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2">
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">PayPal</h4>
                                            <input type="text" name="paypal_client_id" value="{{ old('paypal_client_id', $landing->settings->paypal_client_id ?? '') }}" placeholder="Client ID" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2">
                                            <input type="password" name="paypal_secret" value="{{ old('paypal_secret', $landing->settings->paypal_secret ?? '') }}" placeholder="Secret" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shopping Cart Tab -->
                        <div x-show="tab === 'cart'" x-cloak style="display: none;">
                            <div class="space-y-8">
                                <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Cart Configuration</h3>
                                    
                                    <div class="space-y-6">
                                        <label class="relative flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="enable_cart" name="enable_cart" type="checkbox" value="1" {{ old('enable_cart', $landing->enable_cart ?? false) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="block font-medium text-gray-900 dark:text-white">Enable Shopping Cart</span>
                                                <span class="block text-gray-500 dark:text-gray-400 text-xs mt-1">Show a floating shopping cart on your landing page.</span>
                                            </div>
                                        </label>
                            
                                        <!-- Cart Position -->
                                        <div>
                                            <label for="cart_position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cart Position</label>
                                            <select id="cart_position" name="cart_position" 
                                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                <option value="bottom-right" {{ ($landing->cart_position ?? 'bottom-right') === 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                                                <option value="bottom-left" {{ ($landing->cart_position ?? 'bottom-right') === 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                                                <option value="top-right" {{ ($landing->cart_position ?? 'bottom-right') === 'top-right' ? 'selected' : '' }}>Top Right</option>
                                                <option value="top-left" {{ ($landing->cart_position ?? 'bottom-right') === 'top-left' ? 'selected' : '' }}>Top Left</option>
                                                <option value="bottom-bar" {{ ($landing->cart_position ?? 'bottom-right') === 'bottom-bar' ? 'selected' : '' }}>Full Width Bottom Bar</option>
                                            </select>
                                        </div>

                                        <!-- Offsets (Hidden for Bottom Bar) -->
                                        <div class="grid grid-cols-2 gap-4" x-show="document.getElementById('cart_position').value !== 'bottom-bar'">
                                            <div>
                                                <label for="cart_x_offset" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horizontal Offset (px)</label>
                                                <input type="number" name="cart_x_offset" id="cart_x_offset" value="{{ $landing->cart_x_offset ?? 20 }}"
                                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            </div>
                                            <div>
                                                <label for="cart_y_offset" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vertical Offset (px)</label>
                                                <input type="number" name="cart_y_offset" id="cart_y_offset" value="{{ $landing->cart_y_offset ?? 20 }}"
                                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            </div>
                                        </div>

                                        <!-- Colors Grid -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                                            <div>
                                                <label for="cart_bg_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cart Background Color</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="color" name="cart_bg_color" id="cart_bg_color" value="{{ old('cart_bg_color', $landing->cart_bg_color ?? '#ffffff') }}" class="h-10 w-16 p-1 rounded border border-gray-300 dark:border-gray-600">
                                                    <input type="text" value="{{ old('cart_bg_color', $landing->cart_bg_color ?? '#ffffff') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" oninput="document.getElementById('cart_bg_color').value = this.value">
                                                </div>
                                            </div>

                                            <div>
                                                <label for="cart_text_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cart Text Color</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="color" name="cart_text_color" id="cart_text_color" value="{{ old('cart_text_color', $landing->cart_text_color ?? '#000000') }}" class="h-10 w-16 p-1 rounded border border-gray-300 dark:border-gray-600">
                                                    <input type="text" value="{{ old('cart_text_color', $landing->cart_text_color ?? '#000000') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" oninput="document.getElementById('cart_text_color').value = this.value">
                                                </div>
                                            </div>

                                            <div>
                                                <label for="cart_btn_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Checkout Button Color</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="color" name="cart_btn_color" id="cart_btn_color" value="{{ old('cart_btn_color', $landing->cart_btn_color ?? '#3b82f6') }}" class="h-10 w-16 p-1 rounded border border-gray-300 dark:border-gray-600">
                                                    <input type="text" value="{{ old('cart_btn_color', $landing->cart_btn_color ?? '#3b82f6') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" oninput="document.getElementById('cart_btn_color').value = this.value">
                                                </div>
                                            </div>

                                            <div>
                                                <label for="cart_btn_text_color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Checkout Button Text Color</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="color" name="cart_btn_text_color" id="cart_btn_text_color" value="{{ old('cart_btn_text_color', $landing->cart_btn_text_color ?? '#ffffff') }}" class="h-10 w-16 p-1 rounded border border-gray-300 dark:border-gray-600">
                                                    <input type="text" value="{{ old('cart_btn_text_color', $landing->cart_btn_text_color ?? '#ffffff') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5" oninput="document.getElementById('cart_btn_text_color').value = this.value">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Countdown Tab -->
                        <div x-show="tab === 'countdown'" x-cloak style="display: none;">
                            <div class="space-y-8">
                                <div class="bg-gray-50 dark:bg-gray-700/30 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Countdown Configuration</h3>
                                    
                                    <div class="space-y-6">
                                        <label class="relative flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="countdown_enabled" name="countdown_enabled" type="checkbox" value="1" {{ old('countdown_enabled', $landing->countdown_enabled ?? false) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="block font-medium text-gray-900 dark:text-white">Enable Countdown Timer</span>
                                                <span class="block text-gray-500 dark:text-gray-400 text-xs mt-1">Show countdown timers on your landing page.</span>
                                            </div>
                                        </label>

                                        <div x-data="{ mode: '{{ $landing->countdown_end_at ? 'fixed' : 'duration' }}' }" class="space-y-4 border-t border-gray-200 dark:border-gray-600 pt-4">
                                            <div>
                                                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Timer Mode</span>
                                                <div class="flex items-center gap-4">
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" x-model="mode" value="fixed" class="form-radio text-indigo-600">
                                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Fixed Date & Time</span>
                                                    </label>
                                                    <label class="inline-flex items-center">
                                                        <input type="radio" x-model="mode" value="duration" class="form-radio text-indigo-600">
                                                        <span class="ml-2 text-gray-700 dark:text-gray-300">Evergreen Duration (Minutes)</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <!-- Fixed Date Input -->
                                            <div x-show="mode === 'fixed'" class="transition-all">
                                                <label for="countdown_end_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date & Time</label>
                                                <input type="datetime-local" name="countdown_end_at" id="countdown_end_at" 
                                                       value="{{ old('countdown_end_at', $landing->countdown_end_at ? $landing->countdown_end_at->format('Y-m-d\TH:i') : '') }}" 
                                                       class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Timer will count down to this specific date.</p>
                                            </div>

                                            <!-- Duration Input -->
                                            <div x-show="mode === 'duration'" class="transition-all">
                                                <label for="countdown_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (Minutes)</label>
                                                <input type="number" name="countdown_duration_minutes" id="countdown_duration_minutes" 
                                                       value="{{ old('countdown_duration_minutes', $landing->countdown_duration_minutes ?? 15) }}" 
                                                       min="1"
                                                       class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Timer will loop or restart based on this duration (simulated evergreen).</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('landings.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md transition-all transform hover:scale-105">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
