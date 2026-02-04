{{-- Thank You Layout 4: Full Banner --}}
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
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
    
    {{-- Success Banner --}}
    <div class="bg-gradient-to-r from-green-400 via-green-500 to-emerald-600 py-16 text-center text-white">
        <div class="max-w-4xl mx-auto px-4">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Order Confirmed!</h1>
            <p class="text-xl text-green-100">Thank you for your purchase. We're preparing your order.</p>
            @if(!empty($orderData))
            <p class="mt-4 text-lg">Order <span class="font-bold">{{ $orderData['orderId'] }}</span> placed on {{ $orderData['date'] }}</p>
            @endif
        </div>
    </div>

    {{-- Order Content --}}
    <div class="max-w-4xl mx-auto px-4 -mt-8">
        @if(!empty($orderData))
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
            {{-- Order Summary Table --}}
            @if($showSummary && !empty($orderData['items']))
            <div class="p-6 md:p-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Order Summary
                </h2>
                <table class="w-full">
                    <thead>
                        <tr class="text-sm text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left pb-3">Product</th>
                            <th class="text-center pb-3">Quantity</th>
                            <th class="text-right pb-3">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($orderData['items'] as $item)
                        <tr>
                            <td class="py-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mr-4">
                                        <span class="font-bold text-gray-500">{{ substr($item['name'] ?? 'P', 0, 2) }}</span>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $item['name'] ?? 'Product' }}</span>
                                </div>
                            </td>
                            <td class="py-4 text-center text-gray-600 dark:text-gray-400">{{ $item['qty'] ?? 1 }}</td>
                            <td class="py-4 text-right font-medium text-gray-900 dark:text-white">
                                {{ $orderData['currency'] }} {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="border-t-2 border-gray-200 dark:border-gray-700 mt-4 pt-4 flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-900 dark:text-white">Total Paid</span>
                    <span class="text-2xl font-bold text-green-600">{{ $orderData['currency'] }} {{ $orderData['amount'] }}</span>
                </div>
            </div>
            @endif

            {{-- Customer & Payment Details --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 p-6 md:p-8">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Customer
                        </h3>
                        <div class="text-sm space-y-1">
                            <p class="text-gray-900 dark:text-white">{{ $orderData['customerName'] }}</p>
                            <p class="text-gray-600 dark:text-gray-400">{{ $orderData['email'] }}</p>
                            @if($orderData['phone'])<p class="text-gray-600 dark:text-gray-400">{{ $orderData['phone'] }}</p>@endif
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            Payment
                        </h3>
                        <div class="text-sm space-y-1">
                            <p class="text-gray-900 dark:text-white">{{ $orderData['paymentMethod'] }}</p>
                            <p class="text-gray-600 dark:text-gray-400">Status: <span class="text-green-600 font-medium">{{ $orderData['status'] }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="p-6 md:p-8 space-y-3">
                <div class="flex flex-col sm:flex-row gap-4">
                    @if($showInvoice && !empty($orderData['invoiceUrl']))
                    <a href="{{ $orderData['invoiceUrl'] }}" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Invoice
                    </a>
                    @endif
                    <a href="/" class="flex-1 inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-white rounded-lg font-semibold hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Continue Shopping
                    </a>
                </div>
                {{-- WhatsApp Button --}}
                @if($whatsappEnabled ?? false && $whatsappUrl ?? false)
                <a href="{{ $whatsappUrl }}" target="_blank" class="w-full inline-flex items-center justify-center px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                    Confirm on WhatsApp
                </a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
