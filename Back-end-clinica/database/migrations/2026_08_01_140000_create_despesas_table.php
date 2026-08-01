<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data');
            $table->string('categoria')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('data');
            $table->index('categoria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};
