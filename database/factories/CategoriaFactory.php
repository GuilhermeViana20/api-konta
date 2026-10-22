<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'nome' => fake()->unique()->word(),
            'cor' => fake()->hexColor(),
            'tipo' => 'despesa',
        ];
    }
}
