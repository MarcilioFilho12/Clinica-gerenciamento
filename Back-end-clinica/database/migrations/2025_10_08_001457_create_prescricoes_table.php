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
        Schema::create('prescricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadastro_id')->constrained('cadastros')->onDelete('cascade');
            $table->string('material')->nullable();
            $table->string('tipo_lente')->nullable();
            $table->string('filtro')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('conduta')->nullable();
            $table->date('proximo_controle')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescricoes');
    }
};
