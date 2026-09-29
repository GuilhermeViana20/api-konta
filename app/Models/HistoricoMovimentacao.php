<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoMovimentacao extends Model
{
    protected $table = 'historicos_movimentacoes';

    protected $fillable = ['caixinha_id', 'transacao_id', 'tipo', 'valor', 'descricao'];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2'];
    }

    public function caixinha(): BelongsTo
    {
        return $this->belongsTo(Caixinha::class, 'caixinha_id');
    }

    public function transacao(): BelongsTo
    {
        return $this->belongsTo(Transacao::class, 'transacao_id');
    }
}