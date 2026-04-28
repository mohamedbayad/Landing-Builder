<?php

namespace App\Services\Plugins;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PluginManifestValidator
{
    /**
     * @throws ValidationException
     */
    public function validate(array $manifest): array
    {
        $validator = Validator::make($manifest, [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'version' => ['required', 'string', 'max:40', 'regex:/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/'],
            'author' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', 'string', Rule::in(config('plugin_system.categories', []))],
            'icon' => ['nullable', 'string', 'max:255'],
            'requires' => ['nullable', 'array'],
            'requires.builder_version' => ['nullable', 'string', 'max:40'],
            'requires.php' => ['nullable', 'string', 'max:40'],
            'requires.plugins' => ['nullable', 'array'],
            'requires.plugins.*' => ['string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'hooks' => ['nullable', 'array'],
            'hooks.*' => ['string', 'max:80'],
            'settings' => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:60'],
            'assets' => ['nullable', 'array'],
            'assets.js' => ['nullable', 'array'],
            'assets.js.*' => ['string', 'max:255'],
            'assets.css' => ['nullable', 'array'],
            'assets.css.*' => ['string', 'max:255'],
            'runtime_entry' => ['nullable', 'string', 'max:255'],
        ]);

        $allowedHooks = config('plugin_system.hooks', []);
        $allowedPermissions = config('plugin_system.permissions', []);

        $validator->after(function ($validator) use ($manifest, $allowedHooks, $allowedPermissions) {
            $hooks = collect($manifest['hooks'] ?? [])
                ->filter(fn ($hook) => is_string($hook) && $hook !== '')
                ->unique()
                ->values()
                ->all();

            $unknownHooks = array_values(array_diff($hooks, $allowedHooks));
            if ($unknownHooks !== []) {
                $validator->errors()->add(
                    'hooks',
                    'Unknown hooks requested: ' . implode(', ', $unknownHooks)
                );
            }

            $permissions = collect($manifest['permissions'] ?? [])
                ->filter(fn ($permission) => is_string($permission) && $permission !== '')
                ->unique()
                ->values()
                ->all();

            $unknownPermissions = array_values(array_diff($permissions, $allowedPermissions));
            if ($unknownPermissions !== []) {
                $validator->errors()->add(
                    'permissions',
                    'Unknown permissions requested: ' . implode(', ', $unknownPermissions)
                );
            }
        });

        $validated = $validator->validate();

        $validated['requires'] = $validated['requires'] ?? [];
        $validated['hooks'] = array_values(array_unique($validated['hooks'] ?? []));
        $validated['settings'] = $validated['settings'] ?? [];
        $validated['permissions'] = array_values(array_unique($validated['permissions'] ?? []));
        $validated['assets'] = $validated['assets'] ?? ['js' => [], 'css' => []];

        return $validated;
    }
}

