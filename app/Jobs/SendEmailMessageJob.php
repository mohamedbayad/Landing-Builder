<?php

namespace App\Jobs;

use App\Models\EmailContact;
use App\Models\EmailEvent;
use App\Models\EmailMessage;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Services\Email\EmailContactService;
use App\Services\Email\EmailProviderManager;
use App\Services\Email\EmailTemplateRenderer;
use App\Services\Email\EmailTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendEmailMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $userId,
        public ?int $automationId,
        public ?int $stepId,
        public int $templateId,
        public ?int $contactId = null,
        public array $context = []
    ) {
        $this->queue = config('queue.connections.'.config('queue.default').'.queue', 'default');
    }

    public function handle(
        EmailContactService $contactService,
        EmailTemplateRenderer $renderer,
        EmailTrackingService $trackingService,
        EmailProviderManager $providerManager
    ): void {
        $template = EmailTemplate::query()
            ->where('user_id', $this->userId)
            ->find($this->templateId);

        if (!$template) {
            return;
        }

        $contact = $this->contactId
            ? EmailContact::query()->find($this->contactId)
            : $contactService->upsertFromPayload($this->userId, $this->context);

        if (!$contact) {
            return;
        }

        $mergedContext = array_merge($this->context, [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ]);

        if ($contactService->isSuppressed($contact)) {
            $message = EmailMessage::create([
                'user_id' => $this->userId,
                'automation_id' => $this->automationId,
                'automation_step_id' => $this->stepId,
                'template_id' => $template->id,
                'contact_id' => $contact->id,
                'lead_id' => $this->context['lead_id'] ?? null,
                'order_id' => $this->context['order_id'] ?? null,
                'landing_page_id' => $this->context['landing_page_id'] ?? null,
                'recipient_email' => $contact->email,
                'subject' => $template->subject,
                'body_html' => $template->body_html,
                'status' => 'unsubscribed',
                'meta' => $this->context,
            ]);

            $this->recordEvent($message, 'unsubscribed', ['reason' => 'suppressed_contact']);
            return;
        }

        $unsubscribeUrl = $trackingService->buildUnsubscribeUrl($contact);
        $mergedContext['unsubscribe_url'] = $unsubscribeUrl;

        $rendered = $renderer->renderTemplate($template, $mergedContext);

        $message = EmailMessage::create([
            'user_id' => $this->userId,
            'automation_id' => $this->automationId,
            'automation_step_id' => $this->stepId,
            'template_id' => $template->id,
            'contact_id' => $contact->id,
            'lead_id' => $this->context['lead_id'] ?? null,
            'order_id' => $this->context['order_id'] ?? null,
            'landing_page_id' => $this->context['landing_page_id'] ?? null,
            'recipient_email' => $contact->email,
            'subject' => $rendered['subject'],
            'body_html' => $rendered['body_html'],
            'status' => 'queued',
            'meta' => $this->context,
        ]);

        $customFooter = data_get(
            EmailSetting::query()->firstOrCreate(['user_id' => $this->userId])->settings,
            'default_footer'
        );
        $trackedHtml = $trackingService->prepareTrackedHtml($message, $rendered['body_html'], $contact, $customFooter);
        $message->update(['body_html' => $trackedHtml]);

        try {
            $providerData = $providerManager->sendHtmlEmail(
                userId: $this->userId,
                to: $contact->email,
                subject: $rendered['subject'],
                html: $trackedHtml
            );

            $message->update([
                'status' => 'delivered',
                'provider' => $providerData['provider'] ?? null,
                'provider_message_id' => $providerData['provider_message_id'] ?? null,
                'sent_at' => now(),
                'delivered_at' => now(),
                'error_message' => null,
            ]);

            $contact->increment('total_sent_emails');

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
            ]);

            throw $exception;
        }
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
