<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Landing;
use App\Models\LandingPage;

class LandingSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_landing_settings()
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        $landing = Landing::create(['workspace_id' => $workspace->id, 'name' => 'L1', 'slug' => 'l1']);

        $response = $this->actingAs($user)->put(route('landings.update', $landing), [
            'name' => 'L1 Updated',
            'slug' => 'l1-updated',
            'meta_title' => 'My SEO Title',
            'meta_description' => 'My SEO Description',
            'fb_pixel_id' => '123456789',
            'custom_head_scripts' => '<script>console.log("head")</script>',
        ]);

        $response->assertRedirect(route('landings.index'));
        
        $this->assertDatabaseHas('landings', ['slug' => 'l1-updated']);
        $this->assertDatabaseHas('landing_settings', [
            'landing_id' => $landing->id,
            'meta_title' => 'My SEO Title',
            'fb_pixel_id' => '123456789',
        ]);
    }

    public function test_settings_are_injected_into_view()
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        $landing = Landing::create(['workspace_id' => $workspace->id, 'name' => 'L1', 'slug' => 'l1', 'status' => 'published', 'is_main' => true]);
        $page = LandingPage::create(['landing_id' => $landing->id, 'type' => 'index', 'name' => 'Home', 'slug' => 'index']);
        
        $landing->settings()->create([
            'meta_title' => 'Injected Title',
            'custom_head_scripts' => '<script>alert("injected")</script>',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Injected Title');
        $response->assertSee('<script>alert("injected")</script>', false);
    }
}
