<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white tracking-tight">Email Settings</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 md:px-8">
            @include('email-automation._subnav')

            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 px-4 py-3 text-sm border border-green-100 dark:border-green-800/40">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 px-4 py-3 text-sm border border-red-100 dark:border-red-900/50">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('email-automation.settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-[#161B22] rounded-xl border border-gray-100 dark:border-white/[0.06] shadow-sm p-6 space-y-8">
                    <div class="pb-6 border-b border-gray-100 dark:border-white/[0.06]">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Sender Identity</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Control default sender and reply handling for automation emails.</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Mail Driver</label>
                                <select name="mail_driver" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                    <option value="">Use app default</option>
                                    @foreach(['smtp','ses','postmark','resend','sendmail','log','array'] as $driver)
                                        <option value="{{ $driver }}" @selected(old('mail_driver', $setting->mail_driver) === $driver)>{{ strtoupper($driver) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">From Name</label>
                                <input type="text" name="from_name" value="{{ old('from_name', $setting->from_name) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">From Email</label>
                                <input type="email" name="from_email" value="{{ old('from_email', $setting->from_email) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Reply-To Email</label>
                                <input type="email" name="reply_to_email" value="{{ old('reply_to_email', $setting->reply_to_email) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                            </div>
                        </div>
                    </div>

                    <div class="pb-6 border-b border-gray-100 dark:border-white/[0.06]">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">SMTP Configuration</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Only required when you use the SMTP driver for this workspace.</p>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-3">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">SMTP Host</label>
                                <input type="text" name="smtp_host" value="{{ old('smtp_host', $setting->smtp_host) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">SMTP Port</label>
                                <input type="number" name="smtp_port" value="{{ old('smtp_port', $setting->smtp_port) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">SMTP Username</label>
                                <input type="text" name="smtp_username" value="{{ old('smtp_username', $setting->smtp_username) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">SMTP Encryption</label>
                                <select name="smtp_encryption" class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                    <option value="">None</option>
                                    @foreach(['tls','ssl','starttls'] as $enc)
                                        <option value="{{ $enc }}" @selected(old('smtp_encryption', $setting->smtp_encryption) === $enc)>{{ strtoupper($enc) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">SMTP Password</label>
                                <input type="password" name="smtp_password" placeholder="Leave blank to keep existing"
                                       class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Stored securely and only updated when you provide a new value.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Footer and Unsubscribe</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Set default compliance content shown in automated emails.</p>

                        <div class="mt-3 space-y-4">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Default Footer</label>
                                <textarea name="default_footer" rows="3"
                                          class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">{{ old('default_footer', data_get($setting->settings, 'default_footer')) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Unsubscribe Note</label>
                                <textarea name="unsubscribe_text" rows="2"
                                          class="w-full rounded-lg border-gray-300 dark:border-white/[0.06] dark:bg-[#0D1117] dark:text-white shadow-sm focus:border-brand-orange focus:ring-brand-orange/20">{{ old('unsubscribe_text', data_get($setting->settings, 'unsubscribe_text')) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-5 py-2 bg-brand-orange text-white text-sm font-semibold rounded-lg hover:bg-brand-orange-600 shadow-sm transition-colors">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
