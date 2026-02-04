{{-- Thank You Layout 5: Minimalist --}}
<div class="min-h-screen bg-white dark:bg-gray-900 flex items-center justify-center py-12 px-4">
    {{-- WhatsApp Redirect Overlay --}}
    @if($whatsappEnabled ?? false && $autoWhatsappRedirect ?? false && $whatsappUrl ?? false)
    <div id="whatsapp-redirect-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 items-center justify-center" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md mx-4 text-center shadow-2xl">
            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Redirecting to WhatsApp</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Redirecting in <span id="whatsapp-countdown" class="font-bold text-green-600">{{ intval(($redirectDelay ?? 5000) / 1000) }}</span>s...
            </p>
            <button id="whatsapp-cancel-redirect" class="text-gray-500 hover:text-gray-700 text-sm underline">Cancel</button>
        </div>
    </div>
    @endif

    <div class="max-w-md w-full text-center">
        {{-- Success Icon --}}
        <div class="w-20 h-20 bg-green-50 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-8">
            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        {{-- Simple Message --}}
        <h1 class="text-3xl font-light text-gray-900 dark:text-white mb-2">Thank you</h1>
        <p class="text-gray-500 dark:text-gray-400 mb-8">Your order has been received</p>

        @if(!empty($orderData))
        {{-- Order Info --}}
        <div class="mb-8">
            <p class="text-sm text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">Order</p>
            <p class="text-xl font-medium text-gray-900 dark:text-white">{{ $orderData['orderId'] }}</p>
        </div>

        @if(!empty($orderData['amount']))
        <div class="mb-8">
            <p class="text-sm text-gray-400 dark:text-gray-500 uppercase tracking-wide mb-1">Total</p>
            <p class="text-3xl font-light text-gray-900 dark:text-white">{{ $orderData['currency'] }} {{ $orderData['amount'] }}</p>
        </div>
        @endif

        {{-- Minimal Order List (Table) --}}
        @if($showSummary && !empty($orderData['items']))
        <div class="border-t border-b border-gray-100 dark:border-gray-800 py-6 mb-8 text-left">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-400 dark:text-gray-500">
                        <th class="text-left pb-2 font-normal">Item</th>
                        <th class="text-right pb-2 font-normal">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orderData['items'] as $item)
                    <tr class="border-t border-gray-50 dark:border-gray-800">
                        <td class="py-2 text-gray-600 dark:text-gray-400">{{ $item['name'] ?? 'Product' }}</td>
                        <td class="py-2 text-right text-gray-900 dark:text-white">× {{ $item['qty'] ?? 1 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Minimal Actions --}}
        <div class="space-y-3">
            @if($showInvoice && !empty($orderData['invoiceUrl']))
            <a href="{{ $orderData['invoiceUrl'] }}" class="block w-full py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg font-medium hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors">
                Download Invoice
            </a>
            @endif
            {{-- WhatsApp Button --}}
            @if($whatsappEnabled ?? false && $whatsappUrl ?? false)
            <a href="{{ $whatsappUrl }}" target="_blank" class="block w-full py-3 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors">
                Confirm on WhatsApp
            </a>
            @endif
            <a href="/" class="block w-full py-3 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                ← Back to Store
            </a>
        </div>
        @endif

        {{-- Trust Badge --}}
        <div class="mt-12 flex items-center justify-center text-xs text-gray-400 dark:text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
            </svg>
            Secure transaction
        </div>
    </div>
</div>
