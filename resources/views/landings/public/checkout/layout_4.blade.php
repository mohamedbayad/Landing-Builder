{{-- Layout 4: Full Width with Header --}}
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
@endphp

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <style>[x-cloak] { display: none !important; }</style>
    
    <!-- Header Banner -->
    <div class="bg-indigo-600 text-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Secure Checkout</h1>
                    <p class="text-indigo-200 text-sm">Complete your purchase securely</p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-4">
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                        SSL Secured
                    </div>
                    <div class="flex items-center text-sm">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Money Back Guarantee
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Progress Steps -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center">
                        <span class="bg-indigo-600 text-white w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">1</span>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">Details</span>
                    </div>
                    <div class="w-12 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                    <div class="flex items-center">
                        <span class="bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">2</span>
                        <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">Payment</span>
                    </div>
                    <div class="w-12 h-0.5 bg-gray-300 dark:bg-gray-600"></div>
                    <div class="flex items-center">
                        <span class="bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">3</span>
                        <span class="ml-2 text-sm font-medium text-gray-500 dark:text-gray-400">Confirm</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <form id="checkout-form" action="{{ route('orders.store') }}" method="POST" x-data="{ paymentMethod: '{{ $defaultMethod }}' }">
            @csrf
            <input type="hidden" name="landing_id" value="{{ $landing->id }}">
            <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Customer Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Customer Information</h2>
                        @include('landings.public.checkout._fields')
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Payment Method</h2>
                        @include('landings.public.checkout._payment')
                    </div>
                </div>
                
                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 sticky top-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Order Summary</h2>
                        @include('landings.public.checkout._summary')
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
