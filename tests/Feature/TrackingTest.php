<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Landing;
use App\Models\LandingPage;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_track_event()
    {
        // Setup
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        $landing = Landing::create(['workspace_id' => $workspace->id, 'name' => 'L1', 'slug' => 'l1', 'status' => 'published']);
        $page = LandingPage::create(['landing_id' => $landing->id, 'type' => 'index', 'name' => 'Home', 'slug' => 'index']);

        $response = $this->postJson('/api/events', [
            'landing_id' => $landing->id,
            'page_id' => $page->id,
            'type' => 'page_view',
            'data' => ['referrer' => 'google.com'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tracking_events', [
            'landing_id' => $landing->id,
            'type' => 'page_view',
        ]);
    }

    public function test_can_capture_lead()
    {
        // Setup
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        $landing = Landing::create(['workspace_id' => $workspace->id, 'name' => 'L1', 'slug' => 'l1', 'status' => 'published']);

        $response = $this->postJson('/api/leads', [
            'landing_id' => $landing->id,
            'email' => 'test@example.com',
            'data' => ['name' => 'John Doe'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('leads', [
            'landing_id' => $landing->id,
            'email' => 'test@example.com',
        ]);
    }
}
