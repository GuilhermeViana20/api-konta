<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinancasOverview;
use App\Filament\Widgets\ProximosPagamentos;
use App\Models\Caixinha;
use App\Models\Pagamento;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
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
                    ->columns(3),
            ]);
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 5;
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 2,
        ];
    }

    protected function getMesesDisponiveis(): array
    {
        $mesesCadastrados = Pagamento::query()
            ->where('usuario_id', auth()->id())
            ->selectRaw(
                "DISTINCT DATE_FORMAT(data_vencimento, '%Y-%m') as mes"
            )
            ->pluck('mes')
            ->push(now()->format('Y-m'))
            ->unique()
            ->sortDesc();

        $meses = [];

        foreach ($mesesCadastrados as $mes) {
            $data = Carbon::createFromFormat('Y-m', $mes);

            $meses[$mes] = ucfirst(
                $data->translatedFormat('F/Y')
            );
        }

        return $meses;
    }

    public function getHeaderWidgets(): array
    {
        return [
            FinancasOverview::class,
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
        return [];
    }
}
