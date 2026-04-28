<?php

namespace App\Services\Plugins;

use App\Models\Plugin;
use App\Models\PluginStorageItem;
use App\Models\Workspace;
use App\Models\WorkspacePlugin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PluginManagerService
{
    public function __construct(
        protected PluginManifestValidator $manifestValidator
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function install(array $manifest, bool $isCore = false): Plugin
    {
        $validated = $this->manifestValidator->validate($manifest);

        return DB::transaction(function () use ($manifest, $validated, $isCore) {
            $plugin = Plugin::query()->firstOrNew(['slug' => $validated['slug']]);
            $isNew = !$plugin->exists;

            $plugin->fill([
                'name' => $validated['name'],
                'version' => $validated['version'],
                'author' => $validated['author'] ?? null,
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'],
                'icon' => $validated['icon'] ?? null,
                'manifest' => $manifest,
                'requires' => $validated['requires'],
                'hooks' => $validated['hooks'],
                'settings_schema' => $validated['settings'],
                'permissions' => $validated['permissions'],
                'assets' => $validated['assets'],
                'runtime_entry' => $validated['runtime_entry'] ?? null,
                'is_core' => $isCore || (bool) $plugin->is_core,
                'is_active' => true,
            ]);

            if ($isNew) {
                $plugin->installed_at = now();
            }

            $plugin->save();

            return $plugin->fresh();
        });
    }

    /**
     * @throws ValidationException
     */
    public function activateForWorkspace(
        Workspace $workspace,
        Plugin $plugin,
        array $settings = [],
        array $approvedPermissions = []
    ): WorkspacePlugin {
        $this->ensureDependenciesInstalled($plugin);
        $this->assertPermissionsApproved($plugin, $approvedPermissions);

        return DB::transaction(function () use ($workspace, $plugin, $settings, $approvedPermissions) {
            $workspacePlugin = WorkspacePlugin::query()->firstOrNew([
                'workspace_id' => $workspace->id,
                'plugin_id' => $plugin->id,
            ]);

            $existingSettings = is_array($workspacePlugin->settings) ? $workspacePlugin->settings : [];
            $workspacePlugin->status = 'active';
            $workspacePlugin->settings = array_merge($existingSettings, $settings);
            $workspacePlugin->approved_permissions = array_values(array_unique($approvedPermissions));
            $workspacePlugin->activated_at = now();
            $workspacePlugin->deactivated_at = null;
            $workspacePlugin->last_error = null;
            $workspacePlugin->save();

            $this->callRuntimeMethod(
                $workspacePlugin,
                'onActivate',
                [$workspace, $plugin, $workspacePlugin->settings ?? []],
                true
            );

            return $workspacePlugin->fresh();
        });
    }

    /**
     * @throws ValidationException
     */
    public function deactivateForWorkspace(Workspace $workspace, Plugin $plugin): WorkspacePlugin
    {
        $workspacePlugin = WorkspacePlugin::query()
            ->where('workspace_id', $workspace->id)
            ->where('plugin_id', $plugin->id)
            ->firstOrFail();

        return DB::transaction(function () use ($workspacePlugin, $workspace, $plugin) {
            $workspacePlugin->status = 'inactive';
            $workspacePlugin->deactivated_at = now();
            $workspacePlugin->last_error = null;
            $workspacePlugin->save();

            $this->callRuntimeMethod(
                $workspacePlugin,
                'onDeactivate',
                [$workspace, $plugin, $workspacePlugin->settings ?? []],
                true
            );

            return $workspacePlugin->fresh();
        });
    }

    public function updateWorkspaceSettings(Workspace $workspace, Plugin $plugin, array $settings): WorkspacePlugin
    {
        $workspacePlugin = WorkspacePlugin::query()
            ->where('workspace_id', $workspace->id)
            ->where('plugin_id', $plugin->id)
            ->firstOrFail();

        $workspacePlugin->settings = array_merge(
            is_array($workspacePlugin->settings) ? $workspacePlugin->settings : [],
            $settings
        );
        $workspacePlugin->last_error = null;
        $workspacePlugin->save();

        return $workspacePlugin->fresh();
    }

    /**
     * @throws ValidationException
     */
    public function uninstall(Plugin $plugin): void
    {
        if ($plugin->is_core) {
            throw ValidationException::withMessages([
                'plugin' => ['Core plugins cannot be uninstalled.'],
            ]);
        }

        DB::transaction(function () use ($plugin) {
            WorkspacePlugin::query()->where('plugin_id', $plugin->id)->delete();
            PluginStorageItem::query()->where('plugin_id', $plugin->id)->delete();
            $plugin->delete();
        });
    }

    public function listForWorkspace(Workspace $workspace): Collection
    {
        return Plugin::query()
            ->with([
                'workspacePlugins' => fn ($query) => $query->where('workspace_id', $workspace->id),
            ])
            ->orderByDesc('is_core')
            ->orderBy('name')
            ->get();
    }

    public function dispatchHook(Workspace $workspace, string $hook, array $payload = []): array
    {
        $workspacePlugins = WorkspacePlugin::query()
            ->with('plugin')
            ->where('workspace_id', $workspace->id)
            ->where('status', 'active')
            ->get();

        $results = [];

        foreach ($workspacePlugins as $workspacePlugin) {
            $plugin = $workspacePlugin->plugin;
            if (!$plugin || !$plugin->is_active) {
                continue;
            }

            $registeredHooks = is_array($plugin->hooks) ? $plugin->hooks : [];
            if (!in_array($hook, $registeredHooks, true)) {
                continue;
            }

            $output = $this->callRuntimeMethod(
                $workspacePlugin,
                'handleHook',
                [$hook, $payload, $workspace, $plugin, $workspacePlugin->settings ?? []],
                false
            );

            $results[] = [
                'plugin' => $plugin->slug,
                'hook' => $hook,
                'output' => $output,
            ];
        }

        return $results;
    }

    /**
     * @throws ValidationException
     */
    protected function ensureDependenciesInstalled(Plugin $plugin): void
    {
        $requires = is_array($plugin->requires) ? $plugin->requires : [];
        $requiredPlugins = collect($requires['plugins'] ?? [])
            ->filter(fn ($slug) => is_string($slug) && $slug !== '')
            ->values()
            ->all();

        if ($requiredPlugins === []) {
            return;
        }

        $installed = Plugin::query()
            ->whereIn('slug', $requiredPlugins)
            ->pluck('slug')
            ->all();

        $missing = array_values(array_diff($requiredPlugins, $installed));
        if ($missing !== []) {
            throw ValidationException::withMessages([
                'plugin' => ['Missing plugin dependencies: ' . implode(', ', $missing)],
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function assertPermissionsApproved(Plugin $plugin, array $approvedPermissions): void
    {
        $required = collect($plugin->permissions ?? [])
            ->filter(fn ($permission) => is_string($permission) && $permission !== '')
            ->unique()
            ->values()
            ->all();

        if ($required === []) {
            return;
        }

        $approved = collect($approvedPermissions)
            ->filter(fn ($permission) => is_string($permission) && $permission !== '')
            ->unique()
            ->values()
            ->all();

        $missing = array_values(array_diff($required, $approved));
        if ($missing !== []) {
            throw ValidationException::withMessages([
                'plugin' => ['Missing permission approval for: ' . implode(', ', $missing)],
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function callRuntimeMethod(
        WorkspacePlugin $workspacePlugin,
        string $method,
        array $arguments,
        bool $throwOnFailure
    ): mixed {
        $plugin = $workspacePlugin->plugin;
        if (!$plugin || !is_string($plugin->runtime_entry) || $plugin->runtime_entry === '') {
            return null;
        }

        if (!class_exists($plugin->runtime_entry)) {
            return null;
        }

        $instance = app($plugin->runtime_entry);
        if (!method_exists($instance, $method)) {
            return null;
        }

        try {
            return $instance->{$method}(...$arguments);
        } catch (Throwable $e) {
            $workspacePlugin->status = 'error';
            $workspacePlugin->last_error = Str::limit($e->getMessage(), 1000);
            $workspacePlugin->save();

            report($e);

            if ($throwOnFailure) {
                throw ValidationException::withMessages([
                    'plugin' => ['Plugin runtime failed for ' . $plugin->slug . '.'],
                ]);
            }

            return [
                'error' => 'runtime_error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
