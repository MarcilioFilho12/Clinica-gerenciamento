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
        Schema::create('tipos_parceiros', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        DB::table('tipos_parceiros')->insert([
            ['nome' => 'Convênio', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Laboratório', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Fornecedor', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Indicação Médica', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Empresa Conveniada', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_parceiros');
    }
};
