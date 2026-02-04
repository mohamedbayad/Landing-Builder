{{-- Thank You Layout 1: Confetti Celebration --}}
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 py-16 px-4 relative overflow-hidden">
    {{-- Confetti Animation --}}
    <style>
        @keyframes confetti-fall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            animation: confetti-fall 4s ease-in-out infinite;
        }
        .confetti:nth-child(1) { left: 10%; background: #f59e0b; animation-delay: 0s; border-radius: 50%; }
        .confetti:nth-child(2) { left: 20%; background: #ec4899; animation-delay: 0.5s; }
        .confetti:nth-child(3) { left: 30%; background: #10b981; animation-delay: 1s; border-radius: 50%; }
        .confetti:nth-child(4) { left: 40%; background: #3b82f6; animation-delay: 1.5s; }
        .confetti:nth-child(5) { left: 50%; background: #8b5cf6; animation-delay: 0.3s; border-radius: 50%; }
        .confetti:nth-child(6) { left: 60%; background: #ef4444; animation-delay: 0.8s; }
        .confetti:nth-child(7) { left: 70%; background: #06b6d4; animation-delay: 1.2s; border-radius: 50%; }
        .confetti:nth-child(8) { left: 80%; background: #f97316; animation-delay: 0.6s; }
        .confetti:nth-child(9) { left: 90%; background: #84cc16; animation-delay: 1.8s; border-radius: 50%; }
        .confetti:nth-child(10) { left: 15%; background: #d946ef; animation-delay: 2s; }
    </style>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>

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

    <div class="max-w-2xl mx-auto relative z-10">
        {{-- Success Icon --}}
        <div class="text-center mb-8">
            <div class="w-24 h-24 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">🎉 Thank You!</h1>
            <p class="text-xl text-gray-600 dark:text-gray-300">Your order has been confirmed</p>
        </div>

        {{-- Order Details Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 mb-6">
            @if(!empty($orderData))
            <div class="text-center mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Order Number</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $orderData['orderId'] }}</p>
            </div>

            {{-- Order Summary Table --}}
            @if($showSummary && !empty($orderData['items']))
            <div class="mb-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Order Summary</h3>
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Product</th>
                            <th class="text-center py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Qty</th>
                            <th class="text-right py-2 text-sm font-medium text-gray-500 dark:text-gray-400">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderData['items'] as $item)
                        <tr class="border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <td class="py-3 text-gray-900 dark:text-white">{{ $item['name'] ?? 'Product' }}</td>
                            <td class="py-3 text-center text-gray-600 dark:text-gray-400">{{ $item['qty'] ?? 1 }}</td>
                            <td class="py-3 text-right font-medium text-gray-900 dark:text-white">
                                {{ $orderData['currency'] }} {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1), 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                            <td colspan="2" class="py-4 text-lg font-bold text-gray-900 dark:text-white">Total</td>
                            <td class="py-4 text-right text-xl font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $orderData['currency'] }} {{ $orderData['amount'] }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif

            {{-- Customer Details --}}
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Customer</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $orderData['customerName'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Email</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $orderData['email'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Date</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $orderData['date'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Payment</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $orderData['paymentMethod'] }}</p>
                </div>
            </div>
            @else
            <div class="text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">Order details will appear here once processed.</p>
            </div>
            @endif
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            {{-- Invoice Button --}}
            @if($showInvoice && !empty($orderData['invoiceUrl']))
            <a href="{{ $orderData['invoiceUrl'] }}" class="inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Invoice
            </a>
            @endif

            {{-- WhatsApp Button (Always show if enabled) --}}
            @if($whatsappEnabled ?? false && $whatsappUrl ?? false)
            <a href="{{ $whatsappUrl }}" target="_blank" class="inline-flex items-center justify-center px-6 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Confirm on WhatsApp
            </a>
            @endif

            <a href="/" class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                Continue Shopping
            </a>
        </div>
    </div>
</div>
