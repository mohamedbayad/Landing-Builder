<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">
            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Profile Information</h3>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Update Password</h3>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/[0.06]">
                    <h3 class="text-sm font-semibold text-red-600 dark:text-red-400">Delete Account</h3>
                </div>
                <div class="p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
