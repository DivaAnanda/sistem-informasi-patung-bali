<?php

namespace Database\Factories;

use App\Models\JunctionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<JunctionType>
 */
class JunctionTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'slug' => Str::slug($name),
            'name' => ['id' => $name, 'en' => $name],
            'sort_order' => 0,
        ];
    }
}
