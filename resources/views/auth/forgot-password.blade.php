<x-guest-layout>
    <div class="mb-7">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Reset your password</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">
            {{ __('Enter your email address and we will send you a reset link.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-2.5 text-sm font-semibold justify-center">
            {{ __('Send Reset Link') }}
        </x-primary-button>

        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            <a class="font-medium text-brand-orange hover:text-brand-orange-600" href="{{ route('login') }}">
                &larr; {{ __('Back to sign in') }}
            </a>
        </p>
    </form>
</x-guest-layout>
