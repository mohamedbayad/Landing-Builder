<?php

namespace Database\Factories;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    protected $model = MediaAsset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'landing_id' => null,
            'filename' => $this->faker->word . '.jpg',
            'relative_path' => 'media/' . $this->faker->uuid . '.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1000, 50000),
            'width' => 800,
            'height' => 600,
            'source' => 'manual',
            'hash' => null,
        ];
    }
}
