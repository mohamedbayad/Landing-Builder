<?php

namespace App\Http\Controllers;

use App\Models\CheckoutField;
use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FunnelController extends Controller
{
    private const STEP_TYPES = [
        'landing' => 'Landing',
        'lead_capture' => 'Lead Capture',
        'sales' => 'Sales',
        'checkout' => 'Checkout',
        'upsell' => 'Upsell',
        'downsell' => 'Downsell',
        'thank_you' => 'Thank You',
        'membership' => 'Membership',
        'webinar' => 'Webinar',
        'custom' => 'Custom',
    ];

    public function show(Landing $landing)
    {
        $this->authorizeLanding($landing);

        $pages = $landing->funnelPages()->get();
        $products = $landing->products()->latest()->get();
        $checkoutFields = $landing->checkoutFields()->orderBy('id')->get();

        if ($checkoutFields->isEmpty()) {
            $checkoutFields = collect($this->defaultCheckoutFieldConfig())->map(fn (array $field) => new CheckoutField([
                'field_name' => $field['field_name'],
                'label' => $field['label'],
                'is_enabled' => $field['is_enabled'],
                'is_required' => $field['is_required'],
            ]));
        }

        if ($pages->isNotEmpty() && $pages->whereNull('funnel_step_type')->isNotEmpty()) {
            $pages = $pages->values()->map(function (LandingPage $page, int $index) {
                if (empty($page->funnel_step_type)) {
                    $page->funnel_step_type = $this->mapStepTypeFromPageType((string) $page->type);
                }
                if (empty($page->funnel_position)) {
                    $page->funnel_position = $index + 1;
                }

                return $page;
            })->sortBy('funnel_position')->values();
        }

        return view('landings.funnel', [
            'landing' => $landing,
            'pages' => $pages,
            'products' => $products,
            'checkoutFields' => $checkoutFields,
            'stepTypes' => self::STEP_TYPES,
        ]);
    }

    public function storeStep(Request $request, Landing $landing)
    {
        $this->authorizeLanding($landing);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'funnel_step_type' => ['required', Rule::in(array_keys(self::STEP_TYPES))],
            'funnel_position' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $slugBase = ($validated['slug'] ?? null) ?: Str::slug($validated['name']);
        $slugBase = $slugBase !== '' ? $slugBase : 'step';
        $slug = $this->ensureUniqueSlug($landing, $slugBase);

        $currentMaxPosition = (int) ($landing->pages()->max('funnel_position') ?? 0);
        $position = (int) ($validated['funnel_position'] ?? ($currentMaxPosition + 1));
        if ($position < 1) {
            $position = 1;
        }
        $maxInsertPosition = $currentMaxPosition + 1;
        if ($position > $maxInsertPosition) {
            $position = $maxInsertPosition;
        }

        DB::transaction(function () use ($landing, $validated, $slug, $position): void {
            $landing->pages()
                ->where('funnel_position', '>=', $position)
                ->increment('funnel_position');

            $stepType = $validated['funnel_step_type'];
            $pageType = $this->resolvePageTypeForNewStep($landing, $stepType);

            $landing->pages()->create([
                'name' => $validated['name'],
                'slug' => $slug,
                'type' => $pageType,
                'status' => 'draft',
                'html' => '<section class="py-16"><div class="mx-auto max-w-5xl px-4"><h1 class="text-4xl font-bold">New Step</h1><p class="mt-4 text-lg opacity-80">Edit this section in the visual builder.</p></div></section>',
                'css' => '',
                'js' => '',
                'funnel_step_type' => $stepType,
                'funnel_position' => $position,
                'next_landing_page_id' => null,
            ]);
        });

        return back()->with('status', 'Funnel step created successfully.');
    }

    public function updateSteps(Request $request, Landing $landing)
    {
        $this->authorizeLanding($landing);

        $validated = $request->validate([
            'pages' => ['required', 'array', 'min:1'],
            'pages.*.id' => ['required', 'integer'],
            'pages.*.name' => ['required', 'string', 'max:255'],
            'pages.*.funnel_position' => ['required', 'integer', 'min:1', 'max:999'],
            'pages.*.funnel_step_type' => ['required', Rule::in(array_keys(self::STEP_TYPES))],
            'pages.*.next_landing_page_id' => ['nullable', 'integer'],
        ]);

        $landingPages = $landing->pages()->get()->keyBy('id');
        $landingPageIds = $landingPages->keys()->all();

        $normalizedRows = [];
        $submittedPageIds = [];
        foreach ($validated['pages'] as $index => $row) {
            $pageId = (int) $row['id'];
            if (!in_array($pageId, $landingPageIds, true)) {
                abort(422, 'One or more selected pages do not belong to this funnel.');
            }
            if (in_array($pageId, $submittedPageIds, true)) {
                abort(422, 'Duplicate funnel steps are not allowed in one update.');
            }
            $submittedPageIds[] = $pageId;

            $nextPageId = isset($row['next_landing_page_id']) && $row['next_landing_page_id'] !== ''
                ? (int) $row['next_landing_page_id']
                : null;

            if ($nextPageId !== null && !in_array($nextPageId, $landingPageIds, true)) {
                abort(422, 'Next step must belong to the same funnel.');
            }

            if ($nextPageId !== null && $nextPageId === $pageId) {
                abort(422, 'A step cannot point to itself as the next step.');
            }

            $normalizedRows[] = [
                'id' => $pageId,
                'name' => (string) $row['name'],
                'funnel_step_type' => (string) $row['funnel_step_type'],
                'funnel_position' => (int) $row['funnel_position'],
                'next_landing_page_id' => $nextPageId,
                '_client_index' => $index,
            ];
        }

        if (count($submittedPageIds) !== count($landingPageIds)) {
            abort(422, 'Funnel update payload must include all steps.');
        }

        $orderedRows = collect($normalizedRows)
            ->sortBy(fn (array $row) => [$row['funnel_position'], $row['_client_index']])
            ->values()
            ->all();

        $nextByPageId = [];
        foreach ($orderedRows as $row) {
            $nextByPageId[$row['id']] = $row['next_landing_page_id'];
        }

        if ($this->hasCycle($nextByPageId)) {
            abort(422, 'Funnel flow cannot contain a cycle.');
        }

        DB::transaction(function () use ($orderedRows, $landingPages): void {
            foreach ($orderedRows as $index => $row) {
                /** @var LandingPage $page */
                $page = $landingPages[(int) $row['id']];
                $stepType = (string) $row['funnel_step_type'];

                $pageType = (string) $page->type;
                if ($stepType === 'checkout') {
                    $pageType = 'checkout';
                } elseif ($stepType === 'thank_you') {
                    $pageType = 'thankyou';
                } elseif (in_array($pageType, ['checkout', 'thankyou'], true)) {
                    $pageType = 'page';
                }

                $page->update([
                    'name' => $row['name'],
                    'type' => $pageType,
                    'funnel_step_type' => $stepType,
                    'funnel_position' => $index + 1,
                    'next_landing_page_id' => $row['next_landing_page_id'],
                ]);
            }
        });

        return back()->with('status', 'Funnel steps updated successfully.');
    }

    public function storeProduct(Request $request, Landing $landing)
    {
        $this->authorizeLanding($landing);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'description' => ['nullable', 'string'],
            'label' => ['nullable', 'string', 'max:60'],
        ]);

        $landing->products()->create($validated);

        return back()->with('status', 'Product added successfully.');
    }

    public function deleteProduct(Request $request, Landing $landing, Product $product)
    {
        $this->authorizeLanding($landing);
        if ((int) $product->landing_id !== (int) $landing->id) {
            abort(403);
        }

        $product->delete();

        return back()->with('status', 'Product deleted.');
    }

    public function storeCheckoutFields(Request $request, Landing $landing)
    {
        $this->authorizeLanding($landing);

        $validated = $request->validate([
            'fields' => ['nullable', 'array'],
            'fields.*.label' => ['nullable', 'string', 'max:120'],
            'fields.*.enabled' => ['nullable', 'in:1'],
            'fields.*.required' => ['nullable', 'in:1'],
        ]);

        $fields = $validated['fields'] ?? [];
        foreach ($fields as $fieldName => $data) {
            CheckoutField::updateOrCreate(
                ['landing_id' => $landing->id, 'field_name' => $fieldName],
                [
                    'label' => $data['label'] ?? '',
                    'is_enabled' => isset($data['enabled']),
                    'is_required' => isset($data['required']),
                ]
            );
        }

        return back()->with('status', 'Checkout fields saved.');
    }

    private function authorizeLanding(Landing $landing): void
    {
        if ((int) $landing->workspace->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }

    private function defaultCheckoutFieldConfig(): array
    {
        return [
            ['field_name' => 'billing_first_name', 'label' => 'First Name', 'is_enabled' => true, 'is_required' => true],
            ['field_name' => 'billing_last_name', 'label' => 'Last Name', 'is_enabled' => true, 'is_required' => true],
            ['field_name' => 'billing_email', 'label' => 'Email Address', 'is_enabled' => true, 'is_required' => true],
            ['field_name' => 'billing_phone', 'label' => 'Phone Number', 'is_enabled' => true, 'is_required' => false],
            ['field_name' => 'billing_address', 'label' => 'Address', 'is_enabled' => true, 'is_required' => true],
            ['field_name' => 'billing_city', 'label' => 'City', 'is_enabled' => true, 'is_required' => true],
            ['field_name' => 'billing_zip', 'label' => 'Zip/Postal Code', 'is_enabled' => true, 'is_required' => true],
            ['field_name' => 'billing_country', 'label' => 'Country', 'is_enabled' => true, 'is_required' => true],
        ];
    }

    private function mapStepTypeFromPageType(string $pageType): string
    {
        return match ($pageType) {
            'checkout' => 'checkout',
            'thankyou' => 'thank_you',
            default => 'landing',
        };
    }

    private function ensureUniqueSlug(Landing $landing, string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 2;

        while ($landing->pages()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function resolvePageTypeForNewStep(Landing $landing, string $stepType): string
    {
        return match ($stepType) {
            'checkout' => 'checkout',
            'thank_you' => 'thankyou',
            default => $landing->pages()->where('type', 'index')->exists() ? 'page' : 'index',
        };
    }

    /**
     * @param  array<int, int|null>  $nextByPageId
     */
    private function hasCycle(array $nextByPageId): bool
    {
        $visiting = [];
        $visited = [];

        $visit = function (int $nodeId) use (&$visit, &$visiting, &$visited, $nextByPageId): bool {
            if (isset($visiting[$nodeId])) {
                return true;
            }

            if (isset($visited[$nodeId])) {
                return false;
            }

            $visiting[$nodeId] = true;
            $nextNodeId = $nextByPageId[$nodeId] ?? null;
            if ($nextNodeId !== null && array_key_exists($nextNodeId, $nextByPageId)) {
                if ($visit($nextNodeId)) {
                    return true;
                }
            }

            unset($visiting[$nodeId]);
            $visited[$nodeId] = true;

            return false;
        };

        foreach (array_keys($nextByPageId) as $pageId) {
            if ($visit((int) $pageId)) {
                return true;
            }
        }

        return false;
    }
}
