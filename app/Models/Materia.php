<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Materia extends Model
{
    use HasFactory;

    protected $primaryKey = 'nrc';
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'nrc',
        'clave',
        'Materia',
        'Profesor',
        'profesor_id'
    ];

    // Relación con el Profesor
    public function profesorRelacion()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    // Relación con Alumnos (Muchos a Muchos)
    public function alumnos()
    {
        return $this->belongsToMany(User::class, 'alumno_materia', 'materia_nrc', 'alumno_id')
                    ->withTimestamps();
    }

    /**
     * NUEVAS RELACIONES PARA HOY
     */

    // Relación con las Actividades (Una materia tiene muchas actividades)
    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'materia_nrc', 'nrc');
    }

    // Relación con las Asistencias (Una materia tiene muchos registros de asistencia)
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'materia_nrc', 'nrc');
    }
}