<?php

namespace App\Filament\Resources\Caixinhas;

use App\Filament\Resources\Caixinhas\Pages\CreateCaixinha;
use App\Filament\Resources\Caixinhas\Pages\EditCaixinha;
use App\Filament\Resources\Caixinhas\Pages\ListCaixinhas;
use App\Filament\Resources\Caixinhas\Schemas\CaixinhaForm;
use App\Filament\Resources\Caixinhas\Tables\CaixinhasTable;
use App\Models\Caixinha;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CaixinhaResource extends Resource
{
    protected static ?string $model = Caixinha::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;
    protected static string|UnitEnum|null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Caixinha';
    protected static ?string $pluralModelLabel = 'Caixinhas';
    protected static ?string $navigationLabel = 'Caixinhas';

    public static function form(Schema $schema): Schema
    {
        return CaixinhaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CaixinhasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCaixinhas::route('/'),
            'create' => CreateCaixinha::route('/create'),
            'edit' => EditCaixinha::route('/{record}/edit'),
        ];
    }
}