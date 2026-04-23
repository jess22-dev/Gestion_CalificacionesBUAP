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
        Schema::create('actividads', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Examen Parcial 1"
            $table->string('categoria'); // Ej: "Exámenes", "Tareas", "Proyectos"
            $table->integer('ponderacion'); // El porcentaje que vale (Ej: 20)
            
            // Relación con Materia (Debe ser string porque tu NRC es string)
            $table->string('materia_nrc'); 
            
            // Configuración de la llave foránea
            $table->foreign('materia_nrc')
                  ->references('nrc')
                  ->on('materias')
                  ->onDelete('cascade'); // Si borras la materia, se borran sus actividades

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividads');
    }
};