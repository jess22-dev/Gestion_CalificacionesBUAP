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
    Schema::create('alumno_materia', function (Blueprint $table) {
    $table->id();
    $table->foreignId('alumno_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
    $table->string('clave_unica')->unique(); // Requerimiento 7
    $table->decimal('promedio_real', 5, 2)->default(0); // Requerimiento 4
    $table->integer('promedio_redondeado')->default(0); // Requerimiento 4
    $table->enum('status', ['activo', 'baja'])->default('activo'); // Requerimiento 8
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno_materia');
    }
};
