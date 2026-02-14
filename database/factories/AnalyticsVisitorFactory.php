<?php

namespace Database\Factories;

use App\Models\AnalyticsVisitor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AnalyticsVisitorFactory extends Factory
{
    protected $model = AnalyticsVisitor::class;

    public function definition()
    {
        return [
            'visitor_id' => $this->faker->uuid(),
            'ip_hash' => hash('sha256', $this->faker->ipv4()),
            'user_agent' => $this->faker->userAgent(),
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ];
    }
}
