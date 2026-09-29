<?php

namespace App\Filament\Widgets;

use App\Models\Transacao;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ContasVencendoWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Contas atrasadas e a vencer (próximos 7 dias)')
            ->query(fn (): Builder => Transacao::query()
                ->with('categoria')
                ->where('tipo', 'despesa')
                ->where('status', 'pendente')
                ->whereDate('data_vencimento', '<=', today()->addDays(7))
                ->orderBy('data_vencimento'))
            ->columns([
                TextColumn::make('descricao'),
                TextColumn::make('categoria.nome')->label('Categoria')->badge(),
                TextColumn::make('valor')->money('BRL'),
                TextColumn::make('data_vencimento')
                    ->label('Vencimento')
                    ->date('d/m/Y')
                    ->color(fn (Transacao $record) => $record->data_vencimento->lt(today()) ? 'danger' : null),
                TextColumn::make('situacao')
                    ->label('Situação')
                    ->badge()
                    ->state(function (Transacao $record): string {
                        $dias = (int) today()->diffInDays($record->data_vencimento, false);

                        return match (true) {
                            $dias < 0 => 'Atrasada há '.abs($dias).' dia(s)',
                            $dias === 0 => 'Vence hoje',
                            default => "Vence em {$dias} dia(s)",
                        };
                    })
                    ->color(function (Transacao $record): string {
                        $dias = (int) today()->diffInDays($record->data_vencimento, false);

                        return match (true) {
                            $dias < 0 => 'danger',
                            $dias === 0 => 'warning',
                            default => 'info',
                        };
                    }),
            ])
            ->recordActions([
                Action::make('pagar')
                    ->label('Pagar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Transacao $record) => $record->update([
                        'status' => 'paga',
                        'data_pagamento' => now(),
                    ])),
            ])
            ->paginated([5, 10])
            ->emptyStateHeading('Nenhuma conta atrasada ou a vencer 🎉');
    }
}