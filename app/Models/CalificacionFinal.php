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
        'codigo_estudiante',
        'sin_matricula',
        'actividad_nombre',
        'puntaje',
        'fecha_actividad',
        'participacion',
        'proyecto',
        'examen_u1',
        'examen_u2_u3',
        'recuperacion_u1',
    ];
}