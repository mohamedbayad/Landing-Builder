{{-- Thank You Layout 3: Split View --}}
<div class="min-h-screen flex flex-col lg:flex-row">
    {{-- WhatsApp Redirect Overlay --}}
    @if($whatsappEnabled ?? false && $autoWhatsappRedirect ?? false && $whatsappUrl ?? false)
    <div id="whatsapp-redirect-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 items-center justify-center" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md mx-4 text-center shadow-2xl">
            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Redirecting to WhatsApp</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                You'll be redirected in <span id="whatsapp-countdown" class="font-bold text-green-600">{{ intval(($redirectDelay ?? 5000) / 1000) }}</span> seconds...
            </p>
            <button id="whatsapp-cancel-redirect" class="text-gray-500 hover:text-gray-700 text-sm underline">Cancel</button>
        </div>
    </div>
    @endif

    {{-- Left Panel: Success Message --}}
    <div class="lg:w-1/2 bg-gradient-to-br from-green-500 to-emerald-600 p-8 lg:p-16 flex items-center justify-center">
        <div class="text-center text-white max-w-md">
            <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold mb-4">Thank You!</h1>
            <p class="text-xl text-green-100 mb-8">Your order has been placed successfully</p>
            
            @if(!empty($orderData))
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-left">
                <p class="text-sm text-green-100">Order Number</p>
                <p class="text-2xl font-bold">{{ $orderData['orderId'] }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Right Panel: Order Details --}}
    <div class="lg:w-1/2 bg-white dark:bg-gray-800 p-8 lg:p-16 flex items-center">
        <div class="w-full max-w-lg mx-auto">
            @if(!empty($orderData))
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Order Details</h2>

            {{-- Order Summary Table --}}
            @if($showSummary && !empty($orderData['items']))
            <div class="mb-6">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Items</h3>
                <table class="w-full">
                    <thead>
                        <tr class="text-sm text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left pb-2">Product</th>
                            <th class="text-center pb-2">Qty</th>
                            <th class="text-right pb-2">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderData['items'] as $item)
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-3 text-gray-900 dark:text-white">{{ $item['name'] ?? 'Product' }}</td>
                            <td class="py-3 text-center text-gray-500">{{ $item['qty'] ?? 1 }}</td>
                            <td class="py-3 text-right text-gray-900 dark:text-white">{{ $orderData['currency'] }} {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-lg font-bold text-gray-900 dark:text-white">Total Paid</span>
                    <span class="text-2xl font-bold text-green-600">{{ $orderData['currency'] }} {{ $orderData['amount'] }}</span>
                </div>
            </div>
            @endif

            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 mb-6">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Customer Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Name</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $orderData['customerName'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Email</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $orderData['email'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Phone</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $orderData['phone'] ?: 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400">Payment</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $orderData['paymentMethod'] }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex gap-4">
                    @if($showInvoice && !empty($orderData['invoiceUrl']))
                    <a href="{{ $orderData['invoiceUrl'] }}" class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Invoice
                    </a>
                    @endif
                    <a href="/" class="flex-1 inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-white rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
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
            @endif
        </div>
    </div>
</div>
