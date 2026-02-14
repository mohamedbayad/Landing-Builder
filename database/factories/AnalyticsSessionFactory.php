<?php

namespace Database\Factories;

use App\Models\AnalyticsSession;
use App\Models\AnalyticsVisitor;
use App\Models\Landing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalyticsSessionFactory extends Factory
{
    protected $model = AnalyticsSession::class;

    public function definition()
    {
        return [
            'session_id' => $this->faker->uuid(),
            'visitor_id' => AnalyticsVisitor::factory(),
            'landing_id' => Landing::factory(),
            'started_at' => now(),
            'last_activity_at' => now(),
            'is_bounce' => true,
            'duration_seconds' => 0,
            'source_type' => 'direct',
            'device_type' => 'desktop',
        ];
    }
}
