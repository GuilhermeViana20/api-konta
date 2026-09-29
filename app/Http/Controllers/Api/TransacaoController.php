<?php

namespace App\Http\Controllers;

use App\Models\Transacao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransacaoController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validação básica dos dados recebidos do formulário
        $validated = $request->validate([
            'descricao'       => 'required|string|max:255',
            'valor_total'     => 'required|numeric|min:0.01',
            'data_vencimento' => 'required|date',
            'qtd_parcelas'    => 'nullable|integer|min:1',
            'forma_pagamento' => 'required|string',
            'tipo'            => 'required|in:receita,despesa', // Seguindo a sua estrutura
        ]);

        $qtdParcelas = $request->input('qtd_parcelas', 1);
        $valorTotal = $request->input('valor_total');
        
        // Usamos o Carbon para poder manipular as datas facilmente
        $dataVencimentoBase = Carbon::parse($request->input('data_vencimento'));

        // 2. Inicia a transação no banco de dados
        DB::beginTransaction();

        try {
            if ($qtdParcelas > 1) {
                // Calcula o valor de cada parcela
                $valorParcela = round($valorTotal / $qtdParcelas, 2);
                $transacaoOrigem = null;

                for ($i = 1; $i <= $qtdParcelas; $i++) {
                    // addMonthsNoOverflow evita pular de mês. Ex: 31/01 + 1 mês = 28/02 (e não 02/03)
                    $vencimentoAtual = (clone $dataVencimentoBase)->addMonthsNoOverflow($i - 1);

                    $transacao = Transacao::create([
                        'usuario_id'          => auth()->id(),
                        'transacao_origem_id' => $transacaoOrigem ? $transacaoOrigem->id : null,
                        'tipo'                => $validated['tipo'],
                        'descricao'           => $validated['descricao'] . " ($i/$qtdParcelas)",
                        'valor'               => $valorParcela,
                        'data_vencimento'     => $vencimentoAtual,
                        'status'              => 'pendente',
                        'forma_pagamento'     => $validated['forma_pagamento'],
                        'parcela_atual'       => $i,
                        'qtd_parcelas'        => $qtdParcelas,
                        'recorrente'          => false,
                    ]);

                    // No primeiro loop, salvamos a transação para ser a "origem" das próximas
                    if ($i === 1) {
                        $transacaoOrigem = $transacao;
                    }
                }
            } else {
                // Cenário de compra à vista / parcela única
                Transacao::create([
                    'usuario_id'      => auth()->id(),
                    'tipo'            => $validated['tipo'],
                    'descricao'       => $validated['descricao'],
                    'valor'           => $valorTotal,
                    'data_vencimento' => $dataVencimentoBase,
                    'status'          => 'pendente',
                    'forma_pagamento' => $validated['forma_pagamento'],
                    'parcela_atual'   => null,
                    'qtd_parcelas'    => null,
                    'recorrente'      => false,
                ]);
            }

            // 3. Tudo deu certo? Confirma as inserções no banco
            DB::commit();

            return redirect()->back()->with('success', 'Transação cadastrada com sucesso!');

        } catch (\Exception $e) {
            // Se algo der erro (ex: banco de dados fora do ar na parcela 2), ele desfaz a parcela 1
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao salvar transação: ' . $e->getMessage());
        }
    }
}