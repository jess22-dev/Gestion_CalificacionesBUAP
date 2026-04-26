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

    /**
     * Relación con la Materia
     */
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_nrc', 'nrc');
    }

    /**
     * Relación con los alumnos a través de actividad_user
     */
    public function alumnos()
    {
        return $this->belongsToMany(User::class, 'actividad_user', 'actividad_id', 'alumno_id')
                    ->withPivot('calificacion')
                    ->withTimestamps();
    }
}