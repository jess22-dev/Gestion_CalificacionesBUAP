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
        Schema::create('grupos', function (Blueprint $table) {
        $table->id();
        $table->string('nombre'); // Ejemplo: Bases de Datos
        $table->string('nrc');    // El código de la materia en la BUAP
        $table->foreignId('profesor_id')->constrained()->onDelete('cascade'); // El profesor asignado
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
