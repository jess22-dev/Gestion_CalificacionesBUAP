<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    public function up()
{
    Schema::create('actividads', function (Blueprint $table) {
        $table->id();
        $table->string('nombre'); // Ej: "Examen Parcial 1"
        $table->string('categoria'); // Ej: "Exámenes", "Tareas"
        $table->integer('ponderacion'); // Ej: 20 (para 20%)
        $table->string('materia_nrc'); // Relación con tu tabla Materias
        
        // Clave foránea que apunta al NRC de tu tabla materias
        $table->foreign('materia_nrc')->references('nrc')->on('materias')->onDelete('cascade');
        $table->timestamps();
    });
}
}
