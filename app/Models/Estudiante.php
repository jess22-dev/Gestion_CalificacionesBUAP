<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiantes';

    protected $fillable = [
        'nombre',
        'email',
        'codigo_estudiante',
    ];

    /**
     * Materias en las que está inscrito este estudiante
     */
    public function materias()
    {
        return $this->belongsToMany(
            Materia::class,
            'materia_estudiante',
            'estudiante_id',
            'materia_nrc',
            'id',
            'nrc'
        )->withPivot('profesor_id', 'status')
         ->withTimestamps();
    }

    /**
     * Verificar si el estudiante ya está en otra materia
     */
    public function estaEnOtraMateria(string $nrcActual): bool
    {
        return $this->materias()
                    ->where('materia_nrc', '!=', $nrcActual)
                    ->exists();
    }

    /**
     * Verificar si ya está inscrito en esta materia
     */
    public function estaEnMateria(string $nrc): bool
    {
        return $this->materias()
                    ->where('materia_nrc', $nrc)
                    ->exists();
    }
}