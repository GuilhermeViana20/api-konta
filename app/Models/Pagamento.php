<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'caixinha_id',
        'pagamento_origem_id',
        'descricao',
        'valor',
        'data_vencimento',
        'data_pagamento',
        'recorrente',
        'parcela_atual',
        'qtd_parcelas',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'recorrente' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relacionamentos
    |--------------------------------------------------------------------------
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function caixinha(): BelongsTo
    {
        return $this->belongsTo(Caixinha::class);
    }

    /**
     * Pagamento de origem que gerou este pagamento.
     *
     * Exemplo:
     * Pagamento original → parcelas geradas a partir dele.
     */
    public function pagamentoOrigem(): BelongsTo
    {
        return $this->belongsTo(
            Pagamento::class,
            'pagamento_origem_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Local Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra pagamentos de um determinado mês.
     *
     * Quando mês/ano não são informados, utiliza o mês atual.
     */
    public function scopeDoMes(
        Builder $query,
        ?int $mes = null,
        ?int $ano = null
    ): Builder {
        $mes = $mes ?: now()->month;
        $ano = $ano ?: now()->year;

        return $query
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano);
    }

    /**
     * Pagamentos ainda não pagos.
     */
    public function scopePendentes(Builder $query): Builder
    {
        return $query->whereNull('data_pagamento');
    }

    /**
     * Pagamentos já pagos.
     */
    public function scopePagos(Builder $query): Builder
    {
        return $query->whereNotNull('data_pagamento');
    }

    /**
     * Pagamentos vencidos e ainda não pagos.
     */
    public function scopeAtrasadas(Builder $query): Builder
    {
        return $query
            ->whereNull('data_pagamento')
            ->whereDate(
                'data_vencimento',
                '<',
                now()->toDateString()
            );
    }

    /**
     * Pagamentos recorrentes.
     */
    public function scopeRecorrentes(Builder $query): Builder
    {
        return $query->where('recorrente', true);
    }

    /**
     * Filtra pagamentos de um usuário específico.
     */
    public function scopeDoUsuario(
        Builder $query,
        int $usuarioId
    ): Builder {
        return $query->where('usuario_id', $usuarioId);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Retorna o status atual do pagamento.
     *
     * Possíveis valores:
     * - pago
     * - atrasado
     * - pendente
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->data_pagamento) {
                    return 'pago';
                }

                if ($this->data_vencimento->isPast()) {
                    return 'atrasado';
                }

                return 'pendente';
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Regras de negócio
    |--------------------------------------------------------------------------
    */

    /**
     * Marca o pagamento como pago.
     *
     * Se estiver vinculado a uma caixinha,
     * debita o valor do saldo acumulado dela.
     */
    public function marcarComoPago(?Carbon $data = null): void
    {
        $this->data_pagamento = $data ?? now();

        $this->save();

        if ($this->caixinha) {
            $this->caixinha->sacar(
                (float) $this->valor
            );
        }
    }

    protected static function booted(): void
    {
        static::creating(function ($pagamento) {
            $pagamento->usuario_id ??= auth()->id();
        });
    }
}
