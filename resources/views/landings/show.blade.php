<x-app-layout>
    <x-slot name="header">
        @php
            $source = (string) ($landing->source ?? '');
            $canSyncTemplate = !empty($landing->template_id)
                || str_starts_with($source, 'remote-template:')
                || str_starts_with($source, 'local-template:')
                || $source === 'template'
                || preg_match('/\s*-\s*copy$/i', (string) $landing->name);
        @endphp
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
                {{ $landing->name }}
            </h2>
            <div class="flex items-center gap-3">
                @if($canSyncTemplate)
                    <form action="{{ route('landings.templates.sync', $landing) }}" method="POST" onsubmit="return confirm('Sync this landing with its source template? This will update page content and styles.');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-orange text-white rounded-lg text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">
                            Sync Template
                        </button>
                    </form>
                @endif
                <a href="{{ route('landings.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-orange dark:text-gray-400 transition-colors">
                    &larr; Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

            @if(session('status'))
                <div class="mb-5 p-4 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-100 dark:border-green-500/20 text-sm text-green-700 dark:text-green-400">
                    {{ session('status') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 p-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 text-sm text-red-700 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Pages</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage the pages within this landing.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-white/[0.06]">
                        <thead class="bg-gray-50 dark:bg-white/[0.02]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Page Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                            @foreach($landing->pages as $page)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $page->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/[0.06] dark:text-gray-400">
                                            {{ ucfirst($page->type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-gray-500 dark:text-gray-400">
                                            @if($landing->is_main && $page->type === 'index')
                                                /
                                            @elseif($landing->is_main)
                                                /{{ $page->slug }}
                                            @elseif($page->type === 'index')
                                                /p/{{ $landing->slug }}
                                            @else
                                                /p/{{ $landing->slug }}/{{ $page->slug }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="{{ route('landings.preview', [$landing, $page]) }}" target="_blank"
                                               class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 font-medium transition-colors">
                                                Preview
                                            </a>
                                            <a href="{{ route('landings.pages.edit', [$landing, $page]) }}"
                                               class="text-sm font-semibold text-brand-orange hover:text-brand-orange-600 transition-colors">
                                                Builder
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
