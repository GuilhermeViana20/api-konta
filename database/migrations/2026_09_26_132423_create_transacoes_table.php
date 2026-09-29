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
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('caixinha_id')->nullable()->constrained('caixinhas')->nullOnDelete();
            $table->foreignId('transacao_origem_id')->nullable()->constrained('transacoes')->cascadeOnDelete();

            $table->enum('tipo', ['receita', 'despesa', 'transferencia']);
            $table->string('descricao');
            $table->decimal('valor', 12, 2);
            $table->enum('forma_pagamento', ['dinheiro', 'pix', 'boleto', 'cartao_credito'])->nullable();
            $table->enum('status', ['pendente', 'paga', 'cancelada'])->default('pendente');

            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();

            $table->boolean('recorrente')->default(false);
            $table->unsignedSmallInteger('parcela_atual')->default(1);
            $table->unsignedSmallInteger('qtd_parcelas')->default(1);

            $table->timestamps();

            // índices para os relatórios mensais e busca de parcelas
            $table->index(['usuario_id', 'data_vencimento', 'status'], 'idx_transacoes_relatorio_mensal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes');
    }
};
