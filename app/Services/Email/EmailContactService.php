<?php

namespace App\Services\Email;

use App\Models\EmailContact;
use App\Models\EmailUnsubscribe;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EmailContactService
{
    public function upsertFromPayload(int $userId, array $context): ?EmailContact
    {
        $email = $this->normalizeEmail($context['email'] ?? Arr::get($context, 'data.email'));
        if (!$email) {
            return null;
        }

        $firstName = $context['first_name']
            ?? Arr::get($context, 'data.first_name')
            ?? Arr::get($context, 'data.billing_first_name');
        $lastName = $context['last_name']
            ?? Arr::get($context, 'data.last_name')
            ?? Arr::get($context, 'data.billing_last_name');
        $phone = $context['phone']
            ?? Arr::get($context, 'data.phone')
            ?? Arr::get($context, 'data.billing_phone');

        $contact = EmailContact::firstOrNew([
            'user_id' => $userId,
            'email' => $email,
        ]);

        if (!$contact->exists) {
            $contact->status = 'subscribed';
        }

        $contact->first_name = $firstName ?: $contact->first_name;
        $contact->last_name = $lastName ?: $contact->last_name;
        $contact->phone = $phone ?: $contact->phone;
        $contact->lead_id = $context['lead_id'] ?? $contact->lead_id;
        $contact->source = $context['source'] ?? $contact->source;
        $contact->meta = array_filter(array_merge($contact->meta ?? [], [
            'landing_id' => $context['landing_id'] ?? null,
            'product_id' => $context['product_id'] ?? null,
            'form_endpoint_id' => $context['form_endpoint_id'] ?? null,
            'trigger_type' => $context['trigger_type'] ?? null,
        ]), fn ($value) => !is_null($value));

        $contact->save();

        return $contact;
    }

    public function isSuppressed(?EmailContact $contact): bool
    {
        if (!$contact) {
            return true;
        }

        return in_array($contact->status, ['unsubscribed', 'bounced', 'complained'], true);
    }

    public function markUnsubscribed(EmailContact $contact, ?string $reason = null, ?string $source = null): void
    {
        if ($contact->status !== 'unsubscribed') {
            $contact->update(['status' => 'unsubscribed']);
        }

        EmailUnsubscribe::firstOrCreate(
            ['contact_id' => $contact->id, 'email' => $contact->email],
            [
                'reason' => $reason,
                'source' => $source ?: 'manual',
                'unsubscribed_at' => now(),
            ]
        );
    }

    private function normalizeEmail(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        $email = Str::lower(trim($email));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}

