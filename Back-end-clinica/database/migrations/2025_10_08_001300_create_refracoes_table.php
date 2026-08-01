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
        Schema::create('refracoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadastro_id')->constrained('cadastros')->onDelete('cascade');
            $table->enum('tipo', ['autorrefacao', 'subjetiva']);
            $table->enum('olho', ['OD', 'OE']);
            $table->string('esf')->nullable()->comment('Esférico');
            $table->string('cil')->nullable()->comment('Cilíndrico');
            $table->string('eixo')->nullable();
            $table->string('add')->nullable()->comment('Adição');
            $table->string('av')->nullable()->comment('Acuidade Visual');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refracoes');
    }
};
