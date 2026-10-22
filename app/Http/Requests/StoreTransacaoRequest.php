<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuarioId = $this->user()->id;

        return [
            'descricao' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['receita', 'despesa', 'transferencia'])],
            'valor' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'data_vencimento' => ['required', 'date_format:Y-m-d'],
            'forma_pagamento' => ['nullable', Rule::in(['dinheiro', 'pix', 'boleto', 'cartao_credito'])],
            'categoria_id' => [
                'nullable',
                Rule::exists('categorias', 'id')->where('usuario_id', $usuarioId),
            ],
            'caixinha_id' => [
                'nullable',
                Rule::exists('caixinhas', 'id')->where('usuario_id', $usuarioId),
            ],
            'qtd_parcelas' => ['nullable', 'integer', 'min:1', 'max:48'],
        ];
    }
}
