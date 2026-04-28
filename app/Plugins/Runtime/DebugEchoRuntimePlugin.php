<?php

namespace App\Plugins\Runtime;

use App\Models\Plugin;
use App\Models\Workspace;

class DebugEchoRuntimePlugin
{
    public function onActivate(Workspace $workspace, Plugin $plugin, array $settings = []): void
    {
        // Intentionally empty; this runtime exists to validate lifecycle wiring.
    }

    public function onDeactivate(Workspace $workspace, Plugin $plugin, array $settings = []): void
    {
        // Intentionally empty; this runtime exists to validate lifecycle wiring.
    }

    public function handleHook(
        string $hook,
        array $payload,
        Workspace $workspace,
        Plugin $plugin,
        array $settings = []
    ): array {
        return [
            'hook' => $hook,
            'plugin' => $plugin->slug,
            'workspace_id' => (int) $workspace->id,
            'measurement_id' => $settings['measurement_id'] ?? null,
            'payload' => $payload,
        ];
    }
}

