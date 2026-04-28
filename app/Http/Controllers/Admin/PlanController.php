<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::query()->with('features')->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:plans,slug',
            'description' => 'nullable|string|max:5000',
            'monthly_price' => 'nullable|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,draft',
            'trial_days' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'features_json' => 'nullable|string',
        ]);

        $plan = Plan::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']) . '-' . Str::random(4),
            'description' => $validated['description'] ?? null,
            'monthly_price' => $validated['monthly_price'] ?? 0,
            'yearly_price' => $validated['yearly_price'] ?? 0,
            'status' => $validated['status'],
            'trial_days' => $validated['trial_days'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->syncFeaturesFromJson($plan, $validated['features_json'] ?? null);

        return redirect()->route('plans.index')->with('status', 'Plan created successfully.');
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plans,slug,' . $plan->id,
            'description' => 'nullable|string|max:5000',
            'monthly_price' => 'nullable|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,draft',
            'trial_days' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'features_json' => 'nullable|string',
        ]);

        $plan->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'monthly_price' => $validated['monthly_price'] ?? 0,
            'yearly_price' => $validated['yearly_price'] ?? 0,
            'status' => $validated['status'],
            'trial_days' => $validated['trial_days'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->syncFeaturesFromJson($plan, $validated['features_json'] ?? null);

        return redirect()->route('plans.index')->with('status', 'Plan updated successfully.');
    }

    protected function syncFeaturesFromJson(Plan $plan, ?string $featuresJson): void
    {
        if ($featuresJson === null || trim($featuresJson) === '') {
            return;
        }

        $decoded = json_decode($featuresJson, true);
        if (!is_array($decoded)) {
            return;
        }

        foreach ($decoded as $key => $value) {
            $featureType = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'limit' : 'text');
            $featureValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

            $plan->features()->updateOrCreate(
                ['feature_key' => (string) $key],
                ['feature_type' => $featureType, 'feature_value' => $featureValue]
            );
        }
    }
}
