<?php

namespace App\Filament\Resources\Pagamentos\Pages;

use App\Filament\Resources\Pagamentos\PagamentoResource;
use App\Models\Pagamento;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePagamento extends CreateRecord
{
    protected static string $resource = PagamentoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['usuario_id'] = auth()->id();
        $qtdParcelas = $data['qtd_parcelas'] ?? 1;

        // Cria a 1ª parcela (a "origem")
        $origem = Pagamento::create([
            ...$data,
            'descricao' => $qtdParcelas > 1
                ? "{$data['descricao']} (1/{$qtdParcelas})"
                : $data['descricao'],
            'parcela_atual' => 1,
            'recorrente' => $qtdParcelas > 1,
        ]);

        // Gera as demais parcelas automaticamente
        for ($n = 2; $n <= $qtdParcelas; $n++) {
            Pagamento::create([
                'usuario_id' => $data['usuario_id'],
                'caixinha_id' => $data['caixinha_id'] ?? null,
                'pagamento_origem_id' => $origem->id,
                'descricao' => "{$data['descricao']} ({$n}/{$qtdParcelas})",
                'valor' => $data['valor'],
                'data_vencimento' => now()->parse($data['data_vencimento'])->addMonths($n - 1),
                'recorrente' => true,
                'parcela_atual' => $n,
                'qtd_parcelas' => $qtdParcelas,
            ]);
        }

        return $origem;
    }
}
