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
        Schema::create('acuidades_visuais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadastro_id')->constrained('cadastros')->onDelete('cascade');
            $table->enum('olho', ['OD', 'OE']);
            $table->string('vl')->nullable()->comment('Visão de Longe');
            $table->string('vp')->nullable()->comment('Visão de Perto');
            $table->string('ph')->nullable()->comment('Pinhole');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acuidades_visuais');
    }
};
