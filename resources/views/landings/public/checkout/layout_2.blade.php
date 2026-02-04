{{-- Layout 2: Centered Single Column --}}
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
@endphp

<div class="bg-gray-50 dark:bg-gray-900 min-h-screen py-12">
    <style>[x-cloak] { display: none !important; }</style>
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <form id="checkout-form" action="{{ route('orders.store') }}" method="POST" x-data="{ paymentMethod: '{{ $defaultMethod }}' }">
            @csrf
            <input type="hidden" name="landing_id" value="{{ $landing->id }}">
            <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Checkout</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Complete your order below</p>
            </div>
            
            <!-- Order Summary (Top) -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6 overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Order Summary</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @include('landings.public.checkout._summary')
                </div>
            </div>
            
            <!-- Customer Details -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6 overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">1</span>
                        Your Information
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @include('landings.public.checkout._fields')
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                        <span class="bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">2</span>
                        Payment
                    </h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    @include('landings.public.checkout._payment')
                </div>
            </div>
        </form>
    </div>
</div>
