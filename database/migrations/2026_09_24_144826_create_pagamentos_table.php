<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('caixinha_id')
                ->nullable()
                ->constrained('caixinhas')
                ->nullOnDelete();

            // Autorreferência: aponta para o pagamento de origem
            // que gerou esta parcela ou recorrência.
            $table->foreignId('pagamento_origem_id')
                ->nullable()
                ->constrained('pagamentos')
                ->nullOnDelete();

            $table->string('descricao');

            $table->decimal('valor', 10, 2);

            $table->date('data_vencimento');

            $table->date('data_pagamento')
                ->nullable();

            $table->boolean('recorrente')
                ->default(false);

            $table->unsignedInteger('parcela_atual')
                ->nullable();

            $table->unsignedInteger('qtd_parcelas')
                ->nullable();

            $table->timestamps();

            $table->index([
                'usuario_id',
                'data_vencimento',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagamentos');
    }
};
