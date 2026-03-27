<?php

namespace App\Http\Controllers;

use App\Services\LandingPagePipelineService;
use App\Services\OllamaService;
use App\Services\GeminiService;
use App\Services\ProductResearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class LandingPageAiController extends Controller
{
    protected LandingPagePipelineService $pipeline;
    protected OllamaService $ollama;
    protected GeminiService $gemini;
    protected ProductResearchService $research;

    public function __construct(
        LandingPagePipelineService $pipeline,
        OllamaService $ollama,
        GeminiService $gemini,
        ProductResearchService $research
    ) {
        $this->pipeline = $pipeline;
        $this->ollama = $ollama;
        $this->gemini = $gemini;
        $this->research = $research;
    }

    /**
     * Main endpoint: Analyze image and generate full landing page content (Async).
     */
    public function analyzeAndGenerate(Request $request): JsonResponse
    {
        // 1. Immediate short-term fix: Increase execution time for this request
        set_time_limit(300); // 5 minutes

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240', // Increased to 10MB
            'niche' => 'required|string',
            'audience' => 'required|string',
            'tone' => 'required|string',
            'language' => 'required|string',
            'offer' => 'required|string',
            'brand_name' => 'string|nullable',
            'extra_notes' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $workspace = auth()->user()->workspaces()->first();
            
            // Handle image: Store it properly for the background job
            $file = $request->file('image');
            $path = $file->store('ai/uploads', 'public');
            $fullImagePath = storage_path('app/public/' . $path);

            $input = $request->all();
            $input['image_path'] = $fullImagePath;
            $input['image_url'] = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
            $input['is_temporary'] = true; // Mark for cleanup

            // Create a task record
            $task = \App\Models\AiGenerationTask::create([
                'workspace_id' => $workspace->id,
                'status' => 'pending',
                'input_data' => $input,
            ]);

            // Dispatch background job
            \App\Jobs\GenerateLandingPageJob::dispatch($task->id);

            return response()->json([
                'success' => true,
                'message' => 'Generation started in background',
                'task_id' => $task->uuid,
                'status' => 'pending'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => "Failed to start generation: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Polling endpoint to check task status.
     */
    public function checkStatus($uuid): JsonResponse
    {
        $task = \App\Models\AiGenerationTask::where('uuid', $uuid)->first();

        if (!$task) {
            return response()->json(['success' => false, 'error' => 'Task not found'], 404);
        }

        return response()->json([
            'success'       => true,
            'status'        => $task->status,
            'progress'      => $task->progress ?? 0,  // Real progress from DB
            'result'        => $task->status === 'completed' ? $task->result_data : null,
            'error'         => $task->error,
            'error_message' => $task->error_message,
        ]);
    }

    /**
     * Only analyze the product image.
     */
    public function analyzeProduct(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:5120']);
        
        try {
            $workspace = auth()->user() ? auth()->user()->workspaces()->first() : null;
            $analysis = $this->gemini->analyzeImage($request->file('image')->getRealPath());
            return response()->json(['success' => true, 'analysis' => $analysis]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Perform research based on previously extracted data.
     */
    public function researchProduct(Request $request): JsonResponse
    {
        $request->validate(['analysis' => 'required|array']);
        
        try {
            $workspace = auth()->user() ? auth()->user()->workspaces()->first() : null;
            $research = $this->research->research($request->input('analysis'), $workspace?->id);
            return response()->json(['success' => true, 'research' => $research]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check the health of AI services.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'services' => [
                'ollama' => $this->ollama->health(),
                'gemini' => [
                    'configured' => !empty(config('services.gemini.key')),
                    'model' => config('services.gemini.model')
                ]
            ]
        ]);
    }
}
