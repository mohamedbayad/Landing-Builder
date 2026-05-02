<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
                Email Automation
            </h2>
            <a href="{{ route('email-automation.automations.create') }}"
               class="inline-flex items-center px-4 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg hover:bg-brand-orange-600 transition-colors shadow-sm">
                Create Automation
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

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm overflow-hidden">
                @if($automations->isEmpty())
                    <div class="p-12 text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-white/[0.02] border-2 border-dashed border-gray-200 dark:border-white/[0.08] m-6 rounded-xl">
                        No automations yet. Create your first sequence to start sending follow-up emails.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-white/[0.06]">
                            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Trigger</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Steps</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sent</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Open Rate</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Click Rate</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Activity</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                                @foreach($automations as $automation)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $automation->name }}
                                            @if($automation->builder_mode)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200">Visual</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                            {{ str_replace('_', ' ', $automation->trigger_type) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                {{ $automation->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                                {{ $automation->status === 'paused' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}
                                                {{ $automation->status === 'draft' ? 'bg-gray-100 text-gray-700 dark:bg-white/[0.08] dark:text-gray-300' : '' }}">
                                                {{ ucfirst($automation->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $automation->steps_count }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $automation->sent_count }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $automation->open_rate }}%</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $automation->click_rate }}%</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $automation->messages_max_sent_at ? \Carbon\Carbon::parse($automation->messages_max_sent_at)->diffForHumans() : 'Never' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('email-automation.automations.edit', $automation) }}"
                                                   class="text-xs font-semibold px-2.5 py-1 rounded-md bg-orange-50 text-brand-orange dark:bg-orange-500/10 hover:bg-orange-100 dark:hover:bg-orange-500/20 transition-colors">
                                                    Edit
                                                </a>

                                                <form action="{{ route('email-automation.automations.duplicate', $automation) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-white/[0.08] dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-white/[0.14] transition-colors">
                                                        Duplicate
                                                    </button>
                                                </form>

                                                <form action="{{ route('email-automation.automations.status', $automation) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $automation->status === 'active' ? 'paused' : 'active' }}">
                                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 dark:bg-white/[0.08] dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-white/[0.14] transition-colors">
                                                        {{ $automation->status === 'active' ? 'Pause' : 'Resume' }}
                                                    </button>
                                                </form>

                                                <form action="{{ route('email-automation.automations.destroy', $automation) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Delete this automation?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-md bg-red-50 text-red-600 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
