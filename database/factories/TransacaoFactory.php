<?php

namespace Database\Factories;

use App\Models\Transacao;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transacao>
 */
class TransacaoFactory extends Factory
{
    protected $model = Transacao::class;

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'tipo' => 'despesa',
            'descricao' => fake()->sentence(3),
            'valor' => fake()->randomFloat(2, 10, 500),
            'forma_pagamento' => 'boleto',
            'status' => 'pendente',
            'data_vencimento' => now()->addDays(fake()->numberBetween(1, 60))->toDateString(),
            'recorrente' => false,
            'parcela_atual' => 1,
            'qtd_parcelas' => 1,
        ];
    }

    public function paga(): static
    {
        return $this->state(fn () => [
            'status' => 'paga',
            'data_pagamento' => now()->toDateString(),
        ]);
    }
}
