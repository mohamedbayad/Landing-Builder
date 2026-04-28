<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Subscription Invoices</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">
            <section class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Billing Invoices</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Download invoice PDF for each subscriber plan.</p>
                </div>
                <a href="{{ route('subscriptions.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 dark:border-white/[0.08] text-sm font-semibold text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-colors">
                    Back to Subscriptions
                </a>
            </section>

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.03] text-left text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-4 py-3">Invoice #</th>
                            <th class="px-4 py-3">Subscriber</th>
                            <th class="px-4 py-3">Plan</th>
                            <th class="px-4 py-3">Billing</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                        @forelse($subscriptions as $subscription)
                            @php
                                $amount = match($subscription->billing_cycle) {
                                    'yearly' => (float) ($subscription->plan?->yearly_price ?? 0),
                                    'lifetime' => (float) (($subscription->plan?->yearly_price ?: $subscription->plan?->monthly_price) ?? 0),
                                    default => (float) ($subscription->plan?->monthly_price ?? 0),
                                };
                                $currency = strtoupper((string) (optional($subscription->user?->workspaces->first())->currency ?: 'USD'));
                                $invoiceNumber = 'SUB-' . $subscription->id . '-' . optional($subscription->created_at)->format('Ymd');
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $invoiceNumber }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $subscription->user?->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $subscription->user?->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $subscription->plan?->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ ucfirst($subscription->billing_cycle) }}</td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white font-semibold">{{ $currency }} {{ number_format($amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs {{ in_array($subscription->status, ['active', 'trial']) ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('subscriptions.invoices.download', $subscription) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-md bg-brand-orange text-white text-xs font-semibold hover:bg-brand-orange-600 transition-colors">
                                        Download PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No subscriptions yet, so no invoices to show.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/[0.06]">
                    {{ $subscriptions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
