<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Workspace;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_empty_state_when_no_page_visits()
    {
        // 1. Setup User & Workspace
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        
        // 2. Auth
        $this->actingAs($user);

        // 3. Request Dashboard
        $response = $this->get(route('dashboard'));

        // 4. Verification
        $response->assertStatus(200);
        
        // Assert we DO NOT see the empty state message anymore
        $response->assertDontSee('No traffic data');
        $response->assertDontSee('Waiting for your first visitors to arrive');
        
        // Assert we SEE the chart headers
        $response->assertSee('Traffic Sources'); 
        $response->assertSee('Device Distribution');
        
        // Assert we SEE 0% stats
        $response->assertSee('0%'); // Direct, Mobile, Desktop etc should all be 0%
    }

    public function test_dashboard_displays_stats_from_analytics_service()
    {
        // 1. Setup User & Workspace & Landing
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        $landing = \App\Models\Landing::factory()->create(['workspace_id' => $workspace->id]);

        // 2. Auth
        $this->actingAs($user);

        // 3. Create Analytics Data (Sessions) using new service models
        // Create 5 sessions
        \App\Models\AnalyticsVisitor::factory()->count(5)->create()->each(function($visitor) use ($landing) {
            \App\Models\AnalyticsSession::factory()->create([
                'visitor_id' => $visitor->id,
                'landing_id' => $landing->id,
                'started_at' => now(),
                'last_activity_at' => now(),
            ]);
        });

        // 4. Request Dashboard
        $response = $this->get(route('dashboard'));

        // 5. Verification
        $response->assertStatus(200);
        
        // Should show 5 visits
        $response->assertSee('5'); // Total Visits
        $response->assertSee('Total Visits');
    }
}
