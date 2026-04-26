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
            
            // 1. Relación con el Alumno (Users con rol alumno)
            $table->foreignId('alumno_id')->constrained('users')->onDelete('cascade');

            // 2. Relación con la Materia usando el NRC (String)
            $table->string('materia_nrc'); 
            $table->foreign('materia_nrc')->references('nrc')->on('materias')->onDelete('cascade');

            // 3. Identificación y Acceso (Requerimientos 1 y 7)
            $table->string('clave_unica')->unique(); // Matrícula BUAP
            $table->string('clave_asistencia')->nullable()->unique(); // Enviada por correo
            $table->string('qr_path')->nullable(); // Ruta de la imagen del QR generado

            // 4. Calificaciones y Progreso (Requerimiento 2 y 4)
            $table->decimal('promedio_real', 5, 2)->default(0); 
            $table->integer('promedio_redondeado')->default(0); 
            
            // 5. Gestión de Bajas (Requerimiento 3 y 4)
            $table->enum('status', ['activo', 'baja'])->default('activo'); 
            $table->timestamp('fecha_baja')->nullable(); // Registro de operación automática
            
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