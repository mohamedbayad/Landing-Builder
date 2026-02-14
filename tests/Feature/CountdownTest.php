<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Landing;
use Carbon\Carbon;

class CountdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_countdown_disabled_returns_correct_response()
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        $landing = Landing::create([
            'workspace_id' => $workspace->id, 
            'name' => 'L1', 
            'slug' => 'l1',
            'countdown_enabled' => false
        ]);

        $response = $this->getJson(route('landings.countdown', $landing));

        $response->assertOk()
            ->assertJson([
                'enabled' => false,
                'remaining_seconds' => 0
            ]);
    }

    public function test_countdown_fixed_date_returns_correct_remaining_seconds()
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        
        $future = now()->addHour();
        
        $landing = Landing::create([
            'workspace_id' => $workspace->id, 
            'name' => 'L1', 
            'slug' => 'l1',
            'countdown_enabled' => true,
            'countdown_end_at' => $future
        ]);

        $response = $this->getJson(route('landings.countdown', $landing));

        $response->assertOk()
            ->assertJson([
                'enabled' => true,
                'end_at' => $future->toIso8601String()
            ]);
            
        $data = $response->json();
        // Allow 1-2 seconds difference for execution time
        $this->assertTrue(abs($data['remaining_seconds'] - 3600) < 5);
    }

    public function test_countdown_duration_mode_calculates_end_at_correctly()
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        
        $startedAt = now()->subMinutes(10);
        $duration = 30; // 30 mins total, 20 mins remaining
        
        $landing = Landing::create([
            'workspace_id' => $workspace->id, 
            'name' => 'L1', 
            'slug' => 'l1',
            'countdown_enabled' => true,
            'countdown_duration_minutes' => $duration,
            'countdown_started_at' => $startedAt
        ]);

        $response = $this->getJson(route('landings.countdown', $landing));

        $expectedEndAt = $startedAt->copy()->addMinutes($duration);
        $expectedRemaining = 20 * 60;

        $response->assertOk()
            ->assertJson([
                'enabled' => true,
                'end_at' => $expectedEndAt->toIso8601String()
            ]);

        $data = $response->json();
        $this->assertTrue(abs($data['remaining_seconds'] - $expectedRemaining) < 5);
    }

    public function test_countdown_expired_returns_zero_remaining()
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'WS']);
        
        $past = now()->subHour();
        
        $landing = Landing::create([
            'workspace_id' => $workspace->id, 
            'name' => 'L1', 
            'slug' => 'l1',
            'countdown_enabled' => true,
            'countdown_end_at' => $past
        ]);

        $response = $this->getJson(route('landings.countdown', $landing));

        $response->assertOk()
            ->assertJson([
                'enabled' => true,
                'remaining_seconds' => 0
            ]);
    }
}
