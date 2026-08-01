<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescricoes', function (Blueprint $table) {
            $table->text('encaminhamento')->nullable()->after('conduta');
        });
    }

    public function down(): void
    {
        Schema::table('prescricoes', function (Blueprint $table) {
            $table->dropColumn('encaminhamento');
        });
    }
};
