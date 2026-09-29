<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Transacao;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait FiltraTransacoes
{
    protected function periodoSelecionado(): array
    {
        $mes = $this->pageFilters['mes'] ?? null;

        try {
            $inicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        } catch (\Throwable $e) {
            $inicio = now()->startOfMonth();
        }

        return [$inicio, $inicio->copy()->endOfMonth()];
    }

    protected function transacoesFiltradas(): Builder
    {
        [$inicio, $fim] = $this->periodoSelecionado();

        $caixinhaId = $this->pageFilters['caixinha_id'] ?? null;
        $status = $this->pageFilters['status'] ?? 'todos';

        return Transacao::query()
            ->where('usuario_id', auth()->id())
            ->where('tipo', 'despesa') // dashboard de contas a pagar
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->when($caixinhaId, fn (Builder $q) => $q->where('caixinha_id', $caixinhaId))
            ->when($status === 'pagos', fn (Builder $q) => $q->where('status', 'efetivada'))
            ->when($status === 'pendentes', fn (Builder $q) => $q
                ->where('status', 'pendente')
                ->where('data_vencimento', '>=', now()))
            ->when($status === 'vencidos', fn (Builder $q) => $q
                ->where('status', 'pendente')
                ->where('data_vencimento', '<', now()));
    }
}