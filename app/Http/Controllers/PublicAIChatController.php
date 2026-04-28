<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Services\AgentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicAIChatController extends Controller
{
    public function __construct(protected AgentService $agent)
    {
    }

    public function ask(Request $request)
    {
        $validated = $request->validate([
            'landing_id' => ['required', 'integer', 'exists:landings,id'],
            'page_id' => ['nullable', 'integer'],
            'message' => ['required', 'string', 'max:1200'],
            'history' => ['nullable', 'array', 'max:12'],
            'history.*.role' => ['required', 'string', Rule::in(['user', 'assistant'])],
            'history.*.content' => ['required', 'string', 'max:1200'],
            'current_url' => ['nullable', 'string', 'max:2048'],
        ]);
        $userMessage = trim((string) $validated['message']);
        $history = collect($validated['history'] ?? [])
            ->take(-10)
            ->map(fn ($item) => [
                'role' => (string) ($item['role'] ?? ''),
                'content' => (string) ($item['content'] ?? ''),
            ])
            ->values()
            ->all();

        $landing = Landing::with(['workspace.settings', 'settings'])->findOrFail((int) $validated['landing_id']);

        if ($landing->status !== 'published') {
            $ownerId = (int) ($landing->workspace->user_id ?? 0);
            if (!auth()->check() || (int) auth()->id() !== $ownerId) {
                abort(404);
            }
        }

        $page = null;
        $pageContext = 'Unknown page';
        if (!empty($validated['page_id'])) {
            $page = $landing->pages()->where('id', (int) $validated['page_id'])->first();
            if ($page) {
                $pageContext = trim(($page->name ?: $page->slug) . ' (' . $page->type . ')');
            }
        }
        if (!$page) {
            $page = $landing->pages()->where('type', 'index')->first() ?? $landing->pages()->first();
            if ($page) {
                $pageContext = trim(($page->name ?: $page->slug) . ' (' . $page->type . ')');
            }
        }

        $products = $landing->products()
            ->select(['name', 'price', 'currency', 'description', 'label'])
            ->limit(6)
            ->get();
        $productSummary = $products->isEmpty()
            ? 'No product list available.'
            : $products->map(function ($product) {
                $name = trim((string) ($product->name ?? 'Product'));
                $price = $product->price !== null ? number_format((float) $product->price, 2) : null;
                $currency = trim((string) ($product->currency ?? ''));
                $label = trim((string) ($product->label ?? ''));
                $description = trim((string) ($product->description ?? ''));

                $parts = [$name];
                if ($label !== '') {
                    $parts[] = "label: {$label}";
                }
                if ($price !== null) {
                    $parts[] = "price: {$price}" . ($currency !== '' ? " {$currency}" : '');
                }
                if ($description !== '') {
                    $parts[] = 'desc: ' . Str::limit($description, 90, '...');
                }

                return implode(' | ', $parts);
            })->implode(' || ');
        $landingNameForPrompt = $this->sanitizePromptContextText((string) $landing->name, 90, 'This offer');
        $offerSummary = trim((string) optional($landing->settings)->meta_description);
        $offerSummary = $offerSummary !== ''
            ? $this->sanitizePromptContextText($offerSummary, 240, 'No additional offer summary provided.')
            : 'No additional offer summary provided.';
        $metaTitle = trim((string) optional($landing->settings)->meta_title);
        $metaTitle = $metaTitle !== ''
            ? $this->sanitizePromptContextText($metaTitle, 130, $landingNameForPrompt)
            : $landingNameForPrompt;
        $currentUrl = trim((string) ($validated['current_url'] ?? ''));
        $currentUrl = $currentUrl !== '' ? $currentUrl : 'Not provided.';
        $pageHtml = (string) optional($page)->html;
        $pageTextSummary = $this->extractReadableText($pageHtml, 2200);
        $pageHeadings = $this->extractTagTexts($pageHtml, ['h1', 'h2', 'h3'], 10, 120);
        $pageCtas = $this->extractTagTexts($pageHtml, ['a', 'button'], 12, 90);
        $formFieldNames = $this->extractFormFieldNames($pageHtml, 14);
        $offerSignals = $this->extractOfferSignals($pageHtml, 12);
        $ctaContext = $this->buildCtaContext($pageCtas, $formFieldNames);
        $ctaPayload = $this->resolveActionCtaPayload($landing, $ctaContext, $userMessage);
        $ctaMode = $ctaPayload !== null ? 'enabled' : 'disabled';
        $ctaType = (string) ($ctaPayload['type'] ?? 'none');
        $ctaActionText = (string) ($ctaPayload['action_text'] ?? 'none');
        $intentGuide = $this->buildIntentGuide($userMessage, $history);
        $conversationGuide = $this->buildConversationGuide($userMessage, $history);

        $pageHeadingSummary = empty($pageHeadings) ? 'No heading snippets found.' : implode(' | ', $pageHeadings);
        $pageCtaSummary = empty($pageCtas) ? 'No CTA labels found.' : implode(' | ', $pageCtas);
        $formFieldSummary = empty($formFieldNames) ? 'No form field names found.' : implode(', ', $formFieldNames);
        $offerSignalSummary = empty($offerSignals) ? 'No strong offer signals detected.' : implode(' | ', $offerSignals);
        $pageTextSummary = $pageTextSummary !== '' ? $pageTextSummary : 'No readable LP content found.';

        $systemPrompt = <<<PROMPT
You are the brand's sales assistant for this landing page (commercial + support mindset).
Use this context:
- Landing name: {$landingNameForPrompt}
- Landing slug: {$landing->slug}
- Meta title: {$metaTitle}
- Current page: {$pageContext}
- Current URL: {$currentUrl}
- Offer summary: {$offerSummary}
- Products: {$productSummary}
- Page headings: {$pageHeadingSummary}
- CTA labels: {$pageCtaSummary}
- Form fields: {$formFieldSummary}
- Offer signals: {$offerSignalSummary}
- LP content summary: {$pageTextSummary}
- Primary CTA label: {$ctaContext['label']}
- Form CTA instruction: {$ctaContext['instruction']}
- Action CTA mode: {$ctaMode}
- Action CTA type: {$ctaType}
- Action CTA text: {$ctaActionText}
- Response mode guide: {$intentGuide}
- Conversation handling guide: {$conversationGuide}

Rules:
1. Sound like a professional commercial advisor: confident, persuasive, benefit-led, and human.
2. Prioritize concrete LP facts from headings, offer signals, products, CTA labels, and form cues.
3. Answer only questions related to this landing page, its products, offer, checkout, shipping, returns, and support.
4. If asked about unrelated topics, politely refuse and redirect to offer-related help.
5. Never invent details. If a detail is missing, say: "This detail is not clearly shown on this page."
6. Do not add irrelevant sections (example: podcast/blog/date blocks) unless user explicitly asks about them.
7. Match the user's language (Darija/French/English) and keep a natural brand tone.
8. Keep replies very concise (around 25-70 words).
9. Structure each answer in mini-sales flow:
   - Start with a direct helpful answer.
   - Add value/benefit framing tied to the offer.
   - Ask 1-2 short qualification questions to understand need and guide user.
10. For pricing questions, avoid dry answers. If exact price exists, present it with value framing + next step. If missing, state it is not shown, then ask qualification questions and guide user to CTA/form/checkout to get tailored pricing.
11. End with one practical next step focused on reservation.
12. If user sends a short follow-up (1-3 words), treat it as an answer to your previous question. Continue from that context and DO NOT restart full offer explanation.
13. Avoid repeating the same long pitch across turns. Only recap full offer if user explicitly asks for recap/details.
14. Format output as compact natural text (max 2 short paragraphs). Avoid emoji bullets and avoid long lists unless asked.
15. If user states an objective (example: ventes/sales/leads/CPL), answer with a focused mini-plan for that objective, then ask one precise qualifier question.
16. Only show CTA when conversion-ready:
   - user explicitly asks to proceed/apply/book/order/start now, OR
   - user already shared objective + at least one qualifier (budget, timeline, volume, audience, current performance).
17. If not conversion-ready, do NOT push hard CTA yet. Keep one short discovery question only.
18. Do not over-explain. Prioritize fast guidance to reservation/book call/form submission.
19. If Action CTA mode is disabled, do not push button-based CTA and do not instruct user to click any CTA button.
20. Never discuss technical implementation details (HTML, CSS, JS, code, template files, seeders, APIs, dashboards, settings, model/provider names).
21. If the page contains placeholder/technical text, ignore it and focus only on commercial offer value, benefits, fit, objections, and next step.
22. If Action CTA mode is enabled and type is:
   - form: guide user to complete the LP form only when qualified.
   - whatsapp: guide user to continue on WhatsApp only when qualified.
   - custom_phone: guide user to call only when qualified.
   - custom_link or instagram: guide user to that specific CTA only when qualified.
23. Always ask at least one short need-discovery question before pushing CTA unless user explicitly asks to proceed now.
PROMPT;

        $messages = [...$history, [
            'role' => 'user',
            'content' => $userMessage,
        ]];

        try {
            $landingWorkspaceId = (int) $landing->workspace_id;
            $reply = null;
            $usedWorkspaceId = $landingWorkspaceId;

            try {
                $reply = $this->agent->chatReply($messages, $systemPrompt, 'text_generation', $landingWorkspaceId);
            } catch (\Throwable $primaryError) {
                if (!$this->isRecoverableProviderError($primaryError)) {
                    throw $primaryError;
                }

                $fallbackWorkspaceId = $this->resolveFallbackWorkspaceId($landingWorkspaceId);
                if ($fallbackWorkspaceId === null) {
                    throw $primaryError;
                }

                $reply = $this->agent->chatReply($messages, $systemPrompt, 'text_generation', $fallbackWorkspaceId);
                $usedWorkspaceId = $fallbackWorkspaceId;
            }

            if (!is_string($reply) || trim($reply) === '') {
                throw new \RuntimeException('AI provider returned an empty chat reply.');
            }

            $reply = $this->normalizeReply($reply);
            $showActionCta = $ctaPayload !== null && $this->shouldShowActionCta($userMessage, $history);
            if ($showActionCta) {
                $reply = $this->ensureReplyHasActionCta($reply, $ctaPayload, $userMessage);
            }

            if ($usedWorkspaceId !== $landingWorkspaceId) {
                Log::warning('Public AI chat fallback workspace used', [
                    'landing_id' => $landing->id,
                    'landing_workspace_id' => $landingWorkspaceId,
                    'used_workspace_id' => $usedWorkspaceId,
                    'auth_user_id' => auth()->id(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'reply' => $reply,
                'cta' => $showActionCta ? $ctaPayload : null,
            ]);
        } catch (\Throwable $e) {
            $clientError = $this->mapClientError($e);

            Log::error('Public AI chat failed', [
                'landing_id' => $landing->id,
                'error' => $e->getMessage(),
                'client_message' => $clientError['message'],
                'client_status' => $clientError['status'],
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $clientError['message'],
            ], $clientError['status']);
        }
    }

    private function extractReadableText(string $html, int $maxChars): string
    {
        if (trim($html) === '') {
            return '';
        }

        $withoutScripts = preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html) ?? $html;
        $withoutStyles = preg_replace('/<style\b[^>]*>.*?<\/style>/is', ' ', $withoutScripts) ?? $withoutScripts;
        $text = html_entity_decode(strip_tags($withoutStyles), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = $this->sanitizePromptContextText((string) $text, $maxChars, '');

        return Str::limit($text, $maxChars, '...');
    }

    /**
     * @param string[] $tags
     * @return string[]
     */
    private function extractTagTexts(string $html, array $tags, int $maxItems, int $maxLenPerItem): array
    {
        if (trim($html) === '' || empty($tags)) {
            return [];
        }

        $tagPattern = implode('|', array_map(fn ($tag) => preg_quote($tag, '/'), $tags));
        preg_match_all('/<(' . $tagPattern . ')\b[^>]*>(.*?)<\/\1>/is', $html, $matches);

        return collect($matches[2] ?? [])
            ->map(function ($value) use ($maxLenPerItem) {
                $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
                $text = trim($text);
                return Str::limit($text, $maxLenPerItem, '...');
            })
            ->filter(fn ($text) => is_string($text) && strlen($text) >= 3 && !$this->isLowValueSnippet($text))
            ->unique()
            ->take($maxItems)
            ->values()
            ->all();
    }

    /**
     * @return string[]
     */
    private function extractFormFieldNames(string $html, int $maxItems): array
    {
        if (trim($html) === '') {
            return [];
        }

        preg_match_all('/\bname\s*=\s*["\']([^"\']+)["\']/i', $html, $matches);

        return collect($matches[1] ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '' && !str_starts_with($name, '_'))
            ->unique()
            ->take($maxItems)
            ->values()
            ->all();
    }

    /**
     * @return string[]
     */
    private function extractOfferSignals(string $html, int $maxItems): array
    {
        if (trim($html) === '') {
            return [];
        }

        $candidates = collect([
            ...$this->extractTagTexts($html, ['h1', 'h2', 'h3'], 12, 130),
            ...$this->extractTagTexts($html, ['li', 'p'], 20, 130),
            ...$this->extractTagTexts($html, ['a', 'button'], 14, 80),
        ])
            ->filter(fn ($text) => is_string($text) && $text !== '')
            ->map(fn ($text) => trim((string) $text))
            ->filter(fn ($text) => !$this->isLowValueSnippet($text))
            ->unique()
            ->values();

        return $candidates
            ->map(fn ($line) => [
                'line' => $line,
                'score' => $this->scoreOfferSignal($line),
            ])
            ->sortByDesc('score')
            ->pluck('line')
            ->take($maxItems)
            ->values()
            ->all();
    }

    private function scoreOfferSignal(string $line): int
    {
        $text = mb_strtolower($line);
        $score = 0;

        $keywords = [
            'offer', 'deal', 'discount', 'save', 'free', 'shipping', 'delivery', 'return', 'guarantee',
            'checkout', 'buy', 'order', 'price', 'today', 'benefit', 'results', 'support', 'secure',
            'trial', 'bonus', 'limited', 'cta', 'service', 'solution',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $score += 2;
            }
        }

        if (str_contains($text, '$') || preg_match('/\b\d+([.,]\d{1,2})?\b/', $text)) {
            $score += 2;
        }

        $length = mb_strlen($line);
        if ($length >= 18 && $length <= 110) {
            $score += 2;
        }

        if (preg_match('/\b(get|start|shop|order|buy|claim|subscribe|learn)\b/i', $line)) {
            $score += 2;
        }

        return $score;
    }

    private function isLowValueSnippet(string $text): bool
    {
        $line = mb_strtolower(trim($text));
        if ($line === '') {
            return true;
        }

        if (mb_strlen($line) < 3) {
            return true;
        }

        $noisePatterns = [
            '/\blorem ipsum\b/i',
            '/\b(test|demo|sample|placeholder)\b/i',
            '/\bpodcast\b/i',
            '/\b(insert your html here|templateseeder\.php|edit database\/seeders\/templateseeder\.php)\b/i',
            '/\b(html|css|javascript|js|backend|api key|dashboard settings|seeders?)\b/i',
            '/\boct\b|\bnov\b|\bdec\b|\bjan\b|\bfeb\b|\bmar\b|\bapr\b|\bmay\b|\bjun\b|\bjul\b|\baug\b|\bsep\b/i',
            '/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}$/',
        ];

        foreach ($noisePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    private function buildIntentGuide(string $message, array $history = []): string
    {
        $text = mb_strtolower(trim($message));
        $wordCount = count(array_filter(preg_split('/\s+/u', $text) ?: []));
        $isShortFollowUp = ($wordCount > 0 && $wordCount <= 3) || mb_strlen($text) <= 20;

        $lastAssistantMessage = '';
        foreach (array_reverse($history) as $item) {
            if (($item['role'] ?? '') === 'assistant') {
                $lastAssistantMessage = mb_strtolower(trim((string) ($item['content'] ?? '')));
                break;
            }
        }

        $assistantAskedQuestion = $lastAssistantMessage !== '' && str_contains($lastAssistantMessage, '?');

        $isExplainIntent = preg_match('/\b(explain|summary|summarize|what is this|why|3lach|chno|shno|offer|lp)\b/u', $text) === 1;
        $isPriceIntent = preg_match('/\b(price|cost|how much|flous|thamane|prix)\b/u', $text) === 1;
        $isCheckoutIntent = preg_match('/\b(checkout|order|buy|purchase|delivery|shipping|return|refund)\b/u', $text) === 1;
        $isObjectiveIntent = preg_match('/\b(ventes?|vents|sales?|lead[s]?|prospect[s]?|conversion[s]?|cpl|roas|revenue|ca)\b/u', $text) === 1;

        if ($isShortFollowUp && $assistantAskedQuestion) {
            return 'Follow-up mode: user likely answered your last question. Do not restart from scratch. Acknowledge their short answer, tailor one concrete recommendation, and ask one precise next qualifier question.';
        }

        if ($isExplainIntent) {
            return 'Explain mode: give a persuasive 4-part pitch: (1) what the offer is, (2) who it helps + pain solved, (3) top value/benefits, (4) best next step. Then ask one discovery question to qualify intent.';
        }

        if ($isPriceIntent) {
            return 'Pricing mode: answer like a sales advisor, not a dry FAQ. If price exists, present price + what value is included + who it fits. If missing, say price is not clearly shown, then ask 1-2 qualification questions (goal, scale, budget range, timeline) and guide to CTA/form to unlock tailored pricing.';
        }

        if ($isCheckoutIntent) {
            return 'Checkout mode: explain the process in simple steps using available CTA/form details, reduce friction, reassure user, and end with one action plus one question.';
        }

        if ($isObjectiveIntent) {
            return 'Objective mode: user gave a business objective keyword (sales/leads/etc). Respond with a focused mini-plan linked to the LP offer, then ask one precise qualifier (volume, budget, timeline, or current conversion rate).';
        }

        return 'Default mode: concise persuasive answer grounded in LP facts, include one clear benefit, then ask one short discovery question before any CTA push.';
    }

    private function buildConversationGuide(string $message, array $history): string
    {
        $text = mb_strtolower(trim($message));
        $wordCount = count(array_filter(preg_split('/\s+/u', $text) ?: []));
        $isShortFollowUp = ($wordCount > 0 && $wordCount <= 3) || mb_strlen($text) <= 20;
        $askedRecap = preg_match('/\b(recap|resume|again|details?|more details|aawd|krrr)\b/i', $text) === 1;

        $assistantMessages = collect($history)
            ->filter(fn ($item) => ($item['role'] ?? '') === 'assistant')
            ->pluck('content')
            ->map(fn ($content) => mb_strtolower(trim((string) $content)))
            ->filter()
            ->values();

        $lastAssistant = (string) ($assistantMessages->last() ?? '');
        $assistantAskedQuestion = $lastAssistant !== '' && str_contains($lastAssistant, '?');

        $hasRepeatedPitch = $assistantMessages->contains(function ($content) {
            return str_contains((string) $content, 'offre principale')
                || str_contains((string) $content, 'voici ce que nous proposons')
                || str_contains((string) $content, 'audit strategique');
        });

        return sprintf(
            'short_follow_up=%s; last_assistant_had_question=%s; user_requested_recap=%s; repeated_pitch_detected=%s. Behavior: avoid repetition, stay contextual, and move the conversation forward.',
            $isShortFollowUp ? 'yes' : 'no',
            $assistantAskedQuestion ? 'yes' : 'no',
            $askedRecap ? 'yes' : 'no',
            $hasRepeatedPitch ? 'yes' : 'no'
        );
    }

    private function normalizeReply(string $reply): string
    {
        $clean = trim($reply);
        $clean = preg_replace('/\r\n?/', "\n", $clean) ?? $clean;
        $clean = preg_replace('/\n{3,}/', "\n\n", $clean) ?? $clean;

        // Keep public chat responses compact and conversion-oriented.
        return Str::limit($clean, 420, '...');
    }

    private function sanitizePromptContextText(string $text, int $maxLen = 220, string $fallback = ''): string
    {
        $clean = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? $clean;
        $clean = trim((string) $clean);

        if ($clean === '') {
            return $fallback;
        }

        $blockPatterns = [
            '/insert your html here/i',
            '/edit\s+database\/seeders\/templateseeder\.php\s+to\s+customize\s+this\s+content\.?/i',
            '/templateseeder\.php/i',
            '/\b(html|css|javascript|js|source code|backend|api key|dashboard settings|provider|model)\b/i',
        ];

        foreach ($blockPatterns as $pattern) {
            if (preg_match($pattern, $clean) === 1) {
                $clean = preg_replace($pattern, ' ', $clean) ?? $clean;
            }
        }

        $clean = preg_replace('/\s+/u', ' ', $clean) ?? $clean;
        $clean = trim((string) $clean);

        if ($clean === '') {
            return $fallback;
        }

        return Str::limit($clean, $maxLen, '...');
    }

    /**
     * @param string[] $pageCtas
     * @param string[] $formFieldNames
     * @return array{label:string,instruction:string,fields:array<int,string>,fields_hint:string}
     */
    private function buildCtaContext(array $pageCtas, array $formFieldNames): array
    {
        $cleanCtas = collect($pageCtas)
            ->map(fn ($cta) => trim((string) $cta))
            ->filter(fn ($cta) => $cta !== '' && mb_strlen($cta) >= 3)
            ->unique()
            ->values();

        $label = $cleanCtas
            ->sortByDesc(fn ($cta) => $this->scoreCtaLabel((string) $cta))
            ->first();

        if (!is_string($label) || trim($label) === '') {
            $label = !empty($formFieldNames) ? 'Submit Form' : 'Get Started';
        }

        $humanFields = collect($formFieldNames)
            ->take(2)
            ->map(fn ($field) => $this->humanizeFieldName((string) $field))
            ->filter(fn ($field) => $field !== '')
            ->values()
            ->all();

        $fieldsHint = empty($humanFields)
            ? 'form details'
            : implode(' + ', $humanFields);

        $instruction = 'Click "' . $label . '" and complete the form'
            . (empty($humanFields) ? '' : ' (' . $fieldsHint . ')')
            . '.';

        return [
            'label' => $label,
            'instruction' => $instruction,
            'fields' => $humanFields,
            'fields_hint' => $fieldsHint,
        ];
    }

    private function scoreCtaLabel(string $label): int
    {
        $text = mb_strtolower(trim($label));
        if ($text === '') {
            return 0;
        }

        $score = 0;
        $keywords = [
            'obtenir', 'audit', 'reserve', 'book', 'start', 'get', 'demarrer', 'commander',
            'order', 'acheter', 'buy', 'inscrire', 'register', 'join', 'contact', 'submit',
            'devis', 'quote', 'trial',
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $score += 3;
            }
        }

        $length = mb_strlen($text);
        if ($length >= 6 && $length <= 45) {
            $score += 2;
        }

        if (preg_match('/\b(click|cliquez|ici|now|maintenant)\b/i', $text)) {
            $score += 1;
        }

        return $score;
    }

    private function humanizeFieldName(string $field): string
    {
        $field = trim($field);
        if ($field === '') {
            return '';
        }

        $field = str_replace(['_', '-', '.'], ' ', $field);
        $field = preg_replace('/\s+/', ' ', $field) ?? $field;

        return ucwords(trim($field));
    }

    /**
     * @param array{label:string,action_text:string,type:string,target:?string,fields:array<int,string>,fields_hint:string} $ctaPayload
     */
    private function ensureReplyHasActionCta(string $reply, array $ctaPayload, string $userMessage): string
    {
        $normalized = trim($reply);
        if ($normalized === '') {
            return $this->buildLocalizedCtaLine($userMessage, $ctaPayload);
        }

        $hasActionCue = preg_match('/\b(click|cliquez|tap|form|formulaire|submit|obtenir|commander|next step|prochaine etape|whatsapp|instagram|call|phone|link)\b/i', $normalized) === 1;
        $mentionsAction = str_contains(mb_strtolower($normalized), mb_strtolower((string) ($ctaPayload['action_text'] ?? '')));

        if ($hasActionCue && $mentionsAction) {
            return $normalized;
        }

        return trim($normalized . "\n\n" . $this->buildLocalizedCtaLine($userMessage, $ctaPayload));
    }

    /**
     * @param array{label:string,action_text:string,type:string,target:?string,fields:array<int,string>,fields_hint:string} $ctaPayload
     */
    private function buildLocalizedCtaLine(string $userMessage, array $ctaPayload): string
    {
        $lang = $this->detectReplyLanguage($userMessage);
        $type = (string) ($ctaPayload['type'] ?? 'form');
        $actionText = (string) ($ctaPayload['action_text'] ?? 'Take action now');
        $label = (string) ($ctaPayload['label'] ?? 'Get Started');
        $fieldsHint = (string) ($ctaPayload['fields_hint'] ?? 'form details');

        if ($type === 'whatsapp') {
            if ($lang === 'fr') {
                return 'Prochaine etape: cliquez sur "' . $actionText . '" pour discuter sur WhatsApp et finaliser rapidement.';
            }
            if ($lang === 'darija') {
                return 'Step jaya: klik 3la "' . $actionText . '" bach ndwiw m3ak f WhatsApp w nkemlo b sor3a.';
            }
            return 'Next step: click "' . $actionText . '" to continue quickly on WhatsApp.';
        }

        if ($type === 'instagram') {
            if ($lang === 'fr') {
                return 'Prochaine etape: cliquez sur "' . $actionText . '" pour ouvrir Instagram et envoyer votre demande.';
            }
            if ($lang === 'darija') {
                return 'Step jaya: klik 3la "' . $actionText . '" bach t7el Instagram w tsift talab dyalk.';
            }
            return 'Next step: click "' . $actionText . '" to open Instagram and send your request.';
        }

        if ($type === 'custom_phone') {
            if ($lang === 'fr') {
                return 'Prochaine etape: cliquez sur "' . $actionText . '" pour nous appeler maintenant.';
            }
            if ($lang === 'darija') {
                return 'Step jaya: klik 3la "' . $actionText . '" bach t3ayet lina daba.';
            }
            return 'Next step: click "' . $actionText . '" to call now.';
        }

        if ($type === 'custom_link') {
            if ($lang === 'fr') {
                return 'Prochaine etape: cliquez sur "' . $actionText . '" pour continuer vers la page de reservation.';
            }
            if ($lang === 'darija') {
                return 'Step jaya: klik 3la "' . $actionText . '" bach tkmel l page dyal reservation.';
            }
            return 'Next step: click "' . $actionText . '" to continue to the reservation page.';
        }

        if ($lang === 'fr') {
            return 'Prochaine etape: cliquez sur "' . $label . '" et remplissez le formulaire'
                . (!empty($ctaPayload['fields']) ? ' (' . $fieldsHint . ')' : '')
                . ' pour recevoir une recommandation personnalisee.';
        }

        if ($lang === 'darija') {
            return 'Step jaya: klik 3la "' . $label . '" w 3ammer l-formulaire'
                . (!empty($ctaPayload['fields']) ? ' (' . $fieldsHint . ')' : '')
                . ' bach twsel b plan mnasb lik.';
        }

        return 'Next step: click "' . $label . '" and complete the form'
            . (!empty($ctaPayload['fields']) ? ' (' . $fieldsHint . ')' : '')
            . ' to get a tailored recommendation.';
    }

    private function detectReplyLanguage(string $text): string
    {
        $sample = mb_strtolower(trim($text));
        if ($sample === '') {
            return 'en';
        }

        if (preg_match('/\b(bghit|khas|dyal|m3a|wach|fash|3la|ila|ash)\b/i', $sample)) {
            return 'darija';
        }

        if (preg_match('/\b(le|la|les|des|votre|vos|pour|prix|offre|objectif|budget|ventes)\b/i', $sample)) {
            return 'fr';
        }

        return 'en';
    }

    private function buildActionButtonText(string $userMessage, string $label): string
    {
        $lang = $this->detectReplyLanguage($userMessage);

        if ($lang === 'fr') {
            return 'Passer a l action: ' . $label;
        }

        if ($lang === 'darija') {
            return 'Bda daba: ' . $label;
        }

        return 'Take action: ' . $label;
    }

    /**
     * @param array{label:string,instruction:string,fields:array<int,string>,fields_hint:string} $ctaContext
     * @return array{label:string,action_text:string,type:string,target:?string,fields:array<int,string>,fields_hint:string}|null
     */
    private function resolveActionCtaPayload(Landing $landing, array $ctaContext, string $userMessage): ?array
    {
        $workspaceSettings = optional($landing->workspace)->settings;
        $isCustomEnabled = (bool) optional($workspaceSettings)->chatbot_custom_cta_enabled;
        if (!$isCustomEnabled) {
            return null;
        }

        $landingScopeId = optional($workspaceSettings)->chatbot_custom_cta_landing_id;
        if ($landingScopeId !== null && (int) $landingScopeId > 0 && (int) $landingScopeId !== (int) $landing->id) {
            return null;
        }

        $type = trim((string) optional($workspaceSettings)->chatbot_custom_cta_type);
        $type = in_array($type, ['form', 'whatsapp', 'instagram', 'custom_link', 'custom_phone'], true) ? $type : 'form';
        $target = trim((string) optional($workspaceSettings)->chatbot_custom_cta_target);
        $customText = trim((string) optional($workspaceSettings)->chatbot_custom_cta_text);
        $label = (string) ($ctaContext['label'] ?? 'Get Started');
        $actionText = $customText !== ''
            ? Str::limit($customText, 120, '')
            : $this->buildActionButtonText($userMessage, $label);

        if ($type === 'whatsapp' || $type === 'custom_phone') {
            $target = preg_replace('/[^0-9+]/', '', $target) ?? '';
            if ($target === '') {
                $type = 'form';
            }
        }

        if ($type === 'instagram' || $type === 'custom_link') {
            $isValidUrl = $target !== '' && filter_var($target, FILTER_VALIDATE_URL) !== false;
            if (!$isValidUrl) {
                $type = 'form';
                $target = '';
            }
        }

        return [
            'label' => $label,
            'action_text' => $actionText,
            'type' => $type,
            'target' => $target !== '' ? $target : null,
            'fields' => (array) ($ctaContext['fields'] ?? []),
            'fields_hint' => (string) ($ctaContext['fields_hint'] ?? ''),
        ];
    }

    private function shouldShowActionCta(string $userMessage, array $history): bool
    {
        $current = mb_strtolower(trim($userMessage));
        if ($current === '') {
            return false;
        }

        // Immediate CTA when user explicitly asks to proceed.
        $explicitProceedIntent = preg_match('/\b(start|proceed|go ahead|book|reserve|order|buy|checkout|apply|submit|let\'?s go|ready|bda|yallah|commencer|passer|valider|commander)\b/i', $current) === 1;
        if ($explicitProceedIntent) {
            return true;
        }

        $userMessages = collect($history)
            ->filter(fn ($item) => ($item['role'] ?? '') === 'user')
            ->pluck('content')
            ->map(fn ($content) => mb_strtolower(trim((string) $content)))
            ->filter()
            ->values();

        $conversationText = trim($userMessages->implode(' ') . ' ' . $current);
        $userTurns = $userMessages->count() + 1;

        $hasObjective = preg_match('/\b(ventes?|sales?|lead[s]?|prospect[s]?|conversion[s]?|cpl|roas|revenue|ca|clients?)\b/u', $conversationText) === 1;
        $hasNeedSignal = preg_match('/\b(besoin|need|problem|pain|objectif|goal|results?|performance|grow|croissance|scale|increase)\b/u', $conversationText) === 1;
        $hasQualifier = preg_match('/\b(budget|usd|eur|mad|dh|timeline|week|month|mois|jour|days?|traffic|visitors?|audience|niche|ticket|panier|conversion rate|cvrs?|qualified)\b/u', $conversationText) === 1;
        $hasCommitmentTone = preg_match('/\b(serious|urgent|urgentement|asap|now|today|auj|daba)\b/u', $conversationText) === 1;

        // Discovery first, CTA after at least one meaningful qualifier.
        if (($hasObjective || $hasNeedSignal) && $hasQualifier && $userTurns >= 2) {
            return true;
        }

        if (($hasObjective || $hasNeedSignal) && $hasCommitmentTone && $userTurns >= 3) {
            return true;
        }

        return false;
    }

    /**
     * @return array{message:string,status:int}
     */
    private function mapClientError(\Throwable $e): array
    {
        $raw = trim((string) $e->getMessage());
        $lower = mb_strtolower($raw);

        if (str_contains($lower, "no active ai model found for role: 'text_generation'")) {
            return [
                'message' => "AI chat is not configured for this landing's workspace. Please assign a text model in AI Settings.",
                'status' => 503,
            ];
        }

        if (
            str_contains($lower, 'rate limit') ||
            str_contains($lower, 'quota exceeded') ||
            preg_match('/"code"\s*:\s*429/', $raw) === 1
        ) {
            $retrySeconds = null;
            if (preg_match('/please retry in\s+([0-9]+(?:\.[0-9]+)?)s?/i', $raw, $m) === 1) {
                $retrySeconds = (int) ceil((float) $m[1]);
            }

            return [
                'message' => $retrySeconds && $retrySeconds > 0
                    ? "AI is rate-limited right now. Please retry in {$retrySeconds}s."
                    : 'AI is rate-limited right now. Please retry in about 1 minute.',
                'status' => 429,
            ];
        }

        if (
            str_contains($lower, 'user not found') ||
            str_contains($lower, 'invalid api key') ||
            preg_match('/"code"\s*:\s*401/', $raw) === 1
        ) {
            return [
                'message' => "AI provider authentication failed for this landing's workspace. Please update API key in AI Settings.",
                'status' => 503,
            ];
        }

        if (str_contains($lower, 'empty chat reply')) {
            return [
                'message' => 'AI returned an empty answer. Please retry in a moment.',
                'status' => 502,
            ];
        }

        return [
            'message' => 'AI assistant is temporarily unavailable. Please try again in a moment.',
            'status' => 500,
        ];
    }

    private function isRecoverableProviderError(\Throwable $e): bool
    {
        $raw = mb_strtolower(trim((string) $e->getMessage()));
        if ($raw === '') {
            return false;
        }

        return str_contains($raw, 'no active ai model found')
            || str_contains($raw, 'authentication failed')
            || str_contains($raw, 'invalid api key')
            || str_contains($raw, 'user not found')
            || str_contains($raw, 'rate limit')
            || str_contains($raw, 'quota exceeded')
            || str_contains($raw, '"code":401')
            || str_contains($raw, '"code":429');
    }

    private function resolveFallbackWorkspaceId(int $landingWorkspaceId): ?int
    {
        $authWorkspaceId = (int) (auth()->user()?->workspaces()->first()?->id ?? 0);
        if ($authWorkspaceId > 0 && $authWorkspaceId !== $landingWorkspaceId) {
            return $authWorkspaceId;
        }

        $adminWorkspaceId = \App\Models\Workspace::query()
            ->where('id', '!=', $landingWorkspaceId)
            ->whereHas('user.roles', function ($query) {
                $query->whereIn('slug', ['super-admin', 'admin']);
            })
            ->whereHas('settings', function ($query) {
                $query->whereNotNull('ai_role_assignments');
            })
            ->whereHas('settings', function ($query) {
                $query->where('ai_role_assignments', 'like', '%"text_generation"%');
            })
            ->orderBy('id')
            ->value('id');

        return $adminWorkspaceId ? (int) $adminWorkspaceId : null;
    }
}
