<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Edit Template</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage visibility, plan access, and publishing status.</p>
            </div>
            <a href="{{ route('templates.index') }}" class="px-4 py-2 rounded-lg border border-gray-200 dark:border-white/[0.08] text-sm font-medium text-gray-700 dark:text-gray-200">Back</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8 space-y-4">
            @if(session('status'))
                <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
            @endif

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                <form method="POST" action="{{ route('templates.update', $template) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template Name</label>
                            <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <input type="text" name="category" value="{{ old('category', $template->category) }}" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="4" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">{{ old('description', $template->description) }}</textarea>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Access (Username / Email)</label>
                        <div class="rounded-lg border border-gray-200 dark:border-white/[0.08] p-3 bg-gray-50 dark:bg-[#0D1117]">
                            <input
                                id="client-search-edit"
                                type="text"
                                placeholder="Search client by username or email..."
                                class="w-full rounded-lg border-gray-300 dark:bg-[#0B1220] dark:border-white/[0.08] dark:text-white mb-2"
                            />
                            <div id="client-list-edit" class="max-h-56 overflow-y-auto space-y-1">
                                @php($selectedClientIdsOld = array_map('intval', old('allowed_user_ids', $selectedClientIds ?? [])))
                                @forelse($clients ?? [] as $client)
                                    <label
                                        class="client-item-edit flex items-center justify-between gap-3 p-2 rounded border border-gray-200 dark:border-white/[0.08] bg-white dark:bg-[#0B1220]"
                                        data-search="{{ strtolower(trim(($client->name ?? '') . ' ' . ($client->email ?? ''))) }}"
                                    >
                                        <span class="flex items-center gap-2 min-w-0">
                                            <input
                                                type="checkbox"
                                                name="allowed_user_ids[]"
                                                value="{{ $client->id }}"
                                                {{ in_array((int) $client->id, $selectedClientIdsOld, true) ? 'checked' : '' }}
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
                            <p id="client-count-edit" class="mt-2 text-xs text-gray-500 dark:text-gray-400"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Email/Domain Rules</label>
                            <textarea name="allowed_emails_text" rows="3" placeholder="client@example.com&#10;@gmail.com" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">{{ old('allowed_emails_text', $allowedEmailsText ?? '') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional extra rules. If empty and no client selected, all emails can access (subject to visibility/plan).</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Visibility</label>
                            <select name="visibility" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white">
                                <option value="public" @selected(old('visibility', $template->visibility) === 'public')>Public</option>
                                <option value="private" @selected(old('visibility', $template->visibility) === 'private')>Private</option>
                                <option value="internal" @selected(old('visibility', $template->visibility) === 'internal')>Internal</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2 pt-7">
                            <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} class="rounded border-gray-300" />
                            <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Enabled</label>
                        </div>
                    </div>

                    @if(isset($plans) && $plans->count())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attach to Plans</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($plans as $plan)
                                    <label class="flex items-center gap-2 p-2 rounded border border-gray-200 dark:border-white/[0.08]">
                                        <input type="checkbox" name="plan_ids[]" value="{{ $plan->id }}" {{ in_array($plan->id, old('plan_ids', $selectedPlans ?? [])) ? 'checked' : '' }} class="rounded border-gray-300" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $plan->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Replace Thumbnail</label>
                        <input type="file" name="thumbnail" accept="image/*" class="w-full rounded-lg border-gray-300 dark:bg-[#0D1117] dark:border-white/[0.08] dark:text-white" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand-orange text-white text-sm font-semibold hover:bg-brand-orange-600">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('client-search-edit');
            const items = Array.from(document.querySelectorAll('.client-item-edit'));
            const count = document.getElementById('client-count-edit');

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
