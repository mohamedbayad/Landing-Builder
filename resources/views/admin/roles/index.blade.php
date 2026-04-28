<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Roles & Permissions</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-4">
            @if(session('status'))
                <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                @foreach($roles as $role)
                    <section class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $role->name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $role->description }}</p>

                        <form method="POST" action="{{ route('roles-permissions.update', $role) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="max-h-72 overflow-auto p-3 rounded-lg border border-gray-200 dark:border-white/[0.08] space-y-4">
                                @foreach($permissions as $group => $groupPermissions)
                                    <div>
                                        <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">{{ $group }}</p>
                                        <div class="space-y-1">
                                            @foreach($groupPermissions as $permission)
                                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                    <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }} class="rounded border-gray-300" />
                                                    <span>{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Save Permissions</button>
                            </div>
                        </form>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
