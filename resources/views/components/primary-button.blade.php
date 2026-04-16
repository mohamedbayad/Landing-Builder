<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-brand-orange border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-brand-orange-600 active:bg-brand-orange-700 focus:outline-none focus:ring-2 focus:ring-brand-orange/30 focus:ring-offset-2 dark:focus:ring-offset-brand-dark transition-all duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
