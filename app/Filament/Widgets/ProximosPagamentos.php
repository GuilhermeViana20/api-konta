<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\FiltraPagamentos;
use App\Models\Pagamento;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class ProximosPagamentos extends BaseWidget
{
    use InteractsWithPageFilters, FiltraPagamentos;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pagamentos do período')
            ->query($this->pagamentosFiltrados()->orderBy('data_vencimento'))
            ->columns([
                Tables\Columns\TextColumn::make('descricao')->label('Descrição')->searchable(),
                Tables\Columns\TextColumn::make('valor')->label('Valor')->money('BRL')->sortable(),
                Tables\Columns\TextColumn::make('data_vencimento')->label('Vencimento')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('data_pagamento')
                    ->label('Pago em')
                    ->date('d/m/Y')
                    ->placeholder('Em aberto')
                    ->color(fn ($record) => $record->data_pagamento ? 'success' : 'danger'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('Nenhum pagamento no período')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
