<?php

namespace Tests\Feature\Services;

use App\Models\Transacao;
use App\Models\User;
use App\Services\ParcelamentoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParcelamentoServiceTest extends TestCase
{
    use RefreshDatabase;

    private function dados(User $user, array $extra = []): array
    {
        return array_merge([
            'usuario_id' => $user->id,
            'tipo' => 'despesa',
            'descricao' => 'Notebook',
            'valor' => 1000.00,
            'forma_pagamento' => 'cartao_credito',
            'status' => 'pendente',
            'data_vencimento' => '2026-01-31',
            'qtd_parcelas' => 3,
            'recorrente' => false,
        ], $extra);
    }

    public function test_soma_das_parcelas_bate_com_o_total(): void
    {
        $user = User::factory()->create();

        app(ParcelamentoService::class)->criarCompraParcelada($this->dados($user, ['valor' => 100.00]));

        $this->assertEquals(100.00, Transacao::where('usuario_id', $user->id)->sum('valor'));
    }

    public function test_centavo_residual_fica_na_primeira_parcela(): void
    {
        $user = User::factory()->create();

        // 100,00 / 3 = 33,33 base; diferença de 0,01 vai para a 1ª
        app(ParcelamentoService::class)->criarCompraParcelada($this->dados($user, ['valor' => 100.00]));

        $parcelas = Transacao::where('usuario_id', $user->id)->orderBy('parcela_atual')->get();

        $this->assertEquals(33.34, (float) $parcelas[0]->valor);
        $this->assertEquals(33.33, (float) $parcelas[1]->valor);
        $this->assertEquals(33.33, (float) $parcelas[2]->valor);
    }

    public function test_vencimentos_mensais_sem_pular_mes(): void
    {
        $user = User::factory()->create();

        app(ParcelamentoService::class)->criarCompraParcelada($this->dados($user));

        $datas = Transacao::where('usuario_id', $user->id)
            ->orderBy('parcela_atual')
            ->pluck('data_vencimento')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->all();

        // 31/01 → 28/02 (fevereiro não tem dia 31) → 31/03 (volta ao dia original)
        $this->assertEquals(['2026-01-31', '2026-02-28', '2026-03-31'], $datas);
    }

    public function test_primeira_parcela_e_origem_das_demais(): void
    {
        $user = User::factory()->create();

        $origem = app(ParcelamentoService::class)->criarCompraParcelada($this->dados($user));

        $filhas = Transacao::where('transacao_origem_id', $origem->id)->count();

        $this->assertNull($origem->transacao_origem_id);
        $this->assertEquals(2, $filhas);
    }

    public function test_quantidade_de_registros_igual_ao_numero_de_parcelas(): void
    {
        $user = User::factory()->create();

        app(ParcelamentoService::class)->criarCompraParcelada($this->dados($user, ['qtd_parcelas' => 5]));

        $this->assertEquals(5, Transacao::where('usuario_id', $user->id)->count());
    }
}
