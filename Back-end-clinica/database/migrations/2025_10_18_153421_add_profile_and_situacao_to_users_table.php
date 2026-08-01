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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_id')->nullable()->after('password');
            $table->foreign('profile_id')->references('id')->on('auth_profiles')->onDelete('set null');

            $table->unsignedBigInteger('situacao_id')->nullable()->after('profile_id');
            $table->foreign('situacao_id')->references('id')->on('situacoes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');

            $table->dropForeign(['situacao_id']);
            $table->dropColumn('situacao_id');
        });
    }
};
