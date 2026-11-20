<?php

namespace Database\Factories;

use App\Models\Regency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Regency>
 */
class RegencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Kabupaten '.fake()->unique()->numerify('Uji ####');

        return [
            'slug' => Str::slug($name),
            'name' => $name,
        ];
    }
}
