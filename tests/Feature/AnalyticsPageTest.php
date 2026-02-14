<?php

namespace Tests\Feature;

use App\Models\Landing;
use App\Models\PageVisit;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $workspace;
    protected $landing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->workspace = Workspace::create(['user_id' => $this->user->id, 'name' => 'Test WS']);
        $this->landing = Landing::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Campaign LP',
            'slug' => 'test-lp',
            'status' => 'published',
            'is_main' => false,
        ]);
    }

    public function test_analytics_page_loads()
    {
        $response = $this->actingAs($this->user)->get('/analytics');
        $response->assertStatus(200);
        $response->assertViewIs('analytics.index');
        $response->assertSee('Analytics Overview');
    }

    public function test_analytics_data_api_returns_json()
    {
        // Seed data
        \App\Models\AnalyticsSession::factory()->count(10)->create([
            'landing_id' => $this->landing->id,
            'source_type' => 'social',
            'device_type' => 'mobile',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/analytics/data');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'kpi' => ['sessions', 'uniques', 'leads', 'conversion_rate', 'bounce_rate', 'avg_duration'],
            'charts' => ['labels', 'sessions', 'leads'],
            'breakdowns' => ['sources', 'devices', 'top_referrers', 'visitor_types'],
            'funnel',
            'landing_performance'
        ]);
        
        // Assert counts
        $response->assertJsonPath('kpi.sessions', 10);
        $response->assertJsonPath('breakdowns.sources.Social', 10);
    }

    public function test_analytics_data_filters_by_landing()
    {
        // Another landing
        $otherLanding = Landing::create([
            'workspace_id' => $this->workspace->id,
            'name' => 'Other LP',
            'slug' => 'other-lp',
            'status' => 'published',
        ]);

        \App\Models\AnalyticsSession::factory()->count(5)->create(['landing_id' => $this->landing->id]);
        \App\Models\AnalyticsSession::factory()->count(3)->create(['landing_id' => $otherLanding->id]);

        // Filter for first landing
        $response = $this->actingAs($this->user)->getJson('/analytics/data?landing_id=' . $this->landing->id);
        $response->assertJsonPath('kpi.sessions', 5);

        // Filter for second landing
        $response = $this->actingAs($this->user)->getJson('/analytics/data?landing_id=' . $otherLanding->id);
        $response->assertJsonPath('kpi.sessions', 3);
    }

    public function test_user_cannot_access_other_users_data()
    {
        $otherUser = User::factory()->create();
        $otherWorkspace = Workspace::create(['user_id' => $otherUser->id, 'name' => 'Other WS']);
        $otherLanding = Landing::create([
            'workspace_id' => $otherWorkspace->id,
            'name' => 'Other User LP',
            'slug' => 'other-user-lp',
            'status' => 'published',
        ]);

        // Try to access other user's landing data
        $response = $this->actingAs($this->user)->getJson('/analytics/data?landing_id=' . $otherLanding->id);
        
        $response->assertStatus(403);
    }
}
