<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white leading-tight tracking-tight">
            {{ __('Orders') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">

                @if($orders->isEmpty())
                    <div class="text-center py-16 px-6">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-100 dark:bg-white/[0.06] mb-4">
                            <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">No orders yet</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sales from your landing pages will appear here.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @foreach($orders as $order)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-mono text-xs text-gray-400 dark:text-gray-500">#{{ $order->id }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $order->created_at->format('M d, Y H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $order->customer_name ?? 'Guest' }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $order->customer_email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if(!empty($order->order_items))
                                                <div class="flex flex-col gap-0.5">
                                                    @foreach($order->order_items as $item)
                                                        <div class="text-xs text-gray-900 dark:text-white">
                                                            <span class="font-semibold">{{ $item['qty'] ?? 1 }}x</span> {{ $item['name'] ?? $item['title'] ?? 'Product' }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $order->product->name ?? 'Unknown Product' }}</div>
                                            @endif
                                            <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $order->landing->name ?? 'Unknown Landing' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->currency }} {{ number_format($order->amount, 2) }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $order->payment_provider }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $order->status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : '' }}
                                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400' : '' }}
                                                {{ $order->status === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' : '' }}
                                                {{ $order->status === 'shipped' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400' : '' }}
                                                {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : '' }}
                                                {{ $order->status === 'refunded' ? 'bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-400' : '' }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form action="{{ route('orders.update', $order) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status" onchange="this.form.submit()"
                                                        class="text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-gray-700 dark:text-gray-300 shadow-sm focus:border-brand-orange focus:ring-brand-orange/20 focus:outline-none py-1.5 pl-2 pr-6 transition-colors">
                                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="paid" {{ $order->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                    <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="failed" {{ $order->status === 'failed' ? 'selected' : '' }}>Failed</option>
                                                    <option value="refunded" {{ $order->status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-white/[0.06]">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
