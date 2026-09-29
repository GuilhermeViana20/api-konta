<?php

namespace App\Filament\Resources\Transacoes\Pages;

use App\Filament\Resources\Transacoes\TransacoesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTransacoes extends ListRecords
{
    protected static string $resource = TransacoesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
