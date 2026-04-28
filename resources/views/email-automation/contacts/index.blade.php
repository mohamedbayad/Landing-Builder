<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Email Contacts</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @include('email-automation._subnav')

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-4 mb-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email / name"
                           class="rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white text-sm">
                    <select name="status" class="rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white text-sm">
                        <option value="all">All Status</option>
                        @foreach(['subscribed','unsubscribed','bounced','complained'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg">Filter</button>
                    <a href="{{ route('email-automation.contacts.index') }}" class="px-4 py-2 text-center border border-gray-300 dark:border-white/[0.06] rounded-lg text-sm text-gray-700 dark:text-gray-300">Reset</a>
                </form>
            </div>

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                @if($contacts->isEmpty())
                    <div class="p-12 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-white/[0.02] border-2 border-dashed border-gray-200 dark:border-white/[0.08] m-6 rounded-xl">No contacts found.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Sent</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Opened</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Clicked</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @foreach($contacts as $contact)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 text-sm">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $contact->full_name ?: 'N/A' }}</div>
                                            <div class="text-gray-600 dark:text-gray-300">{{ $contact->email }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                {{ $contact->status === 'subscribed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                                {{ $contact->status === 'unsubscribed' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                                {{ $contact->status === 'bounced' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                                {{ $contact->status === 'complained' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}">
                                                {{ ucfirst($contact->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $contact->source ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $contact->total_sent_emails }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $contact->last_opened_at?->diffForHumans() ?: 'Never' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $contact->last_clicked_at?->diffForHumans() ?: 'Never' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('email-automation.contacts.show', $contact) }}" class="text-xs font-semibold px-2.5 py-1 rounded-md bg-orange-50 text-brand-orange dark:bg-orange-500/10 hover:bg-orange-100 dark:hover:bg-orange-500/20 transition-colors">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100 dark:border-white/[0.06]">
                        {{ $contacts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
