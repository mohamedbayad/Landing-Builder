<?php

namespace Tests\Feature;

use App\Jobs\ExecuteAutomationStepJob;
use App\Jobs\ProcessAutomationTriggerJob;
use App\Models\EmailAutomation;
use App\Models\EmailAutomationStep;
use App\Models\EmailContact;
use App\Models\EmailLink;
use App\Models\EmailMessage;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\Email\EmailTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailAutomationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_trigger_queues_automation_steps(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $template = EmailTemplate::create([
            'user_id' => $user->id,
            'name' => 'Welcome',
            'subject' => 'Hello {{first_name}}',
            'body_html' => '<p>Welcome {{first_name}}</p>',
            'status' => 'active',
        ]);

        $automation = EmailAutomation::create([
            'user_id' => $user->id,
            'name' => 'Form Follow Up',
            'status' => 'active',
            'trigger_type' => 'form_submitted',
        ]);

        EmailAutomationStep::create([
            'automation_id' => $automation->id,
            'step_order' => 1,
            'step_type' => 'send_email',
            'template_id' => $template->id,
        ]);

        $job = new ProcessAutomationTriggerJob(
            userId: $user->id,
            triggerType: 'form_submitted',
            context: [
                'email' => 'lead@example.com',
                'first_name' => 'Lead',
            ]
        );

        $job->handle(app(\App\Services\Email\AutomationEngineService::class));

        Bus::assertDispatched(ExecuteAutomationStepJob::class);
    }

    public function test_open_and_click_tracking_update_email_metrics(): void
    {
        $user = User::factory()->create();
        $contact = EmailContact::create([
            'user_id' => $user->id,
            'email' => 'track@example.com',
            'status' => 'subscribed',
        ]);

        $message = EmailMessage::create([
            'user_id' => $user->id,
            'contact_id' => $contact->id,
            'recipient_email' => $contact->email,
            'subject' => 'Track Me',
            'body_html' => '<a href="https://example.com">Link</a>',
            'status' => 'delivered',
            'delivered_at' => now(),
            'sent_at' => now(),
        ]);

        $hash = app(EmailTrackingService::class)->buildTrackingHash($message);
        $this->get(route('email.track.open', ['message' => $message->id, 'hash' => $hash]))->assertOk();

        $message->refresh();
        $contact->refresh();
        $this->assertNotNull($message->opened_at);
        $this->assertNotNull($contact->last_opened_at);

        $link = EmailLink::create([
            'email_message_id' => $message->id,
            'original_url' => 'https://example.com',
            'tracking_code' => 'abc123',
        ]);

        $this->get(route('email.click', ['code' => $link->tracking_code]))->assertRedirect('https://example.com');

        $link->refresh();
        $message->refresh();
        $contact->refresh();

        $this->assertSame(1, $link->total_clicks);
        $this->assertNotNull($message->first_clicked_at);
        $this->assertNotNull($contact->last_clicked_at);
    }

    public function test_unsubscribe_route_marks_contact_unsubscribed(): void
    {
        $user = User::factory()->create();
        $contact = EmailContact::create([
            'user_id' => $user->id,
            'email' => 'unsub@example.com',
            'status' => 'subscribed',
        ]);

        $url = URL::temporarySignedRoute(
            'email.unsubscribe',
            now()->addMinutes(10),
            ['contact' => $contact->id, 'email' => $contact->email]
        );

        $this->get($url)->assertOk();

        $contact->refresh();
        $this->assertSame('unsubscribed', $contact->status);
        $this->assertDatabaseHas('email_unsubscribes', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
        ]);
    }
}
