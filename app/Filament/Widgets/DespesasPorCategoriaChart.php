<?php

namespace App\Filament\Widgets;

use App\Models\Transacao;
use Filament\Widgets\ChartWidget;

class DespesasPorCategoriaChart extends ChartWidget
{
    protected ?string $heading = 'Despesas por categoria (mês atual)';

    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $dados = Transacao::query()
            ->with('categoria')
            ->selectRaw('categoria_id, SUM(valor) as total')
            ->where('tipo', 'despesa')
            ->where('status', '!=', 'cancelada')
            ->whereBetween('data_vencimento', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->groupBy('categoria_id')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Despesas',
                    'data' => $dados->map(fn ($r) => round((float) $r->total, 2))->all(),
                    'backgroundColor' => $dados->map(fn ($r) => $r->categoria?->cor ?? '#9ca3af')->all(),
                ],
            ],
            'labels' => $dados->map(fn ($r) => $r->categoria?->nome ?? 'Sem categoria')->all(),
        ];
    }
}