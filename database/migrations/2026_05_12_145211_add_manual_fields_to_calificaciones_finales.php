<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('calificacion_finals', function (Blueprint $table) {
            $table->id();
            $table->string('materia_nrc');
            $table->string('email_alumno');
            $table->string('nombre_alumno');
            $table->string('actividad_nombre'); 
            // Todos estos deben ser nullable() para que el autoguardado no falle
            $table->decimal('participacion', 5, 2)->nullable();
            $table->decimal('proyecto', 5, 2)->nullable();
            $table->decimal('examen_u1', 5, 2)->nullable();
            $table->decimal('examen_u2_u3', 5, 2)->nullable();
            $table->decimal('recuperacion_u1', 5, 2)->nullable();
            $table->decimal('puntaje', 5, 2)->nullable(); 
            $table->string('fecha_actividad')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('calificacion_finals'); {
            //
        };
    }
};
