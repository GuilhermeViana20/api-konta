<?php

namespace App\Console\Commands;

use App\Models\Transacao;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GerarContasRecorrentes extends Command
{
    protected $signature = 'contas:gerar-recorrentes';

    protected $description = 'Gera as transações do próximo mês a partir das despesas marcadas como recorrentes';

    public function handle(): void
    {
        $proximoMes = now()->addMonthNoOverflow();

        $templates = Transacao::recorrentes()->doMes()->get();

        $geradas = 0;

        foreach ($templates as $template) {
            $origemId = $template->transacao_origem_id ?? $template->id;
            $vencimento = Carbon::parse($template->data_vencimento)->addMonthNoOverflow();

            $jaExiste = Transacao::where(function ($q) use ($origemId) {
                $q->where('transacao_origem_id', $origemId)->orWhere('id', $origemId);
            })
                ->doMes($proximoMes->month, $proximoMes->year)
                ->exists();

            if ($jaExiste) {
                continue;
            }

            if ($template->qtd_parcelas > 1 && $template->parcela_atual >= $template->qtd_parcelas) {
                continue;
            }

            Transacao::create([
                'usuario_id' => $template->usuario_id,
                'categoria_id' => $template->categoria_id,
                'caixinha_id' => $template->caixinha_id,
                'transacao_origem_id' => $origemId,
                'tipo' => $template->tipo,
                'descricao' => $template->descricao,
                'valor' => $template->valor,
                'forma_pagamento' => $template->forma_pagamento,
                'status' => 'pendente',
                'data_vencimento' => $vencimento,
                'data_pagamento' => null,
                'recorrente' => $template->recorrente,
                'parcela_atual' => $template->qtd_parcelas > 1 ? $template->parcela_atual + 1 : 1,
                'qtd_parcelas' => $template->qtd_parcelas,
            ]);

            $geradas++;
        }

        $this->info("Transações de {$proximoMes->format('m/Y')} geradas: {$geradas} de {$templates->count()} templates processados.");
    }
}
