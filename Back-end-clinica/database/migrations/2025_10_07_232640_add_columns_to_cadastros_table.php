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
        Schema::table('cadastros', function (Blueprint $table) {
            $table->renameColumn('telefone', 'contato');

            $table->enum('sexo', ['M', 'F', 'Outro'])->nullable()->after('data_nascimento');
            $table->string('email')->nullable()->after('contato');
            $table->string('ocupacao')->nullable()->after('email');
            $table->string('rg')->nullable()->after('cpf');
            $table->string('nome_responsavel')->nullable()->after('rg');
            $table->string('cpf_responsavel')->nullable()->after('nome_responsavel');
            $table->text('observacoes')->nullable()->after('cpf_responsavel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cadastros', function (Blueprint $table) {
            $table->dropColumn([
                'sexo',
                'email',
                'ocupacao',
                'rg',
                'nome_responsavel',
                'cpf_responsavel',
                'observacoes'
            ]);

            $table->renameColumn('contato', 'telefone');
        });
    }
};
