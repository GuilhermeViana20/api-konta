<?php 

namespace App\Repositories;

use App\Models\Transacao;
use Illuminate\Support\Collection;

class TransacaoRepository
{
    public function buscarPorPeriodo(int $usuarioId, string $inicio, string $fim): Collection
    {
        return Transacao::with(['categoria:id,nome,cor', 'conta:id,nome'])
            ->where('usuario_id', $usuarioId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->orderBy('data_vencimento')
            ->get();
    }

    public function totaisPorTipoEStatus(int $usuarioId, string $inicio, string $fim): Collection
    {
        return Transacao::where('usuario_id', $usuarioId)
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->selectRaw('tipo, status, SUM(valor) as total')
            ->groupBy('tipo', 'status')
            ->get();
    }

    public function buscarParcelas(int $transacaoOrigemId): Collection
    {
        return Transacao::where('transacao_origem_id', $transacaoOrigemId)
            ->orWhere('id', $transacaoOrigemId)
            ->orderBy('parcela_atual')
            ->get();
    }

    public function criar(array $dados): Transacao
    {
        return Transacao::create($dados);
    }
}