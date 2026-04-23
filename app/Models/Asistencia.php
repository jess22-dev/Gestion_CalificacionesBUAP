<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    public function up()
{
    Schema::create('asistencias', function (Blueprint $table) {
        $table->id();
        $table->date('fecha');
        $table->enum('estatus', ['P', 'F', 'R', 'J']); // Presente, Falta, Retardo, Justificado
        $table->unsignedBigInteger('alumno_id'); // El ID del usuario alumno
        $table->string('materia_nrc');
        
        $table->foreign('alumno_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('materia_nrc')->references('nrc')->on('materias')->onDelete('cascade');
        $table->timestamps();
    });
}
}
