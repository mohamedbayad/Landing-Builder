{{-- Thank You Layout 2: Simple Card --}}
<div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex items-center justify-center py-12 px-4">
    {{-- WhatsApp Redirect Overlay --}}
    @if($whatsappEnabled ?? false && $autoWhatsappRedirect ?? false && $whatsappUrl ?? false)
    <div id="whatsapp-redirect-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 items-center justify-center" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md mx-4 text-center shadow-2xl">
            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Redirecting to WhatsApp</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                You'll be redirected in <span id="whatsapp-countdown" class="font-bold text-green-600">{{ intval(($redirectDelay ?? 5000) / 1000) }}</span> seconds...
            </p>
            <button id="whatsapp-cancel-redirect" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-sm underline">
                Cancel and stay here
            </button>
        </div>
    </div>
    @endif

    <div class="max-w-lg w-full">
        {{-- Clean Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-green-500 px-6 py-8 text-center">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white">Order Confirmed!</h1>
                <p class="text-green-100 mt-2">Thank you for your purchase</p>
            </div>

            {{-- Body --}}
            <div class="p-6">
                @if(!empty($orderData))
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Order ID</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $orderData['orderId'] }}</p>
                </div>

                {{-- Order Summary Table --}}
                @if($showSummary && !empty($orderData['items']))
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Items Ordered</h3>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-gray-500 dark:text-gray-400">
                                <th class="text-left pb-2">Product</th>
                                <th class="text-center pb-2">Qty</th>
                                <th class="text-right pb-2">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderData['items'] as $item)
                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                <td class="py-2 text-gray-900 dark:text-white">{{ $item['name'] ?? 'Product' }}</td>
                                <td class="py-2 text-center text-gray-600 dark:text-gray-400">{{ $item['qty'] ?? 1 }}</td>
                                <td class="py-2 text-right text-gray-900 dark:text-white">{{ $orderData['currency'] }} {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-2 pt-2 flex justify-between font-bold">
                        <span class="text-gray-900 dark:text-white">Total</span>
                        <span class="text-green-600">{{ $orderData['currency'] }} {{ $orderData['amount'] }}</span>
                    </div>
                </div>
                @endif

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Customer</span>
                        <span class="text-gray-900 dark:text-white">{{ $orderData['customerName'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Email</span>
                        <span class="text-gray-900 dark:text-white">{{ $orderData['email'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Payment</span>
                        <span class="text-gray-900 dark:text-white">{{ $orderData['paymentMethod'] }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 space-y-3">
                <div class="flex gap-3">
                    @if($showInvoice && !empty($orderData['invoiceUrl']))
                    <a href="{{ $orderData['invoiceUrl'] }}" class="flex-1 text-center py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                        Download Invoice
                    </a>
                    @endif
                    <a href="/" class="flex-1 text-center py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                        Home
                    </a>
                </div>
                {{-- WhatsApp Button --}}
                @if($whatsappEnabled ?? false && $whatsappUrl ?? false)
                <a href="{{ $whatsappUrl }}" target="_blank" class="block w-full text-center py-2 bg-green-500 text-white rounded-lg font-medium hover:bg-green-600 transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    Confirm on WhatsApp
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
