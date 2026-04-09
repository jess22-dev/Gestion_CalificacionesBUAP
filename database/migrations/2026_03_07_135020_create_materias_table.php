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
        $table->string('nrc')->primary(); // Columna A
        $table->string('clave');         // Columna B (La clave de la materia)
        $table->string('Materia');         // Columna C 
        $table->string('Profesor');         //COLUMNA D

        
        // Relación con el profesor (Columna F / fila[5])
        $table->foreignId('profesor_id')->constrained('users')->onDelete('cascade');
        
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