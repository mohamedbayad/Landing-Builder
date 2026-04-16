@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-gray-200 dark:border-white/10 bg-white dark:bg-[#161B22] text-gray-700 dark:text-white dark:placeholder-gray-500 focus:border-brand-orange focus:ring-brand-orange/20 focus:outline-none focus:ring-2 rounded-lg text-sm transition-colors duration-150 py-2 px-3']) }}>
