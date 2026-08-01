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
        Schema::table('parceiros', function (Blueprint $table) {
            $table->unsignedBigInteger('situacao_id')->nullable()->after('tipo_parceiro_id');
            $table->foreign('situacao_id')->references('id')->on('situacoes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parceiros', function (Blueprint $table) {
            $table->dropForeign(['situacao_id']);
            $table->dropColumn('situacao_id');
        });
    }
};
