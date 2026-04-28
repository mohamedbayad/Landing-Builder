<?php

namespace Tests\Feature;

use App\Models\Plugin;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspacePlugin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PluginSystemManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Workspace $workspace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_owner_can_install_plugin_manifest(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('plugins.install'), [
            'manifest' => $this->baseManifest(),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('plugin.slug', 'google-analytics');
        $response->assertJsonPath('plugin.category', 'integration');

        $this->assertDatabaseHas('plugins', [
            'slug' => 'google-analytics',
            'name' => 'Google Analytics',
            'category' => 'integration',
            'is_core' => 0,
        ]);
    }

    public function test_activation_requires_permissions_approval(): void
    {
        $plugin = $this->installPlugin();

        $this->actingAs($this->user)
            ->postJson(route('plugins.activate', $plugin), [
                'settings' => ['measurement_id' => 'G-123456'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('plugin');

        $this->actingAs($this->user)
            ->postJson(route('plugins.activate', $plugin), [
                'settings' => ['measurement_id' => 'G-123456'],
                'approved_permissions' => ['analytics', 'tracking'],
            ])
            ->assertOk()
            ->assertJsonPath('plugin.workspace_state.status', 'active')
            ->assertJsonPath('plugin.workspace_state.settings.measurement_id', 'G-123456');

        $workspacePlugin = WorkspacePlugin::query()
            ->where('workspace_id', $this->workspace->id)
            ->where('plugin_id', $plugin->id)
            ->firstOrFail();

        $this->assertSame('active', $workspacePlugin->status);
        $this->assertSame('G-123456', $workspacePlugin->settings['measurement_id']);

        $rawSettings = DB::table('workspace_plugins')
            ->where('id', $workspacePlugin->id)
            ->value('settings');

        $this->assertIsString($rawSettings);
        $this->assertStringStartsWith('{', ltrim($rawSettings));
    }

    public function test_owner_can_update_and_deactivate_plugin_for_workspace(): void
    {
        $plugin = $this->installPlugin();

        $this->actingAs($this->user)
            ->postJson(route('plugins.activate', $plugin), [
                'settings' => ['measurement_id' => 'G-111111'],
                'approved_permissions' => ['analytics', 'tracking'],
            ])
            ->assertOk();

        $this->actingAs($this->user)
            ->putJson(route('plugins.settings.update', $plugin), [
                'settings' => ['measurement_id' => 'G-222222'],
            ])
            ->assertOk()
            ->assertJsonPath('plugin.workspace_state.settings.measurement_id', 'G-222222');

        $this->actingAs($this->user)
            ->postJson(route('plugins.deactivate', $plugin))
            ->assertOk()
            ->assertJsonPath('plugin.workspace_state.status', 'inactive');

        $this->assertDatabaseHas('workspace_plugins', [
            'workspace_id' => $this->workspace->id,
            'plugin_id' => $plugin->id,
            'status' => 'inactive',
        ]);
    }

    public function test_dispatch_hook_runs_only_active_plugins_registered_for_event(): void
    {
        $activePlugin = $this->installPlugin([
            'slug' => 'active-runtime-plugin',
            'name' => 'Active Runtime Plugin',
            'runtime_entry' => \App\Plugins\Runtime\DebugEchoRuntimePlugin::class,
        ]);

        $inactivePlugin = $this->installPlugin([
            'slug' => 'inactive-runtime-plugin',
            'name' => 'Inactive Runtime Plugin',
            'runtime_entry' => \App\Plugins\Runtime\DebugEchoRuntimePlugin::class,
        ]);

        $this->actingAs($this->user)
            ->postJson(route('plugins.activate', $activePlugin), [
                'settings' => ['measurement_id' => 'G-ACTIVE'],
                'approved_permissions' => ['analytics', 'tracking'],
            ])
            ->assertOk();

        $this->actingAs($this->user)
            ->postJson(route('plugins.activate', $inactivePlugin), [
                'settings' => ['measurement_id' => 'G-INACTIVE'],
                'approved_permissions' => ['analytics', 'tracking'],
            ])
            ->assertOk();

        $this->actingAs($this->user)
            ->postJson(route('plugins.deactivate', $inactivePlugin))
            ->assertOk();

        $response = $this->actingAs($this->user)
            ->postJson(route('plugins.hooks.dispatch'), [
                'hook' => 'page.render',
                'payload' => [
                    'page_id' => 123,
                ],
            ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'results');
        $response->assertJsonPath('results.0.plugin', 'active-runtime-plugin');
        $response->assertJsonPath('results.0.output.measurement_id', 'G-ACTIVE');
        $response->assertJsonPath('results.0.output.payload.page_id', 123);
    }

    public function test_non_core_plugin_can_be_uninstalled(): void
    {
        $plugin = $this->installPlugin();

        $this->actingAs($this->user)
            ->deleteJson(route('plugins.destroy', $plugin))
            ->assertNoContent();

        $this->assertDatabaseMissing('plugins', [
            'id' => $plugin->id,
        ]);
    }

    public function test_core_plugin_cannot_be_uninstalled(): void
    {
        $plugin = $this->installPlugin([], true);

        $this->actingAs($this->user)
            ->deleteJson(route('plugins.destroy', $plugin))
            ->assertStatus(422)
            ->assertJsonValidationErrors('plugin');
    }

    protected function installPlugin(array $overrides = [], bool $isCore = false): Plugin
    {
        $payload = array_merge($this->baseManifest(), $overrides);

        $this->actingAs($this->user)->postJson(route('plugins.install'), [
            'manifest' => $payload,
            'is_core' => $isCore,
        ])->assertCreated();

        return Plugin::query()->where('slug', $payload['slug'])->firstOrFail();
    }

    protected function baseManifest(): array
    {
        return [
            'name' => 'Google Analytics',
            'slug' => 'google-analytics',
            'version' => '1.2.0',
            'author' => 'Internal Team',
            'description' => 'Track page views and events with GA4',
            'category' => 'integration',
            'hooks' => ['page.render', 'element.click'],
            'settings' => [
                'measurement_id' => [
                    'type' => 'text',
                    'label' => 'GA4 Measurement ID',
                    'required' => true,
                ],
            ],
            'permissions' => ['analytics', 'tracking'],
            'assets' => [
                'js' => ['plugin.js'],
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
