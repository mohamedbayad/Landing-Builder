<?php

use App\Models\Workspace;
use App\Models\WorkspaceSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('workspace_settings', 'workspace_public_endpoint')) {
            Schema::table('workspace_settings', function (Blueprint $table) {
                $table->string('workspace_public_endpoint', 80)->nullable()->after('workspace_id');
            });
        }

        // Ensure every workspace has at least one settings row.
        Workspace::query()
            ->select(['id', 'name'])
            ->chunkById(100, function ($workspaces): void {
                foreach ($workspaces as $workspace) {
                    WorkspaceSetting::query()->firstOrCreate(
                        ['workspace_id' => $workspace->id],
                        ['workspace_public_endpoint' => null]
                    );
                }
            });

        $reserved = [
            'w', 'api', 'dashboard', 'login', 'register', 'logout', 'password', 'email',
            'profile', 'sanctum', 'templates', 'landings', 'app', 'preview', 'settings',
            'plugins', 'users', 'plans', 'subscriptions', 'analytics', 'media',
        ];

        $used = WorkspaceSetting::query()
            ->whereNotNull('workspace_public_endpoint')
            ->pluck('workspace_public_endpoint')
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();
        $usedLookup = array_fill_keys($used, true);

        WorkspaceSetting::query()
            ->with('workspace:id,name')
            ->orderBy('id')
            ->chunkById(100, function ($settings) use (&$usedLookup, $reserved): void {
                foreach ($settings as $setting) {
                    $current = strtolower(trim((string) ($setting->workspace_public_endpoint ?? '')));
                    if ($current !== '' && !isset($usedLookup[$current])) {
                        $usedLookup[$current] = true;
                        continue;
                    }

                    $base = Str::slug((string) optional($setting->workspace)->name);
                    if ($base === '') {
                        $base = 'workspace';
                    }

                    $seed = $base . '-' . (string) $setting->workspace_id;
                    $candidate = strtolower(trim($seed, '-'));
                    if ($candidate === '' || in_array($candidate, $reserved, true)) {
                        $candidate = 'workspace-' . (string) $setting->workspace_id;
                    }

                    $counter = 2;
                    while (isset($usedLookup[$candidate]) || in_array($candidate, $reserved, true)) {
                        $candidate = $base . '-' . (string) $setting->workspace_id . '-' . $counter;
                        $counter++;
                    }

                    $setting->workspace_public_endpoint = $candidate;
                    $setting->save();
                    $usedLookup[$candidate] = true;
                }
            });

        Schema::table('workspace_settings', function (Blueprint $table) {
            $table->unique('workspace_public_endpoint', 'workspace_settings_public_endpoint_unique');
        });

        // Enforce non-nullability after backfill.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE workspace_settings MODIFY workspace_public_endpoint VARCHAR(80) NOT NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('workspace_settings', 'workspace_public_endpoint')) {
            return;
        }

        Schema::table('workspace_settings', function (Blueprint $table) {
            $table->dropUnique('workspace_settings_public_endpoint_unique');
            $table->dropColumn('workspace_public_endpoint');
        });
    }
};
