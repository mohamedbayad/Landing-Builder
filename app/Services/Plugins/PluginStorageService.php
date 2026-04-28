<?php

namespace App\Services\Plugins;

use App\Models\Plugin;
use App\Models\PluginStorageItem;
use App\Models\Workspace;
use Carbon\CarbonInterface;

class PluginStorageService
{
    public function get(Workspace $workspace, Plugin $plugin, string $key, mixed $default = null): mixed
    {
        $item = PluginStorageItem::query()
            ->where('workspace_id', $workspace->id)
            ->where('plugin_id', $plugin->id)
            ->where('key', $key)
            ->first();

        if (!$item) {
            return $default;
        }

        if ($item->expires_at && $item->expires_at->isPast()) {
            $item->delete();

            return $default;
        }

        if ($item->value === null || $item->value === '') {
            return $default;
        }

        $decoded = json_decode($item->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    }

    public function set(
        Workspace $workspace,
        Plugin $plugin,
        string $key,
        mixed $value,
        ?CarbonInterface $expiresAt = null
    ): PluginStorageItem {
        return PluginStorageItem::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'plugin_id' => $plugin->id,
                'key' => $key,
            ],
            [
                'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'expires_at' => $expiresAt,
            ]
        );
    }

    public function forget(Workspace $workspace, Plugin $plugin, string $key): void
    {
        PluginStorageItem::query()
            ->where('workspace_id', $workspace->id)
            ->where('plugin_id', $plugin->id)
            ->where('key', $key)
            ->delete();
    }

    public function flushExpired(): int
    {
        return PluginStorageItem::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();
    }
}

