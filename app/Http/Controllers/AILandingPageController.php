<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\LandingPagePipelineService;
use App\Services\SectionRegenerationService;
use App\Services\ElementRegenerationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Landing;
use App\Models\LandingPage;

class AILandingPageController extends Controller
{
    protected $pipeline;
    protected $imageService;

    public function __construct(
        LandingPagePipelineService $pipeline,
        SectionRegenerationService $sectionService,
        ElementRegenerationService $elementService,
        \App\Services\ImageStorageService $imageService
    ) {
        $this->pipeline = $pipeline;
        $this->sectionService = $sectionService;
        $this->elementService = $elementService;
        $this->imageService = $imageService;
    }

    public function index()
    {
        return view('ai-generator.index');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_image' => 'required|image|max:10240', // 10MB
            'description' => 'nullable|string',
            'audience' => 'nullable|string',
            'offer' => 'nullable|string',
            'cta' => 'nullable|string',
            'language' => 'nullable|string',
        ]);

        try {
            \Illuminate\Support\Facades\Log::info("AI Generation Request Received", ['product' => $request->product_name]);

            $workspace = auth()->user()->workspaces()->first();

            // 1. Diagnose File Input (Debugging Request)
            if (!$request->hasFile('product_image')) {
                \Illuminate\Support\Facades\Log::error("Upload Failure: Request has no 'product_image' file.");
                return response()->json(['status' => 'error', 'message' => "No file uploaded. Please check the 'product_image' field."], 400);
            }

            // 2. Use ImageStorageService for the full pipeline
            $uploadResult = $this->imageService->store($request->file('product_image'));

            if (!$uploadResult['success']) {
                return response()->json(['status' => 'error', 'message' => "Storage failed: " . $uploadResult['error']], 500);
            }

            // 3. Prepare input for pipeline (Fixed: No temporary cleanup)
            $input = $request->all();
            $input['image_path'] = $uploadResult['absolute_path'];
            $input['image_url'] = $uploadResult['url'];
            $input['is_temporary'] = false; // Changed to false to prevent job cleanup if it's meant to be permanent
            $input['niche'] = $request->description;

            \Illuminate\Support\Facades\Log::info("Preparing Generation Task", [
                'image_url' => $input['image_url'],
                'image_path' => $input['image_path']
            ]);

            // Create a task record
            $task = \App\Models\AiGenerationTask::create([
                'workspace_id' => $workspace->id,
                'status' => 'pending',
                'input_data' => $input,
            ]);

            // Dispatch background job
            \App\Jobs\GenerateLandingPageJob::dispatch($task->id);

            return response()->json([
                'status' => 'success',
                'task_id' => $task->uuid,
                'message' => 'Generation started in background'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function regenerate(Request $request)
    {
        $request->validate([
            'section' => 'required|string',
        ]);

        try {
            $context = is_string($request->context) ? json_decode($request->context, true) : (array) $request->context;
            
            $workspace = auth()->user()->workspaces()->first();
            $result = $this->sectionService->regenerate($request->section, $context, null, $workspace->id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function regenerateElement(Request $request)
    {
        $request->validate([
            'element_id' => 'required|string',
            'type' => 'required|string',
            'comment' => 'nullable|string',
        ]);

        try {
            $context = is_string($request->context) ? json_decode($request->context, true) : (array) $request->context;
            
            $workspace = auth()->user()->workspaces()->first();
            $result = $this->elementService->regenerate(
                $request->element_id, 
                $request->type, 
                $context, 
                $request->comment,
                $workspace->id
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Persist generated AI output as a real Landing (never as Template).
     */
    public function publish(Request $request)
    {
        $validated = $request->validate([
            'result' => 'required|array',
            'result.sections' => 'required|array|min:1',
            'result.sections.*.html' => 'required|string',
            'result.seo' => 'nullable|array',
            'result.analysis' => 'nullable|array',
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
        ]);

        $workspace = auth()->user()->workspaces()->first();
        if (!$workspace) {
            return response()->json([
                'status' => 'error',
                'message' => 'No workspace found for authenticated user.',
            ], 422);
        }

        $result = $validated['result'];
        $baseName = $validated['name']
            ?? data_get($result, 'analysis.product_name_guess')
            ?? 'AI Generated Landing';
        $slug = $this->resolveUniqueLandingSlug($validated['slug'] ?? Str::slug($baseName));
        $status = $validated['status'] ?? 'published';

        $sectionsHtml = collect($result['sections'])->pluck('html')->implode("\n\n");
        $seoTitle = data_get($result, 'seo.title');
        $seoDescription = data_get($result, 'seo.description');

        try {
            $landing = DB::transaction(function () use ($workspace, $baseName, $slug, $status, $sectionsHtml, $seoTitle, $seoDescription) {
                $landing = Landing::create([
                    'workspace_id' => $workspace->id,
                    'template_id' => null,
                    'name' => $baseName,
                    'slug' => $slug,
                    'status' => $status,
                    'published_at' => $status === 'published' ? now() : null,
                    'content_type' => 'landing',
                    'source' => 'ai',
                    'is_template' => false,
                    'category' => 'generated',
                    'visibility' => $status === 'published' ? 'public' : 'private',
                ]);

                LandingPage::create([
                    'landing_id' => $landing->id,
                    'type' => 'index',
                    'name' => 'Home',
                    'slug' => 'index',
                    'status' => $status,
                    'html' => $sectionsHtml,
                    'css' => '',
                    'js' => '',
                ]);

                // Keep checkout/thankyou available for funnel flows.
                LandingPage::create([
                    'landing_id' => $landing->id,
                    'type' => 'checkout',
                    'name' => 'Checkout',
                    'slug' => 'checkout',
                    'status' => 'draft',
                    'html' => '<div class="container mx-auto px-4 py-8"><h1 class="text-3xl font-bold mb-4">Checkout</h1><p>Dynamic checkout form will appear here.</p></div>',
                    'css' => '',
                    'js' => '',
                ]);

                LandingPage::create([
                    'landing_id' => $landing->id,
                    'type' => 'thankyou',
                    'name' => 'Thank You',
                    'slug' => 'thank-you',
                    'status' => 'draft',
                    'html' => '<div class="bg-gray-50 min-h-screen flex items-center justify-center"><h1>Thank You</h1></div>',
                    'css' => '',
                    'js' => '',
                ]);

                $landing->settings()->updateOrCreate(
                    ['landing_id' => $landing->id],
                    [
                        'meta_title' => $seoTitle,
                        'meta_description' => $seoDescription,
                    ]
                );

                return $landing;
            });

            Log::info('AI publish persisted landing', [
                'landing_id' => $landing->id,
                'workspace_id' => $landing->workspace_id,
                'template_id' => $landing->template_id,
                'content_type' => $landing->content_type,
                'source' => $landing->source,
                'is_template' => (bool) $landing->is_template,
                'status' => $landing->status,
                'visibility' => $landing->visibility,
                'reason' => 'ai_publish_to_builder',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'AI landing published to My Landings.',
                'landing_id' => $landing->id,
                'landing_slug' => $landing->slug,
                'edit_url' => route('landings.editor', $landing),
                'landings_url' => route('landings.index'),
            ]);
        } catch (\Throwable $e) {
            Log::error('AI publish failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to publish generated landing.',
            ], 500);
        }
    }

    private function resolveUniqueLandingSlug(string $baseSlug): string
    {
        $slug = trim($baseSlug) !== '' ? $baseSlug : ('ai-landing-' . Str::lower(Str::random(6)));
        $slug = Str::slug($slug);
        if ($slug === '') {
            $slug = 'ai-landing';
        }

        $original = $slug;
        $counter = 1;
        while (Landing::where('slug', $slug)->exists()) {
            $counter++;
            $slug = "{$original}-{$counter}";
        }

        return $slug;
    }
}
