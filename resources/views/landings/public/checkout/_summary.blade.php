{{-- Shared Order Summary Partial --}}
@php
    $currency = $landing->products()->first()->currency ?? 'USD';
    $cart = session('cart');
    $hasCart = $cart && !empty($cart['items']);
@endphp

@if($hasCart)
    <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
        @foreach($cart['items'] as $item)
        <div class="flex py-4 border-b border-gray-100 dark:border-gray-700 last:border-0">
            <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
                <span class="text-xs font-bold text-gray-500">{{ substr($item['name'], 0, 2) }}</span>
            </div>
            <div class="ml-4 flex flex-1 flex-col justify-center">
                <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                    <h3>{{ $item['name'] }}</h3>
                    <p class="ml-4">{{ $currency }} {{ number_format($item['price'] * $item['qty'], 2) }}</p>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Qty: {{ $item['qty'] }}</p>
                    <p class="text-xs text-gray-400">{{ $currency }} {{ number_format($item['price'], 2) }} each</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
            <p>Subtotal</p>
            <p>{{ $currency }} {{ number_format($cart['total'], 2) }}</p>
        </div>
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
            <p>Shipping</p>
            <p class="text-green-600">Free</p>
        </div>
        <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white pt-4 border-t border-gray-100 dark:border-gray-700">
            <p>Total</p>
            <p class="text-xl">{{ $currency }} {{ number_format($cart['total'], 2) }}</p>
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" id="main-submit-btn" class="w-full flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
            Pay {{ $currency }} {{ number_format($cart['total'], 2) }}
        </button>
        <p class="mt-4 text-center text-xs text-gray-500">
            <svg class="inline-block w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
            Secure 256-bit SSL Encrypted payment
        </p>
    </div>
@elseif($product)
    <div class="flex py-6">
        <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900 flex items-center justify-center">
            <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <div class="ml-4 flex flex-1 flex-col">
            <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                <h3>{{ $product->name }}</h3>
                <p class="ml-4">{{ $product->currency }} {{ $product->price }}</p>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $product->description ?? 'One-time payment' }}</p>
        </div>
    </div>

    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-4">
        <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
            <p>Total</p>
            <p class="text-xl">{{ $product->currency }} {{ $product->price }}</p>
        </div>
    </div>

    <div class="mt-6">
        <button type="submit" id="main-submit-btn" class="w-full flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
            Pay {{ $product->currency }} {{ $product->price }}
        </button>
    </div>
@else
    <div class="text-center py-10">
        <p class="text-gray-500">No product selected.</p>
    </div>
@endif
