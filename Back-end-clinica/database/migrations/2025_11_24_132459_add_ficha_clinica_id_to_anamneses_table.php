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
        Schema::table('anamneses', function (Blueprint $table) {
            // Adicionar nova coluna ficha_clinica_id
            $table->foreignId('ficha_clinica_id')->nullable()->after('id')->constrained('cadastros_fichas_clinicas')->onDelete('cascade')->comment('ID da ficha clínica');

            // Remover foreign key de cadastro_id antes de remover a coluna
            $table->dropForeign(['cadastro_id']);

            // Remover coluna cadastro_id (não é mais necessária, acesso via ficha_clinica)
            $table->dropColumn('cadastro_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anamneses', function (Blueprint $table) {
            // Reverter: adicionar cadastro_id de volta
            $table->foreignId('cadastro_id')->after('id')->constrained('cadastros')->onDelete('cascade');

            // Remover foreign key de ficha_clinica_id
            $table->dropForeign(['ficha_clinica_id']);

            // Remover coluna ficha_clinica_id
            $table->dropColumn('ficha_clinica_id');
        });
    }
};
