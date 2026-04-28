<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Add Template</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Upload a ZIP and publish it in the landing builder template library.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                <form method="POST" action="{{ route('templates.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <input type="text" name="category" value="{{ old('category', 'general') }}" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="4" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">{{ old('description') }}</textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Access (Username / Email)</label>
                        <div class="rounded-lg border border-gray-200 dark:border-white/[0.08] p-3 bg-gray-50 dark:bg-[#0D1117]">
                            <input
                                id="client-search-create"
                                type="text"
                                placeholder="Search client by username or email..."
                                class="w-full rounded-lg border-gray-300 dark:bg-[#0B1220] dark:border-white/[0.08] dark:text-white mb-2"
                            />
                            <div id="client-list-create" class="max-h-56 overflow-y-auto space-y-1">
                                @forelse($clients ?? [] as $client)
                                    <label
                                        class="client-item-create flex items-center justify-between gap-3 p-2 rounded border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0B1220]"
                                        data-search="{{ strtolower(trim(($client->name ?? '') . ' ' . ($client->email ?? ''))) }}"
                                    >
                                        <span class="flex items-center gap-2 min-w-0">
                                            <input
                                                type="checkbox"
                                                name="allowed_user_ids[]"
                                                value="{{ $client->id }}"
                                                {{ in_array((int) $client->id, array_map('intval', old('allowed_user_ids', [])), true) ? 'checked' : '' }}
                                                class="rounded border-gray-300"
                                            />
                                            <span class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $client->name ?: 'Unnamed' }}</span>
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $client->email }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-500 dark:text-gray-400">No clients found.</p>
                                @endforelse
                            </div>
                            <p id="client-count-create" class="mt-2 text-xs text-gray-500 dark:text-gray-400"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Email/Domain Rules (optional)</label>
                            <textarea name="allowed_emails_text" rows="3" placeholder="client@example.com&#10;@gmail.com" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">{{ old('allowed_emails_text') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional extra rules. One item per line (or comma/semicolon). Domains like <code>@gmail.com</code> are supported.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Visibility</label>
                            <select name="visibility" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="public" @selected(old('visibility') === 'public')>Public</option>
                                <option value="private" @selected(old('visibility') === 'private')>Private</option>
                                <option value="internal" @selected(old('visibility') === 'internal')>Internal</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-7">
                            <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="rounded border-gray-300" />
                            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Enable after upload</label>
                        </div>
                    </div>

                    @if(isset($plans) && $plans->count())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attach to Plans (optional)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($plans as $plan)
                                    <label class="flex items-center gap-2 p-2 rounded border border-gray-200 dark:border-white/[0.08]">
                                        <input type="checkbox" name="plan_ids[]" value="{{ $plan->id }}" {{ in_array($plan->id, old('plan_ids', [])) ? 'checked' : '' }} class="rounded border-gray-300" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $plan->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template ZIP</label>
                            <input type="file" name="template_zip" accept=".zip" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                            @error('template_zip')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thumbnail (optional)</label>
                            <input type="file" name="thumbnail" accept="image/*" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('templates.index') }}" class="px-4 py-2 rounded-lg border border-gray-200 dark:border-white/[0.08] text-sm font-medium text-gray-700 dark:text-gray-200">Cancel</a>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Upload Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('client-search-create');
            const items = Array.from(document.querySelectorAll('.client-item-create'));
            const count = document.getElementById('client-count-create');

            const refreshCount = () => {
                if (!count) return;
                const selected = items.filter((item) => {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    return checkbox && checkbox.checked;
                }).length;
                count.textContent = selected + ' client' + (selected === 1 ? '' : 's') + ' selected';
            };

            if (search) {
                search.addEventListener('input', function () {
                    const keyword = search.value.toLowerCase().trim();
                    items.forEach((item) => {
                        const haystack = (item.dataset.search || '').toLowerCase();
                        item.style.display = haystack.includes(keyword) ? 'flex' : 'none';
                    });
                });
            }

            items.forEach((item) => {
                const checkbox = item.querySelector('input[type="checkbox"]');
                if (checkbox) checkbox.addEventListener('change', refreshCount);
            });

            refreshCount();
        });
    </script>
</x-app-layout>
