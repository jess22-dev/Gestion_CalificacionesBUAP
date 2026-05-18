<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalificacionFinal extends Model
{
    protected $table = 'calificacion_finals' ;
    
    protected $fillable = [
        'materia_nrc',
        'email_alumno',
        'nombre_alumno',
        'actividad_nombre',
        'puntaje',
        'fecha_actividad',
        'participacion',  // Nuevo
        'proyecto',       // Nuevo
        'examen_u1',      // Nuevo
        'examen_u2_u3',   // Nuevo
        'recuperacion_u1' // Nuevo
    ];
}