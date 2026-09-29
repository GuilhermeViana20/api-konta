<?php

namespace App\Filament\Resources\Transacoes\Schemas;

use App\Models\Caixinha;
use App\Models\Categoria;
use App\Models\Conta;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TransacoesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('descricao')->required()->maxLength(255)->columnSpanFull(),

                Select::make('tipo')
                    ->options([
                        'receita' => 'Receita',
                        'despesa' => 'Despesa',
                        'transferencia' => 'Transferência',
                    ])
                    ->required()
                    ->live(),

                TextInput::make('valor')
                    ->numeric()
                    ->prefix('R$')
                    ->required(),

                Select::make('categoria_id')
                    ->label('Categoria')
                    ->options(fn () => Categoria::where('usuario_id', auth()->id())->pluck('nome', 'id'))
                    ->searchable(),

                Select::make('caixinha_id')
                    ->label('Caixinha')
                    ->options(fn () => Caixinha::where('usuario_id', auth()->id())->pluck('nome', 'id'))
                    ->searchable(),

                Select::make('forma_pagamento')
                    ->options([
                        'dinheiro' => 'Dinheiro',
                        'pix' => 'Pix',
                        'boleto' => 'Boleto',
                        'cartao_credito' => 'Cartão de crédito',
                    ])
                    ->live(),

                DatePicker::make('data_vencimento')->required(),

                Toggle::make('parcelado')
                    ->label('Compra parcelada?')
                    ->live()
                    ->visible(fn ($get) => $get('forma_pagamento') === 'cartao_credito')
                    ->dehydrated(false),

                Select::make('status')
                    ->label('Situação')
                    ->options([
                        'pendente' => 'Pendente',
                        'paga' => 'Paga',
                        'cancelada' => 'Cancelada',
                    ])
                    ->default('pendente')
                    ->required()
                    ->live(),

                DatePicker::make('data_pagamento')
                    ->label('Data de Pagamento')
                    ->default(now())
                    ->visible(fn (Get $get) => $get('status') === 'paga'),

                TextInput::make('qtd_parcelas')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(48)
                    ->default(1)
                    ->visible(fn ($get) => $get('parcelado') === true),
            ]),
        ]);
    }
}