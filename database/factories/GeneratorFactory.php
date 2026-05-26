<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GeneratorFactory extends Factory
{
    protected $model = \App\Models\Generator::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);
        return [
            'name' => $name,
            'model_name' => str_replace(' ', '', ucwords($name)),
            'primary_key' => 'id',
            'primary_key_type' => 'bigIncrements',
            'timestamps' => true,
            'soft_deletes' => false,
            'default_card_size' => 'md',
            'fields' => [],
            'relationships' => [],
            'query_conditions' => [],
            'table_columns' => [],
            'status' => 'pending',
            'generated_files' => [],
        ];
    }
}
