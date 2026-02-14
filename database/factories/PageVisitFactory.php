<?php

namespace Database\Factories;

use App\Models\PageVisit;
use App\Models\Landing;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageVisitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PageVisit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'landing_id' => Landing::factory(),
            'path' => $this->faker->url(),
            'full_url' => $this->faker->url(),
            'referrer' => $this->faker->url(),
            'utm_source' => $this->faker->word(),
            'utm_medium' => $this->faker->word(),
            'utm_campaign' => $this->faker->word(),
            'ip_hash' => hash('sha256', $this->faker->ipv4()),
            'user_agent' => $this->faker->userAgent(),
            'device_type' => $this->faker->randomElement(['mobile', 'desktop', 'tablet']),
            'source_type' => $this->faker->randomElement(['direct', 'search', 'social', 'referral', 'paid', 'email']),
            'country' => $this->faker->countryCode(),
        ];
    }
}
