<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Email Activity</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            @include('email-automation._subnav')

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-4 mb-4">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Filters</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Narrow activity by recipient, status, automation, and template.</p>
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-7 gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Recipient or subject"
                           class="rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white text-sm shadow-sm focus:border-brand-orange focus:ring-brand-orange/20 md:col-span-2">
                    <select name="status" class="rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white text-sm shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                        <option value="all">All Status</option>
                        @foreach(['queued','sent','delivered','opened','clicked','failed','bounced','unsubscribed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <select name="automation_id" class="rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white text-sm shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                        <option value="">All Automations</option>
                        @foreach($automations as $automation)
                            <option value="{{ $automation->id }}" @selected((string) request('automation_id') === (string) $automation->id)>{{ $automation->name }}</option>
                        @endforeach
                    </select>
                    <select name="template_id" class="rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white text-sm shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                        <option value="">All Templates</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected((string) request('template_id') === (string) $template->id)>{{ $template->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-brand-orange-600 transition-colors">Filter</button>
                    <a href="{{ route('email-automation.activity.index') }}"
                       class="px-4 py-2 text-center border border-gray-300 dark:border-white/[0.06] rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.04] transition-colors">
                        Reset
                    </a>
                </form>
            </div>

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                @if($messages->isEmpty())
                    <div class="p-12 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-white/[0.02] border-2 border-dashed border-gray-200 dark:border-white/[0.08] m-6 rounded-xl">
                        No activity found.
                    </div>
                @else
                    @php
                        $statusStyles = [
                            'queued' => 'bg-gray-100 text-gray-700 dark:bg-white/[0.08] dark:text-gray-300',
                            'sent' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                            'delivered' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                            'opened' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'clicked' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                            'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                            'bounced' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                            'unsubscribed' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                        ];
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Recipient</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Automation</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Template</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Subject</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Opened</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clicked</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @foreach($messages as $message)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $message->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $message->recipient_email }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $message->automation?->name ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $message->template?->name ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::limit($message->subject, 70) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusStyles[$message->status] ?? 'bg-gray-100 text-gray-700 dark:bg-white/[0.08] dark:text-gray-300' }}">
                                                {{ ucfirst($message->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $message->opened_at ? $message->opened_at->diffForHumans() : '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $message->first_clicked_at ? $message->first_clicked_at->diffForHumans() : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100 dark:border-white/[0.06]">
                        {{ $messages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
