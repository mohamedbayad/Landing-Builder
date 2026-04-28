<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Email Templates</h2>
            <a href="{{ route('email-automation.templates.create') }}"
               class="inline-flex items-center px-4 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg hover:bg-brand-orange-600 shadow-sm transition-colors">
                Create Template
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @include('email-automation._subnav')

            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 px-4 py-3 text-sm border border-green-100 dark:border-green-800/40">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-lg bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 px-4 py-3 text-sm border border-red-100 dark:border-red-900/50">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 rounded-lg bg-amber-50 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 px-4 py-3 text-sm border border-amber-100 dark:border-amber-800/40">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                @if($templates->isEmpty())
                    <div class="p-12 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-white/[0.02] border-2 border-dashed border-gray-200 dark:border-white/[0.08] m-6 rounded-xl">
                        No templates yet.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Messages</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @foreach($templates as $template)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $template->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $template->subject }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $template->messages_count }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex justify-end items-center gap-2">
                                                <a href="{{ route('email-automation.templates.edit', $template) }}"
                                                   class="text-xs font-semibold px-2.5 py-1 rounded-md bg-orange-50 text-brand-orange dark:bg-orange-500/10 hover:bg-orange-100 dark:hover:bg-orange-500/20 transition-colors">Edit</a>

                                                <form action="{{ route('email-automation.templates.duplicate', $template) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-white/[0.08] dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-white/[0.14] transition-colors">Duplicate</button>
                                                </form>

                                                <details class="relative">
                                                    <summary class="list-none text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-white/[0.08] dark:text-gray-200 cursor-pointer hover:bg-gray-200 dark:hover:bg-white/[0.14] transition-colors">Send Test</summary>
                                                    <div class="absolute right-0 mt-2 w-72 bg-white dark:bg-[#0D1117] border border-gray-200 dark:border-white/[0.06] rounded-lg p-3 z-10 shadow-lg">
                                                        <form method="POST" action="{{ route('email-automation.templates.send-test', $template) }}" class="space-y-2">
                                                            @csrf
                                                            <input type="email" name="email" placeholder="test@example.com" required
                                                                   class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#161B22] dark:text-white text-sm">
                                                            <button type="submit" class="w-full px-3 py-2 bg-brand-orange text-white rounded-lg text-sm font-semibold">Send Test Email</button>
                                                        </form>
                                                    </div>
                                                </details>

                                                <form action="{{ route('email-automation.templates.destroy', $template) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Delete this template?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-md bg-red-50 text-red-600 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100 dark:border-white/[0.06]">
                        {{ $templates->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
