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
        Schema::create('auth_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        DB::table('auth_profiles')->insert([
            ['nome' => 'Administrador', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Recepção', 'created_at' => now(), 'updated_at' => now()],
            ['nome' => 'Profissional', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_profiles');
    }
};
