<?php

namespace Database\Factories;

use App\Models\Regency;
use App\Models\Statue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Statue>
 */
class StatueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Patung '.fake()->unique()->firstName();

        return [
            'source_no' => fake()->unique()->numberBetween(1, 100000),
            'slug' => Str::slug($name),
            'name' => $name,
            'regency_id' => Regency::factory(),
            'district' => fake()->city(),
            'village' => fake()->streetName(),
            'latitude' => fake()->randomFloat(7, -8.8, -8.1),
            'longitude' => fake()->randomFloat(7, 114.5, 115.6),
            'description' => ['id' => fake()->paragraph()],
            'sources' => [],
        ];
    }
}
