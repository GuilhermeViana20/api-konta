<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait FiltraPagamentos
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

    protected function pagamentosFiltrados(): Builder
    {
        [$inicio, $fim] = $this->periodoSelecionado();

        $caixinhaId = $this->pageFilters['caixinha_id'] ?? null;
        $status = $this->pageFilters['status'] ?? 'todos';

        return Pagamento::query()
            ->where('usuario_id', auth()->id())
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->when($caixinhaId, fn (Builder $q) => $q->where('caixinha_id', $caixinhaId))
            ->when($status === 'pagos', fn (Builder $q) => $q->whereNotNull('data_pagamento'))
            ->when($status === 'pendentes', fn (Builder $q) => $q
                ->whereNull('data_pagamento')
                ->where('data_vencimento', '>=', now()))
            ->when($status === 'vencidos', fn (Builder $q) => $q
                ->whereNull('data_pagamento')
                ->where('data_vencimento', '<', now()));
    }
}
