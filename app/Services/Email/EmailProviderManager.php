<?php

namespace App\Services\Email;

use App\Models\EmailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EmailProviderManager
{
    public function sendHtmlEmail(int $userId, string $to, string $subject, string $html): array
    {
        $setting = EmailSetting::firstOrCreate(['user_id' => $userId]);
        $driver = Str::lower(trim((string) ($setting->mail_driver ?: config('mail.default'))));
        $driver = $driver !== '' ? $driver : config('mail.default');

        $fromAddress = $setting->from_email ?: config('mail.from.address');
        $fromName = $setting->from_name ?: config('mail.from.name');
        $replyTo = $setting->reply_to_email;

        if ($driver === 'smtp' && $setting->smtp_host && $setting->smtp_port) {
            return $this->sendViaSmtpProfiles(
                setting: $setting,
                to: $to,
                subject: $subject,
                html: $html,
                fromAddress: $fromAddress,
                fromName: $fromName,
                replyTo: $replyTo
            );
        }

        $mailer = $this->resolveMailer($setting, $driver);
        $this->sendWithMailer($mailer, $to, $subject, $html, $fromAddress, $fromName, $replyTo);

        return [
            'provider' => $driver,
            'provider_message_id' => null,
        ];
    }

    private function sendViaSmtpProfiles(
        EmailSetting $setting,
        string $to,
        string $subject,
        string $html,
        ?string $fromAddress,
        ?string $fromName,
        ?string $replyTo
    ): array {
        $profiles = $this->buildSmtpProfiles($setting);
        $errors = [];
        $lastException = null;

        foreach ($profiles as $index => $profile) {
            $mailer = "email_user_{$setting->user_id}_smtp_{$index}";
            Config::set("mail.mailers.$mailer", [
                'transport' => 'smtp',
                'host' => $profile['host'],
                'port' => $profile['port'],
                'username' => $profile['username'],
                'password' => $profile['password'],
                'encryption' => $profile['encryption'],
                'timeout' => 30,
            ]);

            try {
                $this->sendWithMailer($mailer, $to, $subject, $html, $fromAddress, $fromName, $replyTo);

                return [
                    'provider' => 'smtp',
                    'provider_message_id' => null,
                    'profile' => [
                        'host' => $profile['host'],
                        'port' => $profile['port'],
                        'encryption' => $profile['encryption'],
                    ],
                ];
            } catch (Throwable $exception) {
                $lastException = $exception;
                $errors[] = sprintf(
                    '%s:%s (%s) => %s',
                    $profile['host'],
                    $profile['port'],
                    $profile['encryption'] ?: 'none',
                    $exception->getMessage()
                );
            }
        }

        throw new RuntimeException(
            'SMTP send failed for all profiles: '.implode(' | ', $errors),
            0,
            $lastException
        );
    }

    private function buildSmtpProfiles(EmailSetting $setting): array
    {
        $host = trim((string) $setting->smtp_host);
        $port = (int) $setting->smtp_port;
        $encryption = $setting->smtp_encryption
            ? Str::lower(trim((string) $setting->smtp_encryption))
            : null;

        if ($encryption === 'starttls') {
            $encryption = 'tls';
        }

        $profiles = [[
            'host' => $host,
            'port' => $port,
            'username' => $setting->smtp_username,
            'password' => $setting->smtp_password,
            'encryption' => $encryption,
        ]];

        // Add a common fallback profile for providers that support both 465/SSL and 587/TLS.
        if ($port === 465) {
            $profiles[] = [
                'host' => $host,
                'port' => 587,
                'username' => $setting->smtp_username,
                'password' => $setting->smtp_password,
                'encryption' => 'tls',
            ];
        } elseif ($port === 587) {
            $profiles[] = [
                'host' => $host,
                'port' => 465,
                'username' => $setting->smtp_username,
                'password' => $setting->smtp_password,
                'encryption' => 'ssl',
            ];
        }

        // Remove duplicate profiles.
        $unique = [];
        $seen = [];
        foreach ($profiles as $profile) {
            $key = implode('|', [
                $profile['host'],
                $profile['port'],
                $profile['username'],
                $profile['encryption'] ?? '',
            ]);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $profile;
        }

        return $unique;
    }

    private function sendWithMailer(
        string $mailer,
        string $to,
        string $subject,
        string $html,
        ?string $fromAddress,
        ?string $fromName,
        ?string $replyTo
    ): void {
        Mail::mailer($mailer)->html($html, function ($message) use ($to, $subject, $fromAddress, $fromName, $replyTo) {
            $message->to($to)->subject($subject);

            if ($fromAddress) {
                $message->from($fromAddress, $fromName);
            }

            if ($replyTo) {
                $message->replyTo($replyTo);
            }
        });
    }

    private function resolveMailer(EmailSetting $setting, string $driver): string
    {
        $driver = Str::lower(trim($driver));

        if (array_key_exists($driver, config('mail.mailers', []))) {
            return $driver;
        }

        return config('mail.default');
    }
}
