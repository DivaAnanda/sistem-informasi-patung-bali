<?php

namespace Database\Factories;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Theme>
 */
class ThemeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Tema '.fake()->unique()->numerify('###');

        return [
            'slug' => Str::slug($name),
            'name' => ['id' => $name, 'en' => $name],
            'sort_order' => 0,
        ];
    }
}
