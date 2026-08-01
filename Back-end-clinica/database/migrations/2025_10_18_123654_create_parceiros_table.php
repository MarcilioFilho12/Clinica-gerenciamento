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
        Schema::create('parceiros', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('tipo_parceiro_id')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('site')->nullable();
            $table->string('responsavel')->nullable();
            $table->string('cep')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('estado')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tipo_parceiro_id')->references('id')->on('tipos_parceiros')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parceiros');
    }
};
