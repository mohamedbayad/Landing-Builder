<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Email Analytics</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">
            @include('email-automation._subnav')

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-4">
                <form method="GET" class="flex items-center gap-3">
                    <label class="text-sm text-gray-600 dark:text-gray-300">Period</label>
                    <select name="days" class="rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white text-sm">
                        @foreach([7,14,30,60,90] as $period)
                            <option value="{{ $period }}" @selected($days === $period)>Last {{ $period }} days</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg shadow-sm">Apply</button>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Total Sent</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $overview['total_sent'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Delivered</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $overview['delivered'] }}</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Open Rate</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $overview['open_rate'] }}%</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Click Rate</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $overview['click_rate'] }}%</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Bounce Rate</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $overview['bounce_rate'] }}%</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] p-5 shadow-sm">
                    <p class="text-xs text-gray-500">Unsubscribe Rate</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $overview['unsubscribe_rate'] }}%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.06]">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daily Trend</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sent</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Opened</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Clicked</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @forelse($overview['trend'] as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row->date }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row->total }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row->opened }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row->clicked }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.06]">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Automations</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                            @forelse($topAutomations as $automation)
                                <div class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $automation->name }}</div>
                                    <div class="text-xs text-gray-500">Sent: {{ $automation->sent_count }} &middot; Opened: {{ $automation->opened_count }} &middot; Clicked: {{ $automation->clicked_count }}</div>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500">No automation data.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.06]">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Templates</h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                            @forelse($topTemplates as $template)
                                <div class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $template->name }}</div>
                                    <div class="text-xs text-gray-500">Sent: {{ $template->sent_count }} &middot; Opened: {{ $template->opened_count }} &middot; Clicked: {{ $template->clicked_count }}</div>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500">No template data.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

