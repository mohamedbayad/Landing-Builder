<?php

namespace App\Http\Controllers;

use App\Models\WorkflowProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkflowProgramController extends Controller
{
    public function hub()
    {
        $program = WorkflowProgram::query()
            ->where('user_id', Auth::id())
            ->latest('id')
            ->first();

        if (!$program) {
            $program = $this->createDraftProgram();
        }

        return redirect()->route('workflow-builder.programs.show', $program);
    }

    public function store()
    {
        $program = $this->createDraftProgram();

        return redirect()->route('workflow-builder.programs.show', $program)
            ->with('success', 'New workflow program created.');
    }

    public function show(WorkflowProgram $program)
    {
        return $this->renderBuilder($program);
    }

    public function update(Request $request, WorkflowProgram $program)
    {
        $this->authorizeProgram($program);

        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'status' => ['required', Rule::in(['draft', 'active', 'paused'])],
            'trigger_type' => ['required', Rule::in($this->triggerTypes())],
            'trigger_config' => 'nullable|array',
            'timezone' => 'nullable|string|max:64',
            'nodes' => 'required|array|min:1',
            'edges' => 'required|array|min:0',
        ]);

        $this->validateVisualGraph($payload['nodes'], $payload['edges']);

        $program->update([
            'name' => $payload['name'],
            'status' => $payload['status'],
            'trigger_type' => $payload['trigger_type'],
            'trigger_config' => $payload['trigger_config'] ?? [],
            'timezone' => $payload['timezone'] ?? config('app.timezone'),
            'visual_nodes' => array_values($payload['nodes']),
            'visual_edges' => array_values($payload['edges']),
            'builder_version' => max(1, (int) $program->builder_version + 1),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Workflow program saved.',
            'program' => [
                'id' => $program->id,
                'name' => $program->name,
                'status' => $program->status,
            ],
        ]);
    }

    public function preview(Request $request, WorkflowProgram $program)
    {
        $this->authorizeProgram($program);

        $context = $request->validate([
            'context' => 'nullable|array',
        ]);

        return response()->json([
            'ok' => true,
            'path' => $this->previewPath(
                $program->visual_nodes ?? [],
                $program->visual_edges ?? [],
                $context['context'] ?? []
            ),
        ]);
    }

    public function publish(WorkflowProgram $program)
    {
        $this->authorizeProgram($program);

        $this->validateVisualGraph($program->visual_nodes ?? [], $program->visual_edges ?? []);

        $program->update([
            'status' => 'active',
            'published_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Workflow program published.',
            'status' => $program->status,
        ]);
    }

    public function destroy(WorkflowProgram $program)
    {
        $this->authorizeProgram($program);
        $program->delete();

        return redirect()->route('workflow-builder.index')
            ->with('success', 'Workflow program deleted.');
    }

    private function renderBuilder(WorkflowProgram $program)
    {
        $this->authorizeProgram($program);

        return view('workflow-builder.builder', [
            'automation' => $program,
            'templates' => collect(),
            'builderConfig' => [
                'mode' => 'workflow-program',
                'title' => 'Visual Workflow Automation Builder',
                'subtitle' => 'Standalone multi-channel workflow builder for customer journeys, offers, and scheduled automations.',
                'header_action_label' => 'New Program',
                'header_action_url' => route('workflow-builder.programs.store'),
                'header_action_method' => 'post',
                'save_url' => route('workflow-builder.programs.update', $program),
                'preview_url' => route('workflow-builder.programs.preview', $program),
                'publish_url' => route('workflow-builder.programs.publish', $program),
                'trigger_label' => 'Trigger Type',
                'trigger_options' => [
                    'date_based' => 'Date based',
                    'event_based' => 'Event based',
                    'tag_added' => 'Tag added',
                    'form_submitted' => 'Form submitted',
                    'purchase_made' => 'Purchase made',
                    'manual' => 'Manual',
                    'api' => 'API / webhook',
                ],
            ],
        ]);
    }

    private function createDraftProgram(): WorkflowProgram
    {
        return WorkflowProgram::query()->create([
            'user_id' => Auth::id(),
            'name' => 'New Workflow Program',
            'status' => 'draft',
            'trigger_type' => 'date_based',
            'trigger_config' => [],
            'timezone' => config('app.timezone'),
            'visual_nodes' => [
                [
                    'id' => 'start_'.str()->random(6),
                    'type' => 'trigger',
                    'label' => 'Schedule Start',
                    'x' => 180,
                    'y' => 180,
                    'config' => [],
                ],
                [
                    'id' => 'end_'.str()->random(6),
                    'type' => 'end',
                    'label' => 'End Workflow',
                    'x' => 520,
                    'y' => 180,
                    'config' => [],
                ],
            ],
            'visual_edges' => [],
            'builder_version' => 1,
            'settings' => [],
        ]);
    }

    private function authorizeProgram(WorkflowProgram $program): void
    {
        if ($program->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function triggerTypes(): array
    {
        return [
            'date_based',
            'event_based',
            'tag_added',
            'form_submitted',
            'purchase_made',
            'manual',
            'api',
        ];
    }

    private function validateVisualGraph(array $nodes, array $edges): void
    {
        $triggerCount = collect($nodes)->filter(fn ($node) => ($node['type'] ?? null) === 'trigger')->count();
        if ($triggerCount !== 1) {
            throw ValidationException::withMessages([
                'nodes' => 'Workflow must include exactly one trigger node.',
            ]);
        }

        $hasEnd = collect($nodes)->contains(fn ($node) => in_array(($node['type'] ?? ''), ['end', 'end_workflow'], true));
        if (!$hasEnd) {
            throw ValidationException::withMessages([
                'nodes' => 'Workflow must include at least one end node.',
            ]);
        }

        $nodeIds = collect($nodes)->pluck('id')->filter()->values();
        if ($nodeIds->count() !== $nodeIds->unique()->count()) {
            throw ValidationException::withMessages([
                'nodes' => 'Node IDs must be unique.',
            ]);
        }

        foreach ($edges as $idx => $edge) {
            $source = (string) ($edge['source'] ?? '');
            $target = (string) ($edge['target'] ?? '');

            if (!$nodeIds->contains($source) || !$nodeIds->contains($target)) {
                throw ValidationException::withMessages([
                    "edges.$idx" => 'Each edge source and target must reference existing nodes.',
                ]);
            }
        }
    }

    private function previewPath(array $nodes, array $edges, array $context = []): array
    {
        $indexedNodes = collect($nodes)->keyBy(fn ($node) => (string) ($node['id'] ?? ''));
        if ($indexedNodes->isEmpty()) {
            return [];
        }

        $triggerNode = $indexedNodes->first(fn ($node) => ($node['type'] ?? '') === 'trigger') ?: $indexedNodes->first();
        $currentNodeId = (string) ($triggerNode['id'] ?? '');
        $path = [];
        $guard = 0;

        while ($currentNodeId !== '' && $guard < 25) {
            $guard++;
            $node = $indexedNodes->get($currentNodeId);
            if (!$node) {
                break;
            }

            $path[] = [
                'id' => $currentNodeId,
                'type' => $node['type'] ?? 'unknown',
                'label' => $node['label'] ?? null,
            ];

            if (in_array(($node['type'] ?? ''), ['end', 'end_workflow'], true)) {
                break;
            }

            $branchLabel = null;
            if (($node['type'] ?? '') === 'branch') {
                $branchLabel = $this->evaluateBranch((array) ($node['config'] ?? []), $context) ? 'yes' : 'no';
            }

            $currentNodeId = $this->resolveNextNodeId($edges, $currentNodeId, $branchLabel)
                ?: $this->resolveNextNodeId($edges, $currentNodeId, null)
                ?: '';
        }

        return $path;
    }

    private function resolveNextNodeId(array $edges, string $sourceId, ?string $label): ?string
    {
        $matches = collect($edges)
            ->filter(fn ($edge) => (string) ($edge['source'] ?? '') === $sourceId)
            ->values();

        if ($matches->isEmpty()) {
            return null;
        }

        if ($label !== null) {
            $labeled = $matches->first(function ($edge) use ($label) {
                $edgeLabel = strtolower((string) ($edge['label'] ?? $edge['branch'] ?? $edge['conditionLabel'] ?? ''));
                return $edgeLabel === strtolower($label);
            });

            if ($labeled) {
                return (string) ($labeled['target'] ?? '');
            }
        }

        return (string) ($matches->first()['target'] ?? '');
    }

    private function evaluateBranch(array $config, array $context): bool
    {
        $field = (string) ($config['field'] ?? '');
        $operator = strtolower((string) ($config['operator'] ?? 'equals'));
        $expected = $config['value'] ?? null;
        $actual = $field !== '' ? data_get($context, $field) : null;

        return match ($operator) {
            'not_equals' => (string) $actual !== (string) $expected,
            'contains' => str_contains(strtolower((string) $actual), strtolower((string) $expected)),
            'exists' => !is_null($actual) && (string) $actual !== '',
            default => (string) $actual === (string) $expected,
        };
    }
}
