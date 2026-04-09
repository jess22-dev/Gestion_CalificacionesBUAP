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
            
            // 1. Relación con el Alumno (Sigue siendo ID numérico de la tabla users)
            $table->foreignId('alumno_id')->constrained('users')->onDelete('cascade');

            // 2. Relación con la Materia usando el NRC (String)
            // IMPORTANTE: Debe ser string porque el NRC en 'materias' es string
            $table->string('materia_nrc'); 
            $table->foreign('materia_nrc')->references('nrc')->on('materias')->onDelete('cascade');

            // 3. Datos del alumno en esta materia específica
            $table->string('clave_unica')->unique(); // Requerimiento 7 (Matrícula BUAP)
            $table->decimal('promedio_real', 5, 2)->default(0); // Requerimiento 4
            $table->integer('promedio_redondeado')->default(0); // Requerimiento 4
            
            // 4. Estado del alumno
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