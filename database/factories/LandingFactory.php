<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Landing>
 */
class LandingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workspace_id' => \App\Models\Workspace::factory(),
            'name' => $this->faker->sentence(3),
            'slug' => $this->faker->slug,
            'status' => 'draft',
            'is_main' => false,
            'uuid' => $this->faker->uuid,
        ];
    }
}
