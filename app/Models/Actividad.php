<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividads';

    protected $fillable = [
        'nombre',
        'categoria',
        'ponderacion',
        'materia_nrc',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_nrc', 'nrc');
    }

    /**
     * Alumnos (users) vinculados a esta actividad
     */
    public function alumnos()
    {
        return $this->belongsToMany(User::class, 'actividad_user', 'actividad_id', 'alumno_id')
                    ->withPivot('calificacion', 'archivo_path', 'archivo_nombre', 'entregado')
                    ->withTimestamps();
    }

    /**
     * Estudiantes vinculados a esta actividad
     */
    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'actividad_user', 'actividad_id', 'alumno_id')
                    ->withPivot('calificacion', 'archivo_path', 'archivo_nombre', 'entregado')
                    ->withTimestamps();
    }
}