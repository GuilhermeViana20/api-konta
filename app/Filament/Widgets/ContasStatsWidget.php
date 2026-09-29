<?php

namespace App\Filament\Widgets;

use App\Models\Transacao;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;
use Carbon\Carbon;

class ContasStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    public ?string $dataInicio = null;
    public ?string $dataFim = null;
    public int $recarregar = 0;

    #[On('atualizarPeriodoStats')]
    public function atualizarPeriodo($inicio, $fim): void
    {
        $this->dataInicio = Carbon::parse($inicio)->format('Y-m-d');
        $this->dataFim = Carbon::parse($fim)->format('Y-m-d');
    }

    #[On('atualizarEstatisticas')]
    public function atualizarEstatisticas(): void
    {
        $this->recarregar++;
    }

    protected function getStats(): array
    {
        $inicio = $this->dataInicio ?? now()->startOfMonth()->format('Y-m-d');
        $fim = $this->dataFim ?? now()->endOfMonth()->format('Y-m-d');

        // Busca transações do usuário logado no período (ignorando as canceladas)
        $query = Transacao::where('usuario_id', auth()->id())
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->where('status', '!=', 'cancelada');

        // Faz os cálculos
        $receitas = (clone $query)->where('tipo', 'receita')->sum('valor');
        $despesas = (clone $query)->where('tipo', 'despesa')->sum('valor');
        
        $saldo = $receitas - $despesas;

        return [
            Stat::make('Receitas Previstas', 'R$ ' . number_format($receitas, 2, ',', '.'))
                ->color('success')
                ->icon('heroicon-o-arrow-trending-up'),

            Stat::make('Despesas Previstas', 'R$ ' . number_format($despesas, 2, ',', '.'))
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),

            Stat::make('Saldo do Período', 'R$ ' . number_format($saldo, 2, ',', '.'))
                ->description(Carbon::parse($inicio)->format('d/m/Y') . ' até ' . Carbon::parse($fim)->format('d/m/Y'))
                ->color($saldo >= 0 ? 'success' : 'danger') // Fica verde se sobrou dinheiro, vermelho se faltou
                ->icon('heroicon-o-banknotes'),
        ];
    }
}