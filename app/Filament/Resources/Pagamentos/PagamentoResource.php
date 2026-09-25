<?php

namespace App\Filament\Resources\Pagamentos;

use App\Filament\Resources\Pagamentos\Pages\CreatePagamento;
use App\Filament\Resources\Pagamentos\Pages\EditPagamento;
use App\Filament\Resources\Pagamentos\Pages\ListPagamentos;
use App\Filament\Resources\Pagamentos\Schemas\PagamentoForm;
use App\Filament\Resources\Pagamentos\Tables\PagamentosTable;
use App\Models\Pagamento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PagamentoResource extends Resource
{
    protected static ?string $model = Pagamento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Pagamento';

    public static function form(Schema $schema): Schema
    {
        return PagamentoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PagamentosTable::configure($table);
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
            'index' => ListPagamentos::route('/'),
            'create' => CreatePagamento::route('/create'),
            'edit' => EditPagamento::route('/{record}/edit'),
        ];
    }
}
