<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\RelatorioTransacoes;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioTransacoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_nao_inclui_transacoes_de_outros_usuarios(): void
    {
        $eu = User::factory()->create();
        $outro = User::factory()->create();

        Transacao::factory()->create([
            'usuario_id' => $eu->id,
            'descricao' => 'Minha conta',
            'data_vencimento' => '2026-10-10',
        ]);

        Transacao::factory()->create([
            'usuario_id' => $outro->id,
            'descricao' => 'Conta alheia',
            'data_vencimento' => '2026-10-10',
        ]);

        $this->actingAs($eu);

        $resultado = app(RelatorioTransacoes::class)->buscarTransacoes([]);

        $this->assertCount(1, $resultado);
        $this->assertEquals('Minha conta', $resultado->first()->descricao);
    }

    public function test_filtro_de_periodo_respeita_o_usuario(): void
    {
        $eu = User::factory()->create();
        $outro = User::factory()->create();

        Transacao::factory()->create([
            'usuario_id' => $eu->id,
            'data_vencimento' => '2026-10-10',
        ]);

        Transacao::factory()->create([
            'usuario_id' => $outro->id,
            'data_vencimento' => '2026-10-10',
        ]);

        $this->actingAs($eu);

        $resultado = app(RelatorioTransacoes::class)->buscarTransacoes([
            'data_inicio' => '2026-10-01',
            'data_fim' => '2026-10-31',
        ]);

        $this->assertCount(1, $resultado);
        $this->assertTrue($resultado->every(fn ($t) => $t->usuario_id === $eu->id));
    }
}
