<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
                {{ __('Edit Landing Page') }}
            </h2>
            <a href="{{ route('landings.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-orange dark:text-gray-400 dark:hover:text-brand-orange transition-colors">
                &larr; Back to Landings
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ tab: 'general' }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-[#161B22] shadow-sm rounded-xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">

                <!-- Tabs Header -->
                <div class="border-b border-gray-100 dark:border-white/[0.06]">
                    <nav class="flex overflow-x-auto" aria-label="Tabs">
                        @foreach([
                            ['id' => 'general',   'label' => 'General'],
                            ['id' => 'seo',       'label' => 'SEO & Metadata'],
                            ['id' => 'scripts',   'label' => 'Scripts & Pixels'],
                            ['id' => 'payments',  'label' => 'Payment Settings'],
                            ['id' => 'cart',      'label' => 'Shopping Cart'],
                            ['id' => 'countdown', 'label' => 'Countdown Timer'],
                        ] as $t)
                        <button @click="tab = '{{ $t['id'] }}'"
                                :class="tab === '{{ $t['id'] }}' ? 'border-brand-orange text-brand-orange' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                                class="whitespace-nowrap py-3.5 px-5 border-b-2 font-medium text-sm transition-colors duration-150">
                            {{ $t['label'] }}
                        </button>
                        @endforeach
                    </nav>
                </div>

                <div class="p-8">
                    <form action="{{ route('landings.update', $landing) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- General Tab -->
                        <div x-show="tab === 'general'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="space-y-5">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Landing Page Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $landing->name) }}"
                                           class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white placeholder-gray-400 px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors"
                                           required>
                                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL Slug</label>
                                    <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-white/10 focus-within:border-brand-orange focus-within:ring-2 focus-within:ring-brand-orange/20 transition-all">
                                        <span class="inline-flex items-center px-3 bg-gray-50 dark:bg-white/[0.04] text-gray-500 dark:text-gray-400 text-sm border-r border-gray-200 dark:border-white/10">
                                            {{ config('app.url') }}/
                                        </span>
                                        <input type="text" name="slug" id="slug" value="{{ old('slug', $landing->slug) }}"
                                               class="flex-1 min-w-0 block px-3 py-2.5 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white focus:outline-none"
                                               required>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The public URL where your landing page will be accessible.</p>
                                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SEO Tab -->
                        <div x-show="tab === 'seo'" x-cloak style="display: none;">
                            <div class="space-y-5">
                                <div>
                                    <label for="meta_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title</label>
                                    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $landing->settings->meta_title ?? '') }}"
                                           class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors"
                                           placeholder="Page Title | Brand">
                                </div>
                                <div>
                                    <label for="meta_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                                    <textarea name="meta_description" id="meta_description" rows="4"
                                              class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors"
                                              placeholder="Brief description for search engines...">{{ old('meta_description', $landing->settings->meta_description ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Scripts Tab -->
                        <div x-show="tab === 'scripts'" x-cloak style="display: none;">
                            <div class="space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label for="fb_pixel_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facebook Pixel ID</label>
                                        <input type="text" name="fb_pixel_id" id="fb_pixel_id" value="{{ old('fb_pixel_id', $landing->settings->fb_pixel_id ?? '') }}"
                                               class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors"
                                               placeholder="1234567890">
                                    </div>
                                    <div>
                                        <label for="ga_measurement_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">GA Measurement ID</label>
                                        <input type="text" name="ga_measurement_id" id="ga_measurement_id" value="{{ old('ga_measurement_id', $landing->settings->ga_measurement_id ?? '') }}"
                                               class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors"
                                               placeholder="G-XXXXXXXXXX">
                                    </div>
                                </div>
                                <div>
                                    <label for="custom_head_scripts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Head Scripts</label>
                                    <textarea name="custom_head_scripts" id="custom_head_scripts" rows="6"
                                              class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-300 dark:text-gray-300 font-mono px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors"
                                              placeholder="<script>...</script>">{{ old('custom_head_scripts', $landing->settings->custom_head_scripts ?? '') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Injected before the closing &lt;/head&gt; tag.</p>
                                </div>
                                <div>
                                    <label for="custom_body_scripts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Custom Body Scripts</label>
                                    <textarea name="custom_body_scripts" id="custom_body_scripts" rows="6"
                                              class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-300 dark:text-gray-300 font-mono px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors"
                                              placeholder="<script>...</script>">{{ old('custom_body_scripts', $landing->settings->custom_body_scripts ?? '') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Injected before the closing &lt;/body&gt; tag.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div x-show="tab === 'payments'" x-cloak style="display: none;">
                            <div class="space-y-8">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Accepted Payment Methods</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @foreach([
                                            ['id' => 'enable_card',   'label' => 'Credit Card',  'sub' => 'Via Stripe',         'default' => true],
                                            ['id' => 'enable_paypal', 'label' => 'PayPal',        'sub' => 'Express Checkout',   'default' => true],
                                            ['id' => 'enable_cod',    'label' => 'COD',           'sub' => 'Cash on Delivery',   'default' => false],
                                        ] as $method)
                                        @php $checked = old($method['id'], $landing->settings->{$method['id']} ?? $method['default']); @endphp
                                        <label class="relative flex items-start p-4 border rounded-xl cursor-pointer transition-colors
                                            {{ $checked ? 'border-brand-orange/40 bg-orange-50 dark:bg-orange-500/10 dark:border-brand-orange/30' : 'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/[0.02]' }}">
                                            <div class="flex items-center h-5">
                                                <input id="{{ $method['id'] }}" name="{{ $method['id'] }}" type="checkbox" value="1"
                                                       {{ $checked ? 'checked' : '' }}
                                                       class="h-4 w-4 text-brand-orange border-gray-300 rounded focus:ring-brand-orange/20">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <span class="block font-medium text-gray-900 dark:text-white">{{ $method['label'] }}</span>
                                                <span class="block text-gray-500 dark:text-gray-400 text-xs mt-0.5">{{ $method['sub'] }}</span>
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">API Key Overrides <span class="font-normal text-gray-400">(Optional)</span></h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Leave blank to use the global workspace keys from Settings.</p>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-3">
                                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stripe</h4>
                                            <input type="text" name="stripe_publishable_key" value="{{ old('stripe_publishable_key', $landing->settings->stripe_publishable_key ?? '') }}" placeholder="Publishable Key"
                                                   class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                            <input type="password" name="stripe_secret_key" value="{{ old('stripe_secret_key', $landing->settings->stripe_secret_key ?? '') }}" placeholder="Secret Key"
                                                   class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                        </div>
                                        <div class="space-y-3">
                                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">PayPal</h4>
                                            <input type="text" name="paypal_client_id" value="{{ old('paypal_client_id', $landing->settings->paypal_client_id ?? '') }}" placeholder="Client ID"
                                                   class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                            <input type="password" name="paypal_secret" value="{{ old('paypal_secret', $landing->settings->paypal_secret ?? '') }}" placeholder="Secret"
                                                   class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shopping Cart Tab -->
                        <div x-show="tab === 'cart'" x-cloak style="display: none;">
                            <div class="space-y-6">
                                <div class="bg-gray-50 dark:bg-white/[0.02] p-5 rounded-xl border border-gray-100 dark:border-white/[0.06]">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Cart Configuration</h3>
                                    <div class="space-y-5">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input id="enable_cart" name="enable_cart" type="checkbox" value="1"
                                                   {{ old('enable_cart', $landing->enable_cart ?? false) ? 'checked' : '' }}
                                                   class="mt-0.5 h-4 w-4 text-brand-orange border-gray-300 rounded focus:ring-brand-orange/20">
                                            <div class="text-sm">
                                                <span class="block font-medium text-gray-900 dark:text-white">Enable Shopping Cart</span>
                                                <span class="block text-gray-500 dark:text-gray-400 text-xs mt-0.5">Show a floating shopping cart on your landing page.</span>
                                            </div>
                                        </label>

                                        <div>
                                            <label for="cart_position" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cart Position</label>
                                            <select id="cart_position" name="cart_position"
                                                    class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                                <option value="bottom-right" {{ ($landing->cart_position ?? 'bottom-right') === 'bottom-right' ? 'selected' : '' }}>Bottom Right</option>
                                                <option value="bottom-left"  {{ ($landing->cart_position ?? 'bottom-right') === 'bottom-left'  ? 'selected' : '' }}>Bottom Left</option>
                                                <option value="top-right"    {{ ($landing->cart_position ?? 'bottom-right') === 'top-right'    ? 'selected' : '' }}>Top Right</option>
                                                <option value="top-left"     {{ ($landing->cart_position ?? 'bottom-right') === 'top-left'     ? 'selected' : '' }}>Top Left</option>
                                                <option value="bottom-bar"   {{ ($landing->cart_position ?? 'bottom-right') === 'bottom-bar'   ? 'selected' : '' }}>Full Width Bottom Bar</option>
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="cart_x_offset" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Horizontal Offset (px)</label>
                                                <input type="number" name="cart_x_offset" id="cart_x_offset" value="{{ $landing->cart_x_offset ?? 20 }}"
                                                       class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                            </div>
                                            <div>
                                                <label for="cart_y_offset" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vertical Offset (px)</label>
                                                <input type="number" name="cart_y_offset" id="cart_y_offset" value="{{ $landing->cart_y_offset ?? 20 }}"
                                                       class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-gray-100 dark:border-white/[0.06]">
                                            @foreach([
                                                ['name' => 'cart_bg_color',       'label' => 'Cart Background Color',        'default' => '#ffffff'],
                                                ['name' => 'cart_text_color',     'label' => 'Cart Text Color',              'default' => '#000000'],
                                                ['name' => 'cart_btn_color',      'label' => 'Checkout Button Color',        'default' => '#3b82f6'],
                                                ['name' => 'cart_btn_text_color', 'label' => 'Checkout Button Text Color',   'default' => '#ffffff'],
                                            ] as $color)
                                            <div>
                                                <label for="{{ $color['name'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $color['label'] }}</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="color" name="{{ $color['name'] }}" id="{{ $color['name'] }}"
                                                           value="{{ old($color['name'], $landing->{$color['name']} ?? $color['default']) }}"
                                                           class="h-9 w-14 p-1 rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer">
                                                    <input type="text" value="{{ old($color['name'], $landing->{$color['name']} ?? $color['default']) }}"
                                                           class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors"
                                                           oninput="document.getElementById('{{ $color['name'] }}').value = this.value">
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Countdown Tab -->
                        <div x-show="tab === 'countdown'" x-cloak style="display: none;">
                            <div class="space-y-6">
                                <div class="bg-gray-50 dark:bg-white/[0.02] p-5 rounded-xl border border-gray-100 dark:border-white/[0.06]">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Countdown Configuration</h3>
                                    <div class="space-y-5">
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input id="countdown_enabled" name="countdown_enabled" type="checkbox" value="1"
                                                   {{ old('countdown_enabled', $landing->countdown_enabled ?? false) ? 'checked' : '' }}
                                                   class="mt-0.5 h-4 w-4 text-brand-orange border-gray-300 rounded focus:ring-brand-orange/20">
                                            <div class="text-sm">
                                                <span class="block font-medium text-gray-900 dark:text-white">Enable Countdown Timer</span>
                                                <span class="block text-gray-500 dark:text-gray-400 text-xs mt-0.5">Show countdown timers on your landing page.</span>
                                            </div>
                                        </label>

                                        <div x-data="{ mode: '{{ $landing->countdown_end_at ? 'fixed' : 'duration' }}' }" class="space-y-4 border-t border-gray-100 dark:border-white/[0.06] pt-4">
                                            <div>
                                                <span class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Timer Mode</span>
                                                <div class="flex items-center gap-4">
                                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" x-model="mode" value="fixed" class="text-brand-orange focus:ring-brand-orange/20">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">Fixed Date & Time</span>
                                                    </label>
                                                    <label class="inline-flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" x-model="mode" value="duration" class="text-brand-orange focus:ring-brand-orange/20">
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">Evergreen Duration (Minutes)</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div x-show="mode === 'fixed'">
                                                <label for="countdown_end_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date & Time</label>
                                                <input type="datetime-local" name="countdown_end_at" id="countdown_end_at"
                                                       value="{{ old('countdown_end_at', $landing->countdown_end_at ? $landing->countdown_end_at->format('Y-m-d\TH:i') : '') }}"
                                                       class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Timer will count down to this specific date.</p>
                                            </div>

                                            <div x-show="mode === 'duration'">
                                                <label for="countdown_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (Minutes)</label>
                                                <input type="number" name="countdown_duration_minutes" id="countdown_duration_minutes"
                                                       value="{{ old('countdown_duration_minutes', $landing->countdown_duration_minutes ?? 15) }}" min="1"
                                                       class="block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-white px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange transition-colors">
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Timer will loop or restart based on this duration.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100 dark:border-white/[0.06]">
                            <a href="{{ route('landings.index') }}"
                               class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-white/8 transition-all">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center justify-center px-5 py-2 bg-brand-orange text-white rounded-lg text-sm font-semibold hover:bg-brand-orange-600 focus:outline-none focus:ring-2 focus:ring-brand-orange/30 transition-all shadow-sm">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
