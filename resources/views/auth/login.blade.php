<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-7">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Welcome back</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">Sign in to your account to continue</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1">
                <x-input-label for="password" :value="__('Password')" class="mb-0" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-brand-orange hover:text-brand-orange-600 focus:outline-none" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox"
                   class="w-4 h-4 rounded border-gray-300 text-brand-orange shadow-sm focus:ring-brand-orange/20"
                   name="remember">
            <label for="remember_me" class="ml-2 text-sm text-gray-600 dark:text-gray-400 select-none cursor-pointer">
                {{ __('Keep me signed in') }}
            </label>
        </div>

        <x-primary-button class="w-full mt-1 py-2.5 text-sm font-semibold justify-center">
            {{ __('Sign in') }}
        </x-primary-button>
    </form>
</x-guest-layout>
