<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->boolean('pago')->default(false)->after('observacoes');
            $table->string('forma_pagamento', 50)->nullable()->after('pago');
            $table->decimal('valor', 10, 2)->nullable()->after('forma_pagamento');
        });
    }

    public function down(): void
    {
        Schema::table('consultas', function (Blueprint $table) {
            $table->dropColumn(['pago', 'forma_pagamento', 'valor']);
        });
    }
};
