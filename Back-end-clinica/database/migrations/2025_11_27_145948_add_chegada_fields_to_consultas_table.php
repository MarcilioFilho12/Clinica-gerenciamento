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
        Schema::table('consultas', function (Blueprint $table) {
            $table->timestamp('chegada_em')->nullable()->after('situacao_id');
            $table->string('codigo_chegada', 50)->nullable()->after('chegada_em');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropColumn(['chegada_em', 'codigo_chegada']);
        });
    }
};
