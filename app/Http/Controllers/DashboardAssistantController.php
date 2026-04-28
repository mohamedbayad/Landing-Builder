<?php

namespace App\Http\Controllers;

use App\Models\EmailAutomation;
use App\Models\EmailAutomationStep;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Workspace;
use App\Services\AgentService;
use App\Services\Email\EmailContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class DashboardAssistantController extends Controller
{
    public function __construct(
        private AgentService $agent,
        private EmailContactService $contactService
    )
    {
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1400'],
            'history' => ['nullable', 'array', 'max:12'],
            'history.*.role' => ['required', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required', 'string', 'max:1400'],
            'current_url' => ['nullable', 'string', 'max:2048'],
            'current_route' => ['nullable', 'string', 'max:255'],
            'page_title' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $workspace = $user?->workspaces()->first();

        if (!$user || !$workspace) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active workspace found for this account.',
            ], 422);
        }

        $routeContext = trim((string) ($validated['current_route'] ?? ''));
        $routeContext = $routeContext !== '' ? $routeContext : 'unknown';
        $urlContext = trim((string) ($validated['current_url'] ?? ''));
        $urlContext = $urlContext !== '' ? $urlContext : 'not provided';
        $pageTitle = trim((string) ($validated['page_title'] ?? ''));
        $pageTitle = $pageTitle !== '' ? $pageTitle : 'not provided';
        $dashboardUrls = $this->dashboardUrlCatalog();
        $urlCatalogText = collect($dashboardUrls)
            ->map(fn (string $url, string $label) => "- {$label}: {$url}")
            ->implode("\n");

        $systemPrompt = <<<PROMPT
You are the admin dashboard assistant for Landing Builder.
Current admin context:
- User name: {$user->name}
- Route name: {$routeContext}
- Page title: {$pageTitle}
- URL: {$urlContext}

What you can help with:
- Dashboard navigation
- Landings, templates, editor usage
- Funnel setup (steps, products, checkout fields)
- Leads, forms, analytics, media
- AI settings and provider setup
- Plugin management and activation
- Email automation module usage

Allowed dashboard URLs (use only these links):
{$urlCatalogText}

Rules:
1. Give practical step-by-step instructions for actions inside dashboard.
2. Do not claim you already changed settings or clicked buttons.
3. If a task requires permissions, mention that clearly.
4. Keep replies concise and actionable.
5. Match the user's language.
6. If unclear, ask one short clarifying question.
7. If the user asks about a page or where to do an action, include one direct clickable URL from the allowed list.
8. Put the link on a separate final line as: URL: https://...
9. If an action needs selecting a specific landing/item first, point first to the list page URL.
PROMPT;

        $history = collect($validated['history'] ?? [])
            ->take(-10)
            ->map(fn ($item) => [
                'role' => (string) ($item['role'] ?? ''),
                'content' => (string) ($item['content'] ?? ''),
            ])
            ->values()
            ->all();

        $messages = [...$history, [
            'role' => 'user',
            'content' => (string) $validated['message'],
        ]];

        try {
            $executionReply = $this->maybeExecuteEmailAutomationSetup(
                validated: $validated,
                history: $history,
                user: $user,
                workspace: $workspace
            );

            if ($executionReply !== null) {
                return response()->json([
                    'status' => 'success',
                    'reply' => $executionReply,
                ]);
            }

            $reply = $this->agent->chatReply($messages, $systemPrompt, 'text_generation', (int) $workspace->id);

            return response()->json([
                'status' => 'success',
                'reply' => $reply,
            ]);
        } catch (Throwable $e) {
            Log::error('Dashboard assistant failed', [
                'user_id' => $user->id,
                'workspace_id' => $workspace->id,
                'route' => $routeContext,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Assistant is temporarily unavailable. Please try again.',
            ], 500);
        }
    }

    private function maybeExecuteEmailAutomationSetup(
        array $validated,
        array $history,
        User $user,
        Workspace $workspace
    ): ?string {
        $message = trim((string) ($validated['message'] ?? ''));
        if (!$this->isExecutionRequest($message, $history)) {
            return null;
        }

        $plan = $this->buildExecutionPlan($message, $history, $workspace);
        $summary = $this->executeEmailAutomationPlan($plan, $user, $workspace);

        return $this->buildExecutionReply($summary);
    }

    private function isExecutionRequest(string $message, array $history): bool
    {
        $messageText = Str::lower($message);
        $historyText = collect($history)
            ->pluck('content')
            ->implode(' ');
        $combined = Str::lower(trim($message . ' ' . $historyText));

        $executionPhrases = [
            'do that for me',
            'do it for me',
            'set it up for me',
            'setup it for me',
            'run it now',
            'execute now',
            'from a to z',
            'man a-z',
            'man az',
            'dirha',
            'diriha',
            'nafdha',
            'نفذ',
            'نفذها',
        ];

        $hasExecutionPhrase = collect($executionPhrases)
            ->contains(fn (string $phrase) => str_contains($messageText, $phrase));

        if (!$hasExecutionPhrase) {
            return false;
        }

        $hasEmailContext = str_contains($combined, 'email')
            && (
                str_contains($combined, 'automation')
                || str_contains($combined, 'template')
                || str_contains($combined, 'smtp')
                || str_contains($combined, 'contacts')
            );

        return $hasEmailContext;
    }

    private function buildExecutionPlan(string $message, array $history, Workspace $workspace): array
    {
        $conversation = collect($history)
            ->take(-8)
            ->map(function (array $item): string {
                $role = strtoupper((string) ($item['role'] ?? 'user'));
                $content = trim((string) ($item['content'] ?? ''));
                return "{$role}: {$content}";
            })
            ->implode("\n");

        $instructions = <<<PROMPT
You are an execution planner for dashboard automation setup.
Generate a JSON plan to setup email automation end-to-end.

Rules:
1. Keep values practical and production-ready.
2. Always include one template and one automation.
3. Steps must contain at least one send_email step.
4. If data is missing, fill reasonable defaults.
5. Keep all strings concise.
PROMPT;

        $outputFormat = <<<'JSON'
{
  "template": {
    "name": "string",
    "subject": "string",
    "preview_text": "string",
    "body_html": "string",
    "status": "active"
  },
  "automation": {
    "name": "string",
    "status": "active",
    "trigger_type": "lead_created",
    "timezone": "UTC",
    "trigger_config": {
      "landing_id": null,
      "form_endpoint_id": null,
      "product_id": null
    },
    "steps": [
      {"step_type":"send_email"},
      {"step_type":"wait","delay_value":1,"delay_unit":"days"}
    ]
  },
  "email_settings": {
    "mail_driver": null,
    "from_name": null,
    "from_email": null,
    "reply_to_email": null,
    "smtp_host": null,
    "smtp_port": null,
    "smtp_username": null,
    "smtp_password": null,
    "smtp_encryption": null,
    "default_footer": null,
    "unsubscribe_text": null
  },
  "contacts": [
    {
      "email": null,
      "first_name": null,
      "last_name": null,
      "phone": null
    }
  ]
}
JSON;

        try {
            $result = $this->agent->generateDirect(
                prompt: "Conversation context:\n{$conversation}\n\nLatest user request:\n{$message}",
                instructions: $instructions,
                outputFormat: $outputFormat,
                imageUrl: null,
                role: 'text_generation',
                workspaceId: (int) $workspace->id
            );

            return $this->normalizePlan($result, $message);
        } catch (Throwable $e) {
            Log::warning('Dashboard assistant plan generation failed, using fallback', [
                'workspace_id' => $workspace->id,
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackPlan($message);
        }
    }

    private function normalizePlan(array $result, string $message): array
    {
        $candidate = $result;
        if (isset($candidate['plan']) && is_array($candidate['plan'])) {
            $candidate = $candidate['plan'];
        }

        if (isset($candidate['reply']) && is_string($candidate['reply'])) {
            $decoded = json_decode($candidate['reply'], true);
            if (is_array($decoded)) {
                $candidate = $decoded;
            }
        }

        if (!is_array($candidate)) {
            return $this->fallbackPlan($message);
        }

        return array_replace_recursive($this->fallbackPlan($message), $candidate);
    }

    private function fallbackPlan(string $message): array
    {
        $headline = trim($message) !== ''
            ? Str::limit($message, 80, '')
            : 'New lead follow-up sequence';

        return [
            'template' => [
                'name' => 'AI Follow Up Template',
                'subject' => 'Quick follow-up from {{landing_page_name}}',
                'preview_text' => 'Thanks for your interest. Here is your next step.',
                'body_html' => $this->defaultTemplateHtml($headline),
                'status' => 'active',
            ],
            'automation' => [
                'name' => 'AI Email Automation',
                'status' => 'active',
                'trigger_type' => 'lead_created',
                'timezone' => config('app.timezone', 'UTC'),
                'trigger_config' => [],
                'steps' => [
                    ['step_type' => 'send_email'],
                    ['step_type' => 'wait', 'delay_value' => 1, 'delay_unit' => 'days'],
                    ['step_type' => 'send_email'],
                ],
            ],
            'email_settings' => [],
            'contacts' => [],
        ];
    }

    private function executeEmailAutomationPlan(array $plan, User $user, Workspace $workspace): array
    {
        return DB::transaction(function () use ($plan, $user, $workspace): array {
            $templateData = (array) ($plan['template'] ?? []);
            $template = EmailTemplate::query()->create([
                'user_id' => $user->id,
                'name' => Str::limit(trim((string) ($templateData['name'] ?? 'AI Template')), 255, ''),
                'subject' => Str::limit(trim((string) ($templateData['subject'] ?? 'Follow up from {{landing_page_name}}')), 255, ''),
                'preview_text' => Str::limit(trim((string) ($templateData['preview_text'] ?? 'A quick update from our team.')), 255, ''),
                'body_html' => $this->normalizeTemplateHtml((string) ($templateData['body_html'] ?? '')),
                'status' => Str::limit(trim((string) ($templateData['status'] ?? 'active')), 50, ''),
            ]);

            $automationData = (array) ($plan['automation'] ?? []);
            $automation = EmailAutomation::query()->create([
                'user_id' => $user->id,
                'name' => Str::limit(trim((string) ($automationData['name'] ?? 'AI Automation')), 255, ''),
                'status' => $this->normalizeAutomationStatus((string) ($automationData['status'] ?? 'active')),
                'trigger_type' => $this->normalizeTriggerType((string) ($automationData['trigger_type'] ?? 'lead_created')),
                'trigger_config' => $this->sanitizeTriggerConfig((array) ($automationData['trigger_config'] ?? []), $workspace),
                'conditions' => null,
                'timezone' => Str::limit(trim((string) ($automationData['timezone'] ?? config('app.timezone', 'UTC'))), 64, ''),
                'settings' => null,
            ]);

            $steps = $this->normalizeSteps((array) ($automationData['steps'] ?? []));
            if (collect($steps)->where('step_type', 'send_email')->isEmpty()) {
                array_unshift($steps, ['step_type' => 'send_email']);
            }

            foreach ($steps as $index => $step) {
                EmailAutomationStep::query()->create([
                    'automation_id' => $automation->id,
                    'step_order' => $index + 1,
                    'step_type' => $step['step_type'],
                    'delay_value' => $step['step_type'] === 'wait' ? (int) ($step['delay_value'] ?? 0) : null,
                    'delay_unit' => $step['step_type'] === 'wait' ? (string) ($step['delay_unit'] ?? 'minutes') : null,
                    'template_id' => $step['step_type'] === 'send_email' ? $template->id : null,
                    'rules' => null,
                    'settings' => null,
                ]);
            }

            $settingsUpdated = $this->applyEmailSettings($user, (array) ($plan['email_settings'] ?? []));
            $contactsCreated = $this->createSeedContacts($user, (array) ($plan['contacts'] ?? []));

            return [
                'template' => $template,
                'automation' => $automation,
                'steps_count' => count($steps),
                'contacts_created' => $contactsCreated,
                'settings_updated' => $settingsUpdated,
            ];
        });
    }

    private function normalizeTemplateHtml(string $html): string
    {
        $value = trim($html);
        if ($value !== '') {
            return $value;
        }

        return $this->defaultTemplateHtml('Follow-up');
    }

    private function defaultTemplateHtml(string $headline): string
    {
        $safeHeadline = e(Str::limit(trim($headline), 120, ''));

        return <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:24px 0;">
  <tr>
    <td align="center">
      <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="max-width:680px;width:100%;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
        <tr>
          <td style="padding:28px 24px;font-family:Arial,sans-serif;color:#0f172a;">
            <h1 style="margin:0 0 12px;font-size:24px;line-height:1.3;">{$safeHeadline}</h1>
            <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#334155;">Thanks for your interest. We prepared the next step for you and kept it simple.</p>
            <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#334155;">If you need any help, just reply to this email and our team will guide you.</p>
            <table role="presentation" cellpadding="0" cellspacing="0">
              <tr>
                <td style="background:#ea580c;border-radius:8px;">
                  <a href="{{unsubscribe_url}}" style="display:inline-block;padding:12px 18px;color:#fff7ed;text-decoration:none;font-weight:700;font-size:14px;">Manage Preferences</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
HTML;
    }

    private function normalizeAutomationStatus(string $status): string
    {
        $value = Str::lower(trim($status));
        if (in_array($value, ['draft', 'active', 'paused'], true)) {
            return $value;
        }

        return 'active';
    }

    private function normalizeTriggerType(string $triggerType): string
    {
        $value = Str::lower(trim($triggerType));
        if (in_array($value, ['form_submitted', 'checkout_completed', 'lead_created'], true)) {
            return $value;
        }

        return 'lead_created';
    }

    private function sanitizeTriggerConfig(array $config, Workspace $workspace): array
    {
        $clean = [];
        if (!empty($config['landing_id']) && is_numeric($config['landing_id'])) {
            $landingId = (int) $config['landing_id'];
            $exists = $workspace->landings()->where('id', $landingId)->exists();
            if ($exists) {
                $clean['landing_id'] = $landingId;
            }
        }

        if (!empty($config['form_endpoint_id']) && is_numeric($config['form_endpoint_id'])) {
            $endpointId = (int) $config['form_endpoint_id'];
            $exists = $workspace->formEndpoints()->where('id', $endpointId)->exists();
            if ($exists) {
                $clean['form_endpoint_id'] = $endpointId;
            }
        }

        if (!empty($config['product_id']) && is_numeric($config['product_id'])) {
            $productId = (int) $config['product_id'];
            $exists = DB::table('products')
                ->join('landings', 'landings.id', '=', 'products.landing_id')
                ->where('products.id', $productId)
                ->where('landings.workspace_id', $workspace->id)
                ->exists();
            if ($exists) {
                $clean['product_id'] = $productId;
            }
        }

        return $clean;
    }

    private function normalizeSteps(array $steps): array
    {
        $normalized = [];

        foreach ($steps as $step) {
            if (!is_array($step)) {
                continue;
            }

            $stepType = Str::lower(trim((string) ($step['step_type'] ?? '')));
            if ($stepType === 'send_email') {
                $normalized[] = ['step_type' => 'send_email'];
                continue;
            }

            if ($stepType === 'wait') {
                $delayValue = max(0, (int) ($step['delay_value'] ?? 0));
                $delayUnit = Str::lower(trim((string) ($step['delay_unit'] ?? 'minutes')));
                if (!in_array($delayUnit, ['minutes', 'hours', 'days'], true)) {
                    $delayUnit = 'minutes';
                }

                $normalized[] = [
                    'step_type' => 'wait',
                    'delay_value' => $delayValue,
                    'delay_unit' => $delayUnit,
                ];
            }
        }

        if ($normalized === []) {
            return [['step_type' => 'send_email']];
        }

        return $normalized;
    }

    private function applyEmailSettings(User $user, array $settings): bool
    {
        if ($settings === []) {
            return false;
        }

        $setting = EmailSetting::query()->firstOrCreate(['user_id' => $user->id]);
        $update = [];

        $driver = Str::lower(trim((string) ($settings['mail_driver'] ?? '')));
        if (in_array($driver, ['smtp', 'ses', 'postmark', 'resend', 'sendmail', 'log', 'array'], true)) {
            $update['mail_driver'] = $driver;
        }

        $fromEmail = trim((string) ($settings['from_email'] ?? ''));
        if ($fromEmail !== '' && filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $update['from_email'] = Str::lower($fromEmail);
        }

        $replyTo = trim((string) ($settings['reply_to_email'] ?? ''));
        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $update['reply_to_email'] = Str::lower($replyTo);
        }

        $fromName = trim((string) ($settings['from_name'] ?? ''));
        if ($fromName !== '') {
            $update['from_name'] = Str::limit($fromName, 255, '');
        }

        $smtpHost = trim((string) ($settings['smtp_host'] ?? ''));
        if ($smtpHost !== '') {
            $update['smtp_host'] = Str::limit($smtpHost, 255, '');
        }

        if (!empty($settings['smtp_port']) && is_numeric($settings['smtp_port'])) {
            $port = (int) $settings['smtp_port'];
            if ($port >= 1 && $port <= 65535) {
                $update['smtp_port'] = $port;
            }
        }

        $smtpUsername = trim((string) ($settings['smtp_username'] ?? ''));
        if ($smtpUsername !== '') {
            $update['smtp_username'] = Str::limit($smtpUsername, 255, '');
        }

        $smtpPassword = (string) ($settings['smtp_password'] ?? '');
        if (trim($smtpPassword) !== '') {
            $update['smtp_password'] = Str::limit($smtpPassword, 255, '');
        }

        $smtpEncryption = Str::lower(trim((string) ($settings['smtp_encryption'] ?? '')));
        if ($smtpEncryption === 'starttls') {
            $smtpEncryption = 'tls';
        }
        if (in_array($smtpEncryption, ['tls', 'ssl'], true)) {
            $update['smtp_encryption'] = $smtpEncryption;
        }

        $settingsPayload = is_array($setting->settings) ? $setting->settings : [];
        $defaultFooter = trim((string) ($settings['default_footer'] ?? ''));
        if ($defaultFooter !== '') {
            $settingsPayload['default_footer'] = Str::limit($defaultFooter, 2000, '');
        }
        $unsubscribeText = trim((string) ($settings['unsubscribe_text'] ?? ''));
        if ($unsubscribeText !== '') {
            $settingsPayload['unsubscribe_text'] = Str::limit($unsubscribeText, 1000, '');
        }
        if ($settingsPayload !== []) {
            $update['settings'] = $settingsPayload;
        }

        if ($update === []) {
            return false;
        }

        $setting->update($update);
        return true;
    }

    private function createSeedContacts(User $user, array $contacts): int
    {
        $created = 0;
        foreach (array_slice($contacts, 0, 25) as $contact) {
            if (!is_array($contact)) {
                continue;
            }

            $email = trim((string) ($contact['email'] ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $createdContact = $this->contactService->upsertFromPayload($user->id, [
                'email' => Str::lower($email),
                'first_name' => trim((string) ($contact['first_name'] ?? '')),
                'last_name' => trim((string) ($contact['last_name'] ?? '')),
                'phone' => trim((string) ($contact['phone'] ?? '')),
                'source' => 'dashboard_ai_assistant',
            ]);

            if ($createdContact) {
                $created++;
            }
        }

        return $created;
    }

    private function buildExecutionReply(array $summary): string
    {
        /** @var \App\Models\EmailTemplate $template */
        $template = $summary['template'];
        /** @var \App\Models\EmailAutomation $automation */
        $automation = $summary['automation'];
        $stepsCount = (int) ($summary['steps_count'] ?? 0);
        $contactsCreated = (int) ($summary['contacts_created'] ?? 0);
        $settingsUpdated = (bool) ($summary['settings_updated'] ?? false);

        $lines = [
            'Done. I completed the email automation setup for you end-to-end.',
            '',
            'Created:',
            "- Template: {$template->name}",
            "- Automation: {$automation->name} ({$automation->status})",
            "- Steps: {$stepsCount}",
            '- Trigger: ' . str_replace('_', ' ', $automation->trigger_type),
            "- Seed contacts added: {$contactsCreated}",
            '- Email sender settings: ' . ($settingsUpdated ? 'updated' : 'not changed (no credentials provided)'),
            '',
            'URL: ' . route('email-automation.automations.index'),
            'URL: ' . route('email-automation.templates.index'),
            'URL: ' . route('email-automation.settings.index'),
        ];

        return implode("\n", $lines);
    }

    private function dashboardUrlCatalog(): array
    {
        return [
            'Dashboard' => route('dashboard'),
            'Online Users' => route('online-users.index'),
            'Landings' => route('landings.index'),
            'Templates' => route('templates.index'),
            'Leads' => route('leads.index'),
            'Forms' => route('forms.index'),
            'Analytics' => route('analytics.index'),
            'Media Library' => route('media.index'),
            'AI Generator' => route('ai-generator.index'),
            'Settings' => route('settings.index'),
            'Plugin Settings' => route('settings.plugins.index'),
            'Plugin Manager' => route('plugins.index'),
            'Email Automations' => route('email-automation.automations.index'),
            'Email Templates' => route('email-automation.templates.index'),
            'Email Contacts' => route('email-automation.contacts.index'),
            'Email Activity' => route('email-automation.activity.index'),
            'Email Analytics' => route('email-automation.analytics.index'),
            'Email Settings' => route('email-automation.settings.index'),
            'Domains' => route('domains.index'),
            'Session Recordings' => route('recordings.index'),
            'Profile' => route('profile.edit'),
        ];
    }
}
