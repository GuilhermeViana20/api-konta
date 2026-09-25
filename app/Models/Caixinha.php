<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caixinha extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'nome',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function depositar(float $valor): void
    {
        $this->increment('saldo', $valor);
    }

    public function sacar(float $valor): void
    {
        $this->decrement('saldo', $valor);
    }

    protected static function booted(): void
    {
        static::creating(function ($caixinha) {
            $caixinha->usuario_id ??= auth()->id();
        });
    }
}
