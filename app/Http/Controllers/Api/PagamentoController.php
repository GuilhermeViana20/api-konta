<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PagamentoResource;
use App\Models\Pagamento;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PagamentoController extends Controller
{
    /**
     * Lista os pagamentos do usuário autenticado.
     *
     * Filtros opcionais via query string:
     * - mes
     * - ano
     * - somente_pendentes
     * - somente_pagos
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pagamento::query()
            ->doUsuario($request->user()->id)
            ->with('caixinha')
            ->doMes(
                $request->integer('mes') ?: null,
                $request->integer('ano') ?: null
            );

        if ($request->boolean('somente_pendentes')) {
            $query->pendentes();
        }

        if ($request->boolean('somente_pagos')) {
            $query->pagos();
        }

        return PagamentoResource::collection(
            $query->orderBy('data_vencimento')->get()
        );
    }

    /**
     * Cria um novo pagamento.
     */
    public function store(Request $request): PagamentoResource
    {
        $dados = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0'],
            'data_vencimento' => ['required', 'date'],
            'caixinha_id' => ['nullable', 'exists:caixinhas,id'],
            'recorrente' => ['boolean'],
            'qtd_parcelas' => ['nullable', 'integer', 'min:1'],
        ]);

        $dados['usuario_id'] = $request->user()->id;

        $dados['parcela_atual'] = !empty($dados['qtd_parcelas'])
            ? 1
            : null;

        $pagamento = Pagamento::create($dados);

        return new PagamentoResource($pagamento);
    }

    /**
     * Atualiza um pagamento.
     */
    public function update(
        Request $request,
        Pagamento $pagamento
    ): PagamentoResource {
        $this->autorizarUsuario($request, $pagamento);

        $dados = $request->validate([
            'descricao' => ['sometimes', 'string', 'max:255'],
            'valor' => ['sometimes', 'numeric', 'min:0'],
            'data_vencimento' => ['sometimes', 'date'],
            'caixinha_id' => ['nullable', 'exists:caixinhas,id'],
        ]);

        $pagamento->update($dados);

        return new PagamentoResource($pagamento->fresh('caixinha'));
    }

    /**
     * Marca um pagamento como pago.
     */
    public function marcarPago(
        Request $request,
        Pagamento $pagamento
    ): PagamentoResource {
        $this->autorizarUsuario($request, $pagamento);

        $pagamento->marcarComoPago();

        return new PagamentoResource(
            $pagamento->fresh('caixinha')
        );
    }

    /**
     * Remove um pagamento.
     */
    public function destroy(
        Request $request,
        Pagamento $pagamento
    ): Response {
        $this->autorizarUsuario($request, $pagamento);

        $pagamento->delete();

        return response()->noContent();
    }

    /**
     * Garante que o pagamento pertence ao usuário autenticado.
     */
    private function autorizarUsuario(
        Request $request,
        Pagamento $pagamento
    ): void {
        abort_if(
            $pagamento->usuario_id !== $request->user()->id,
            403
        );
    }
}
