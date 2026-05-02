<?php

namespace App\Jobs;

use App\Models\EmailContact;
use App\Models\EmailEvent;
use App\Models\EmailMessage;
use App\Models\EmailSetting;
use App\Services\Email\EmailContactService;
use App\Services\Email\EmailProviderManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

class SendAutomationChannelMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public int $automationId,
        public string $nodeId,
        public string $channel,
        public array $payload = [],
        public ?int $contactId = null,
        public array $context = []
    ) {
        $this->queue = config('queue.connections.'.config('queue.default').'.queue', 'default');
    }

    public function handle(
        EmailContactService $contactService,
        EmailProviderManager $providerManager
    ): void {
        $contact = $this->contactId
            ? EmailContact::query()->find($this->contactId)
            : null;

        if (!$contact && !empty($this->context)) {
            $contact = $contactService->upsertFromPayload($this->userId, $this->context);
        }

        if (!$contact) {
            return;
        }

        $mergedContext = array_merge($this->context, [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ]);

        $subject = $this->interpolate((string) ($this->payload['subject'] ?? ''), $mergedContext);
        $body = $this->interpolate((string) ($this->payload['body'] ?? ''), $mergedContext);

        $message = EmailMessage::create([
            'user_id' => $this->userId,
            'automation_id' => $this->automationId,
            'automation_step_id' => null,
            'template_id' => null,
            'contact_id' => $contact->id,
            'lead_id' => $this->context['lead_id'] ?? null,
            'order_id' => $this->context['order_id'] ?? null,
            'landing_page_id' => $this->context['landing_page_id'] ?? null,
            'channel' => $this->channel,
            'recipient_email' => $contact->email ?? '',
            'recipient_phone' => $contact->phone,
            'subject' => $subject,
            'body_html' => nl2br(e($body)),
            'body_text' => $body,
            'status' => 'queued',
            'meta' => array_merge($this->context, [
                'node_id' => $this->nodeId,
                'channel' => $this->channel,
            ]),
        ]);

        try {
            if ($this->channel === 'email') {
                if (!$contact->email) {
                    throw new \RuntimeException('Missing email recipient.');
                }

                $providerData = $providerManager->sendHtmlEmail(
                    userId: $this->userId,
                    to: $contact->email,
                    subject: $subject !== '' ? $subject : 'Notification',
                    html: nl2br(e($body))
                );

                $message->update([
                    'status' => 'delivered',
                    'provider' => $providerData['provider'] ?? 'email',
                    'provider_message_id' => $providerData['provider_message_id'] ?? null,
                    'sent_at' => now(),
                    'delivered_at' => now(),
                ]);
            } else {
                $this->sendThroughChannelWebhook($message, $contact, $subject, $body);
            }

            $this->recordEvent($message, 'sent');
            $this->recordEvent($message, 'delivered');
        } catch (Throwable $exception) {
            $message->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => mb_substr($exception->getMessage(), 0, 65000),
            ]);

            $this->recordEvent($message, 'failed', [
                'error' => $exception->getMessage(),
                'channel' => $this->channel,
            ]);

            throw $exception;
        }
    }

    private function sendThroughChannelWebhook(EmailMessage $message, EmailContact $contact, string $subject, string $body): void
    {
        $settings = (array) (EmailSetting::query()->firstOrCreate(['user_id' => $this->userId])->settings ?? []);
        $webhook = (string) ($settings[$this->channel.'_webhook_url'] ?? '');
        $webhook = trim($webhook);

        if ($webhook === '') {
            throw new \RuntimeException("Missing {$this->channel} webhook URL in email settings.");
        }

        $recipient = match ($this->channel) {
            'instagram' => (string) ($this->context['instagram_handle'] ?? ''),
            default => (string) ($contact->phone ?? ''),
        };

        if ($recipient === '') {
            throw new \RuntimeException("Missing recipient for {$this->channel}.");
        }

        $response = Http::timeout(15)->acceptJson()->post($webhook, [
            'channel' => $this->channel,
            'to' => $recipient,
            'subject' => $subject,
            'message' => $body,
            'context' => $this->context,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("{$this->channel} webhook failed with status {$response->status()}.");
        }

        $providerMessageId = $response->json('message_id') ?? $response->json('id');
        $message->update([
            'status' => 'delivered',
            'provider' => $this->channel.'_webhook',
            'provider_message_id' => is_scalar($providerMessageId) ? (string) $providerMessageId : null,
            'sent_at' => now(),
            'delivered_at' => now(),
        ]);
    }

    private function interpolate(string $text, array $context): string
    {
        return (string) preg_replace_callback('/\{\{\s*([a-zA-Z0-9_\-\.]+)\s*\}\}/', function ($matches) use ($context) {
            $key = $matches[1];
            $value = data_get($context, $key);

            if (is_scalar($value)) {
                return (string) $value;
            }

            return '';
        }, $text);
    }

    private function recordEvent(EmailMessage $message, string $type, array $data = []): void
    {
        EmailEvent::create([
            'email_message_id' => $message->id,
            'event_type' => $type,
            'event_data' => $data,
            'occurred_at' => now(),
        ]);
    }
}

