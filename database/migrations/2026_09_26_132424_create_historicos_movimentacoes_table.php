<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historicos_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caixinha_id')->constrained('caixinhas')->cascadeOnDelete();
            $table->foreignId('transacao_id')->nullable()->constrained('transacoes')->nullOnDelete();
            $table->enum('tipo', ['entrada', 'saida']);
            $table->decimal('valor', 12, 2);
            $table->string('descricao')->nullable();
            $table->timestamps();

            $table->index('caixinha_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historicos_movimentacoes');
    }
};
