<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailMessageJob;
use App\Models\EmailTemplate;
use App\Services\AgentService;
use App\Services\Email\EmailContactService;
use App\Services\Email\EmailTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::query()
            ->where('user_id', Auth::id())
            ->withCount('messages')
            ->latest()
            ->paginate(20);

        return view('email-automation.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('email-automation.templates.form', [
            'template' => new EmailTemplate(),
            'action' => route('email-automation.templates.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatedPayload($request);
        $payload['user_id'] = Auth::id();

        EmailTemplate::create($payload);

        return redirect()->route('email-automation.templates.index')
            ->with('success', 'Template created.');
    }

    public function edit(EmailTemplate $template)
    {
        $this->authorizeTemplate($template);

        return view('email-automation.templates.form', [
            'template' => $template,
            'action' => route('email-automation.templates.update', $template),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $this->authorizeTemplate($template);
        $template->update($this->validatedPayload($request));

        return redirect()->route('email-automation.templates.index')
            ->with('success', 'Template updated.');
    }

    public function destroy(EmailTemplate $template)
    {
        $this->authorizeTemplate($template);
        $template->delete();

        return redirect()->route('email-automation.templates.index')
            ->with('success', 'Template deleted.');
    }

    public function duplicate(EmailTemplate $template)
    {
        $this->authorizeTemplate($template);

        $copy = $template->replicate();
        $copy->name = $template->name.' (Copy)';
        $copy->save();

        return redirect()->route('email-automation.templates.edit', $copy)
            ->with('success', 'Template duplicated.');
    }

    public function sendTest(
        Request $request,
        EmailTemplate $template,
        EmailContactService $contactService,
        EmailTrackingService $trackingService
    )
    {
        $this->authorizeTemplate($template);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
        ]);

        $contact = $contactService->upsertFromPayload(Auth::id(), [
            'email' => $validated['email'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'source' => 'test_send',
        ]);

        try {
            // For manual testing we run synchronously so users see real delivery errors immediately.
            SendEmailMessageJob::dispatchSync(
                userId: Auth::id(),
                automationId: null,
                stepId: null,
                templateId: $template->id,
                contactId: $contact?->id,
                context: [
                    'source' => 'test_send',
                    'landing_page_name' => 'Test Context',
                ]
            );

            $redirect = redirect()->route('email-automation.templates.index')
                ->with('success', 'Test email sent successfully.');

            if (!$trackingService->isLikelyPublicBaseUrl()) {
                $redirect->with('warning', 'Open tracking may not register because your tracking URL is local/private ('
                    .$trackingService->trackingBaseUrl()
                    .'). Use a public domain or tunnel URL for reliable open tracking in real inboxes.');
            }

            return $redirect;
        } catch (Throwable $exception) {
            return redirect()->route('email-automation.templates.index')
                ->with('error', 'Test email failed: '.$exception->getMessage());
        }
    }

    public function generateBody(Request $request, AgentService $agent)
    {
        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'brief' => 'nullable|string|max:1200',
            'tone' => 'nullable|string|max:80',
        ]);

        $subject = trim((string) ($validated['subject'] ?? ''));
        $previewText = trim((string) ($validated['preview_text'] ?? ''));
        $brief = trim((string) ($validated['brief'] ?? ''));
        $tone = trim((string) ($validated['tone'] ?? 'professional and persuasive'));

        if ($subject === '' && $previewText === '' && $brief === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Add at least a subject or a short AI brief before generating.',
            ], 422);
        }

        $workspaceId = optional(Auth::user()?->workspaces()->first())->id;

        if (!$workspaceId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active workspace found for AI generation.',
            ], 422);
        }

        $mergeTags = $this->supportedMergeTags();
        $mergeTagText = collect($mergeTags)
            ->map(fn (string $tag) => '{{' . $tag . '}}')
            ->implode(', ');

        $systemPrompt = <<<PROMPT
You are a senior email marketing copywriter and HTML email designer.
Create high-converting, professional email body HTML.

Rules:
1. Return only JSON using the required output format keys.
2. body_html must be valid email HTML with table-based structure, inline styles, and good spacing.
3. Make copy clear, persuasive, and trustworthy.
4. Keep a strong CTA button and mobile-friendly width.
5. Use these merge tags naturally when relevant: {$mergeTagText}
6. Include unsubscribe placeholder when footer/support context is relevant: {{unsubscribe_url}}
7. Avoid scripts, forms, external JS, and CSS files.
8. Keep the result production-ready for email clients.
PROMPT;

        $userPrompt = <<<PROMPT
Generate an email template body.

Subject: {$subject}
Preview text: {$previewText}
Goal/brief: {$brief}
Tone: {$tone}
PROMPT;

        $outputFormat = <<<'JSON'
{
  "subject_suggestion": "string",
  "body_html": "<table role=\"presentation\" ...>...</table>"
}
JSON;

        try {
            $result = $agent->generateDirect(
                $userPrompt,
                $systemPrompt,
                $outputFormat,
                null,
                'text_generation',
                (int) $workspaceId
            );

            $bodyHtml = $this->extractGeneratedBodyHtml($result);
            $subjectSuggestion = trim((string) ($result['subject_suggestion'] ?? ''));

            if ($bodyHtml === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'AI generated an empty email body. Please refine your brief and try again.',
                ], 422);
            }

            return response()->json([
                'status' => 'success',
                'body_html' => $bodyHtml,
                'subject_suggestion' => $subjectSuggestion,
            ]);
        } catch (Throwable $e) {
            Log::error('Email template AI body generation failed', [
                'user_id' => Auth::id(),
                'workspace_id' => $workspaceId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'AI generation failed. Please try again in a moment.',
            ], 500);
        }
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'preview_text' => 'nullable|string|max:255',
            'body_html' => 'required|string',
            'body_json' => 'nullable|array',
            'status' => 'nullable|string|max:50',
        ]);
    }

    private function authorizeTemplate(EmailTemplate $template): void
    {
        if ($template->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function supportedMergeTags(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'phone',
            'product_name',
            'order_total',
            'landing_page_name',
            'unsubscribe_url',
        ];
    }

    private function extractGeneratedBodyHtml(array $result): string
    {
        $candidate = $result['body_html'] ?? $result['html'] ?? $result['content'] ?? '';

        if (is_array($candidate)) {
            $candidate = json_encode($candidate);
        }

        $html = trim((string) $candidate);
        if ($html === '') {
            return '';
        }

        // Strip markdown fences if model wrapped output.
        $html = preg_replace('/^```(?:html)?\s*/i', '', $html) ?? $html;
        $html = preg_replace('/\s*```$/i', '', $html) ?? $html;

        return trim($html);
    }
}
