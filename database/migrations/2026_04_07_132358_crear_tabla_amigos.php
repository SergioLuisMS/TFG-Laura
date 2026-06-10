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
        Schema::create('amigos', function (Blueprint $table) {
            $table->id();

            // La patata que envía la solicitud
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');

            // La patata que recibe la solicitud
            $table->foreignId('amigo_id')->constrained('users')->onDelete('cascade');

            // El estado: puede ser 'pendiente', 'aceptado' o 'rechazado'
            $table->string('estado')->default('pendiente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amigos');
    }
};
