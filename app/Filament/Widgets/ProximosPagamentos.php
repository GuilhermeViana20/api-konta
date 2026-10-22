<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\FiltraTransacoes;
use App\Models\Transacao;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class ProximosPagamentos extends BaseWidget
{
    use FiltraTransacoes, InteractsWithPageFilters;

    // ProximosPagamentos.php
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Contas do período')
            ->query($this->transacoesFiltradas()->orderBy('data_vencimento'))
            ->columns([
                Tables\Columns\TextColumn::make('descricao')->label('Descrição')->searchable(),
                Tables\Columns\TextColumn::make('valor')->label('Valor')->money('BRL')->sortable(),
                Tables\Columns\TextColumn::make('data_vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('data_pagamento')
                    ->label('Pago em')
                    ->date('d/m/Y')
                    ->placeholder('Em aberto')
                    ->color(fn (Transacao $record) => $record->status === 'paga' ? 'success' : 'danger'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('Nenhuma conta no período')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
