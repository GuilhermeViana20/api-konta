<?php

namespace App\Console\Commands;

use App\Models\Pagamento;
use Illuminate\Console\Command;

class GerarContasRecorrentes extends Command
{
    protected $signature = 'contas:gerar-recorrentes';

    protected $description = 'Gera as contas do próximo mês a partir dos pagamentos marcadas como recorrentes';

    public function handle(): void
    {
        $proximoMes = now()->addMonthNoOverflow();

        // Pega os pagamentos recorrentes com vencimento no mês atual (o "template" do mês corrente)
        $templates = Pagamento::recorrentes()
            ->doMes()
            ->get();

        $geradas = 0;

        foreach ($templates as $template) {
            // Evita duplicar caso o comando rode mais de uma vez no período
            $jaExiste = Pagamento::where('pagamento_origem_id', $template->pagamento_origem_id ?? $template->id)
                ->doMes($proximoMes->month, $proximoMes->year)
                ->exists();

            if ($jaExiste) {
                continue;
            }

            // Se for parcelado (qtd_parcelas definido), para de gerar ao atingir o total
            if ($template->qtd_parcelas && $template->parcela_atual >= $template->qtd_parcelas) {
                continue;
            }

            Pagamento::create([
                'usuario_id' => $template->usuario_id,
                'caixinha_id' => $template->caixinha_id,
                'pagamento_origem_id' => $template->pagamento_origem_id ?? $template->id,
                'descricao' => $template->descricao,
                'valor' => $template->valor,
                'data_vencimento' => $template->data_vencimento->copy()->addMonthNoOverflow(),
                'data_pagamento' => null,
                'recorrente' => $template->recorrente,
                'parcela_atual' => $template->parcela_atual ? $template->parcela_atual + 1 : null,
                'qtd_parcelas' => $template->qtd_parcelas,
            ]);

            $geradas++;
        }

        $this->info("Contas de {$proximoMes->format('m/Y')} geradas: {$geradas} de {$templates->count()} templates processados.");
    }
}
