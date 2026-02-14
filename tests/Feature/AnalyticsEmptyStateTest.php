<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Workspace;

class AnalyticsEmptyStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_api_returns_empty_state_when_no_data()
    {
        // 1. Setup User & Workspace
        $user = User::factory()->create();
        $workspace = Workspace::factory()->create(['user_id' => $user->id]);
        
        // 2. Auth
        $this->actingAs($user);

        // 3. Request Data (Default Range)
        $response = $this->getJson(route('analytics.data'));

        // 4. Verification
        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Check has_data flag
        $this->assertFalse($data['kpi']['has_data'], 'has_data should be false for empty DB');
        
        // Check KPI zeros
        $this->assertEquals(0, $data['kpi']['sessions']);
        $this->assertEquals(0, $data['kpi']['uniques']);
        
        // Check Breakdowns are empty/zero
        $this->assertEquals(0, $data['breakdowns']['sources_pct']['Direct']);
        $this->assertEquals(0, $data['breakdowns']['devices']['mobile']);
        
        // Check Charts arrays are populated with zeros (for the timeline)
        // Timeline should have labels (dates) but sessions array should be all 0s
        $this->assertGreaterThan(0, count($data['charts']['labels']));
        $this->assertEquals(0, array_sum($data['charts']['sessions']), 'Sessions chart data should be all zeros');
    }
}
