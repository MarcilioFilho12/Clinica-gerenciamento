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
        Schema::create('anamneses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadastro_id')->constrained('cadastros')->onDelete('cascade');
            $table->text('motivo_consulta');
            $table->date('ultimo_controle')->nullable();
            $table->text('antecedentes_pessoais')->nullable();
            $table->text('antecedentes_familiares')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anamneses');
    }
};
