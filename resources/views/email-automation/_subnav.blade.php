<div class="mb-6">
    @php
        $items = [
            ['route' => 'email-automation.automations.index', 'label' => 'Automations'],
            ['route' => 'email-automation.templates.index', 'label' => 'Templates'],
            ['route' => 'email-automation.contacts.index', 'label' => 'Contacts'],
            ['route' => 'email-automation.activity.index', 'label' => 'Activity'],
            ['route' => 'email-automation.analytics.index', 'label' => 'Analytics'],
            ['route' => 'email-automation.settings.index', 'label' => 'Settings'],
        ];
    @endphp

    <div class="flex flex-wrap gap-1 rounded-xl bg-gray-100 dark:bg-white/[0.04] p-1 border border-gray-200 dark:border-white/[0.06]">
        @foreach($items as $item)
            @php
                $active = request()->routeIs($item['route']);
            @endphp
            <a
                href="{{ route($item['route']) }}"
                class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all
                {{ $active ? 'bg-white dark:bg-[#161B22] text-brand-orange shadow-sm border border-gray-200 dark:border-white/[0.06]' : 'text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white hover:bg-white/70 dark:hover:bg-white/[0.06]' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>
</div>
