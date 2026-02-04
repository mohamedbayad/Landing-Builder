{{-- Layout 5: Minimalist (Clean & Simple) --}}
@php
    $s = $landing->settings;
    $enableCard = $s->enable_card ?? true;
    $enablePaypal = $s->enable_paypal ?? true;
    $enableCod = $s->enable_cod ?? false;
    $paypalClientId = $s->paypal_client_id ?? $landing->workspace->paypal_client_id ?? '';
    $stripePubKey = $s->stripe_publishable_key ?? $landing->workspace->stripe_publishable_key ?? '';
    $defaultMethod = '';
    if ($enableCard && $stripePubKey) $defaultMethod = 'card';
    elseif ($enablePaypal && $paypalClientId) $defaultMethod = 'paypal';
    elseif ($enableCod) $defaultMethod = 'cod';
    else $defaultMethod = 'none';
    $currency = $landing->products()->first()->currency ?? 'USD';
    $cart = session('cart');
    $total = $cart ? $cart['total'] : ($product->price ?? 0);
@endphp

<div class="bg-white dark:bg-gray-900 min-h-screen">
    <style>[x-cloak] { display: none !important; }</style>
    
    <div class="max-w-xl mx-auto px-4 py-16">
        <form id="checkout-form" action="{{ route('orders.store') }}" method="POST" x-data="{ paymentMethod: '{{ $defaultMethod }}' }">
            @csrf
            <input type="hidden" name="landing_id" value="{{ $landing->id }}">
            <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
            
            <!-- Simple Header -->
            <div class="text-center mb-12">
                <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 11-4 0v-4m-6 8a2 2 0 100-4 2 2 0 000 4zm10 0a2 2 0 100-4 2 2 0 000 4z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Checkout</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    Total: <span class="font-semibold text-gray-900 dark:text-white">{{ $currency }} {{ number_format($total, 2) }}</span>
                </p>
            </div>
            
            <!-- Minimal Form -->
            <div class="space-y-6">
                @include('landings.public.checkout._fields')
            </div>
            
            <!-- Divider -->
            <div class="my-10 border-t border-gray-200 dark:border-gray-700"></div>
            
            <!-- Payment -->
            <div class="space-y-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wide">Payment</h3>
                @include('landings.public.checkout._payment')
            </div>
            
            <!-- Submit -->
            <div class="mt-10">
                <button type="submit" id="main-submit-btn" class="w-full py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-medium rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                    Pay {{ $currency }} {{ number_format($total, 2) }}
                </button>
                <div class="mt-6 flex items-center justify-center space-x-6 text-xs text-gray-400">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        Secure
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Encrypted
                    </span>
                </div>
            </div>
        </form>
    </div>
</div>
