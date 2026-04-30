<x-app-layout>
<div class="py-8" x-data="{ showModal: false, domain: '', statusFilter: 'all' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Custom Domains</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Connect external domains to your landing pages</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </div>
                    <select x-model="statusFilter" class="pl-9 pr-4 py-2 border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-orange/20 focus:border-brand-orange">
                        <option value="all">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                    </select>
                </div>
                <button @click="showModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-orange text-white rounded-lg text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Domain
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-100 dark:border-green-500/20 flex items-center gap-3">
                <svg class="h-4 w-4 text-green-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                <p class="text-sm font-medium text-green-700 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 flex items-center gap-3">
                <svg class="h-4 w-4 text-red-500 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                <p class="text-sm font-medium text-red-700 dark:text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        @if($domains->isEmpty())
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-16 flex flex-col items-center justify-center text-center">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gray-100 dark:bg-white/[0.06] mb-4">
                    <svg class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">No custom domains</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Connect a domain to host your landing pages on your own URL.</p>
                <button @click="showModal = true" class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-brand-orange text-white rounded-lg text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add your first domain
                </button>
            </div>
        @else
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-white/[0.06]">
                        <thead class="bg-gray-50 dark:bg-white/[0.02]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Domain</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned Landing</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                            @foreach($domains as $domain)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors" x-show="statusFilter === 'all' || statusFilter === '{{ $domain->status }}'">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0 h-9 w-9 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-white/[0.06] text-gray-500 dark:text-gray-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $domain->domain }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Added {{ $domain->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($domain->status === 'active')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <svg class="mr-1 h-2 w-2 text-emerald-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Active
                                        </span>
                                    @elseif($domain->status === 'verified')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                            <svg class="mr-1 h-2 w-2 text-blue-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Verified
                                        </span>
                                    @elseif($domain->status === 'pending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400">
                                            <svg class="mr-1 h-2 w-2 text-yellow-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Pending DNS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400" title="{{ $domain->error_message }}">
                                            <svg class="mr-1 h-2 w-2 text-red-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Error
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($domain->landing)
                                        <a href="{{ route('landings.editor', $domain->landing->id) }}" class="inline-flex items-center gap-1 text-sm text-brand-orange hover:text-brand-orange-600 font-medium">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            {{ $domain->landing->name }}
                                        </a>
                                    @else
                                        <span class="text-sm text-gray-400 dark:text-gray-500 italic">Not assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('domains.show', $domain->id) }}" class="text-sm font-semibold text-brand-orange hover:text-brand-orange-600 transition-colors">Manage</a>
                                        <span class="h-4 border-l border-gray-200 dark:border-white/10"></span>
                                        <form action="{{ route('domains.destroy', $domain->id) }}" method="POST" onsubmit="return confirm('Remove this domain? This will break any live links using it.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Add Domain Modal -->
    <div x-show="showModal" class="fixed z-50 inset-0 overflow-y-auto" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showModal"
                 x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 class="relative inline-block align-bottom bg-white dark:bg-[#161B22] rounded-xl text-left overflow-hidden shadow-dropdown border border-gray-100 dark:border-white/[0.06] transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{ route('domains.store') }}" method="POST">
                    @csrf
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-lg bg-orange-50 dark:bg-orange-500/10 text-brand-orange">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Connect Custom Domain</h3>
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Domain Name</label>
                                    <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-white/10 focus-within:border-brand-orange focus-within:ring-2 focus-within:ring-brand-orange/20 transition-all">
                                        <span class="inline-flex items-center px-3 bg-gray-50 dark:bg-white/[0.04] text-gray-500 dark:text-gray-400 text-sm border-r border-gray-200 dark:border-white/10">
                                            https://
                                        </span>
                                        <input type="text" name="domain" x-model="domain"
                                               placeholder="e.g. example.com"
                                               class="flex-1 min-w-0 block px-3 py-2 bg-white dark:bg-[#161B22] text-sm text-gray-700 dark:text-gray-300 placeholder-gray-400 focus:outline-none"
                                               required>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5 text-yellow-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Do not include http:// or www.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-white/[0.02] border-t border-gray-100 dark:border-white/[0.06] flex items-center justify-end gap-3">
                        <button type="button" @click="showModal = false"
                                class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-white/8 transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 bg-brand-orange text-white rounded-lg text-sm font-semibold hover:bg-brand-orange-600 transition-all shadow-sm">
                            Connect Domain
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
