<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransacaoRequest;
use App\Http\Resources\TransacaoResource;
use App\Models\Transacao;
use App\Services\ParcelamentoService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransacaoController extends Controller
{
    public function __construct(private ParcelamentoService $parcelamento) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'mes' => ['nullable', 'date_format:Y-m'],
            'status' => ['nullable', 'in:pendente,paga,cancelada'],
        ]);

        $inicio = $request->filled('mes')
            ? Carbon::createFromFormat('Y-m', $request->mes)->startOfMonth()
            : now()->startOfMonth();

        $transacoes = Transacao::query()
            ->where('usuario_id', $request->user()->id)
            ->whereBetween('data_vencimento', [$inicio, $inicio->copy()->endOfMonth()])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->with(['categoria', 'caixinha'])
            ->orderBy('data_vencimento')
            ->get();

        return TransacaoResource::collection($transacoes)->response();
    }

    public function store(StoreTransacaoRequest $request): JsonResponse
    {
        $dados = $request->validated();
        $dados['usuario_id'] = $request->user()->id;
        $dados['status'] = 'pendente';
        $dados['qtd_parcelas'] ??= 1;
        $dados['parcela_atual'] = 1;

        $transacao = $dados['qtd_parcelas'] > 1
            ? $this->parcelamento->criarCompraParcelada($dados)
            : Transacao::create($dados);

        return (new TransacaoResource($transacao->load(['categoria', 'caixinha'])))
            ->response()
            ->setStatusCode(201);
    }

    public function pagar(Request $request, Transacao $transacao): JsonResponse
    {
        abort_unless($transacao->usuario_id === $request->user()->id, 404);

        $transacao->marcarComoPaga();

        return (new TransacaoResource($transacao->fresh(['categoria', 'caixinha'])))->response();
    }
}
