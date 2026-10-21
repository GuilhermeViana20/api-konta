<?php

namespace Tests\Feature\Models;

use App\Models\Caixinha;
use App\Models\Transacao;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarcarComoPagaTest extends TestCase
{
    use RefreshDatabase;

    private function caixinha(User $user, float $saldo = 1000): Caixinha
    {
        return Caixinha::create([
            'usuario_id' => $user->id,
            'nome' => 'Principal',
            'saldo' => $saldo,
        ]);
    }

    public function test_marca_como_paga_e_registra_data(): void
    {
        $user = User::factory()->create();
        $transacao = Transacao::factory()->create([
            'usuario_id' => $user->id,
            'status' => 'pendente',
        ]);

        $transacao->marcarComoPaga(Carbon::parse('2026-10-20'));

        $this->assertEquals('paga', $transacao->fresh()->status);
        $this->assertEquals('2026-10-20', $transacao->fresh()->data_pagamento->toDateString());
    }

    public function test_despesa_debita_a_caixinha(): void
    {
        $user = User::factory()->create();
        $caixinha = $this->caixinha($user, 1000);
        $transacao = Transacao::factory()->create([
            'usuario_id' => $user->id,
            'tipo' => 'despesa',
            'valor' => 250.00,
            'caixinha_id' => $caixinha->id,
        ]);

        $transacao->marcarComoPaga();

        $this->assertEquals(750.00, (float) $caixinha->fresh()->saldo);
        $this->assertDatabaseHas('historicos_movimentacoes', [
            'caixinha_id' => $caixinha->id,
            'transacao_id' => $transacao->id,
            'tipo' => 'saida',
        ]);
    }

    public function test_receita_credita_a_caixinha(): void
    {
        $user = User::factory()->create();
        $caixinha = $this->caixinha($user, 1000);
        $transacao = Transacao::factory()->create([
            'usuario_id' => $user->id,
            'tipo' => 'receita',
            'valor' => 300.00,
            'caixinha_id' => $caixinha->id,
        ]);

        $transacao->marcarComoPaga();

        $this->assertEquals(1300.00, (float) $caixinha->fresh()->saldo);
    }

    public function test_transferencia_nao_movimenta_a_caixinha(): void
    {
        $user = User::factory()->create();
        $caixinha = $this->caixinha($user, 1000);
        $transacao = Transacao::factory()->create([
            'usuario_id' => $user->id,
            'tipo' => 'transferencia',
            'valor' => 300.00,
            'caixinha_id' => $caixinha->id,
        ]);

        $transacao->marcarComoPaga();

        $this->assertEquals(1000.00, (float) $caixinha->fresh()->saldo);
    }

    public function test_sem_caixinha_apenas_marca_como_paga(): void
    {
        $user = User::factory()->create();
        $transacao = Transacao::factory()->create([
            'usuario_id' => $user->id,
            'caixinha_id' => null,
        ]);

        $transacao->marcarComoPaga();

        $this->assertEquals('paga', $transacao->fresh()->status);
    }

    public function test_nao_debita_duas_vezes_se_ja_paga(): void
    {
        $user = User::factory()->create();
        $caixinha = $this->caixinha($user, 1000);
        $transacao = Transacao::factory()->create([
            'usuario_id' => $user->id,
            'tipo' => 'despesa',
            'valor' => 250.00,
            'caixinha_id' => $caixinha->id,
        ]);

        $transacao->marcarComoPaga();
        $transacao->fresh()->marcarComoPaga();

        $this->assertEquals(750.00, (float) $caixinha->fresh()->saldo);
    }
}
