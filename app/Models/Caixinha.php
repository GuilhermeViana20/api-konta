<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caixinha extends Model
{
    use HasFactory;

    protected $fillable = ['usuario_id', 'nome', 'saldo'];

    protected function casts(): array
    {
        return ['saldo' => 'decimal:2'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function transacoes(): HasMany
    {
        return $this->hasMany(Transacao::class, 'caixinha_id');
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoMovimentacao::class, 'caixinha_id');
    }

    public function sacar(float $valor, ?string $descricao = null): void
    {
        $this->decrement('saldo', $valor);

        $this->historicos()->create([
            'tipo' => 'saida',
            'valor' => $valor,
            'descricao' => $descricao,
        ]);
    }

    public function depositar(float $valor, ?string $descricao = null): void
    {
        $this->increment('saldo', $valor);

        $this->historicos()->create([
            'tipo' => 'entrada',
            'valor' => $valor,
            'descricao' => $descricao,
        ]);
    }
}