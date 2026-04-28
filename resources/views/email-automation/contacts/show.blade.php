<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
                Contact Detail
            </h2>
            <a href="{{ route('email-automation.contacts.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-300 dark:border-white/[0.06] text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-colors">
                Back to Contacts
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">
            @include('email-automation._subnav')

            @if(session('success'))
                <div class="rounded-lg bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 px-4 py-3 text-sm border border-green-100 dark:border-green-800/40">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Name</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $contact->full_name ?: 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $contact->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $contact->phone ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Source</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $contact->source ?: '-' }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('email-automation.contacts.status', $contact) }}" class="mt-4 flex items-end gap-2">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Status</label>
                        <select name="status" class="rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white text-sm shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                            @foreach(['subscribed','unsubscribed','bounced','complained'] as $status)
                                <option value="{{ $status }}" @selected($contact->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-3 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-brand-orange-600 transition-colors">Update</button>
                </form>
            </div>

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Email Activity Timeline</h3>
                </div>

                @if($contact->messages->isEmpty())
                    <div class="p-10 text-sm text-gray-500 dark:text-gray-400 text-center bg-gray-50 dark:bg-white/[0.02] border-2 border-dashed border-gray-200 dark:border-white/[0.08] m-6 rounded-xl">
                        No activity for this contact yet.
                    </div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                        @foreach($contact->messages as $message)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $message->subject }}</p>
                                        <p class="text-xs text-gray-500">{{ $message->created_at->format('Y-m-d H:i') }}</p>
                                    </div>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-white/[0.08] text-gray-700 dark:text-gray-300">
                                        {{ ucfirst($message->status) }}
                                    </span>
                                </div>
                                @if($message->events->isNotEmpty())
                                    <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                        {{ $message->events->pluck('event_type')->unique()->map(fn($type) => ucfirst($type))->join(' | ') }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
