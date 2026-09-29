<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transacao extends Model
{
    protected $table = 'transacoes';

    use HasFactory;

    protected $fillable = [
        'usuario_id', 'conta_id', 'categoria_id', 'caixinha_id', 'transacao_origem_id',
        'tipo', 'descricao', 'valor', 'forma_pagamento', 'status',
        'data_vencimento', 'data_pagamento', 'recorrente', 'parcela_atual', 'qtd_parcelas',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_vencimento' => 'date',
            'data_pagamento' => 'date',
            'recorrente' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function conta(): BelongsTo
    {
        return $this->belongsTo(Conta::class, 'conta_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function caixinha(): BelongsTo
    {
        return $this->belongsTo(Caixinha::class, 'caixinha_id');
    }

    public function transacaoOrigem(): BelongsTo
    {
        return $this->belongsTo(Transacao::class, 'transacao_origem_id');
    }

    public function parcelas(): HasMany
    {
        return $this->hasMany(Transacao::class, 'transacao_origem_id');
    }

    public function scopeDoMes(Builder $query, ?int $mes = null, ?int $ano = null): Builder
    {
        $mes = $mes ?: now()->month;
        $ano = $ano ?: now()->year;

        return $query
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano);
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', 'pendente');
    }

    public function scopePagas(Builder $query): Builder
    {
        return $query->where('status', 'efetivada');
    }

    public function scopeAtrasadas(Builder $query): Builder
    {
        return $query
            ->where('status', 'pendente')
            ->whereDate('data_vencimento', '<', now()->toDateString());
    }

    public function scopeRecorrentes(Builder $query): Builder
    {
        return $query->where('recorrente', true);
    }

    public function scopeDoUsuario(Builder $query, int $usuarioId): Builder
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function marcarComoPaga(?Carbon $data = null): void
    {
        $this->data_pagamento = $data ?? now();
        $this->status = 'efetivada';
        $this->save();

        if ($this->caixinha) {
            match ($this->tipo) {
                'despesa' => $this->caixinha->sacar((float) $this->valor),
                'receita' => $this->caixinha->depositar((float) $this->valor),
                default => null,
            };
        }
    }

    protected static function booted(): void
    {
        static::creating(function (Transacao $transacao) {
            $transacao->usuario_id ??= auth()->id();
        });
    }
}