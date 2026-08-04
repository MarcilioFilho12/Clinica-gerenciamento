<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cadastro mínimo: apenas nome + CPF obrigatórios.
     */
    public function up(): void
    {
        Schema::table('cadastros', function (Blueprint $table) {
            $table->date('data_nascimento')->nullable()->change();
            $table->string('contato')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cadastros', function (Blueprint $table) {
            $table->date('data_nascimento')->nullable(false)->change();
            $table->string('contato')->nullable(false)->change();
        });
    }
};
