<?php

namespace App\Services;

use App\Repositories\TransacaoRepository;
use App\Models\Transacao;
use Illuminate\Support\Facades\DB;

class ParcelamentoService
{
    public function __construct(private TransacaoRepository $repository) {}

    public function criarCompraParcelada(array $dados): Transacao
    {
        $qtdParcelas = (int) $dados['qtd_parcelas'];
        $valorTotal = $this->parseValorMonetario($dados['valor']);

        return DB::transaction(function () use ($dados, $qtdParcelas, $valorTotal) {
            [$valorParcela, $diferenca] = $this->calcularValorParcela($valorTotal, $qtdParcelas);

            $dataVencimento = \Carbon\Carbon::parse($dados['data_vencimento']);

            $origem = $this->repository->criar([
                ...$dados,
                'valor' => $valorParcela + $diferenca,
                'parcela_atual' => 1,
                'qtd_parcelas' => $qtdParcelas,
                'data_vencimento' => $dataVencimento->copy(),
                'transacao_origem_id' => null,
            ]);

            for ($i = 2; $i <= $qtdParcelas; $i++) {
                $this->repository->criar([
                    ...$dados,
                    'valor' => $valorParcela,
                    'parcela_atual' => $i,
                    'qtd_parcelas' => $qtdParcelas,
                    'data_vencimento' => $dataVencimento->copy()->addMonthsNoOverflow($i - 1),
                    'transacao_origem_id' => $origem->id,
                ]);
            }

            return $origem;
        });
    }

    /**
     * Converte valores monetários vindos como string (formato BR "1.234,56"
     * ou já em formato numérico "1234.56"/1234.56) para float de forma segura.
     */
    private function parseValorMonetario(int|float|string $valor): float
    {
        if (! is_string($valor)) {
            return (float) $valor;
        }

        $valor = trim(str_replace(['R$', ' '], '', $valor));

        // Formato BR: "1.234,56" -> remove ponto de milhar, troca vírgula por ponto
        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }

    private function calcularValorParcela(float $valorTotal, int $qtdParcelas): array
    {
        $valorEmCentavos = (int) round($valorTotal * 100);
        $parcelaBaseCentavos = intdiv($valorEmCentavos, $qtdParcelas);
        $diferencaCentavos = $valorEmCentavos - ($parcelaBaseCentavos * $qtdParcelas);

        return [
            $parcelaBaseCentavos / 100,
            $diferencaCentavos / 100,
        ];
    }
}