<?php

namespace App\Filament\Resources\Pagamentos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PagamentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('descricao')
                    ->required()
                    ->maxLength(255),
                TextInput::make('valor')
                    ->numeric()
                    ->prefix('R$')
                    ->required()
                    ->helperText(fn ($get) => $get('parcelado')
                        ? 'Valor de cada parcela'
                        : null),
                Toggle::make('parcelado')
                    ->live()
                    ->dehydrated(false),
                DatePicker::make('data_vencimento')
                    ->label(fn ($get) => $get('parcelado')
                        ? 'Vencimento da 1ª parcela'
                        : 'Data de vencimento')
                    ->required(),
                DatePicker::make('data_pagamento')
                    ->label('Data de pagamento (deixe vazio se em aberto)'),
                TextInput::make('qtd_parcelas')
                    ->label('Quantas parcelas')
                    ->numeric()
                    ->minValue(2)
                    ->required()
                    ->visible(fn ($get) => $get('parcelado')),
            ]);
    }
}
