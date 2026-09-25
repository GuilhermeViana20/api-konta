<?php

namespace App\Filament\Resources\Pagamentos\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class PagamentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('descricao')->searchable(),
                TextColumn::make('valor')->money('BRL')->sortable(),
                TextColumn::make('data_vencimento')->date('d/m/Y')->sortable(),
                TextColumn::make('data_pagamento')
                    ->date('d/m/Y')
                    ->placeholder('Em aberto')
                    ->color(fn ($record) => $record->data_pagamento ? 'success' : 'danger'),
                IconColumn::make('recorrente')->boolean(),
            ])
            ->defaultSort('data_vencimento', 'asc')
            ->filters([
                Filter::make('pendentes')
                    ->query(fn ($query) => $query->whereNull('data_pagamento')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
