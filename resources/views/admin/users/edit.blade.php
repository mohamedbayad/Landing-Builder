<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Edit User</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password (optional)</label>
                            <input type="password" name="password" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company / Brand</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                                <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Suspended</option>
                                <option value="pending" @selected(old('status', $user->status) === 'pending')>Pending</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                            <select name="role_id" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id', $user->roles->first()?->id) == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription Plan</label>
                            <select name="subscription_plan_id" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="">No plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('subscription_plan_id', $activeSubscription?->plan_id) == $plan->id)>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription Status</label>
                            @php($currentStatus = old('subscription_status', $activeSubscription?->status ?? 'active'))
                            <select name="subscription_status" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="active" @selected($currentStatus === 'active')>Active</option>
                                <option value="trial" @selected($currentStatus === 'trial')>Trial</option>
                                <option value="paused" @selected($currentStatus === 'paused')>Paused</option>
                                <option value="expired" @selected($currentStatus === 'expired')>Expired</option>
                                <option value="canceled" @selected($currentStatus === 'canceled')>Canceled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Cycle</label>
                            @php($currentCycle = old('billing_cycle', $activeSubscription?->billing_cycle ?? 'monthly'))
                            <select name="billing_cycle" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="monthly" @selected($currentCycle === 'monthly')>Monthly</option>
                                <option value="yearly" @selected($currentCycle === 'yearly')>Yearly</option>
                                <option value="lifetime" @selected($currentCycle === 'lifetime')>Lifetime</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom Permissions</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-auto p-3 rounded-lg border border-gray-200 dark:border-white/[0.08]">
                            @php($selectedPermissions = old('permission_ids', $user->permissions->pluck('id')->all()))
                            @foreach($permissions as $group => $groupPermissions)
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">{{ $group }}</p>
                                    <div class="space-y-1">
                                        @foreach($groupPermissions as $permission)
                                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" {{ in_array($permission->id, $selectedPermissions) ? 'checked' : '' }} class="rounded border-gray-300" />
                                                <span>{{ $permission->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-lg border border-gray-200 dark:border-white/[0.08] text-sm font-medium text-gray-700 dark:text-gray-200">Cancel</a>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
