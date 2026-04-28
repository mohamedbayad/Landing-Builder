<?php

namespace App\Http\Controllers;

use App\Models\Landing;
use App\Models\LandingPage;
use App\Models\Plan;
use App\Models\Template;
use App\Models\TemplatePage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class TemplateController extends Controller
{
    public function proxyTemplateAsset(Request $request)
    {
        $validated = $request->validate([
            'u' => 'required|url',
        ]);

        $url = (string) $validated['u'];

        if (!str_contains($url, '/storage/builder-templates/')) {
            return response('/* forbidden */', 200, ['Content-Type' => 'text/css']);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return response('/* not available */', 200, ['Content-Type' => 'text/plain']);
            }

            return response($response->body(), 200, [
                'Content-Type' => $response->header('Content-Type') ?: 'application/octet-stream',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        } catch (\Throwable $e) {
            return response('/* asset proxy error */', 200, ['Content-Type' => 'text/plain']);
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Template::query()
            ->with(['plans:id,name,slug', 'owner:id,name'])
            ->withCount('pages')
            ->latest();

        if (!$user->hasAnyRole(['super-admin', 'admin'])) {
            $query->where('is_active', true);
        }

        $templates = $query->get();
        if (!$user->hasAnyRole(['super-admin', 'admin'])) {
            $templates = $templates
                ->filter(fn (Template $template) => $this->canUseTemplate($user, $template))
                ->values();
        }

        return view('templates.index', compact('templates'));
    }

    public function myTemplates(Request $request)
    {
        $this->ensureTemplateAdminAccess($request->user());

        $templates = Template::query()
            ->where('owner_user_id', $request->user()->id)
            ->with(['plans:id,name,slug'])
            ->withCount('pages')
            ->latest()
            ->get();

        return view('templates.my', compact('templates'));
    }

    public function create(Request $request)
    {
        $this->ensureTemplateAdminAccess($request->user());
        $plans = Plan::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $clients = $this->getClientDirectory();
        return view('templates.create', compact('plans', 'clients'));
    }

    public function store(Request $request)
    {
        $this->ensureTemplateAdminAccess($request->user());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:120',
            'visibility' => 'required|in:public,private,internal',
            'is_active' => 'nullable|boolean',
            'template_zip' => 'required|file|mimes:zip|max:51200',
            'thumbnail' => 'nullable|image|max:5120',
            'plan_ids' => 'nullable|array',
            'plan_ids.*' => 'exists:plans,id',
            'allowed_emails_text' => 'nullable|string|max:8000',
            'allowed_user_ids' => 'nullable|array',
            'allowed_user_ids.*' => 'integer|exists:users,id',
        ]);

        $user = $request->user();
        $slug = $this->generateUniqueSlug($validated['name']);
        $workingDirectory = storage_path('app/tmp/template-upload-' . Str::uuid());
        $allowedEmails = $this->resolveAllowedEmails(
            (string) ($validated['allowed_emails_text'] ?? ''),
            array_map('intval', $validated['allowed_user_ids'] ?? [])
        );

        File::ensureDirectoryExists($workingDirectory);

        try {
            $zip = new ZipArchive();
            $source = $request->file('template_zip')->getRealPath();

            if ($source === false || $zip->open($source) !== true) {
                return back()->withInput()->withErrors(['template_zip' => 'Unable to open the ZIP file.']);
            }

            $zip->extractTo($workingDirectory);
            $zip->close();

            $pages = $this->collectTemplatePages($workingDirectory);
            if ($pages->isEmpty()) {
                return back()->withInput()->withErrors(['template_zip' => 'No importable template pages were found in the ZIP.']);
            }

            $storageDirectory = 'builder-templates/' . $slug . '-' . Str::random(8);
            $publicStorageDirectory = storage_path('app/public/' . $storageDirectory);
            File::ensureDirectoryExists($publicStorageDirectory);
            File::copyDirectory($workingDirectory, $publicStorageDirectory);

            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('builder-templates/thumbnails', 'public');
            } else {
                $detectedThumbnail = $this->detectAutoThumbnail($workingDirectory, $storageDirectory);
                if ($detectedThumbnail) {
                    $thumbnailPath = $detectedThumbnail;
                }
            }

            $zipPath = $request->file('template_zip')->store('builder-templates/zips');

            $template = DB::transaction(function () use ($validated, $user, $slug, $storageDirectory, $zipPath, $thumbnailPath, $pages) {
                $template = Template::create([
                    'owner_user_id' => $user->id,
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'description' => $validated['description'] ?? null,
                    'category' => $validated['category'] ?? 'general',
                    'preview_image_path' => $thumbnailPath,
                    'storage_path' => $storageDirectory,
                    'zip_file_path' => $zipPath,
                    'visibility' => $validated['visibility'],
                    'is_active' => (bool) ($validated['is_active'] ?? true),
                    'allowed_emails' => $allowedEmails,
                ]);

                foreach ($pages as $page) {
                    TemplatePage::create([
                        'template_id' => $template->id,
                        'type' => $page['type'],
                        'name' => $page['name'],
                        'slug' => $page['slug'],
                        'html' => $this->rewriteTemplateHtmlAssets($page['html'], $storageDirectory),
                        'css' => $this->rewriteTemplateCssAssets($page['css'] ?? '', $storageDirectory),
                        'js' => $page['js'] ?? '',
                        'grapesjs_json' => null,
                    ]);
                }

                $planIds = collect($validated['plan_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
                if ($planIds->isNotEmpty()) {
                    $template->plans()->sync($planIds->all());
                }

                return $template;
            });

            return redirect()->route('templates.edit', $template)->with('status', 'Template uploaded successfully.');
        } finally {
            if (File::isDirectory($workingDirectory)) {
                File::deleteDirectory($workingDirectory);
            }
        }
    }

    public function upload(Request $request)
    {
        return $this->store($request);
    }

    public function edit(Request $request, Template $template)
    {
        $this->ensureTemplateAdminAccess($request->user());
        $this->authorizeTemplateManagement($template, Auth::user());

        $plans = Plan::query()->where('status', 'active')->orderBy('sort_order')->orderBy('name')->get();
        $selectedPlans = $template->plans()->pluck('plans.id')->all();
        $clients = $this->getClientDirectory();
        $selectedClientIds = $clients
            ->filter(function ($client) use ($template) {
                $email = strtolower(trim((string) ($client->email ?? '')));
                $rules = collect($template->allowed_emails ?? [])->map(fn ($rule) => strtolower(trim((string) $rule)));
                return $email !== '' && $rules->contains($email);
            })
            ->pluck('id')
            ->values()
            ->all();
        $allowedEmailsText = implode(PHP_EOL, $template->allowed_emails ?? []);

        return view('templates.edit', compact('template', 'plans', 'selectedPlans', 'allowedEmailsText', 'clients', 'selectedClientIds'));
    }

    public function update(Request $request, Template $template)
    {
        $this->ensureTemplateAdminAccess($request->user());
        $this->authorizeTemplateManagement($template, $request->user());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:120',
            'visibility' => 'required|in:public,private,internal',
            'is_active' => 'nullable|boolean',
            'plan_ids' => 'nullable|array',
            'plan_ids.*' => 'exists:plans,id',
            'thumbnail' => 'nullable|image|max:5120',
            'allowed_emails_text' => 'nullable|string|max:8000',
            'allowed_user_ids' => 'nullable|array',
            'allowed_user_ids.*' => 'integer|exists:users,id',
        ]);
        $allowedEmails = $this->resolveAllowedEmails(
            (string) ($validated['allowed_emails_text'] ?? ''),
            array_map('intval', $validated['allowed_user_ids'] ?? [])
        );

        $payload = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? 'general',
            'visibility' => $validated['visibility'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'allowed_emails' => $allowedEmails,
        ];

        if ($template->slug === null || $template->slug === '') {
            $payload['slug'] = $this->generateUniqueSlug($validated['name']);
        }

        if ($request->hasFile('thumbnail')) {
            $payload['preview_image_path'] = $request->file('thumbnail')->store('builder-templates/thumbnails', 'public');
        }

        $template->update($payload);

        $template->plans()->sync(collect($validated['plan_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->all());

        return redirect()->route('templates.edit', $template)->with('status', 'Template updated successfully.');
    }

    public function toggleStatus(Request $request, Template $template)
    {
        $this->ensureTemplateAdminAccess($request->user());
        $this->authorizeTemplateManagement($template, $request->user());

        $template->update([
            'is_active' => !$template->is_active,
        ]);

        return back()->with('status', 'Template status updated successfully.');
    }

    public function import(Request $request, $id)
    {
        $localId = (int) str_replace('local-', '', (string) $id);
        $template = Template::with(['pages', 'plans'])->find($localId);

        if (!$template || !$template->is_active) {
            return redirect()->route('templates.index')->with('error', 'Template not found or disabled.');
        }

        if (!$this->canUseTemplate($request->user(), $template)) {
            return redirect()->route('templates.index')->with('error', 'This template is not available for your account or plan.');
        }

        return $this->importLocalTemplate($template);
    }

    protected function importLocalTemplate(Template $template)
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();
        if (!$workspace) {
            $workspace = $user->workspaces()->create(['name' => 'My Workspace']);
        }

        $landingName = $template->name . ' - Copy';
        $slug = Str::slug($landingName) . '-' . Str::random(6);

        $landing = Landing::create([
            'workspace_id' => $workspace->id,
            'template_id' => $template->id,
            'name' => $landingName,
            'slug' => $slug,
            'status' => 'draft',
            'uuid' => (string) Str::uuid(),
            'content_type' => 'landing',
            'source' => 'builder-template:' . $template->id,
            'is_template' => false,
            'category' => 'imported',
            'visibility' => 'private',
        ]);

        foreach ($template->pages as $page) {
            LandingPage::create([
                'landing_id' => $landing->id,
                'type' => $page->type,
                'name' => $page->name,
                'slug' => $page->slug,
                'status' => 'draft',
                'html' => $page->html,
                'css' => $page->css,
                'js' => $page->js,
                'grapesjs_json' => $page->grapesjs_json,
            ]);
        }

        if ($landing->pages()->where('type', 'checkout')->doesntExist()) {
            $this->createDefaultPages($landing);
        }

        return redirect()->route('landings.show', $landing)->with('success', 'Template imported successfully.');
    }

    public function syncLandingTemplate(Request $request, Landing $landing)
    {
        if ($landing->workspace->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!$landing->template_id) {
            return back()->with('error', 'This landing is not linked to a template.');
        }

        $result = DB::transaction(function () use ($landing) {
            return $this->syncFromLocalTemplate($landing, (int) $landing->template_id);
        });

        return back()->with('status', "Template synchronized. Updated {$result['updated']} page(s), created {$result['created']} page(s).");
    }

    protected function syncFromLocalTemplate(Landing $landing, int $templateId): array
    {
        $template = Template::with('pages')->find($templateId);
        if (!$template || !$template->is_active) {
            throw new \RuntimeException('Template not found or inactive.');
        }

        $updated = 0;
        $created = 0;

        foreach ($template->pages as $templatePage) {
            $landingPage = $landing->pages()->where('slug', $templatePage->slug)->first();
            if (!$landingPage) {
                $landingPage = $landing->pages()->where('type', $templatePage->type)->first();
            }

            $payload = [
                'html' => $templatePage->html,
                'css' => $templatePage->css,
                'js' => $templatePage->js,
                'grapesjs_json' => $templatePage->grapesjs_json,
            ];

            if ($landingPage) {
                $landingPage->update($payload);
                $updated++;
                continue;
            }

            LandingPage::create(array_merge($payload, [
                'landing_id' => $landing->id,
                'type' => $templatePage->type,
                'name' => $templatePage->name,
                'slug' => $templatePage->slug,
                'status' => 'draft',
            ]));
            $created++;
        }

        if (!str_starts_with((string) $landing->source, 'builder-template:')) {
            $landing->update(['source' => 'builder-template:' . $templateId]);
        }

        return ['updated' => $updated, 'created' => $created];
    }

    protected function createDefaultPages(Landing $landing): void
    {
        if ($landing->pages()->where('type', 'checkout')->doesntExist()) {
            LandingPage::create([
                'landing_id' => $landing->id,
                'type' => 'checkout',
                'name' => 'Checkout',
                'slug' => 'checkout',
                'status' => 'draft',
                'html' => '<div class="container mx-auto px-4 py-8"><h1 class="text-3xl font-bold mb-4">Checkout</h1><p>Dynamic Checkout Form will appear here.</p></div>',
            ]);
        }

        if ($landing->pages()->where('type', 'thankyou')->doesntExist()) {
            LandingPage::create([
                'landing_id' => $landing->id,
                'type' => 'thankyou',
                'name' => 'Thank You',
                'slug' => 'thank-you',
                'status' => 'draft',
                'html' => '<div class="bg-gray-50 min-h-screen flex items-center justify-center"><h1>Thank You</h1></div>',
            ]);
        }
    }

    protected function authorizeTemplateManagement(Template $template, $user): void
    {
        if (!$user || !$user->hasAnyRole(['super-admin', 'admin'])) {
            abort(403);
        }
    }

    protected function canUseTemplate($user, Template $template): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return true;
        }

        if (!$template->is_active) {
            return false;
        }

        if ($template->visibility === 'internal') {
            return false;
        }

        $isOwner = $template->owner_user_id === (int) $user->id;
        $hasEmailRules = $this->hasTemplateEmailRules($template);
        $matchesEmail = $this->matchesTemplateEmailAccess($user, $template);
        if ($template->visibility === 'private' && !$isOwner && !$matchesEmail) {
            return false;
        }

        $templatePlanIds = $template->relationLoaded('plans')
            ? $template->plans->pluck('id')
            : $template->plans()->pluck('plans.id');

        $activePlanId = $user->activeSubscription()?->plan_id;
        $hasPlanRules = $templatePlanIds->isNotEmpty();
        $matchesPlan = $hasPlanRules && $activePlanId && $templatePlanIds->contains($activePlanId);

        if (!$hasPlanRules && !$hasEmailRules) {
            return true;
        }

        if ($hasPlanRules && $hasEmailRules) {
            return (bool) ($matchesPlan || $matchesEmail);
        }

        if ($hasPlanRules) {
            return (bool) $matchesPlan;
        }

        return $matchesEmail;
    }

    protected function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'template';
        }

        $slug = $base;
        $counter = 2;

        while (Template::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function detectAutoThumbnail(string $workingDirectory, string $storageDirectory): ?string
    {
        $candidates = [
            'screenshot.png',
            'screenshot.jpg',
            'screenshot.jpeg',
            'thumbnail.png',
            'thumbnail.jpg',
            'thumbnail.jpeg',
            'preview.png',
            'preview.jpg',
            'preview.jpeg',
        ];

        foreach ($candidates as $candidate) {
            $source = $workingDirectory . DIRECTORY_SEPARATOR . $candidate;
            if (!File::exists($source)) {
                continue;
            }

            $targetRelative = 'builder-templates/thumbnails/' . Str::random(16) . '-' . basename($candidate);
            Storage::disk('public')->put($targetRelative, File::get($source));
            return $targetRelative;
        }

        $storagePreviewCandidates = [
            $storageDirectory . '/screenshot.png',
            $storageDirectory . '/screenshot.jpg',
            $storageDirectory . '/thumbnail.png',
            $storageDirectory . '/thumbnail.jpg',
        ];

        foreach ($storagePreviewCandidates as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function collectTemplatePages(string $workingDirectory)
    {
        $manifestPath = $workingDirectory . DIRECTORY_SEPARATOR . 'manifest.json';
        if (File::exists($manifestPath)) {
            $manifest = json_decode((string) File::get($manifestPath), true);
            $pages = collect($manifest['pages'] ?? [])
                ->map(function ($page) use ($workingDirectory) {
                    $file = (string) ($page['file'] ?? '');
                    if ($file === '') {
                        return null;
                    }

                    $absoluteFilePath = $workingDirectory . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);
                    if (!File::exists($absoluteFilePath)) {
                        return null;
                    }

                    return [
                        'type' => (string) ($page['type'] ?? 'index'),
                        'name' => (string) ($page['name'] ?? Str::headline((string) ($page['slug'] ?? 'index'))),
                        'slug' => (string) ($page['slug'] ?? pathinfo($file, PATHINFO_FILENAME)),
                        'html' => (string) File::get($absoluteFilePath),
                        'css' => '',
                        'js' => '',
                    ];
                })
                ->filter();

            if ($pages->isNotEmpty()) {
                return $pages->values();
            }
        }

        $fallbackFiles = [
            'index.html' => ['type' => 'index', 'name' => 'Home', 'slug' => 'index'],
            'checkout.html' => ['type' => 'checkout', 'name' => 'Checkout', 'slug' => 'checkout'],
            'thank-you.html' => ['type' => 'thankyou', 'name' => 'Thank You', 'slug' => 'thank-you'],
            'thankyou.html' => ['type' => 'thankyou', 'name' => 'Thank You', 'slug' => 'thank-you'],
        ];

        $pages = collect();
        foreach ($fallbackFiles as $file => $meta) {
            $path = $workingDirectory . DIRECTORY_SEPARATOR . $file;
            if (!File::exists($path)) {
                continue;
            }

            $pages->push([
                'type' => $meta['type'],
                'name' => $meta['name'],
                'slug' => $meta['slug'],
                'html' => (string) File::get($path),
                'css' => '',
                'js' => '',
            ]);
        }

        return $pages->values();
    }

    protected function rewriteTemplateHtmlAssets(string $html, string $storageDirectory): string
    {
        $html = preg_replace_callback('/\b(src|href)=(["\'])([^"\']+)\2/i', function ($matches) use ($storageDirectory) {
            $attribute = $matches[1];
            $quote = $matches[2];
            $rawValue = $matches[3];
            $rewritten = $this->rewriteAssetPath($rawValue, $storageDirectory);
            return $attribute . '=' . $quote . $rewritten . $quote;
        }, $html) ?? $html;

        $html = preg_replace_callback('/url\(([^)]+)\)/i', function ($matches) use ($storageDirectory) {
            $raw = trim($matches[1], " \t\n\r\0\x0B\"'");
            $rewritten = $this->rewriteAssetPath($raw, $storageDirectory);
            return 'url(' . $rewritten . ')';
        }, $html) ?? $html;

        return $html;
    }

    protected function rewriteTemplateCssAssets(string $css, string $storageDirectory): string
    {
        if ($css === '') {
            return '';
        }

        return preg_replace_callback('/url\(([^)]+)\)/i', function ($matches) use ($storageDirectory) {
            $raw = trim($matches[1], " \t\n\r\0\x0B\"'");
            $rewritten = $this->rewriteAssetPath($raw, $storageDirectory);
            return 'url(' . $rewritten . ')';
        }, $css) ?? $css;
    }

    protected function rewriteAssetPath(string $path, string $storageDirectory): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return $trimmed;
        }

        if (preg_match('/^(?:https?:)?\/\//i', $trimmed)) {
            return $trimmed;
        }

        if (preg_match('/^(?:data:|#|mailto:|tel:|javascript:)/i', $trimmed)) {
            return $trimmed;
        }

        $parsedPath = parse_url($trimmed, PHP_URL_PATH);
        if (!is_string($parsedPath) || $parsedPath === '') {
            return $trimmed;
        }

        $extension = strtolower((string) pathinfo($parsedPath, PATHINFO_EXTENSION));
        $assetExtensions = [
            'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'avif', 'ico',
            'mp4', 'webm', 'mp3', 'wav', 'woff', 'woff2', 'ttf', 'otf',
            'json', 'glb', 'gltf', 'bin', 'wasm',
        ];

        if (!in_array($extension, $assetExtensions, true)) {
            return $trimmed;
        }

        $relativePath = ltrim($parsedPath, '/');
        $relativePath = preg_replace('/^\.\//', '', $relativePath) ?? $relativePath;

        $query = parse_url($trimmed, PHP_URL_QUERY);
        $fragment = parse_url($trimmed, PHP_URL_FRAGMENT);

        $rewritten = Storage::url(trim($storageDirectory, '/') . '/' . $relativePath);

        if (is_string($query) && $query !== '') {
            $rewritten .= '?' . $query;
        }

        if (is_string($fragment) && $fragment !== '') {
            $rewritten .= '#' . $fragment;
        }

        return $rewritten;
    }

    protected function ensureTemplateAdminAccess($user): void
    {
        if (!$user || !$user->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Only admin or super admin can manage templates.');
        }
    }

    protected function matchesTemplateEmailAccess($user, Template $template): bool
    {
        $rules = collect($template->allowed_emails ?? [])
            ->map(fn ($rule) => strtolower(trim((string) $rule)))
            ->filter()
            ->unique()
            ->values();

        if ($rules->isEmpty()) {
            return true;
        }

        $email = strtolower(trim((string) ($user->email ?? '')));
        if ($email === '') {
            return false;
        }

        foreach ($rules as $rule) {
            if (str_starts_with($rule, '@')) {
                if (Str::endsWith($email, $rule)) {
                    return true;
                }
                continue;
            }

            if ($email === $rule) {
                return true;
            }
        }

        return false;
    }

    protected function hasTemplateEmailRules(Template $template): bool
    {
        return collect($template->allowed_emails ?? [])
            ->map(fn ($rule) => trim((string) $rule))
            ->filter()
            ->isNotEmpty();
    }

    protected function parseAllowedEmails(string $raw): array
    {
        $chunks = preg_split('/[\r\n,;]+/', $raw) ?: [];

        return collect($chunks)
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->map(function (string $item) {
                if ($item !== '' && !str_contains($item, '@')) {
                    return '@' . ltrim($item, '@');
                }

                return $item;
            })
            ->filter(function (string $item) {
                if ($item === '') {
                    return false;
                }

                if (str_starts_with($item, '@')) {
                    return preg_match('/^@[a-z0-9.-]+\.[a-z]{2,}$/i', $item) === 1;
                }

                return filter_var($item, FILTER_VALIDATE_EMAIL) !== false;
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveAllowedEmails(string $manualRulesRaw, array $allowedUserIds): array
    {
        $manual = $this->parseAllowedEmails($manualRulesRaw);
        $userEmails = User::query()
            ->whereIn('id', $allowedUserIds)
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->values()
            ->all();

        return collect([...$manual, ...$userEmails])
            ->map(fn ($rule) => strtolower(trim((string) $rule)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function getClientDirectory()
    {
        return User::query()
            ->select(['id', 'name', 'email'])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('slug', ['super-admin', 'admin']);
            })
            ->orderBy('name')
            ->orderBy('email')
            ->limit(1200)
            ->get();
    }
}
