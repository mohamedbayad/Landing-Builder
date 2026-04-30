<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
                {{ __('My Landings') }}
            </h2>
            <a href="{{ route('templates.index') }}" class="inline-flex items-center px-4 py-2 bg-brand-orange border border-transparent rounded-lg text-sm font-semibold text-white hover:bg-brand-orange-600 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="bg-white dark:bg-[#161B22] overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-white/[0.06] transition-all duration-300">
                <div class="p-6">

                    @if(session('status'))
                        <x-ui.alert type="success" class="mb-6" dismissible>
                            {{ session('status') }}
                        </x-ui.alert>
                    @endif

                    @if($landings->count() > 0)
                        <div class="overflow-x-auto rounded-lg border border-gray-100 dark:border-white/[0.06]">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                                    @foreach($landings as $landing)
                                        @php
                                            $source = (string) ($landing->source ?? '');
                                            $canUsePlatformRootLabel = auth()->user()->hasAnyRole(['admin', 'super-admin']);
                                            $canSyncTemplate = !empty($landing->template_id)
                                                || str_starts_with($source, 'remote-template:')
                                                || str_starts_with($source, 'local-template:')
                                                || $source === 'template'
                                                || preg_match('/\s*-\s*copy$/i', (string) $landing->name);
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $landing->name }}</div>
                                                @if($landing->is_main)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300 mt-1">
                                                        {{ $canUsePlatformRootLabel ? 'Main Landing (Platform Root)' : 'Workspace Default' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php($landingUrl = \App\Support\LandingPublicUrl::indexUrl($landing))
                                                <a href="{{ $landingUrl }}" target="_blank" class="text-sm text-brand-orange hover:text-brand-orange-600 flex items-center group">
                                                    {{ parse_url($landingUrl, PHP_URL_PATH) }}
                                                    <svg class="w-4 h-4 ml-1 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $landing->status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-300' }}">
                                                    <span class="w-2 h-2 mr-1.5 rounded-full {{ $landing->status === 'published' ? 'bg-green-400' : 'bg-yellow-400' }}"></span>
                                                    {{ ucfirst($landing->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end items-center gap-3">
                                                    @if($canSyncTemplate)
                                                        <form action="{{ route('landings.templates.sync', $landing) }}" method="POST" onsubmit="event.preventDefault(); window.confirmAction('Sync this landing with its source template? This will update template content and styles.', this);">
                                                            @csrf
                                                            <button type="submit" class="text-gray-400 hover:text-cyan-500 dark:hover:text-cyan-400" title="Sync Template">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5.64 17.66A9 9 0 0019 8.36M18.36 6.34A9 9 0 005 15.64"></path>
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if(!$landing->is_main)
                                                        <form action="{{ route('landings.main', $landing) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="text-gray-400 hover:text-brand-orange dark:hover:text-brand-orange" title="{{ $canUsePlatformRootLabel ? 'Set as Main Landing (Platform Root)' : 'Set as Workspace Default' }}">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($landing->status !== 'published')
                                                        <form action="{{ route('landings.publish', $landing) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="text-gray-400 hover:text-green-600 dark:hover:text-green-400" title="Publish">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($landing->pages->first())
                                                        <a href="{{ route('landings.preview', [$landing, $landing->pages->first()]) }}" target="_blank" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400" title="Preview Draft">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                        </a>
                                                    @endif

                                                    <a href="{{ route('landings.funnel', $landing) }}" class="text-gray-400 hover:text-purple-600 dark:hover:text-purple-400" title="Funnel & Products">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                                    </a>

                                                    <a href="{{ route('landings.edit', $landing) }}" class="text-gray-400 hover:text-orange-600 dark:hover:text-orange-400" title="Settings">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    </a>

                                                    <form action="{{ route('landings.destroy', $landing) }}" method="POST" onsubmit="event.preventDefault(); window.confirmAction('Are you sure you want to delete this landing? This action cannot be undone.', this);">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400" title="Delete">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-20 bg-gray-50 dark:bg-white/[0.02] rounded-lg border-2 border-dashed border-gray-200 dark:border-white/[0.06]">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No landings yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new landing page from a template.</p>
                            <div class="mt-6">
                                <a href="{{ route('templates.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-lg text-white bg-brand-orange hover:bg-brand-orange-600 focus:outline-none focus:ring-2 focus:ring-brand-orange/20 transition-all">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Browse Templates
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
