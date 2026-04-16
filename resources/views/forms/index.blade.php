<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
            {{ __('Forms & Submissions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">

            <!-- Form Endpoints Section -->
            <div class="bg-white dark:bg-[#161B22] overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-white/[0.06]">
                <div class="p-6 border-b border-gray-100 dark:border-white/[0.06] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Form Endpoints</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create unique endpoints to collect form data from external sites.</p>
                    </div>

                    <!-- Create Form Endpoint -->
                    <form action="{{ route('form-endpoints.store') }}" method="POST" class="flex w-full sm:w-auto gap-2">
                        @csrf
                        <input type="text" name="name" placeholder="Endpoint Name (e.g. Contact Us)" class="w-full sm:w-64 rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-gray-200 shadow-sm focus:border-brand-orange focus:ring-brand-orange/20 text-sm px-3 py-2" required>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-orange border border-transparent rounded-lg text-sm font-semibold text-white hover:bg-brand-orange-600 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 transition-all shadow-sm">
                            Create
                        </button>
                    </form>
                </div>

                @if($endpoints->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm bg-gray-50 dark:bg-white/[0.02]">
                        <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        No endpoints created yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Submission URL</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Stats</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @foreach($endpoints as $endpoint)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $endpoint->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div class="flex items-center gap-2 group">
                                                <code class="bg-gray-100 dark:bg-[#0D1117] px-2 py-1 rounded text-xs select-all text-brand-orange dark:text-brand-orange border border-gray-200 dark:border-white/[0.06]">{{ route('forms.endpoint.submit', $endpoint->uuid) }}</code>
                                                <button onclick="navigator.clipboard.writeText('{{ route('forms.endpoint.submit', $endpoint->uuid) }}'); window.Toast ? window.Toast.success('URL Copied!') : alert('Copied!');" class="text-gray-400 hover:text-brand-orange dark:hover:text-brand-orange opacity-0 group-hover:opacity-100 transition-opacity" title="Copy URL">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                                                {{ $endpoint->forms_count }} Submissions
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <form action="{{ route('form-endpoints.destroy', $endpoint) }}" method="POST" onsubmit="event.preventDefault(); window.confirmAction('Delete this endpoint? All associated submissions will be lost.', this);">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Delete">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Recent Submissions -->
            <div class="bg-white dark:bg-[#161B22] overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-white/[0.06]">
                <div class="p-6 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Recent Submissions</h3>
                </div>

                @if($forms->isEmpty())
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                        No form submissions found.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Source</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @foreach($forms as $form)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $form->created_at->format('M d, H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            @if($form->formEndpoint)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-300">
                                                    Endpoint: {{ $form->formEndpoint->name }}
                                                </span>
                                            @elseif($form->landing)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                                                    Page: {{ $form->landing->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">Unknown Source</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $form->email ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            @if($form->data)
                                                <div class="grid gap-1">
                                                    @foreach($form->data as $key => $value)
                                                        @if(!is_array($value) && $key !== 'email' && $key !== 'submit' && $key !== '_token')
                                                            <div class="flex text-xs">
                                                                <span class="font-semibold text-gray-700 dark:text-gray-300 mr-1">{{ ucfirst($key) }}:</span>
                                                                <span class="truncate max-w-xs">{{ \Illuminate\Support\Str::limit($value, 80) }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100 dark:border-white/[0.06]">
                        {{ $forms->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
