<?php

namespace App\Http\Controllers;

use App\Models\EmailAutomation;
use App\Models\EmailAutomationStep;
use App\Models\EmailTemplate;
use App\Models\FormEndpoint;
use App\Models\Landing;
use App\Models\Product;
use App\Services\Email\AutomationEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmailAutomationController extends Controller
{
    public function builderHub()
    {
        return redirect()->route('email-automation.automations.index');
    }

    public function globalBuilder(EmailAutomation $automation)
    {
        return $this->renderBuilderPage($automation);
    }

    public function index()
    {
        $automations = EmailAutomation::query()
            ->where('user_id', Auth::id())
            ->withCount('steps')
            ->withCount([
                'messages as sent_count' => fn ($query) => $query->whereNotNull('sent_at'),
                'messages as opened_count' => fn ($query) => $query->whereNotNull('opened_at'),
                'messages as clicked_count' => fn ($query) => $query->whereNotNull('first_clicked_at'),
            ])
            ->withMax('messages', 'sent_at')
            ->latest()
            ->get()
            ->map(function (EmailAutomation $automation) {
                $base = max((int) $automation->sent_count, 1);
                $automation->open_rate = $automation->sent_count > 0
                    ? round(($automation->opened_count / $base) * 100, 1)
                    : 0;
                $automation->click_rate = $automation->sent_count > 0
                    ? round(($automation->clicked_count / $base) * 100, 1)
                    : 0;

                return $automation;
            });

        return view('email-automation.automations.index', compact('automations'));
    }

    public function create()
    {
        return view('email-automation.automations.form', [
            'automation' => new EmailAutomation([
                'status' => 'draft',
                'trigger_type' => 'form_submitted',
                'timezone' => config('app.timezone'),
            ]),
            'templates' => $this->templates(),
            'landings' => $this->landings(),
            'formEndpoints' => $this->formEndpoints(),
            'products' => $this->products(),
            'action' => route('email-automation.automations.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->validatedPayload($request);

        $automation = EmailAutomation::create([
            'user_id' => Auth::id(),
            'name' => $payload['name'],
            'status' => $payload['status'],
            'trigger_type' => $payload['trigger_type'],
            'trigger_config' => $payload['trigger_config'],
            'conditions' => $payload['conditions'] ?? null,
            'timezone' => $payload['timezone'] ?? config('app.timezone'),
            'settings' => $payload['settings'] ?? null,
        ]);

        $this->syncSteps($automation, $payload['steps']);

        return redirect()->route('email-automation.automations.index')
            ->with('success', 'Automation created.');
    }

    public function edit(EmailAutomation $automation)
    {
        $this->authorizeAutomation($automation);
        $automation->load('steps');

        return view('email-automation.automations.form', [
            'automation' => $automation,
            'templates' => $this->templates(),
            'landings' => $this->landings(),
            'formEndpoints' => $this->formEndpoints(),
            'products' => $this->products(),
            'action' => route('email-automation.automations.update', $automation),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, EmailAutomation $automation)
    {
        $this->authorizeAutomation($automation);
        $payload = $this->validatedPayload($request);

        $automation->update([
            'name' => $payload['name'],
            'status' => $payload['status'],
            'trigger_type' => $payload['trigger_type'],
            'trigger_config' => $payload['trigger_config'],
            'conditions' => $payload['conditions'] ?? null,
            'timezone' => $payload['timezone'] ?? config('app.timezone'),
            'settings' => $payload['settings'] ?? null,
        ]);

        $automation->steps()->delete();
        $this->syncSteps($automation, $payload['steps']);

        return redirect()->route('email-automation.automations.index')
            ->with('success', 'Automation updated.');
    }

    public function builder(EmailAutomation $automation)
    {
        return $this->renderBuilderPage($automation);
    }

    public function saveBuilder(Request $request, EmailAutomation $automation)
    {
        $this->authorizeAutomation($automation);

        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'status' => ['required', Rule::in(['draft', 'active', 'paused'])],
            'trigger_type' => ['required', Rule::in(['form_submitted', 'checkout_completed', 'lead_created'])],
            'trigger_config' => 'nullable|array',
            'timezone' => 'nullable|string|max:64',
            'nodes' => 'required|array|min:1',
            'edges' => 'required|array|min:0',
        ]);

        $this->validateVisualGraph($payload['nodes'], $payload['edges']);

        $automation->update([
            'name' => $payload['name'],
            'status' => $payload['status'],
            'trigger_type' => $payload['trigger_type'],
            'trigger_config' => $payload['trigger_config'] ?? [],
            'timezone' => $payload['timezone'] ?? config('app.timezone'),
            'builder_mode' => true,
            'visual_nodes' => array_values($payload['nodes']),
            'visual_edges' => array_values($payload['edges']),
            'builder_version' => max(1, (int) $automation->builder_version + 1),
        ]);

        // Compatibility bridge for legacy flows and metrics.
        $compiledSteps = $this->compileLegacyStepsFromGraph($payload['nodes'], $payload['edges']);
        $automation->steps()->delete();
        $this->syncSteps($automation, $compiledSteps);

        return response()->json([
            'ok' => true,
            'message' => 'Builder saved successfully.',
            'automation' => [
                'id' => $automation->id,
                'name' => $automation->name,
                'status' => $automation->status,
            ],
        ]);
    }

    public function previewBuilder(Request $request, EmailAutomation $automation, AutomationEngineService $engine)
    {
        $this->authorizeAutomation($automation);

        $context = $request->validate([
            'context' => 'nullable|array',
        ]);

        $path = $engine->previewPath($automation, $context['context'] ?? []);

        return response()->json([
            'ok' => true,
            'path' => $path,
        ]);
    }

    public function publishBuilder(EmailAutomation $automation)
    {
        $this->authorizeAutomation($automation);

        $nodes = is_array($automation->visual_nodes) ? $automation->visual_nodes : [];
        $edges = is_array($automation->visual_edges) ? $automation->visual_edges : [];

        $this->validateVisualGraph($nodes, $edges);

        $automation->update([
            'status' => 'active',
            'builder_mode' => true,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Workflow published and activated.',
            'status' => $automation->status,
        ]);
    }

    public function destroy(EmailAutomation $automation)
    {
        $this->authorizeAutomation($automation);
        $automation->delete();

        return redirect()->route('email-automation.automations.index')
            ->with('success', 'Automation deleted.');
    }

    public function duplicate(EmailAutomation $automation)
    {
        $this->authorizeAutomation($automation);
        $automation->load('steps');

        $copy = $automation->replicate();
        $copy->name = $automation->name.' (Copy)';
        $copy->status = 'draft';
        $copy->push();

        foreach ($automation->steps as $step) {
            $newStep = $step->replicate();
            $newStep->automation_id = $copy->id;
            $newStep->save();
        }

        return redirect()->route('email-automation.automations.edit', $copy)
            ->with('success', 'Automation duplicated.');
    }

    public function updateStatus(Request $request, EmailAutomation $automation)
    {
        $this->authorizeAutomation($automation);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'active', 'paused'])],
        ]);

        $automation->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('email-automation.automations.index')
            ->with('success', 'Automation status updated.');
    }

    private function syncSteps(EmailAutomation $automation, array $steps): void
    {
        foreach ($steps as $index => $step) {
            EmailAutomationStep::create([
                'automation_id' => $automation->id,
                'step_order' => $index + 1,
                'step_type' => $step['step_type'],
                'delay_value' => $step['step_type'] === 'wait' ? (int) ($step['delay_value'] ?? 0) : null,
                'delay_unit' => $step['step_type'] === 'wait' ? ($step['delay_unit'] ?? 'minutes') : null,
                'template_id' => $step['step_type'] === 'send_email' ? ($step['template_id'] ?? null) : null,
                'rules' => $step['rules'] ?? null,
                'settings' => $step['settings'] ?? null,
            ]);
        }
    }

    private function validatedPayload(Request $request): array
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'status' => ['required', Rule::in(['draft', 'active', 'paused'])],
            'trigger_type' => ['required', Rule::in(['form_submitted', 'checkout_completed', 'lead_created'])],
            'trigger_config' => 'nullable|array',
            'trigger_config.landing_id' => 'nullable|integer|exists:landings,id',
            'trigger_config.form_endpoint_id' => 'nullable|integer|exists:form_endpoints,id',
            'trigger_config.product_id' => 'nullable|integer|exists:products,id',
            'conditions' => 'nullable|array',
            'timezone' => 'nullable|string|max:64',
            'settings' => 'nullable|array',
            'steps' => 'required|array|min:1',
            'steps.*.step_type' => ['required', Rule::in(['send_email', 'wait'])],
            'steps.*.delay_value' => 'nullable|integer|min:0',
            'steps.*.delay_unit' => ['nullable', Rule::in(['minutes', 'hours', 'days'])],
            'steps.*.template_id' => 'nullable|integer|exists:email_templates,id',
        ]);

        $steps = $payload['steps'];
        foreach ($steps as $i => $step) {
            if ($step['step_type'] === 'send_email' && empty($step['template_id'])) {
                throw ValidationException::withMessages([
                    "steps.$i.template_id" => "Step ".($i + 1)." requires a template.",
                ]);
            }
        }

        $templateIds = collect($steps)
            ->where('step_type', 'send_email')
            ->pluck('template_id')
            ->filter()
            ->unique()
            ->values();

        if ($templateIds->isNotEmpty()) {
            $ownedCount = EmailTemplate::query()
                ->where('user_id', Auth::id())
                ->whereIn('id', $templateIds)
                ->count();

            if ($ownedCount !== $templateIds->count()) {
                throw ValidationException::withMessages([
                    'steps' => 'One or more selected templates are not available in your account.',
                ]);
            }
        }

        $config = $payload['trigger_config'] ?? [];
        if (!empty($config['landing_id'])) {
            $validLanding = Landing::query()
                ->where('id', $config['landing_id'])
                ->whereHas('workspace', fn ($query) => $query->where('user_id', Auth::id()))
                ->exists();
            if (!$validLanding) {
                throw ValidationException::withMessages([
                    'trigger_config.landing_id' => 'Selected landing is not available in your account.',
                ]);
            }
        }

        if (!empty($config['form_endpoint_id'])) {
            $validEndpoint = FormEndpoint::query()
                ->where('id', $config['form_endpoint_id'])
                ->whereHas('workspace', fn ($query) => $query->where('user_id', Auth::id()))
                ->exists();
            if (!$validEndpoint) {
                throw ValidationException::withMessages([
                    'trigger_config.form_endpoint_id' => 'Selected endpoint is not available in your account.',
                ]);
            }
        }

        if (!empty($config['product_id'])) {
            $validProduct = Product::query()
                ->where('id', $config['product_id'])
                ->whereHas('landing.workspace', fn ($query) => $query->where('user_id', Auth::id()))
                ->exists();
            if (!$validProduct) {
                throw ValidationException::withMessages([
                    'trigger_config.product_id' => 'Selected product is not available in your account.',
                ]);
            }
        }

        $payload['trigger_config'] = array_filter($payload['trigger_config'] ?? [], fn ($value) => $value !== null && $value !== '');
        return $payload;
    }

    private function authorizeAutomation(EmailAutomation $automation): void
    {
        if ($automation->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function renderBuilderPage(EmailAutomation $automation)
    {
        $this->authorizeAutomation($automation);

        return view('email-automation.automations.builder', [
            'automation' => $automation,
            'templates' => $this->templates(),
        ]);
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

    private function compileLegacyStepsFromGraph(array $nodes, array $edges): array
    {
        $indexedNodes = collect($nodes)->keyBy(fn ($node) => (string) ($node['id'] ?? ''));
        $trigger = collect($nodes)->first(fn ($node) => ($node['type'] ?? '') === 'trigger');
        if (!$trigger) {
            return [['step_type' => 'wait', 'delay_value' => 1, 'delay_unit' => 'minutes']];
        }

        $currentId = (string) ($trigger['id'] ?? '');
        $steps = [];
        $guard = 0;

        while ($currentId !== '' && $guard < 40) {
            $guard++;
            $nextEdge = collect($edges)->first(fn ($edge) => (string) ($edge['source'] ?? '') === $currentId);
            if (!$nextEdge) {
                break;
            }

            $nextId = (string) ($nextEdge['target'] ?? '');
            $node = $indexedNodes->get($nextId);
            if (!$node) {
                break;
            }

            $type = (string) ($node['type'] ?? '');
            $config = (array) ($node['config'] ?? []);

            if ($type === 'send_message' && strtolower((string) ($config['channel'] ?? 'email')) === 'email' && !empty($config['template_id'])) {
                $steps[] = [
                    'step_type' => 'send_email',
                    'template_id' => (int) $config['template_id'],
                ];
            }

            if ($type === 'delay') {
                $steps[] = [
                    'step_type' => 'wait',
                    'delay_value' => max(1, (int) ($config['value'] ?? $config['delay_value'] ?? 1)),
                    'delay_unit' => (string) ($config['unit'] ?? $config['delay_unit'] ?? 'minutes'),
                ];
            }

            if (in_array($type, ['end', 'end_workflow'], true)) {
                break;
            }

            $currentId = $nextId;
        }

        return !empty($steps) ? $steps : [['step_type' => 'wait', 'delay_value' => 1, 'delay_unit' => 'minutes']];
    }

    private function templates()
    {
        return EmailTemplate::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function landings()
    {
        $workspace = Auth::user()->workspaces()->first();

        return $workspace
            ? $workspace->landings()->orderBy('name')->get(['id', 'name'])
            : collect();
    }

    private function formEndpoints()
    {
        $workspace = Auth::user()->workspaces()->first();

        return $workspace
            ? $workspace->formEndpoints()->orderBy('name')->get(['id', 'name'])
            : collect();
    }

    private function products()
    {
        $workspace = Auth::user()->workspaces()->first();
        if (!$workspace) {
            return collect();
        }

        $landingIds = $workspace->landings()->pluck('id');
        return Product::query()
            ->whereIn('landing_id', $landingIds)
            ->orderBy('name')
            ->get(['id', 'landing_id', 'name']);
    }
}
