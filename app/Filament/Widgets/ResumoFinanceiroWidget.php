<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\FiltraTransacoes;
use App\Models\Transacao;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class ResumoFinanceiroWidget extends StatsOverviewWidget
{
    use FiltraTransacoes;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $inicio = now()->startOfMonth()->toDateString();
        $fim = now()->endOfMonth()->toDateString();

        $base = fn () => Transacao::query()
            ->whereBetween('data_vencimento', [$inicio, $fim])
            ->where('status', '!=', 'cancelada');
        
        $totalVencido = (clone $this->transacoesFiltradas())
            ->where('status', 'pendente')
            ->where('data_vencimento', '<', now())
            ->sum('valor');

        $receitas = (float) $base()->where('tipo', 'receita')->sum('valor');
        $despesas = (float) $base()->where('tipo', 'despesa')->sum('valor');
        $aPagar = (float) $base()->where('tipo', 'despesa')->where('status', 'pendente')->sum('valor');
        $pago = (float) $base()->where('tipo', 'despesa')->where('status', 'paga')->sum('valor');
        $saldo = $receitas - $despesas;
        $percentualPago = $despesas > 0 ? round(($pago / $despesas) * 100) : 0;

        return [
            Stat::make('Receitas do mês', $this->brl($receitas))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Despesas do mês', $this->brl($despesas))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Saldo do mês', $this->brl($saldo))
                ->description($saldo >= 0 ? 'Positivo' : 'Negativo')
                ->descriptionIcon($saldo >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($saldo >= 0 ? 'success' : 'danger'),
            Stat::make('A pagar', $this->brl($aPagar))
                ->description('Despesas pendentes no mês')
                ->color('warning'),
            Stat::make('Já pago', $this->brl($pago))
                ->description("{$percentualPago}% das despesas do mês")
                ->color('success'),
            Stat::make('Vencido', $this->brl($totalVencido))
                ->description($totalVencido > 0 ? 'Contas atrasadas' : 'Tudo em dia')
                ->color($totalVencido > 0 ? 'danger' : 'success'),
        ];
    }

    private function brl(float $valor): string
    {
        return 'R$ '.number_format($valor, 2, ',', '.');
    }
}