<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .meta { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f4f4f4; }
        .valor-receita { color: #16a34a; }
        .valor-despesa { color: #dc2626; }
        .resumo { margin-top: 20px; font-size: 13px; }
        .resumo strong { display: inline-block; width: 140px; }
    </style>
</head>
<body>
    <h1>Relatório de Transações</h1>
    <div class="meta">
        Gerado em {{ $geradoEm->format('d/m/Y H:i') }}
        @if($filtros['data_inicio'] ?? null)
            — Período: {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }}
            até {{ \Carbon\Carbon::parse($filtros['data_fim'] ?? now())->format('d/m/Y') }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Tipo</th>
                <th>Vencimento</th>
                <th>Status</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transacoes as $transacao)
                <tr>
                    <td>{{ $transacao->descricao }}</td>
                    <td>{{ $transacao->categoria?->nome ?? '—' }}</td>
                    <td>{{ ucfirst($transacao->tipo) }}</td>
                    <td>{{ $transacao->data_vencimento->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($transacao->status) }}</td>
                    <td class="{{ $transacao->tipo === 'receita' ? 'valor-receita' : 'valor-despesa' }}">
                        R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">Nenhuma transação encontrada para os filtros selecionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="resumo">
        <div><strong>Total receitas:</strong> R$ {{ number_format($totalReceitas, 2, ',', '.') }}</div>
        <div><strong>Total despesas:</strong> R$ {{ number_format($totalDespesas, 2, ',', '.') }}</div>
        <div><strong>Saldo:</strong> R$ {{ number_format($totalReceitas - $totalDespesas, 2, ',', '.') }}</div>
    </div>
</body>
</html>