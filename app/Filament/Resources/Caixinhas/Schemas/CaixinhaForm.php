<?php

namespace App\Filament\Resources\Caixinhas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CaixinhaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('saldo')
                    ->numeric()
                    ->prefix('R$')
                    ->default(0)
                    ->required(),
            ]);
    }
}
