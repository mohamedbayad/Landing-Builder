<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
                Funnel Management: <span class="text-brand-orange">{{ $landing->name }}</span>
            </h2>
            <a href="{{ route('landings.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                &larr; Back to Landings
            </a>
        </div>
    </x-slot>

    <div class="py-10" x-data="{ tab: '{{ old('tab', 'steps') }}' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-5">
            @if(session('status'))
                <x-ui.alert type="success" dismissible>
                    {{ session('status') }}
                </x-ui.alert>
            @endif

            @if($errors->any())
                <x-ui.alert type="error" title="Please review the following issues">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <div class="bg-white dark:bg-[#161B22] shadow-sm rounded-xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                <div class="border-b border-gray-100 dark:border-white/[0.06]">
                    <nav class="flex overflow-x-auto px-2" aria-label="Tabs">
                        <button @click="tab = 'steps'"
                                :class="tab === 'steps' ? 'border-brand-orange text-brand-orange bg-orange-50/50 dark:bg-orange-500/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            Funnel Steps
                        </button>
                        <button @click="tab = 'products'"
                                :class="tab === 'products' ? 'border-brand-orange text-brand-orange bg-orange-50/50 dark:bg-orange-500/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            Products
                        </button>
                        <button @click="tab = 'checkout'"
                                :class="tab === 'checkout' ? 'border-brand-orange text-brand-orange bg-orange-50/50 dark:bg-orange-500/10' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150">
                            Checkout Fields
                        </button>
                    </nav>
                </div>

                <div class="p-6 md:p-8">
                    <div x-show="tab === 'steps'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        @php
                            $sortedPages = $pages->sortBy('funnel_position')->values();
                            $pagesById = $pages->keyBy('id');
                        @endphp

                        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Funnel Flow</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Manage step names, step types, order, and the next-step connection for each page.</p>
                            </div>
                            <button type="button"
                                    @click="$refs.addStepModal.classList.remove('hidden')"
                                    class="inline-flex items-center gap-2 rounded-lg bg-brand-orange px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-orange-600">
                                + Add Step
                            </button>
                        </div>

                        <div class="mb-8 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @forelse($sortedPages as $page)
                                @php
                                    $nextLabel = 'End Funnel';
                                    if ($page->next_landing_page_id && $pagesById->has($page->next_landing_page_id)) {
                                        $next = $pagesById->get($page->next_landing_page_id);
                                        $nextLabel = $next->name;
                                    }
                                @endphp
                                <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Step {{ $page->funnel_position ?? $loop->iteration }}</div>
                                            <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $page->name }}</div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">/{{ $page->slug }}</div>
                                        </div>
                                        <span class="rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-500/20 dark:text-orange-300">
                                            {{ $stepTypes[$page->funnel_step_type ?? 'landing'] ?? ucfirst(str_replace('_', ' ', $page->funnel_step_type ?? 'landing')) }}
                                        </span>
                                    </div>
                                    <div class="mt-3 border-t border-gray-200 pt-3 text-xs text-gray-600 dark:border-white/10 dark:text-gray-300">
                                        Next: <span class="font-medium">{{ $nextLabel }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                                    No steps found yet.
                                </div>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('funnel.steps.update', $landing) }}" class="space-y-4" id="funnel-steps-form">
                            @csrf
                            @method('PUT')

                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                                    <thead class="bg-gray-50 dark:bg-white/[0.03]">
                                        <tr>
                                            <th class="w-14 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Order</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Step</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Next Step</th>
                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="funnel-steps-sortable" class="divide-y divide-gray-100 bg-white dark:divide-white/10 dark:bg-[#161B22]">
                                        @foreach($sortedPages as $index => $page)
                                            <tr class="align-top js-step-row" draggable="true" data-step-row data-page-id="{{ $page->id }}">
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <button type="button"
                                                                class="js-drag-handle inline-flex h-9 w-9 cursor-grab items-center justify-center rounded-lg border border-gray-300 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 active:cursor-grabbing dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                                                                title="Drag to reorder"
                                                                aria-label="Drag to reorder step">
                                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path d="M7 4a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm-1.5 7.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm9-13.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm-1.5 7.5a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                                                            </svg>
                                                        </button>
                                                        <span class="js-row-order inline-flex min-w-[1.8rem] items-center justify-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                                            {{ old("pages.$index.funnel_position", $page->funnel_position ?? ($index + 1)) }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <input type="hidden" name="pages[{{ $index }}][id]" value="{{ $page->id }}">
                                                    <input type="hidden"
                                                           class="js-position-input"
                                                           name="pages[{{ $index }}][funnel_position]"
                                                           value="{{ old("pages.$index.funnel_position", $page->funnel_position ?? ($index + 1)) }}">
                                                    <input type="text"
                                                           name="pages[{{ $index }}][name]"
                                                           value="{{ old("pages.$index.name", $page->name) }}"
                                                           class="mb-2 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">/{{ $page->slug }}</div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <select name="pages[{{ $index }}][funnel_step_type]"
                                                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                                        @foreach($stepTypes as $value => $label)
                                                            <option value="{{ $value }}" {{ old("pages.$index.funnel_step_type", $page->funnel_step_type ?? 'landing') === $value ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <select name="pages[{{ $index }}][next_landing_page_id]"
                                                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                                        <option value="">End Funnel</option>
                                                        @foreach($sortedPages as $candidate)
                                                            @if((int) $candidate->id !== (int) $page->id)
                                                                <option value="{{ $candidate->id }}" {{ (string) old("pages.$index.next_landing_page_id", $page->next_landing_page_id) === (string) $candidate->id ? 'selected' : '' }}>
                                                                    {{ $candidate->name }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center justify-end gap-2">
                                                        <a href="{{ route('landings.pages.edit', [$landing, $page]) }}"
                                                           class="inline-flex items-center rounded-lg bg-brand-orange px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-orange-600">
                                                            Builder
                                                        </a>
                                                        <a href="{{ route('landings.preview', [$landing, $page]) }}"
                                                           target="_blank"
                                                           class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                                                            Preview
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Drag rows using the handle to reorder steps. Position updates automatically.
                            </p>

                            <div class="flex justify-end">
                                <button type="submit" class="rounded-lg bg-brand-orange px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-orange-600">
                                    Save Funnel Flow
                                </button>
                            </div>
                        </form>

                        <div x-ref="addStepModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
                            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-700/70" @click="$refs.addStepModal.classList.add('hidden')"></div>
                                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                                <div class="inline-block w-full max-w-lg transform overflow-hidden rounded-xl bg-white p-6 text-left align-bottom shadow-xl transition-all dark:bg-[#161B22] sm:align-middle">
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Add New Funnel Step</h4>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a new page step and place it in your funnel flow.</p>

                                    <form method="POST" action="{{ route('funnel.steps.store', $landing) }}" class="mt-5 space-y-4">
                                        @csrf

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Step Name</label>
                                            <input type="text" name="name" required
                                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white"
                                                   placeholder="Example: Upsell Offer">
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Slug (optional)</label>
                                            <input type="text" name="slug"
                                                   class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white"
                                                   placeholder="upsell-offer">
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Step Type</label>
                                            <select name="funnel_step_type"
                                                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                                @foreach($stepTypes as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-white/10">
                                            <button type="button"
                                                    @click="$refs.addStepModal.classList.add('hidden')"
                                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                                                Cancel
                                            </button>
                                            <button type="submit" class="rounded-lg bg-brand-orange px-4 py-2 text-sm font-semibold text-white hover:bg-brand-orange-600">
                                                Create Step
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="tab === 'products'" x-cloak style="display: none;">
                        @php
                            $checkoutPage = $pages->firstWhere('type', 'checkout');
                            $checkoutSlug = $checkoutPage?->slug ?? 'checkout';
                            $checkoutPath = $landing->is_main ? '/' . ltrim($checkoutSlug, '/') : '/' . $landing->slug . '/' . ltrim($checkoutSlug, '/');
                            $baseCheckoutUrl = url($checkoutPath);
                        @endphp

                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Product Offers</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Manage the products available on this funnel checkout flow.</p>
                            </div>
                            <button @click="$refs.addProductModal.classList.remove('hidden')" class="rounded-lg bg-brand-orange px-4 py-2 text-sm font-semibold text-white hover:bg-brand-orange-600">
                                + Add Product
                            </button>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                                <thead class="bg-gray-50 dark:bg-white/[0.03]">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Label</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Direct Link</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white dark:divide-white/10 dark:bg-[#161B22]">
                                    @forelse($products as $product)
                                        <tr>
                                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ $product->name }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $product->currency }} {{ number_format((float) $product->price, 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                @if($product->label)
                                                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">{{ $product->label }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                <div class="group flex items-center gap-2">
                                                    <code class="max-w-[220px] truncate rounded border border-gray-200 bg-gray-50 px-2 py-1 text-xs dark:border-white/10 dark:bg-white/[0.03]">{{ $baseCheckoutUrl }}?product={{ $product->id }}</code>
                                                    <button type="button"
                                                            onclick="navigator.clipboard.writeText('{{ $baseCheckoutUrl }}?product={{ $product->id }}'); window.Toast ? window.Toast.success('Link copied') : alert('Link copied');"
                                                            class="text-xs font-semibold text-brand-orange hover:text-brand-orange-600">
                                                        Copy
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <form action="{{ route('funnel.products.destroy', [$landing, $product]) }}" method="POST" onsubmit="event.preventDefault(); window.confirmAction('Delete this product?', this);">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-semibold text-red-500 hover:text-red-600">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No products added yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div x-ref="addProductModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
                            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-700/70" @click="$refs.addProductModal.classList.add('hidden')"></div>
                                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                                <div class="inline-block w-full max-w-lg transform overflow-hidden rounded-xl bg-white p-6 text-left align-bottom shadow-xl transition-all dark:bg-[#161B22] sm:align-middle">
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">Add New Product</h4>
                                    <form action="{{ route('funnel.products.store', $landing) }}" method="POST" class="mt-4 space-y-4">
                                        @csrf

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name</label>
                                            <input type="text" name="name" required class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                        </div>

                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="col-span-2">
                                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                                                <input type="number" step="0.01" name="price" required class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                            </div>
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                                                <select name="currency" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                                    <option value="USD">USD</option>
                                                    <option value="EUR">EUR</option>
                                                    <option value="MAD">MAD</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Badge Label (optional)</label>
                                            <input type="text" name="label" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                        </div>

                                        <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-white/10">
                                            <button type="button"
                                                    @click="$refs.addProductModal.classList.add('hidden')"
                                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10">
                                                Cancel
                                            </button>
                                            <button type="submit" class="rounded-lg bg-brand-orange px-4 py-2 text-sm font-semibold text-white hover:bg-brand-orange-600">
                                                Add Product
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="tab === 'checkout'" x-cloak style="display: none;">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Checkout Form Configuration</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Control which fields are visible and required for checkout.</p>
                        </div>

                        <form action="{{ route('funnel.fields.store', $landing) }}" method="POST">
                            @csrf

                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                                    <thead class="bg-gray-50 dark:bg-white/[0.03]">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Field Name</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Visible</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Required</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Custom Label</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-white/10 dark:bg-[#161B22]">
                                        @foreach($checkoutFields as $field)
                                            <tr>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-700 capitalize dark:text-gray-200">
                                                    {{ str_replace('_', ' ', $field->field_name) }}
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <input type="checkbox" name="fields[{{ $field->field_name }}][enabled]" value="1" {{ $field->is_enabled ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22]">
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <input type="checkbox" name="fields[{{ $field->field_name }}][required]" value="1" {{ $field->is_required ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22]">
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input type="text" name="fields[{{ $field->field_name }}][label]" value="{{ $field->label }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-orange focus:ring-brand-orange/20 dark:border-white/10 dark:bg-[#161B22] dark:text-white">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="rounded-lg bg-brand-orange px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-orange-600">
                                    Save Checkout Fields
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sortableBody = document.getElementById('funnel-steps-sortable');
        if (!sortableBody) return;

        let draggedRow = null;

        const refreshPositions = () => {
            const rows = sortableBody.querySelectorAll('[data-step-row]');
            rows.forEach((row, idx) => {
                const nextPosition = idx + 1;
                const positionInput = row.querySelector('.js-position-input');
                const orderBadge = row.querySelector('.js-row-order');

                if (positionInput) {
                    positionInput.value = String(nextPosition);
                }
                if (orderBadge) {
                    orderBadge.textContent = String(nextPosition);
                }
            });
        };

        refreshPositions();

        sortableBody.addEventListener('dragstart', (event) => {
            const row = event.target.closest('[data-step-row]');
            if (!row) return;

            draggedRow = row;
            row.classList.add('opacity-60');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', row.dataset.pageId || '');
        });

        sortableBody.addEventListener('dragover', (event) => {
            if (!draggedRow) return;
            event.preventDefault();

            const targetRow = event.target.closest('[data-step-row]');
            if (!targetRow || targetRow === draggedRow) return;

            const rect = targetRow.getBoundingClientRect();
            const shouldInsertAfter = event.clientY > rect.top + rect.height / 2;

            if (shouldInsertAfter) {
                targetRow.after(draggedRow);
            } else {
                targetRow.before(draggedRow);
            }
        });

        sortableBody.addEventListener('drop', (event) => {
            if (!draggedRow) return;
            event.preventDefault();
            refreshPositions();
        });

        sortableBody.addEventListener('dragend', () => {
            if (draggedRow) {
                draggedRow.classList.remove('opacity-60');
                draggedRow = null;
            }
            refreshPositions();
        });
    });
</script>
