<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Builder Templates</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Use templates that your role and subscription can access.</p>
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasPermission('tech.manage'))
                    <a href="{{ route('templates.my') }}" class="px-4 py-2 rounded-lg border border-gray-200 dark:border-white/[0.08] text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/[0.05]">My Templates</a>
                @endif
                @if(auth()->user()->hasAnyRole(['super-admin', 'admin']))
                    <form action="{{ route('templates.repair-upload') }}" method="POST" class="inline-block" onsubmit="return confirm('Run template upload repair now?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg border border-amber-300 text-amber-700 bg-amber-50 text-sm font-semibold hover:bg-amber-100">
                            Repair Upload Issues
                        </button>
                    </form>
                    <a href="{{ route('templates.create') }}" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Add Template</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @if(session('status'))
                <x-ui.alert type="success" class="mb-6" dismissible>{{ session('status') }}</x-ui.alert>
            @endif
            @if(session('error'))
                <x-ui.alert type="error" class="mb-6" dismissible>{{ session('error') }}</x-ui.alert>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($templates as $template)
                    <article class="bg-white dark:bg-[#161B22] border border-gray-100 dark:border-white/[0.06] rounded-xl overflow-hidden shadow-sm">
                        <div class="h-44 bg-gray-100 dark:bg-[#0D1117] overflow-hidden">
                            @if($template->preview_image_path)
                                <img src="{{ Str::startsWith($template->preview_image_path, ['http://', 'https://']) ? $template->preview_image_path : Storage::url($template->preview_image_path) }}" alt="{{ $template->name }}" class="w-full h-full object-cover" />
                            @else
                                <div class="h-full flex items-center justify-center text-sm text-gray-400">No thumbnail</div>
                            @endif
                        </div>
                        <div class="p-5 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $template->name }}</h3>
                                <span class="text-xs px-2 py-1 rounded-full {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">{{ $template->is_active ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $template->description ?: 'No description provided.' }}</p>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-white/[0.06] text-gray-600 dark:text-gray-300">{{ ucfirst($template->visibility) }}</span>
                                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-white/[0.06] text-gray-600 dark:text-gray-300">{{ $template->category ?: 'general' }}</span>
                                <span class="px-2 py-1 rounded bg-gray-100 dark:bg-white/[0.06] text-gray-600 dark:text-gray-300">{{ $template->pages_count }} pages</span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @forelse($template->plans as $plan)
                                    <span class="text-[11px] px-2 py-1 rounded bg-blue-100 text-blue-800">{{ $plan->name }}</span>
                                @empty
                                    <span class="text-[11px] px-2 py-1 rounded bg-amber-100 text-amber-800">All plans</span>
                                @endforelse
                            </div>
                            <div class="flex items-center gap-2 pt-2">
                                <form action="{{ route('templates.import', $template->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Use Template</button>
                                </form>
                                @if(auth()->user()->hasAnyRole(['super-admin', 'admin']))
                                    <a href="{{ route('templates.edit', $template) }}" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/[0.08] text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/[0.05]">Edit</a>
                                    <form action="{{ route('templates.destroy', $template) }}" method="POST" class="inline-block" onsubmit="return confirm('Remove this template permanently? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-2 rounded-lg border border-red-200 text-red-700 text-sm hover:bg-red-50 dark:border-red-500/30 dark:text-red-300 dark:hover:bg-red-500/10">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-gray-300 dark:border-white/[0.12] p-10 text-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">No templates available</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Upload a template under Landing Builder to get started.</p>
                        @if(auth()->user()->hasAnyRole(['super-admin', 'admin']))
                            <a href="{{ route('templates.create') }}" class="inline-block mt-4 px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Upload Template ZIP</a>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
