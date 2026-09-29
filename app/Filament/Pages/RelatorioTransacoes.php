<?php

namespace App\Filament\Pages;

use App\Models\Categoria;
use App\Models\Transacao;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class RelatorioTransacoes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;
    protected static string|UnitEnum|null $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Relatórios';

    protected static ?string $title = 'Relatório de Transações';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'data_inicio' => now()->startOfMonth()->toDateString(),
            'data_fim' => now()->endOfMonth()->toDateString(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('data_inicio')
                    ->label('Data início')
                    ->native(false),
                DatePicker::make('data_fim')
                    ->label('Data fim')
                    ->native(false)
                    ->afterOrEqual('data_inicio'),
                Select::make('categoria_id')
                    ->label('Categorias')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn() => Categoria::query()->orderBy('nome')->pluck('nome', 'id')),
                Select::make('tipo')
                    ->label('Tipo')
                    ->multiple()
                    ->options([
                        'receita' => 'Receita',
                        'despesa' => 'Despesa',
                        'transferencia' => 'Transferência',
                    ]),
                Select::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options([
                        'paga' => 'Paga',
                        'pendente' => 'Pendente',
                        'cancelada' => 'Cancelada',
                    ]),
            ])
            ->statePath('data')
            ->columns(2);
    }

    public function gerarPdf()
    {
        $filtros = $this->form->getState();

        $transacoes = $this->buscarTransacoes($filtros);

        $pdf = Pdf::loadView('pdf.relatorio-transacoes', [
            'transacoes' => $transacoes,
            'filtros' => $filtros,
            'totalReceitas' => $transacoes->where('tipo', 'receita')->sum('valor'),
            'totalDespesas' => $transacoes->where('tipo', 'despesa')->sum('valor'),
            'geradoEm' => now(),
        ]);

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'relatorio-transacoes-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    private function buscarTransacoes(array $filtros)
    {
        return Transacao::query()
            ->with('categoria')
            ->when($filtros['data_inicio'] ?? null, fn($q, $data) => $q->whereDate('data_vencimento', '>=', $data))
            ->when($filtros['data_fim'] ?? null, fn($q, $data) => $q->whereDate('data_vencimento', '<=', $data))
            ->when($filtros['categoria_id'] ?? null, fn($q, $ids) => $q->whereIn('categoria_id', $ids))
            ->when($filtros['tipo'] ?? null, fn($q, $tipos) => $q->whereIn('tipo', $tipos))
            ->when($filtros['status'] ?? null, fn($q, $status) => $q->whereIn('status', $status))
            ->orderBy('data_vencimento')
            ->get();
    }
}
