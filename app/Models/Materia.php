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
        'nrc', 'clave', 'Materia', 'Profesor', 'profesor_id'
    ];

    public function profesorRelacion()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    // Alumnos del sistema antiguo (users con rol alumno)
    public function alumnos()
    {
        return $this->belongsToMany(User::class, 'alumno_materia', 'materia_nrc', 'alumno_id')
                    ->withTimestamps();
    }

    // Estudiantes del nuevo módulo de alta
    public function estudiantes()
    {
        return $this->belongsToMany(
            Estudiante::class,
            'materia_estudiante',
            'materia_nrc',
            'estudiante_id',
            'nrc',
            'id'
        )->withPivot('profesor_id', 'status')
         ->withTimestamps();
    }

    // Estudiantes activos de esta materia
    public function estudiantesActivos()
    {
        return $this->estudiantes()->wherePivot('status', 'activo');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'materia_nrc', 'nrc');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'materia_nrc', 'nrc');
    }
}