<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Users</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage Subscribers, Admins, Developers, Marketers, and Managers.</p>
            </div>
            @if(auth()->user()->hasPermission('users.create'))
                <a href="{{ route('users.create') }}" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Add User</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-4">
            @if(session('status'))
                <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
            @endif

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.03] text-left text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Role</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Subscription</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                        @foreach($users as $user)
                            @php($activeSubscription = $user->subscriptions->first(fn($subscription) => in_array($subscription->status, ['active', 'trial'])))
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                    @if($user->company_name)
                                        <div class="text-xs text-gray-500">{{ $user->company_name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $user->roles->pluck('name')->implode(', ') ?: 'Unassigned' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $user->status === 'active' ? 'bg-green-100 text-green-800' : ($user->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($user->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    @if($activeSubscription)
                                        {{ $activeSubscription->plan?->name ?? 'Plan #' . $activeSubscription->plan_id }}
                                        <div class="text-xs text-gray-500">{{ ucfirst($activeSubscription->status) }} · {{ ucfirst($activeSubscription->billing_cycle) }}</div>
                                    @else
                                        <span class="text-gray-400">No active subscription</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if(auth()->user()->hasPermission('users.edit'))
                                        <a href="{{ route('users.edit', $user) }}" class="px-3 py-1.5 rounded-md border border-gray-200 dark:border-white/[0.08] text-xs text-gray-700 dark:text-gray-200">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/[0.06]">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
