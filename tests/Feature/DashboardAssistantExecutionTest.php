<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Services\AgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class DashboardAssistantExecutionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_assistant_executes_email_automation_setup_when_user_requests_do_it_for_me(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->create(['user_id' => $user->id]);

        $agentMock = Mockery::mock(AgentService::class);
        $agentMock->shouldReceive('generateDirect')->once()->andReturn([
            'template' => [
                'name' => 'Welcome A-Z',
                'subject' => 'Welcome {{first_name}}',
                'preview_text' => 'Start here',
                'body_html' => '<p>Hello {{first_name}}</p>',
                'status' => 'active',
            ],
            'automation' => [
                'name' => 'A-Z Flow',
                'status' => 'active',
                'trigger_type' => 'lead_created',
                'timezone' => 'UTC',
                'steps' => [
                    ['step_type' => 'send_email'],
                    ['step_type' => 'wait', 'delay_value' => 2, 'delay_unit' => 'days'],
                    ['step_type' => 'send_email'],
                ],
            ],
            'contacts' => [
                ['email' => 'lead@example.com', 'first_name' => 'Lead', 'last_name' => 'One'],
            ],
            'email_settings' => [
                'mail_driver' => 'smtp',
                'from_name' => 'Support Team',
                'from_email' => 'support@example.com',
            ],
        ]);
        $agentMock->shouldReceive('chatReply')->never();
        $this->app->instance(AgentService::class, $agentMock);

        $response = $this->actingAs($user)->postJson(route('dashboard.assistant.chat'), [
            'message' => 'do that for me, setup email automation from A to Z',
            'history' => [
                ['role' => 'assistant', 'content' => 'I can help you set it up.'],
            ],
            'current_route' => 'email-automation.templates.index',
            'current_url' => route('email-automation.templates.index'),
            'page_title' => 'Email Templates',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('email_templates', [
            'user_id' => $user->id,
            'name' => 'Welcome A-Z',
            'subject' => 'Welcome {{first_name}}',
        ]);

        $this->assertDatabaseHas('email_automations', [
            'user_id' => $user->id,
            'name' => 'A-Z Flow',
            'status' => 'active',
            'trigger_type' => 'lead_created',
        ]);

        $automationId = \DB::table('email_automations')
            ->where('user_id', $user->id)
            ->where('name', 'A-Z Flow')
            ->value('id');

        $this->assertDatabaseHas('email_automation_steps', [
            'automation_id' => $automationId,
            'step_order' => 1,
            'step_type' => 'send_email',
        ]);

        $this->assertDatabaseHas('email_contacts', [
            'user_id' => $user->id,
            'email' => 'lead@example.com',
        ]);

        $this->assertDatabaseHas('email_settings', [
            'user_id' => $user->id,
            'mail_driver' => 'smtp',
            'from_name' => 'Support Team',
            'from_email' => 'support@example.com',
        ]);
    }

    public function test_assistant_stays_in_guidance_mode_for_non_execution_question(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->create(['user_id' => $user->id]);

        $agentMock = Mockery::mock(AgentService::class);
        $agentMock->shouldReceive('generateDirect')->never();
        $agentMock->shouldReceive('chatReply')->once()->andReturn(
            'Open Email Automations and click Create Automation.' . "\n" .
            'URL: ' . route('email-automation.automations.index')
        );
        $this->app->instance(AgentService::class, $agentMock);

        $response = $this->actingAs($user)->postJson(route('dashboard.assistant.chat'), [
            'message' => 'how can I setup email automation?',
            'history' => [],
            'current_route' => 'email-automation.automations.index',
            'current_url' => route('email-automation.automations.index'),
            'page_title' => 'Email Automations',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('reply', 'Open Email Automations and click Create Automation.' . "\n" . 'URL: ' . route('email-automation.automations.index'));

        $this->assertDatabaseCount('email_templates', 0);
        $this->assertDatabaseCount('email_automations', 0);
    }
}

