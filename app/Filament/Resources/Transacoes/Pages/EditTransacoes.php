<?php

namespace App\Filament\Resources\Transacoes\Pages;

use App\Filament\Resources\Transacoes\TransacoesResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransacoes extends EditRecord
{
    protected static string $resource = TransacoesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
