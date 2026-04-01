<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\AgentService;

/**
 * EditorAIController
 *
 * Provides AI actions accessible directly from the GrapesJS editor:
 *   - POST /editor/ai/improve-copy    → Rewrite text for higher conversion
 *   - POST /editor/ai/generate-image  → Generate an image for a section
 *   - POST /editor/ai/suggest-section → Generate a full section HTML
 */
class EditorAIController extends Controller
{
    protected AgentService $agent;

    public function __construct(AgentService $agent)
    {
        $this->agent = $agent;
    }

    /**
     * Improve/rewrite selected text for higher conversion.
     */
    public function improveCopy(Request $request)
    {
        $validated = $request->validate([
            'text'        => 'required|string|max:5000',
            'element_tag' => 'nullable|string|max:20',   // h1, p, button, span ...
            'context'     => 'nullable|string|max:3000',  // surrounding text for tone matching
            'instruction' => 'nullable|string|max:1000',  // user direction like "make it urgent"
            'tone'        => 'nullable|string|max:50',
        ]);

        $workspace = Auth::user()->workspaces()->first();

        $elementTag = $validated['element_tag'] ?? 'text';
        $instruction = $validated['instruction'] ?? '';
        $tone = $validated['tone'] ?? 'professional and persuasive';

        $systemPrompt = "You are an elite conversion copywriter. Rewrite the provided text to maximize conversions.
RULES:
1. Keep the SAME general meaning and intent.
2. Use {$tone} tone throughout.
3. For headlines (h1-h3): Make punchy, benefit-driven, under 12 words.
4. For body text (p): Use short sentences, sensory language, and social proof signals.
5. For buttons: Use action verbs, create urgency, max 5 words.
6. Return ONLY the improved text (no quotes, no explanation, no markdown).
7. Match the original language (if French, reply in French, etc.).";

        if (!empty($instruction)) {
            $systemPrompt .= "\n\nUSER DIRECTION: {$instruction}";
        }

        $userPrompt = "Element type: <{$elementTag}>\nOriginal text: \"{$validated['text']}\"";

        if (!empty($validated['context'])) {
            $userPrompt .= "\nSurrounding context: \"{$validated['context']}\"";
        }

        try {
            $result = $this->agent->generateDirect(
                $userPrompt,
                $systemPrompt,
                '',
                null,
                'text_generation',
                $workspace?->id
            );

            // The AI may return array or string — normalize
            $improved = is_array($result)
                ? ($result['text'] ?? $result['content'] ?? $result['output'] ?? json_encode($result))
                : (string) $result;

            // Strip wrapping quotes if present
            $improved = trim($improved, "\" \n\r\t");

            return response()->json([
                'status'   => 'success',
                'original' => $validated['text'],
                'improved' => $improved,
            ]);
        } catch (\Throwable $e) {
            Log::error('Editor AI improve-copy failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'AI generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate an image for a specific section context.
     */
    public function generateImage(Request $request)
    {
        $validated = $request->validate([
            'prompt'       => 'required|string|max:2000',
            'section_type' => 'nullable|string|max:50',
            'style'        => 'nullable|string|max:100',
        ]);

        $workspace = Auth::user()->workspaces()->first();

        $prompt = $validated['prompt'];
        $style = $validated['style'] ?? 'modern, clean, professional marketing photo';

        $fullPrompt = "{$prompt}. Style: {$style}. High quality, photorealistic, suitable for a landing page.";

        try {
            $imageUrl = $this->agent->generateImage($fullPrompt, [], $workspace?->id);

            return response()->json([
                'status' => 'success',
                'url'    => $imageUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Editor AI generate-image failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Image generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate a full section HTML based on a description.
     */
    public function suggestSection(Request $request)
    {
        $validated = $request->validate([
            'type'        => 'required|string|max:50',   // hero, testimonials, faq, pricing, etc.
            'description' => 'nullable|string|max:2000',
            'product'     => 'nullable|string|max:500',
            'tone'        => 'nullable|string|max:50',
        ]);

        $workspace = Auth::user()->workspaces()->first();

        $type = $validated['type'];
        $description = $validated['description'] ?? '';
        $product = $validated['product'] ?? 'the product';
        $tone = $validated['tone'] ?? 'professional';

        $systemPrompt = "You are a landing page section generator. Create a single, high-converting {$type} section.
RULES:
1. Return ONLY the raw HTML (no markdown, no code fences, no explanation).
2. Use TailwindCSS utility classes exclusively — NO inline styles.
3. Wrap everything in a single <section> tag.
4. Include realistic, compelling copy.
5. Use {$tone} tone.
6. Make it responsive (mobile-first with md: breakpoints).
7. The HTML must be immediately droppable into GrapesJS.";

        $userPrompt = "Section type: {$type}\nProduct: {$product}";
        if ($description) {
            $userPrompt .= "\nAdditional instructions: {$description}";
        }

        try {
            $result = $this->agent->generateDirect(
                $userPrompt,
                $systemPrompt,
                '',
                null,
                'text_generation',
                $workspace?->id
            );

            $html = is_array($result)
                ? ($result['html'] ?? $result['content'] ?? $result['output'] ?? '')
                : (string) $result;

            // Strip markdown code fences if AI wraps them
            $html = preg_replace('/^```(?:html)?\s*/i', '', $html);
            $html = preg_replace('/\s*```\s*$/i', '', $html);
            $html = trim($html);

            return response()->json([
                'status' => 'success',
                'html'   => $html,
            ]);
        } catch (\Throwable $e) {
            Log::error('Editor AI suggest-section failed', ['error' => $e->getMessage()]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Section generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
