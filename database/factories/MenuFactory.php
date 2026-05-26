<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MenuFactory extends Factory
{
    protected $model = \App\Models\Menu::class;

    public function definition(): array
    {
        $pageName = fake()->words(2, true);
        return [
            'page_name' => $pageName,
            'page_type' => fake()->randomElement(['content', 'url']),
            'url' => fake()->optional()->url(),
            'content' => fake()->optional()->paragraph(),
            'placement' => fake()->randomElement(['header', 'footer']),
            'sort_order' => fake()->numberBetween(1, 10),
            'is_active' => true,
            'slug' => Str::slug($pageName),
        ];
    }
}
