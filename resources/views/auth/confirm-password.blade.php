<x-guest-layout>
    <div class="mb-7">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Confirm your password</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">
            {{ __('This is a secure area. Please confirm your password before continuing.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full py-2.5 text-sm font-semibold justify-center">
            {{ __('Confirm Password') }}
        </x-primary-button>
    </form>
</x-guest-layout>
