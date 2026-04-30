<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Models\Workspace;
use App\Models\WorkspacePlugin;
use App\Services\Plugins\PluginManagerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PluginController extends Controller
{
    public function dashboard(PluginManagerService $pluginManager): View
    {
        $this->ensureCoreEditorPlugins($pluginManager);
        $workspace = $this->resolveWorkspace();

        $plugins = $pluginManager->listForWorkspace($workspace)->map(function (Plugin $plugin) {
            $workspaceState = $plugin->workspacePlugins->first();

            return $this->serializePlugin($plugin, $workspaceState);
        })->values();

        return view('plugins.index', [
            'workspace' => $workspace,
            'plugins' => $plugins,
            'availablePermissions' => config('plugin_system.permissions', []),
        ]);
    }

    public function index(PluginManagerService $pluginManager): JsonResponse
    {
        $this->ensureCoreEditorPlugins($pluginManager);
        $workspace = $this->resolveWorkspace();

        $plugins = $pluginManager->listForWorkspace($workspace)->map(function (Plugin $plugin) {
            $workspaceState = $plugin->workspacePlugins->first();

            return $this->serializePlugin($plugin, $workspaceState);
        })->values();

        return response()->json([
            'workspace_id' => $workspace->id,
            'plugins' => $plugins,
        ]);
    }

    public function installFromDashboard(Request $request, PluginManagerService $pluginManager): RedirectResponse
    {
        $validated = $request->validate([
            'manifest_json' => ['required', 'string'],
            'is_core' => ['nullable', 'boolean'],
        ]);

        $manifest = json_decode($validated['manifest_json'], true);
        if (!is_array($manifest)) {
            return back()->withErrors([
                'manifest_json' => 'Invalid JSON format in manifest.',
            ])->withInput();
        }

        try {
            $pluginManager->install(
                $manifest,
                (bool) ($validated['is_core'] ?? false)
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('settings.plugins.index')->with('status', 'plugin-installed');
    }

    public function install(Request $request, PluginManagerService $pluginManager): JsonResponse
    {
        $validated = $request->validate([
            'manifest' => ['required', 'array'],
            'is_core' => ['nullable', 'boolean'],
        ]);

        $plugin = $pluginManager->install(
            $validated['manifest'],
            (bool) ($validated['is_core'] ?? false)
        );

        return response()->json([
            'plugin' => $this->serializePlugin($plugin),
        ], 201);
    }

    public function activateFromDashboard(
        Request $request,
        Plugin $plugin,
        PluginManagerService $pluginManager
    ): RedirectResponse {
        $workspace = $this->resolveWorkspace();

        $settings = (array) $request->input('settings', []);
        $approvedPermissions = array_values(array_filter(
            (array) $request->input('approved_permissions', []),
            fn ($permission) => is_string($permission) && $permission !== ''
        ));

        try {
            $pluginManager->activateForWorkspace(
                $workspace,
                $plugin,
                $settings,
                $approvedPermissions
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('settings.plugins.index')->with('status', 'plugin-activated');
    }

    public function activate(
        Request $request,
        Plugin $plugin,
        PluginManagerService $pluginManager
    ): JsonResponse {
        $workspace = $this->resolveWorkspace();

        $validated = $request->validate([
            'settings' => ['nullable', 'array'],
            'approved_permissions' => ['nullable', 'array'],
            'approved_permissions.*' => ['string', 'max:60'],
        ]);

        $workspacePlugin = $pluginManager->activateForWorkspace(
            $workspace,
            $plugin,
            $validated['settings'] ?? [],
            $validated['approved_permissions'] ?? []
        );

        return response()->json([
            'plugin' => $this->serializePlugin($plugin->fresh(), $workspacePlugin),
        ]);
    }

    public function deactivateFromDashboard(
        Plugin $plugin,
        PluginManagerService $pluginManager
    ): RedirectResponse {
        $workspace = $this->resolveWorkspace();

        try {
            $pluginManager->deactivateForWorkspace($workspace, $plugin);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('settings.plugins.index')->with('status', 'plugin-deactivated');
    }

    public function deactivate(
        Plugin $plugin,
        PluginManagerService $pluginManager
    ): JsonResponse {
        $workspace = $this->resolveWorkspace();
        $workspacePlugin = $pluginManager->deactivateForWorkspace($workspace, $plugin);

        return response()->json([
            'plugin' => $this->serializePlugin($plugin->fresh(), $workspacePlugin),
        ]);
    }

    public function updateSettingsFromDashboard(
        Request $request,
        Plugin $plugin,
        PluginManagerService $pluginManager
    ): RedirectResponse {
        $workspace = $this->resolveWorkspace();

        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        try {
            $pluginManager->updateWorkspaceSettings(
                $workspace,
                $plugin,
                $validated['settings']
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('settings.plugins.index')->with('status', 'plugin-settings-updated');
    }

    public function updateSettings(
        Request $request,
        Plugin $plugin,
        PluginManagerService $pluginManager
    ): JsonResponse {
        $workspace = $this->resolveWorkspace();

        $validated = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $workspacePlugin = $pluginManager->updateWorkspaceSettings(
            $workspace,
            $plugin,
            $validated['settings']
        );

        return response()->json([
            'plugin' => $this->serializePlugin($plugin->fresh(), $workspacePlugin),
        ]);
    }

    public function destroy(Plugin $plugin, PluginManagerService $pluginManager): JsonResponse
    {
        $pluginManager->uninstall($plugin);

        return response()->json([], 204);
    }

    public function dispatchHook(
        Request $request,
        PluginManagerService $pluginManager
    ): JsonResponse {
        $workspace = $this->resolveWorkspace();

        $validated = $request->validate([
            'hook' => ['required', 'string', 'max:80'],
            'payload' => ['nullable', 'array'],
        ]);

        $results = $pluginManager->dispatchHook(
            $workspace,
            $validated['hook'],
            $validated['payload'] ?? []
        );

        return response()->json([
            'hook' => $validated['hook'],
            'results' => $results,
        ]);
    }

    protected function resolveWorkspace(): Workspace
    {
        $user = Auth::user();
        $workspace = $user->workspaces()->first();

        if (!$workspace) {
            $workspace = $user->workspaces()->create([
                'name' => $user->name . "'s Workspace",
            ]);
        }

        return $workspace;
    }

    protected function serializePlugin(Plugin $plugin, ?WorkspacePlugin $workspaceState = null): array
    {
        $manifest = is_array($plugin->manifest) ? $plugin->manifest : [];
        $latestVersion = is_string($manifest['latest_version'] ?? null) && ($manifest['latest_version'] ?? '') !== ''
            ? (string) $manifest['latest_version']
            : (string) $plugin->version;

        return [
            'id' => $plugin->id,
            'name' => $plugin->name,
            'slug' => $plugin->slug,
            'version' => $plugin->version,
            'author' => $plugin->author,
            'description' => $plugin->description,
            'category' => $plugin->category,
            'icon' => $plugin->icon,
            'hooks' => $plugin->hooks ?? [],
            'settings_schema' => $plugin->settings_schema ?? [],
            'permissions' => $plugin->permissions ?? [],
            'is_core' => (bool) $plugin->is_core,
            'is_active' => (bool) $plugin->is_active,
            'runtime_entry' => $plugin->runtime_entry,
            'latest_version' => $latestVersion,
            'has_update' => version_compare($latestVersion, (string) $plugin->version, '>'),
            'installed_at' => optional($plugin->installed_at)->toISOString(),
            'updated_at' => optional($plugin->updated_at)->toISOString(),
            'workspace_state' => $workspaceState ? [
                'status' => $workspaceState->status,
                'settings' => $workspaceState->settings ?? [],
                'approved_permissions' => $workspaceState->approved_permissions ?? [],
                'auto_update' => (bool) $workspaceState->auto_update,
                'activated_at' => optional($workspaceState->activated_at)->toISOString(),
                'deactivated_at' => optional($workspaceState->deactivated_at)->toISOString(),
                'last_error' => $workspaceState->last_error,
            ] : null,
        ];
    }

    protected function ensureCoreEditorPlugins(PluginManagerService $pluginManager): void
    {
        try {
            $pluginManager->install($this->tailwindEditorManifest(), true);
        } catch (ValidationException $e) {
            report($e);
        }

        try {
            $pluginManager->install($this->googleMaterialIconsManifest(), true);
        } catch (ValidationException $e) {
            report($e);
        }

        try {
            $pluginManager->install($this->tailwindClassesAutocompleteManifest(), true);
        } catch (ValidationException $e) {
            report($e);
        }

        try {
            $pluginManager->install($this->tailwindCardsManifest(), true);
        } catch (ValidationException $e) {
            report($e);
        }

        try {
            $pluginManager->install($this->grapesJsLpBuilderManifest(), true);
        } catch (ValidationException $e) {
            report($e);
        }
    }

    protected function tailwindEditorManifest(): array
    {
        return [
            'name' => 'Tailwind CSS Bridge',
            'slug' => 'tailwind-css',
            'version' => '1.0.0',
            'author' => 'Funnel Builder Core',
            'description' => 'Enables Tailwind utility classes directly inside the GrapesJS editor canvas.',
            'category' => 'enhancement',
            'icon' => 'tailwind',
            'hooks' => ['editor.init', 'editor.ready'],
            'settings' => [
                'use_cdn' => [
                    'type' => 'select',
                    'label' => 'Enable Tailwind CDN Runtime',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
                'config_json' => [
                    'type' => 'textarea',
                    'label' => 'Tailwind Config JSON (optional)',
                    'required' => false,
                ],
                'runtime_src' => [
                    'type' => 'text',
                    'label' => 'Tailwind Runtime Script URL',
                    'required' => false,
                ],
                'fallback_cdn' => [
                    'type' => 'select',
                    'label' => 'Fallback to CDN if Local Runtime Fails',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
            ],
            'permissions' => [],
            'assets' => [
                'js' => [],
                'css' => [],
            ],
            'requires' => [
                'builder_version' => '>=2.0.0',
                'php' => '>=8.1',
                'plugins' => [],
            ],
        ];
    }

    protected function googleMaterialIconsManifest(): array
    {
        return [
            'name' => 'Google Material Icons',
            'slug' => 'google-material-icons',
            'version' => '1.0.0',
            'author' => 'Funnel Builder Core',
            'description' => 'Adds Google Material Icons/Material Symbols support inside the GrapesJS editor canvas.',
            'category' => 'enhancement',
            'icon' => 'material-icons',
            'hooks' => ['editor.init', 'editor.ready'],
            'settings' => [
                'use_cdn' => [
                    'type' => 'select',
                    'label' => 'Enable Google Fonts CDN',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
                'variant' => [
                    'type' => 'select',
                    'label' => 'Icon Variant',
                    'required' => false,
                    'options' => [
                        ['value' => 'material_symbols_outlined', 'label' => 'Material Symbols Outlined'],
                        ['value' => 'material_symbols_rounded', 'label' => 'Material Symbols Rounded'],
                        ['value' => 'material_symbols_sharp', 'label' => 'Material Symbols Sharp'],
                        ['value' => 'material_icons', 'label' => 'Material Icons'],
                        ['value' => 'material_icons_outlined', 'label' => 'Material Icons Outlined'],
                        ['value' => 'material_icons_round', 'label' => 'Material Icons Round'],
                        ['value' => 'material_icons_sharp', 'label' => 'Material Icons Sharp'],
                    ],
                ],
            ],
            'permissions' => [],
            'assets' => [
                'js' => [],
                'css' => [],
            ],
            'requires' => [
                'builder_version' => '>=2.0.0',
                'php' => '>=8.1',
                'plugins' => [],
            ],
        ];
    }

    protected function tailwindClassesAutocompleteManifest(): array
    {
        return [
            'name' => 'Tailwind Classes Autocomplete',
            'slug' => 'tailwind-classes-autocomplete',
            'version' => '1.0.0',
            'author' => 'Funnel Builder Core',
            'description' => 'Adds Tailwind class autocomplete to the default GrapesJS Classes input.',
            'category' => 'enhancement',
            'icon' => 'tailwind-autocomplete',
            'hooks' => ['editor.init', 'editor.ready'],
            'settings' => [
                'enabled' => [
                    'type' => 'select',
                    'label' => 'Enable Autocomplete',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
                'min_chars' => [
                    'type' => 'number',
                    'label' => 'Min Characters Before Suggestions',
                    'required' => false,
                ],
                'max_suggestions' => [
                    'type' => 'number',
                    'label' => 'Max Suggestions',
                    'required' => false,
                ],
            ],
            'permissions' => [],
            'assets' => [
                'js' => [],
                'css' => [],
            ],
            'requires' => [
                'builder_version' => '>=2.0.0',
                'php' => '>=8.1',
                'plugins' => ['tailwind-css'],
            ],
        ];
    }

    protected function tailwindCardsManifest(): array
    {
        return [
            'name' => 'Tailwind Cards Blocks',
            'slug' => 'tailwind-cards',
            'version' => '1.0.0',
            'author' => 'Funnel Builder Core',
            'description' => 'Adds 12 Tailwind card blocks to GrapesJS and keeps their card styles visible inside the editor canvas.',
            'category' => 'element',
            'icon' => 'cards',
            'hooks' => ['editor.init', 'editor.ready'],
            'settings' => [
                'enabled' => [
                    'type' => 'select',
                    'label' => 'Enable Cards Blocks',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
                'load_builder_css' => [
                    'type' => 'select',
                    'label' => 'Load Builder CSS in Canvas',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
            ],
            'permissions' => [],
            'assets' => [
                'js' => [],
                'css' => [],
            ],
            'requires' => [
                'builder_version' => '>=2.0.0',
                'php' => '>=8.1',
                'plugins' => ['tailwind-css'],
            ],
        ];
    }

    protected function grapesJsLpBuilderManifest(): array
    {
        return [
            'name' => 'GrapesJS LP Builder',
            'slug' => 'grapesjs-lp-builder',
            'version' => '1.0.0',
            'author' => 'Funnel Builder Core',
            'description' => 'Adds section-aware GSAP and Three.js controls for LP templates, plus manifest prefill and export animation regeneration.',
            'category' => 'enhancement',
            'icon' => 'layers',
            'hooks' => ['editor.init', 'editor.ready', 'page.save'],
            'settings' => [
                'enabled' => [
                    'type' => 'select',
                    'label' => 'Enable LP Builder Plugin',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
                'gsap' => [
                    'type' => 'select',
                    'label' => 'Enable GSAP Sections',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
                'threejs' => [
                    'type' => 'select',
                    'label' => 'Enable Three.js Sections',
                    'required' => false,
                    'options' => [
                        ['value' => '1', 'label' => 'Enabled'],
                        ['value' => '0', 'label' => 'Disabled'],
                    ],
                ],
                'gsap_version' => [
                    'type' => 'text',
                    'label' => 'GSAP Version',
                    'required' => false,
                ],
                'three_version' => [
                    'type' => 'text',
                    'label' => 'Three.js Version',
                    'required' => false,
                ],
                'debug' => [
                    'type' => 'select',
                    'label' => 'Debug Logging',
                    'required' => false,
                    'options' => [
                        ['value' => '0', 'label' => 'Disabled'],
                        ['value' => '1', 'label' => 'Enabled'],
                    ],
                ],
            ],
            'permissions' => [],
            'assets' => [
                'js' => [],
                'css' => [],
            ],
            'requires' => [
                'builder_version' => '>=2.0.0',
                'php' => '>=8.1',
                'plugins' => [],
            ],
        ];
    }
}
