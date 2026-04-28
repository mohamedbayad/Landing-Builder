<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Subscriptions</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">
            @if(session('status'))
                <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
            @endif

            @if(auth()->user()->hasPermission('subscriptions.create'))
                <section class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                    <div class="flex flex-col gap-1 mb-4">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Create Subscription</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pick subscriber, plan, and clear billing dates.</p>
                    </div>

                    <div class="mb-5 rounded-lg border border-blue-100 bg-blue-50/70 px-4 py-3 text-xs text-blue-900 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-200">
                        <span class="font-semibold">Date meanings:</span>
                        <span class="ml-1">Start = activation day, End = access stop day (optional), Renewal = next billing charge date.</span>
                    </div>

                    <form method="POST" action="{{ route('subscriptions.store') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        @csrf
                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Subscriber</span>
                            <select name="user_id" required class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="">Select user</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Plan</span>
                            <select name="plan_id" required class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="">Select plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Subscription status</span>
                            <select name="status" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="active" @selected(old('status') === 'active')>Active</option>
                                <option value="trial" @selected(old('status') === 'trial')>Trial</option>
                                <option value="paused" @selected(old('status') === 'paused')>Paused</option>
                                <option value="expired" @selected(old('status') === 'expired')>Expired</option>
                                <option value="canceled" @selected(old('status') === 'canceled')>Canceled</option>
                            </select>
                        </label>

                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Billing cycle</span>
                            <select name="billing_cycle" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="monthly" @selected(old('billing_cycle') === 'monthly')>Monthly</option>
                                <option value="yearly" @selected(old('billing_cycle') === 'yearly')>Yearly</option>
                                <option value="lifetime" @selected(old('billing_cycle') === 'lifetime')>Lifetime</option>
                            </select>
                        </label>

                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Payment status</span>
                            <input type="text" name="payment_status" value="{{ old('payment_status') }}" placeholder="paid / pending / failed" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </label>

                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Start date</span>
                            <input type="date" name="starts_at" value="{{ old('starts_at') }}" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </label>

                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">End date (optional)</span>
                            <input type="date" name="ends_at" value="{{ old('ends_at') }}" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </label>

                        <label class="flex flex-col gap-1.5">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">Renewal date</span>
                            <input type="date" name="renews_at" value="{{ old('renews_at') }}" class="rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </label>

                        <div class="md:col-span-2 xl:col-span-4 flex justify-end">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Create Subscription</button>
                        </div>
                    </form>
                </section>
            @endif

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[980px]">
                    <thead class="bg-gray-50 dark:bg-white/[0.03] text-left text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-4 py-3">Subscriber</th>
                            <th class="px-4 py-3">Plan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Cycle</th>
                            <th class="px-4 py-3">Start Date</th>
                            <th class="px-4 py-3">End Date</th>
                            <th class="px-4 py-3">Renewal Date</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                        @foreach($subscriptions as $subscription)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $subscription->user?->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $subscription->user?->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $subscription->plan?->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs {{ in_array($subscription->status, ['active','trial']) ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">{{ ucfirst($subscription->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ ucfirst($subscription->billing_cycle) }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200">{{ optional($subscription->starts_at)->format('d M Y') ?: '-' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200">{{ optional($subscription->ends_at)->format('d M Y') ?: '-' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-700 dark:text-gray-200">{{ optional($subscription->renews_at)->format('d M Y') ?: '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('subscriptions.invoices.download', $subscription) }}"
                                           class="px-3 py-1.5 rounded-md border border-gray-200 dark:border-white/[0.08] text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/[0.04]">
                                            Invoice
                                        </a>
                                        @if(auth()->user()->hasPermission('subscriptions.edit'))
                                            <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="plan_id" value="{{ $subscription->plan_id }}" />
                                                <input type="hidden" name="billing_cycle" value="{{ $subscription->billing_cycle }}" />
                                                <input type="hidden" name="status" value="paused" />
                                                <button type="submit" class="px-3 py-1.5 rounded-md border border-gray-200 dark:border-white/[0.08] text-xs text-gray-700 dark:text-gray-200">Pause</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/[0.06]">
                    {{ $subscriptions->links() }}
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('subscriptions.invoices.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600 transition-colors">
                    Open Subscription Invoices
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
