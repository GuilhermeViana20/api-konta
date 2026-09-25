<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\FiltraPagamentos;
use App\Models\Caixinha;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancasOverview extends BaseWidget
{
    use InteractsWithPageFilters, FiltraPagamentos;

    protected function getStats(): array
    {
        $caixinhaId = $this->pageFilters['caixinha_id'] ?? null;

        $saldoTotal = Caixinha::query()
            ->where('usuario_id', auth()->id())
            ->when($caixinhaId, fn ($q) => $q->where('id', $caixinhaId))
            ->sum('saldo');

        $totalPago = (clone $this->pagamentosFiltrados())->whereNotNull('data_pagamento')->sum('valor');

        $totalPendente = (clone $this->pagamentosFiltrados())
            ->whereNull('data_pagamento')
            ->sum('valor');

        $totalVencido = (clone $this->pagamentosFiltrados())
            ->whereNull('data_pagamento')
            ->where('data_vencimento', '<', now())
            ->sum('valor');

        return [
            Stat::make('Saldo nas caixinhas', $this->money($saldoTotal))
                ->color('success'),

            Stat::make('Total pago', $this->money($totalPago))
                ->description('No período selecionado')
                ->color('success'),

            Stat::make('A pagar', $this->money($totalPendente))
                ->color($totalPendente > 0 ? 'warning' : 'success'),

            Stat::make('Vencido', $this->money($totalVencido))
                ->description($totalVencido > 0 ? 'Contas atrasadas' : 'Tudo em dia')
                ->color($totalVencido > 0 ? 'danger' : 'success'),
        ];
    }

    protected function money(float|int $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}
