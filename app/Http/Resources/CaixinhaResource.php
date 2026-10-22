<?php

namespace App\Http\Resources;

use App\Models\Caixinha;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Caixinha */
class CaixinhaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'saldo' => (float) $this->saldo,
        ];
    }
}
