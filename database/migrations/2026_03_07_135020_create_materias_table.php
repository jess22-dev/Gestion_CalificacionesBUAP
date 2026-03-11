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
        Schema::create('materias', function (Blueprint $table) {
        // El NRC es el identificador único de la clase/grupo
        $table->id(); 
        $table->string('nombre_materia'); // Ej: Modelos de Desarrollo Web
        $table->string('grupo');          // Ej: ITI-101 (Lo que pidió el profe)
        $table->string('seccion');        // Ej: 001
        $table->string('dias');           // Ej: Lunes, Miércoles
        $table->string('horario');        // Ej: 10:00 - 12:00
        
        // Relación con el profesor (Usuario)
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};