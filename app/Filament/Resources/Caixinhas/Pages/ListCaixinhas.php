<?php

namespace App\Filament\Resources\Caixinhas\Pages;

use App\Filament\Resources\Caixinhas\CaixinhaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCaixinhas extends ListRecords
{
    protected static string $resource = CaixinhaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
