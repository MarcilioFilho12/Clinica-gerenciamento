<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('situacoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        DB::table('situacoes')->insert([
            ['nome' => 'ativo', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'inativo', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'suspenso', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'encerrado', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'cancelado', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'em_atendimento', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('situacoes');
    }
};
