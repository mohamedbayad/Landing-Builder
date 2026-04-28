<?php

namespace App\Http\Controllers;

use App\Models\EmailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmailSettingsController extends Controller
{
    public function index()
    {
        $setting = EmailSetting::query()->firstOrCreate(['user_id' => Auth::id()]);

        return view('email-automation.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = EmailSetting::query()->firstOrCreate(['user_id' => Auth::id()]);

        $validated = $request->validate([
            'mail_driver' => ['nullable', Rule::in(['smtp', 'ses', 'postmark', 'resend', 'sendmail', 'log', 'array'])],
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => ['nullable', Rule::in(['tls', 'ssl', 'starttls'])],
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'reply_to_email' => 'nullable|email|max:255',
            'default_footer' => 'nullable|string|max:2000',
            'unsubscribe_text' => 'nullable|string|max:1000',
        ]);

        $settingsPayload = $setting->settings ?? [];
        $settingsPayload['default_footer'] = $validated['default_footer'] ?? '';
        $settingsPayload['unsubscribe_text'] = $validated['unsubscribe_text'] ?? '';

        $normalizedDriver = isset($validated['mail_driver'])
            ? Str::lower(trim((string) $validated['mail_driver']))
            : null;
        if ($normalizedDriver === '') {
            $normalizedDriver = null;
        }

        $normalizedEncryption = isset($validated['smtp_encryption'])
            ? Str::lower(trim((string) $validated['smtp_encryption']))
            : null;
        if ($normalizedEncryption === '') {
            $normalizedEncryption = null;
        }
        if ($normalizedEncryption === 'starttls') {
            $normalizedEncryption = 'tls';
        }

        $nullIfBlank = static function ($value): ?string {
            if ($value === null) {
                return null;
            }

            $value = trim((string) $value);
            return $value === '' ? null : $value;
        };

        $updateData = [
            'mail_driver' => $normalizedDriver,
            'smtp_host' => $nullIfBlank($validated['smtp_host'] ?? null),
            'smtp_port' => $validated['smtp_port'] ?? null,
            'smtp_username' => $nullIfBlank($validated['smtp_username'] ?? null),
            'smtp_encryption' => $normalizedEncryption,
            'from_email' => $nullIfBlank(isset($validated['from_email']) ? Str::lower((string) $validated['from_email']) : null),
            'from_name' => $nullIfBlank($validated['from_name'] ?? null),
            'reply_to_email' => $nullIfBlank(isset($validated['reply_to_email']) ? Str::lower((string) $validated['reply_to_email']) : null),
            'settings' => $settingsPayload,
        ];

        if (!empty($validated['smtp_password'])) {
            $updateData['smtp_password'] = $validated['smtp_password'];
        }

        $setting->update($updateData);

        return redirect()->route('email-automation.settings.index')
            ->with('success', 'Email settings updated.');
    }
}
