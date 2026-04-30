<x-app-layout>
<div class="py-6" x-data="{ 
    copy(text, el) { 
        navigator.clipboard.writeText(text);
        const originalText = el.innerText;
        el.innerText = 'Copied!';
        setTimeout(() => el.innerText = originalText, 2000);
    } 
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('domains.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors mb-4">
                <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Domains
            </a>
            
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate flex items-center">
                        <svg class="h-8 w-8 mr-3 text-brand-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        {{ $domain->domain }}
                        
                        @if($domain->status === 'active')
                            <span class="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <svg class="-ml-1 mr-1.5 h-3 w-3 text-green-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Active
                            </span>
                        @elseif($domain->status === 'pending')
                            <span class="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                <svg class="-ml-1 mr-1.5 h-3 w-3 text-yellow-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Pending DNS
                            </span>
                        @else
                            <span class="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                <svg class="-ml-1 mr-1.5 h-3 w-3 text-red-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg> Error
                            </span>
                        @endif
                    </h2>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <form action="{{ route('domains.verify', $domain->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-white/10 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-[#161B22] hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-orange/20 dark:focus:ring-offset-gray-900 transition-colors">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Check DNS Again
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 dark:bg-green-900 dark:border-green-800 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error') || $domain->error_message)
            <div class="bg-red-50 border border-red-200 dark:bg-red-900 dark:border-red-800 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-500 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Verification Issue</h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <p>{{ session('error') ?? $domain->error_message }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            
            <!-- Instructions Column -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Step 1: DNS Records -->
                <div class="bg-white dark:bg-[#161B22] shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-white/[0.06]">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-white/[0.06] flex items-center justify-between">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white flex items-center">
                                <span class="flex-shrink-0 h-6 w-6 rounded-full bg-orange-50 dark:bg-orange-500/10 text-brand-orange flex items-center justify-center text-sm font-bold mr-3">1</span>
                                Configure DNS Records
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add these records to your domain registrar's DNS settings (e.g. GoDaddy, Namecheap).</p>
                        </div>
                        @if($domain->status !== 'active')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                Required
                            </span>
                        @endif
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-[#0D1117]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name / Host</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Value / Target</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-[#161B22] divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @foreach($domain->instructions as $record)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">{{ $record['type'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $record['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <code class="px-2 py-1 bg-gray-100 dark:bg-[#0D1117] text-gray-800 dark:text-gray-200 rounded font-mono text-xs">{{ $record['value'] }}</code>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="copy('{{ $record['value'] }}', $event.target)" class="text-brand-orange hover:text-indigo-900 dark:hover:text-indigo-300 focus:outline-none transition-colors">
                                            Copy
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-gray-50 dark:bg-[#0D1117] px-4 py-3 sm:px-6 border-t border-gray-200 dark:border-white/[0.06]">
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-start">
                            <svg class="flex-shrink-0 h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>DNS changes can take up to 48 hours to propagate globally, though often it's much faster map. Note that some registrars use `@` for the root domain.</span>
                        </p>
                    </div>
                </div>

                <!-- Step 2: Hosting Platform (Information) -->
                <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-blue-900 dark:text-blue-200 flex items-center mb-3">
                            <span class="flex-shrink-0 h-6 w-6 rounded-full bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-300 flex items-center justify-center text-sm font-bold mr-3">2</span>
                            Server SSL Configuration
                        </h3>
                        <div class="mt-2 text-sm text-blue-800 dark:text-blue-300">
                            <p class="mb-3">To ensure a secure connection (HTTPS) for your visitors, you must configure the server to accept this domain.</p>
                            <ul class="list-disc pl-5 space-y-2">
                                <li>Log into your hosting dashboard (e.g. cPanel).</li>
                                <li>Navigate to "Addon Domains" or "Aliases" and add <strong>{{ $domain->domain }}</strong> pointing to your installation directory.</li>
                                <li>Go to "SSL/TLS Status" passing and run "AutoSSL" to generate a free secure certificate.</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Settings Column -->
            <div class="space-y-6">
                
                <!-- Assignment Card -->
                <div class="bg-white dark:bg-[#161B22] shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-white/[0.06]">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-1">
                            Assigned Landing Page
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Choose which landing page visitors will see when they visit this domain.
                        </p>
                        
                        @if($domain->status === 'active')
                            <form action="{{ route('domains.assign', $domain->id) }}" method="POST">
                                @csrf
                                <div class="space-y-3">
                                    <div>
                                        <select name="landing_page_id" class="mt-1 block w-full pl-3 pr-10 py-2 border-gray-300 dark:border-white/10 dark:bg-[#0D1117] dark:text-white rounded-md shadow-sm focus:outline-none focus:ring-brand-orange/20 focus:border-brand-orange sm:text-sm">
                                            <option value="">-- Choose a landing page --</option>
                                            @foreach($landingPages as $lp)
                                                <option value="{{ $lp->id }}" {{ $domain->landing_page_id === $lp->id ? 'selected' : '' }}>
                                                    {{ $lp->name }} ({{ $lp->slug }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-orange hover:bg-brand-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-orange/20 dark:focus:ring-offset-gray-900 transition-colors">
                                        Save Assignment
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="rounded-md bg-gray-50 dark:bg-[#0D1117] p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Verify your domain's DNS records before you can assign a landing page.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white dark:bg-[#161B22] shadow-sm rounded-xl overflow-hidden border border-red-200 dark:border-red-900/50">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-red-700 dark:text-red-400 mb-1">
                            Danger Zone
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Permanently delete this custom domain connection. Live links using this domain will break immediately.
                        </p>
                        
                        <form action="{{ route('domains.destroy', $domain->id) }}" method="POST" onsubmit="return confirm('Are you absolutely sure you want to remove this domain? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-red-300 dark:border-red-800 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-[#161B22] hover:bg-red-50 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900 transition-colors">
                                Delete Domain
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
</x-app-layout>
