<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Add User</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                            <input type="password" name="password" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Company / Brand</label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                            <select name="role_id" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription Plan (optional)</label>
                            <select name="subscription_plan_id" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="">No plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('subscription_plan_id') == $plan->id)>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription Status</label>
                            <select name="subscription_status" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="active">Active</option>
                                <option value="trial">Trial</option>
                                <option value="paused">Paused</option>
                                <option value="expired">Expired</option>
                                <option value="canceled">Canceled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Cycle</label>
                            <select name="billing_cycle" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                                <option value="lifetime">Lifetime</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Custom Permissions (optional)</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-auto p-3 rounded-lg border border-gray-200 dark:border-white/[0.08]">
                            @foreach($permissions as $group => $groupPermissions)
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">{{ $group }}</p>
                                    <div class="space-y-1">
                                        @foreach($groupPermissions as $permission)
                                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" {{ in_array($permission->id, old('permission_ids', [])) ? 'checked' : '' }} class="rounded border-gray-300" />
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
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
