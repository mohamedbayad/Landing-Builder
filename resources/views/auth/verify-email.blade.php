<x-guest-layout>
    <div class="mb-7">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Verify your email</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">
            {{ __('Thanks for signing up! Please verify your email address by clicking the link we sent you. If you didn\'t receive it, we\'ll send another.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 p-4 rounded-lg bg-green-50 dark:bg-green-500/10 border border-green-100 dark:border-green-500/20">
            <p class="text-sm font-medium text-green-700 dark:text-green-400">
                {{ __('A new verification link has been sent to your email address.') }}
            </p>
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="py-2.5 text-sm font-semibold">
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-brand-orange dark:hover:text-brand-orange transition-colors focus:outline-none">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
