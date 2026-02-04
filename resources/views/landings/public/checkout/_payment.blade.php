{{-- Shared Payment Methods Partial --}}
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

@if($defaultMethod === 'none')
    <p class="text-red-500">No payment methods are configured or enabled.</p>
@endif

<div class="space-y-4" x-data="{ paymentMethod: '{{ $defaultMethod }}' }">
    @if($enableCard && $stripePubKey)
    <div class="border rounded-lg p-4 cursor-pointer transition-colors" 
         :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900 dark:border-indigo-400': paymentMethod === 'card', 'border-gray-200 dark:border-gray-700': paymentMethod !== 'card' }"
         @click="paymentMethod = 'card'">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input type="radio" name="payment_method" value="card" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" :checked="paymentMethod === 'card'">
                <label class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Credit Card (Stripe)</label>
            </div>
        </div>
        <div class="mt-4" x-show="paymentMethod === 'card'" x-transition>
            <div id="payment-element" class="p-4 border rounded bg-white"></div>
            <div id="stripe-error-message" class="text-red-500 text-sm mt-2 hidden"></div>
        </div>
    </div>
    @endif

    @if($enablePaypal && $paypalClientId)
    <div class="border rounded-lg p-4 cursor-pointer transition-colors"
         :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900 dark:border-indigo-400': paymentMethod === 'paypal', 'border-gray-200 dark:border-gray-700': paymentMethod !== 'paypal' }"
         @click="paymentMethod = 'paypal'">
        <div class="flex items-center">
            <input type="radio" name="payment_method" value="paypal" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" :checked="paymentMethod === 'paypal'">
            <label class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">PayPal</label>
        </div>
        <div class="mt-4" x-show="paymentMethod === 'paypal'" x-transition>
            <div id="paypal-button-container" class="mt-2" style="max-width: 300px;"></div>
        </div>
    </div>
    @endif

    @if($enableCod)
    <div class="border rounded-lg p-4 cursor-pointer transition-colors"
         :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900 dark:border-indigo-400': paymentMethod === 'cod', 'border-gray-200 dark:border-gray-700': paymentMethod !== 'cod' }"
         @click="paymentMethod = 'cod'">
        <div class="flex items-center">
            <input type="radio" name="payment_method" value="cod" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" :checked="paymentMethod === 'cod'">
            <label class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Cash on Delivery (COD)</label>
        </div>
        <div class="mt-4" x-show="paymentMethod === 'cod'" x-transition>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pay with cash when your order arrives.</p>
        </div>
    </div>
    @endif
</div>
