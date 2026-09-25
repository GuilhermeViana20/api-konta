<?php

namespace App\Filament\Resources\Caixinhas\Pages;

use App\Filament\Resources\Caixinhas\CaixinhaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCaixinha extends EditRecord
{
    protected static string $resource = CaixinhaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
