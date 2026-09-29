<?php

namespace App\Filament\Resources\Transacoes;

use App\Filament\Resources\Transacoes\Schemas\TransacoesForm;
use App\Filament\Resources\Transacoes\Pages\CreateTransacoes;
use App\Filament\Resources\Transacoes\Pages\EditTransacoes;
use App\Filament\Resources\Transacoes\Pages\ListTransacoes;
use App\Filament\Resources\Transacoes\Tables\TransacoesTable;
use App\Models\Transacao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TransacoesResource extends Resource
{
    protected static ?string $model = Transacao::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;
    protected static string|UnitEnum|null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Transação';
    protected static ?string $pluralModelLabel = 'Transações';
    protected static ?string $navigationLabel = 'Transações';

    public static function form(Schema $schema): Schema
    {
        return TransacoesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransacoesTable::configure($table);
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
            'index' => ListTransacoes::route('/'),
            'create' => CreateTransacoes::route('/create'),
            'edit' => EditTransacoes::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $atrasadas = static::getModel()::query()
            ->where('tipo', 'despesa')
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', today())
            ->count();

        return $atrasadas > 0 ? (string) $atrasadas : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Contas atrasadas';
    }
}