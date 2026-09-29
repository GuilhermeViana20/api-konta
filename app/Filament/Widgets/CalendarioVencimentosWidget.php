<?php

namespace App\Filament\Widgets;

use App\Models\Transacao;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;

class CalendarioVencimentosWidget extends FullCalendarWidget
{
    // 1. Mudamos para o model correto
    public Model|string|null $model = Transacao::class;

    public function fetchEvents(array $fetchInfo): array
    {
        // Dispara evento pro StatsWidget
        $this->dispatch('atualizarPeriodoStats', 
            inicio: $fetchInfo['start'], 
            fim: $fetchInfo['end']
        );

        return Transacao::query()
            ->where('usuario_id', auth()->id()) // Filtra apenas transações do usuário logado!
            ->whereBetween('data_vencimento', [$fetchInfo['start'], $fetchInfo['end']])
            ->get()
            ->map(function (Transacao $transacao) {
                // Define a cor baseada no tipo e status
                $cor = match($transacao->tipo) {
                    'receita' => '#10b981', // Verde
                    'despesa' => '#ef4444', // Vermelho
                    'transferencia' => '#3b82f6', // Azul
                    default => '#6b7280',
                };

                // Se não estiver paga, deixa amarelo pra chamar atenção de que tá pendente
                if ($transacao->status === 'pendente') {
                    $cor = '#f59e0b'; 
                }

                return [
                    'id' => $transacao->id,
                    'title' => $transacao->descricao . ' (R$ ' . number_format($transacao->valor, 2, ',', '.') . ')',
                    'start' => $transacao->data_vencimento->format('Y-m-d'),
                    'backgroundColor' => $cor,
                    'borderColor' => 'transparent',
                ];
            })
            ->all();
    }

    public function getFormSchema(): array
    {
        return [
            Select::make('tipo')
                ->label('Tipo de Transação')
                ->options([
                    'receita' => 'Receita',
                    'despesa' => 'Despesa',
                    'transferencia' => 'Transferência',
                ])
                ->required(),

            TextInput::make('descricao')
                ->label('Descrição')
                ->required(),
                
            TextInput::make('valor')
                ->label('Valor')
                ->numeric()
                ->prefix('R$')
                ->required(),
                
            DatePicker::make('data_vencimento')
                ->label('Data de Vencimento')
                ->required(),
                
            Select::make('status')
                ->label('Status')
                ->options([
                    'pendente' => 'Pendente',
                    'paga' => 'Paga', // Corrigido para bater com a migration
                    'cancelada' => 'Cancelada',
                ])
                ->default('pendente')
                ->required(),
        ];
    }

    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->form($this->getFormSchema()) 
                
                // Remova o tipo "?Form" e deixe apenas "$form"
                ->mountUsing(function ($form, array $arguments) {
                    $form->fill([
                        'data_vencimento' => $arguments['start'] ?? null,
                    ]);
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['usuario_id'] = auth()->id();
                    return $data;
                })
                ->after(function () {
                    $this->dispatch('filament-fullcalendar--refresh'); 
                    $this->dispatch('atualizarEstatisticas');
                })
        ];
    }

    protected function modalActions(): array
    {
        return [
            EditAction::make()
                ->form($this->getFormSchema())
                ->after(function () {
                    // E substitua aqui:
                    $this->dispatch('filament-fullcalendar--refresh');
                    $this->dispatch('atualizarEstatisticas');
                }),
                
            DeleteAction::make()
                ->after(function () {
                    // E aqui:
                    $this->dispatch('filament-fullcalendar--refresh');
                    $this->dispatch('atualizarEstatisticas');
                }),
        ];
    }

    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        $transacao = Transacao::find($event['id']);

        if ($transacao) {
            $transacao->update([
                'data_vencimento' => \Carbon\Carbon::parse($event['start'])->format('Y-m-d'),
            ]);
            
            // 1. Avisa o Javascript do calendário para recarregar visualmente
            $this->dispatch('filament-fullcalendar--refresh');
            
            // 2. Avisa os cards de estatísticas
            $this->dispatch('atualizarEstatisticas');
            
            return true; 
        }

        return false; 
    }
}