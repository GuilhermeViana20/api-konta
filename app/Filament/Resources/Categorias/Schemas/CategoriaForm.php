<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('usuario_id')
                    ->relationship('usuario', 'name')
                    ->required(),
                TextInput::make('nome')
                    ->required(),
                ColorPicker::make('cor')
                    ->required()
                    ->default('#6366f1'),
                Select::make('tipo')
                    ->options(['receita' => 'Receita', 'despesa' => 'Despesa'])
                    ->default('despesa')
                    ->required(),
            ]);
    }
}
