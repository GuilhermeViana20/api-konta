<?php

namespace Tests\Feature\Api;

use App\Models\Categoria;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransacaoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_apenas_transacoes_do_usuario_no_mes(): void
    {
        $eu = User::factory()->create();
        $outro = User::factory()->create();

        Transacao::factory()->create(['usuario_id' => $eu->id, 'data_vencimento' => '2026-10-10']);
        Transacao::factory()->create(['usuario_id' => $outro->id, 'data_vencimento' => '2026-10-10']);

        Sanctum::actingAs($eu);

        $this->getJson('/api/transacoes?mes=2026-10')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_cria_transacao_com_categoria_propria(): void
    {
        $eu = User::factory()->create();
        $categoria = Categoria::factory()->create(['usuario_id' => $eu->id]);

        Sanctum::actingAs($eu);

        $this->postJson('/api/transacoes', [
            'descricao' => 'Mercado',
            'tipo' => 'despesa',
            'valor' => 150.50,
            'data_vencimento' => '2026-10-15',
            'categoria_id' => $categoria->id,
        ])->assertCreated()
            ->assertJsonPath('data.valor', 150.5)
            ->assertJsonPath('data.status', 'pendente');
    }

    public function test_rejeita_categoria_de_outro_usuario(): void
    {
        $eu = User::factory()->create();
        $outro = User::factory()->create();
        $categoriaAlheia = Categoria::factory()->create(['usuario_id' => $outro->id]);

        Sanctum::actingAs($eu);

        $this->postJson('/api/transacoes', [
            'descricao' => 'Mercado',
            'tipo' => 'despesa',
            'valor' => 10,
            'data_vencimento' => '2026-10-15',
            'categoria_id' => $categoriaAlheia->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('categoria_id');
    }

    public function test_nao_paga_transacao_de_outro_usuario(): void
    {
        $eu = User::factory()->create();
        $outro = User::factory()->create();
        $transacaoAlheia = Transacao::factory()->create(['usuario_id' => $outro->id]);

        Sanctum::actingAs($eu);

        $this->patchJson("/api/transacoes/{$transacaoAlheia->id}/pagar")
            ->assertNotFound();

        $this->assertEquals('pendente', $transacaoAlheia->fresh()->status);
    }

    public function test_pagar_transacao_propria(): void
    {
        $eu = User::factory()->create();
        $transacao = Transacao::factory()->create(['usuario_id' => $eu->id, 'tipo' => 'despesa']);

        Sanctum::actingAs($eu);

        $this->patchJson("/api/transacoes/{$transacao->id}/pagar")
            ->assertOk()
            ->assertJsonPath('data.status', 'paga');
    }

    public function test_exige_autenticacao(): void
    {
        $this->getJson('/api/transacoes')->assertUnauthorized();
    }
}
