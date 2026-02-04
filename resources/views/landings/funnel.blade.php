<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight">
                {{ __('Funnel Management') }}: <span class="text-indigo-600 dark:text-indigo-400">{{ $landing->name }}</span>
            </h2>
            <a href="{{ route('landings.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                &larr; Back to Landings
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'steps' }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <!-- Tabs -->
                <div class="border-b border-gray-100 dark:border-gray-700">
                    <nav class="flex overflow-x-auto px-2" aria-label="Tabs">
                        <button @click="tab = 'steps'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20': tab === 'steps', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'steps' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Funnel Steps
                        </button>
                        <button @click="tab = 'products'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20': tab === 'products', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'products' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            Products
                        </button>
                        <button @click="tab = 'checkout'" :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20': tab === 'checkout', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300': tab !== 'checkout' }" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-150 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            Checkout Fields
                        </button>
                    </nav>
                </div>

                <div class="p-8">
                    
                    <!-- Steps Tab -->
                    <div x-show="tab === 'steps'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="space-y-4">
                            @foreach($pages as $page)
                                <div class="flex items-center justify-between p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow transition-shadow">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-100 dark:border-indigo-800">
                                            @if($page->type == 'index') 1
                                            @elseif($page->type == 'checkout') 2
                                            @elseif($page->type == 'thankyou') 3
                                            @else ? @endif
                                        </div>
                                        <div>
                                            <h4 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                                {{ $page->name }}
                                                <span class="text-xs font-normal px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 capitalize">{{ $page->type }}</span>
                                            </h4>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                                Route: <span class="font-mono text-xs bg-gray-50 dark:bg-gray-700/50 px-1 py-0.5 rounded">/{{ $page->slug == 'index' ? '' : $page->slug }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('landings.pages.edit', [$landing, $page]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            Edit Design
                                        </a>
                                        <a href="{{ route('landings.preview', [$landing, $page]) }}" target="_blank" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Preview
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Products Tab -->
                    <div x-show="tab === 'products'" x-cloak style="display: none;">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Product Offers</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Manage the products available for purchase on this funnel.</p>
                            </div>
                            <button @click="$refs.addProductModal.classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 shadow-sm transition-all">
                                + Add Product
                            </button>
                        </div>

                        <!-- Product List -->
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Label</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Direct Link</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @php
                                        // Calc Base URL safely
                                        $checkoutPage = $landing->pages->where('type', 'checkout')->first();
                                        $checkoutSlug = $checkoutPage ? $checkoutPage->slug : 'checkout';
                                        $baseUrl = $landing->is_main ? url($checkoutSlug) : url('/preview/' . $landing->id . '/' . ($checkoutPage->id ?? ''));
                                    @endphp
                                    
                                    @forelse($products as $product)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">{{ $product->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 font-mono">{{ $product->currency }} {{ $product->price }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if($product->label)
                                                    <span class="px-2 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs">{{ $product->label }}</span>
                                                @else 
                                                    <span class="text-gray-300 dark:text-gray-600">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <div class="flex items-center gap-2 group">
                                                    <code class="text-xs bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 px-2 py-1 rounded select-all max-w-[150px] truncate text-gray-600 dark:text-gray-400">...product={{ $product->id }}</code>
                                                    <button onclick="navigator.clipboard.writeText('{{ $baseUrl }}?product={{ $product->id }}'); window.Toast ? window.Toast.success('Link Copied') : alert('Copied!');" class="text-indigo-600 hover:text-indigo-800 dark:hover:text-indigo-400 text-xs font-bold opacity-75 group-hover:opacity-100">Copy</button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form action="{{ route('funnel.products.destroy', [$landing, $product]) }}" method="POST" onsubmit="event.preventDefault(); window.confirmAction('Are you sure you want to delete this product?', this);">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30">
                                                <svg class="mx-auto h-10 w-10 text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                                No products added yet. Add a product to get started.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Add Product Modal -->
                        <div x-ref="addProductModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-gray-600 bg-opacity-75 transition-opacity" aria-hidden="true" @click="$refs.addProductModal.classList.add('hidden')"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                    <form action="{{ route('funnel.products.store', $landing) }}" method="POST">
                                        @csrf
                                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4" id="modal-title">Add New Product</h3>
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name</label>
                                                    <input type="text" name="name" placeholder="E.g. Premium Plan" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                                </div>
                                                <div class="flex gap-4">
                                                    <div class="w-2/3">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                                                        <input type="number" step="0.01" name="price" placeholder="49.99" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                                                    </div>
                                                    <div class="w-1/3">
                                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                                                        <select name="currency" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                            <option value="USD">USD ($)</option>
                                                            <option value="EUR">EUR (€)</option>
                                                            <option value="MAD">MAD (DH)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Badge Label (Optional)</label>
                                                    <input type="text" name="label" placeholder="e.g. Best Value" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                Add Product
                                            </button>
                                            <button type="button" @click="$refs.addProductModal.classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout Fields Tab -->
                    <div x-show="tab === 'checkout'" x-cloak style="display: none;">
                         <div class="mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Checkout Form Configuration</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Control which fields appear on your checkout form and set custom labels.</p>
                         </div>
                         
                         <form action="{{ route('funnel.fields.store', $landing) }}" method="POST">
                             @csrf
                             <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                 <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                     <thead class="bg-gray-50 dark:bg-gray-700/50">
                                         <tr>
                                             <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Field Name</th>
                                             <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visible</th>
                                             <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Required</th>
                                             <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Custom Label</th>
                                         </tr>
                                     </thead>
                                     <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                         @foreach($checkoutFields as $field)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-200 capitalize">
                                                    {{ str_replace('_', ' ', $field->field_name) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <input type="checkbox" name="fields[{{ $field->field_name }}][enabled]" value="1" {{ $field->is_enabled ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 h-4 w-4 cursor-pointer">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                     <input type="checkbox" name="fields[{{ $field->field_name }}][required]" value="1" {{ $field->is_required ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 h-4 w-4 cursor-pointer">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="text" name="fields[{{ $field->field_name }}][label]" value="{{ $field->label }}" placeholder="{{ str_replace('_', ' ', $field->field_name) }}" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 py-1.5">
                                                </td>
                                            </tr>
                                         @endforeach
                                     </tbody>
                                 </table>
                             </div>
                             <div class="mt-6 flex justify-end">
                                 <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md transition-all">Save Field Settings</button>
                             </div>
                         </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
