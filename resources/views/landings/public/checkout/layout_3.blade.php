{{-- Layout 3: Split Screen 50/50 --}}
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

<div class="min-h-screen flex flex-col lg:flex-row">
    <style>[x-cloak] { display: none !important; }</style>
    
    <!-- Left Panel: Product/Brand Display -->
    <div class="lg:w-1/2 bg-gradient-to-br from-indigo-600 to-purple-700 p-8 lg:p-12 flex flex-col justify-center">
        <div class="max-w-lg mx-auto text-white">
            <h1 class="text-4xl font-bold mb-4">Complete Your Purchase</h1>
            <p class="text-indigo-100 text-lg mb-8">You're one step away from enjoying your order.</p>
            
            <!-- Product Preview -->
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6">
                <h3 class="font-semibold text-lg mb-4">Your Order</h3>
                @if(session('cart') && !empty(session('cart')['items']))
                    @foreach(session('cart')['items'] as $item)
                    <div class="flex items-center mb-3 last:mb-0">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                            <span class="font-bold">{{ substr($item['name'], 0, 2) }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium">{{ $item['name'] }}</p>
                            <p class="text-sm text-indigo-200">Qty: {{ $item['qty'] }}</p>
                        </div>
                        <p class="font-semibold">{{ $landing->products()->first()->currency ?? 'USD' }} {{ number_format($item['price'] * $item['qty'], 2) }}</p>
                    </div>
                    @endforeach
                    <div class="border-t border-white/20 mt-4 pt-4 flex justify-between text-xl font-bold">
                        <span>Total</span>
                        <span>{{ $landing->products()->first()->currency ?? 'USD' }} {{ number_format(session('cart')['total'], 2) }}</span>
                    </div>
                @elseif($product)
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium">{{ $product->name }}</p>
                        </div>
                        <p class="font-semibold text-xl">{{ $product->currency }} {{ $product->price }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Right Panel: Checkout Form -->
    <div class="lg:w-1/2 bg-gray-50 dark:bg-gray-900 p-8 lg:p-12 overflow-y-auto">
        <div class="max-w-lg mx-auto">
            <form id="checkout-form" action="{{ route('orders.store') }}" method="POST" x-data="{ paymentMethod: '{{ $defaultMethod }}' }">
                @csrf
                <input type="hidden" name="landing_id" value="{{ $landing->id }}">
                <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
                
                <!-- Customer Details -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Your Information</h2>
                    @include('landings.public.checkout._fields')
                </div>
                
                <!-- Payment Method -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Payment Method</h2>
                    @include('landings.public.checkout._payment')
                </div>
                
                <!-- Submit -->
                <button type="submit" id="main-submit-btn" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition-colors flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    Complete Order
                </button>
                <p class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
                    Secure 256-bit SSL encrypted payment
                </p>
            </form>
        </div>
    </div>
</div>
