<?php

namespace Database\Factories;

use App\Models\StatueType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StatueType>
 */
class StatueTypeFactory extends Factory
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
