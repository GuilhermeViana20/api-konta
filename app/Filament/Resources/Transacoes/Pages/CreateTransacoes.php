<?php

namespace App\Filament\Resources\Transacoes\Pages;

use App\Filament\Resources\Transacoes\TransacoesResource;
use App\Models\Transacao;
use App\Services\ParcelamentoService;
use Filament\Resources\Pages\CreateRecord;

class CreateTransacoes extends CreateRecord
{
    protected static string $resource = TransacoesResource::class;

    protected function handleRecordCreation(array $data): Transacao
    {
        $data['usuario_id'] = auth()->id();

        if (($data['qtd_parcelas'] ?? 1) > 1) {
            return app(ParcelamentoService::class)->criarCompraParcelada($data);
        }

        return Transacao::create($data);
    }
}