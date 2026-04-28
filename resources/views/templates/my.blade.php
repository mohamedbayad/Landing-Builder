<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">My Templates</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Templates you uploaded under Landing Builder.</p>
            </div>
            @if(auth()->user()->hasAnyRole(['super-admin', 'admin']))
                <a href="{{ route('templates.create') }}" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Add Template</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.03] text-left text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Visibility</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Pages</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                        @forelse($templates as $template)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ $template->name }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $template->category ?: 'general' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ ucfirst($template->visibility) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">{{ $template->is_active ? 'Enabled' : 'Disabled' }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $template->pages_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('templates.edit', $template) }}" class="px-3 py-1.5 rounded-md border border-gray-200 dark:border-white/[0.08] text-xs text-gray-700 dark:text-gray-200">Edit</a>
                                        <form method="POST" action="{{ route('templates.toggle-status', $template) }}" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1.5 rounded-md bg-gray-900 text-white text-xs">{{ $template->is_active ? 'Disable' : 'Enable' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No templates yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
