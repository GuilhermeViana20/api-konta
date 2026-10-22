<?php

namespace App\Http\Resources;

use App\Models\Caixinha;
use App\Models\Categoria;
use App\Models\Transacao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Transacao */
class TransacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'descricao' => $this->descricao,
            'tipo' => $this->tipo,
            'valor' => (float) $this->valor,
            'status' => $this->status,
            'forma_pagamento' => $this->forma_pagamento,
            'data_vencimento' => $this->data_vencimento
                ? Carbon::parse($this->data_vencimento)->toDateString()
                : null,
            'data_pagamento' => $this->data_pagamento
                ? Carbon::parse($this->data_pagamento)->toDateString()
                : null,
            'parcela_atual' => $this->parcela_atual,
            'qtd_parcelas' => $this->qtd_parcelas,
            'categoria' => $this->whenLoaded('categoria', fn () => $this->formatarCategoria()),
            'caixinha' => $this->whenLoaded('caixinha', fn () => $this->formatarCaixinha()),
        ];
    }

    private function formatarCategoria(): ?array
    {
        /** @var Categoria|null $categoria */
        $categoria = $this->categoria;

        return $categoria === null ? null : [
            'id' => $categoria->id,
            'nome' => $categoria->nome,
            'cor' => $categoria->cor,
        ];
    }

    private function formatarCaixinha(): ?array
    {
        /** @var Caixinha|null $caixinha */
        $caixinha = $this->caixinha;

        return $caixinha === null ? null : [
            'id' => $caixinha->id,
            'nome' => $caixinha->nome,
        ];
    }
}
