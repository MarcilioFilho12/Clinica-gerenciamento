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
        Schema::create('biomicroscopias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadastro_id')->constrained('cadastros')->onDelete('cascade');
            $table->enum('olho', ['OD', 'OE']);
            $table->text('cornea')->nullable();
            $table->text('iris')->nullable();
            $table->text('conjuntiva')->nullable();
            $table->text('cristalino')->nullable();
            $table->text('pupilas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biomicroscopias');
    }
};
