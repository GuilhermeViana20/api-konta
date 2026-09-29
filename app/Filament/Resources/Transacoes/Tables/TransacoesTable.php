<?php

namespace App\Filament\Resources\Transacoes\Tables;

use App\Models\Transacao;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransacoesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('descricao')->searchable(),
                TextColumn::make('categoria.nome')
                    ->badge()
                    ->color(fn ($record) => $record->categoria?->cor
                        ? Color::hex($record->categoria->cor)
                        : 'gray'),
                TextColumn::make('valor')->money('BRL')->sortable(),
                TextColumn::make('data_vencimento')->date('d/m/Y')->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paga' => 'success',
                        'pendente' => 'warning',
                        'cancelada' => 'danger',
                    }),
                TextColumn::make('parcela_atual')
                    ->label('Parcela')
                    ->formatStateUsing(fn ($record) => $record->qtd_parcelas > 1
                        ? "{$record->parcela_atual}/{$record->qtd_parcelas}"
                        : '—'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'paga' => 'Paga',
                    'pendente' => 'Pendente',
                    'cancelada' => 'Cancelada',
                ]),
                SelectFilter::make('tipo')->options([
                    'receita' => 'Receita',
                    'despesa' => 'Despesa',
                    'transferencia' => 'Transferência',
                ]),
                Filter::make('mes_vencimento')
                    ->label('Período')
                    ->schema([
                        Select::make('mes')
                            ->label('Mês')
                            ->native(false)
                            ->options(self::opcoesDeMeses()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (blank($data['mes'] ?? null)) {
                            return $query;
                        }

                        [$ano, $mes] = explode('-', $data['mes']);

                        return $query
                            ->whereYear('data_vencimento', $ano)
                            ->whereMonth('data_vencimento', $mes);
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (blank($data['mes'] ?? null)) {
                            return null;
                        }

                        return 'Período: '.\Carbon\Carbon::createFromFormat('Y-m', $data['mes'])
                            ->translatedFormat('F/Y');
                    }),
            ])
            ->defaultSort('data_vencimento', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('pagar')
                    ->label('Pagar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pendente')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Pagamento')
                    ->modalDescription('Deseja marcar esta transação como paga hoje?')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'paga',
                            'data_pagamento' => now(),
                        ]);
                    }),
                ActionGroup::make([
                    // Duplicar transação
                    Action::make('duplicar')
                        ->label('Duplicar')
                        ->icon('heroicon-o-document-duplicate')
                        ->modalHeading('Duplicar transação')
                        ->modalDescription('Cria uma cópia pendente. Escolha o vencimento da nova conta.')
                        ->fillForm(fn (Transacao $record): array => [
                            'data_vencimento' => $record->data_vencimento->copy()->addMonthNoOverflow()->toDateString(),
                        ])
                        ->schema([
                            DatePicker::make('data_vencimento')
                                ->label('Novo vencimento')
                                ->native(false)
                                ->required(),
                        ])
                        ->action(function (Transacao $record, array $data) {
                            $nova = $record->replicate(['data_pagamento', 'transacao_origem_id']);
                            $nova->status = 'pendente';
                            $nova->parcela_atual = 1;
                            $nova->qtd_parcelas = 1;
                            $nova->data_vencimento = $data['data_vencimento'];
                            $nova->save();

                            Notification::make()
                                ->title('Transação duplicada')
                                ->success()
                                ->send();
                        }),

                    // Cancelar/excluir esta parcela e as próximas
                    Action::make('cancelar_parcelamento')
                        ->label('Cancelar parcelas restantes')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Transacao $record) => $record->qtd_parcelas > 1 && $record->status === 'pendente')
                        ->modalHeading('Cancelar parcelas restantes')
                        ->modalDescription('Afeta esta parcela e todas as próximas que ainda estão pendentes. Parcelas já pagas não são alteradas.')
                        ->schema([
                            Radio::make('acao')
                                ->label('O que fazer com elas?')
                                ->options([
                                    'cancelar' => 'Marcar como canceladas (mantém o histórico)',
                                    'excluir' => 'Excluir definitivamente',
                                ])
                                ->default('cancelar')
                                ->required(),
                        ])
                        ->action(function (Transacao $record, array $data) {
                            $origemId = $record->transacao_origem_id ?? $record->id;

                            $query = Transacao::query()
                                ->where(fn ($q) => $q
                                    ->where('id', $origemId)
                                    ->orWhere('transacao_origem_id', $origemId))
                                ->where('status', 'pendente')
                                ->where('parcela_atual', '>=', $record->parcela_atual);

                            $total = $query->count();

                            $data['acao'] === 'excluir'
                                ? $query->delete()
                                : $query->update(['status' => 'cancelada']);

                            Notification::make()
                                ->title("{$total} parcela(s) ".($data['acao'] === 'excluir' ? 'excluída(s)' : 'cancelada(s)'))
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Gera as opções de mês/ano a partir do intervalo real de datas
     * cadastradas na tabela transacoes (evita listar meses sem nenhum dado).
     */
    private static function opcoesDeMeses(): array
    {
        $primeira = Transacao::min('data_vencimento');
        $ultima = Transacao::max('data_vencimento');

        if (blank($primeira) || blank($ultima)) {
            return [];
        }

        $inicio = \Carbon\Carbon::parse($primeira)->startOfMonth();
        $fim = \Carbon\Carbon::parse($ultima)->startOfMonth();

        $opcoes = [];

        while ($fim->greaterThanOrEqualTo($inicio)) {
            $opcoes[$fim->format('Y-m')] = ucfirst($fim->translatedFormat('F/Y'));
            $fim->subMonth();
        }

        return $opcoes;
    }
}