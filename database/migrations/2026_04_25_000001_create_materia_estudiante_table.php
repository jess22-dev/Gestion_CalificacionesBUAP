<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materia_estudiante', function (Blueprint $table) {
            $table->id();

            // FK a estudiantes
            $table->foreignId('estudiante_id')
                  ->constrained('estudiantes')
                  ->onDelete('cascade');

            // FK a materias (NRC es string)
            $table->string('materia_nrc');
            $table->foreign('materia_nrc')
                  ->references('nrc')
                  ->on('materias')
                  ->onDelete('cascade');

            // FK al profesor que lo dio de alta
            $table->foreignId('profesor_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Estado del alumno en la materia
            $table->enum('status', ['activo', 'baja'])->default('activo');

            // Evitar duplicados
            $table->unique(['estudiante_id', 'materia_nrc']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia_estudiante');
    }
};
