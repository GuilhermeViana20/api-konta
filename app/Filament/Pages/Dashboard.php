<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProximosPagamentos;
use App\Filament\Widgets\ContasVencendoWidget;
use App\Filament\Widgets\DespesasPorCategoriaChart;
use App\Filament\Widgets\DespesasProxMesChart;
use App\Filament\Widgets\ResumoFinanceiroWidget;
use App\Models\Caixinha;
use App\Models\Transacao;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;
    protected static ?int $navigationSort = -2;
    protected static ?string $navigationLabel = 'Painel de Controle';

    public function filtersForm(Schema $schema): Schema
    {

        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ])
                    ->schema([
                        Select::make('mes')
                            ->label('Período')
                            ->options($this->getMesesDisponiveis())
                            ->default(now()->format('Y-m'))
                            ->native(false)
                            ->live()
                            ->searchable(),

                        Select::make('caixinha_id')
                            ->label('Caixinha')
                            ->options(
                                Caixinha::query()
                                    ->where(
                                        'usuario_id',
                                        auth()->id()
                                    )
                                    ->pluck('nome', 'id')
                                    ->toArray()
                            )
                            ->placeholder('Todas as caixinhas')
                            ->native(false)
                            ->live()
                            ->searchable(),

                        Select::make('status')
                            ->label('Situação')
                            ->options([
                                'todos' => 'Todos',
                                'pagos' => 'Pagos',
                                'pendentes' => 'Pendentes',
                                'vencidos' => 'Vencidos',
                            ])
                            ->default('todos')
                            ->native(false)
                            ->live(),
                    ])
            ]);
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getMesesDisponiveis(): array
    {
        $mesesCadastrados = Transacao::query()
            ->where('usuario_id', auth()->id())
            ->where('tipo', 'despesa')
            ->selectRaw("DISTINCT DATE_FORMAT(data_vencimento, '%Y-%m') as mes")
            ->pluck('mes')
            ->push(now()->format('Y-m'))
            ->unique()
            ->sortDesc();

        $meses = [];

        foreach ($mesesCadastrados as $mes) {
            $data = Carbon::createFromFormat('Y-m', $mes);
            $meses[$mes] = ucfirst($data->translatedFormat('F/Y'));
        }

        return $meses;
    }

    public function getHeaderWidgets(): array
    {
        return [
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ProximosPagamentos::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            DespesasPorCategoriaChart::class,
            DespesasProxMesChart::class,
            ResumoFinanceiroWidget::class,
            ContasVencendoWidget::class,
        ];
    }
}
