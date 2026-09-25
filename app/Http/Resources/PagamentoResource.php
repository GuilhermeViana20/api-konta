<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PagamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'valor' => (float) $this->valor,
            'data_vencimento' => $this->data_vencimento->format('Y-m-d'),
            'data_pagamento' => $this->data_pagamento?->format('Y-m-d'),
            'status' => $this->status, // pendente | paga | atrasada
            'recorrente' => $this->recorrente,
            'parcela_atual' => $this->parcela_atual,
            'qtd_parcelas' => $this->qtd_parcelas,
            'caixinha' => new CaixinhaResource($this->whenLoaded('caixinha')),
        ];
    }
}
