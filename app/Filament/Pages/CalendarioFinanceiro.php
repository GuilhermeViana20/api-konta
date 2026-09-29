<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CalendarioVencimentosWidget;
use App\Filament\Widgets\ContasStatsWidget;
use Filament\Pages\Page;

class CalendarioFinanceiro extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Calendário';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Financeiro';
    
    protected static ?int $navigationSort = 1;

    // Tipagem corrigida para bater exatamente com a classe BasePage
    protected static ?string $title = 'Visão Geral de Vencimentos';

    protected string $view = 'filament.pages.calendario-financeiro';

    protected function getHeaderWidgets(): array
    {
        return [
            ContasStatsWidget::class,
            CalendarioVencimentosWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1; 
    }
}