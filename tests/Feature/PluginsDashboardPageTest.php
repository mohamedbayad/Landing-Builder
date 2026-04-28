<?php

namespace Tests\Feature;

use App\Models\Plugin;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PluginsDashboardPageTest extends TestCase
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

    public function test_plugins_dashboard_page_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.plugins.index'));

        $response->assertOk();
        $response->assertViewIs('plugins.index');
        $response->assertSee('Plugin Marketplace');
    }

    public function test_dashboard_auto_registers_tailwind_css_plugin(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.plugins.index'));

        $response->assertOk();
        $response->assertSee('Tailwind CSS Bridge');

        $this->assertDatabaseHas('plugins', [
            'slug' => 'tailwind-css',
            'is_core' => 1,
        ]);
    }

    public function test_dashboard_auto_registers_google_material_icons_plugin(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.plugins.index'));

        $response->assertOk();
        $response->assertSee('Google Material Icons');

        $this->assertDatabaseHas('plugins', [
            'slug' => 'google-material-icons',
            'is_core' => 1,
        ]);
    }

    public function test_dashboard_auto_registers_tailwind_classes_autocomplete_plugin(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.plugins.index'));

        $response->assertOk();
        $response->assertSee('Tailwind Classes Autocomplete');

        $this->assertDatabaseHas('plugins', [
            'slug' => 'tailwind-classes-autocomplete',
            'is_core' => 1,
        ]);
    }

    public function test_owner_can_install_plugin_from_dashboard(): void
    {
        $manifest = [
            'name' => 'Google Analytics',
            'slug' => 'google-analytics',
            'version' => '1.2.0',
            'category' => 'integration',
            'hooks' => ['page.render'],
            'permissions' => ['analytics', 'tracking'],
            'settings' => [
                'measurement_id' => [
                    'type' => 'text',
                    'label' => 'GA4 Measurement ID',
                    'required' => true,
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('settings.plugins.install'), [
                'manifest_json' => json_encode($manifest),
            ])
            ->assertRedirect(route('settings.plugins.index'))
            ->assertSessionHas('status', 'plugin-installed');

        $this->assertDatabaseHas('plugins', [
            'slug' => 'google-analytics',
            'name' => 'Google Analytics',
        ]);
    }

    public function test_owner_can_activate_update_and_deactivate_plugin_from_dashboard(): void
    {
        $plugin = $this->createPlugin();

        $this->actingAs($this->user)
            ->post(route('settings.plugins.activate', $plugin), [
                'approved_permissions' => ['analytics', 'tracking'],
                'settings' => [
                    'measurement_id' => 'G-AAA111',
                ],
            ])
            ->assertRedirect(route('settings.plugins.index'))
            ->assertSessionHas('status', 'plugin-activated');

        $this->assertDatabaseHas('workspace_plugins', [
            'workspace_id' => $this->workspace->id,
            'plugin_id' => $plugin->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->put(route('settings.plugins.settings.update', $plugin), [
                'settings' => [
                    'measurement_id' => 'G-BBB222',
                ],
            ])
            ->assertRedirect(route('settings.plugins.index'))
            ->assertSessionHas('status', 'plugin-settings-updated');

        $workspaceState = $this->workspace->workspacePlugins()
            ->where('plugin_id', $plugin->id)
            ->firstOrFail();

        $this->assertSame('G-BBB222', $workspaceState->settings['measurement_id']);

        $this->actingAs($this->user)
            ->post(route('settings.plugins.deactivate', $plugin))
            ->assertRedirect(route('settings.plugins.index'))
            ->assertSessionHas('status', 'plugin-deactivated');

        $this->assertDatabaseHas('workspace_plugins', [
            'workspace_id' => $this->workspace->id,
            'plugin_id' => $plugin->id,
            'status' => 'inactive',
        ]);
    }

    protected function createPlugin(): Plugin
    {
        return Plugin::query()->create([
            'name' => 'Google Analytics',
            'slug' => 'google-analytics',
            'version' => '1.2.0',
            'author' => 'Internal',
            'description' => 'Track page views and events',
            'category' => 'integration',
            'manifest' => [
                'name' => 'Google Analytics',
                'slug' => 'google-analytics',
                'version' => '1.2.0',
                'category' => 'integration',
            ],
            'requires' => [],
            'hooks' => ['page.render'],
            'settings_schema' => [
                'measurement_id' => [
                    'type' => 'text',
                    'label' => 'GA4 Measurement ID',
                    'required' => true,
                ],
            ],
            'permissions' => ['analytics', 'tracking'],
            'assets' => ['js' => [], 'css' => []],
            'is_core' => false,
            'is_active' => true,
            'installed_at' => now(),
        ]);
    }
}
