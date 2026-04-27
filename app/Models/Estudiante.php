<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiantes';

    protected $fillable = [
        'nombre',
        'email',
        'codigo_estudiante',
        'clave_unica',
    ];

    /**
     * Generar clave única alfanumérica de 10 dígitos
     * Solo si el estudiante no tiene una ya
     */
    public static function generarClaveUnica(): string
    {
        do {
            // Genera clave alfanumérica de 10 caracteres en mayúsculas
            $clave = strtoupper(Str::random(10));
        } while (self::where('clave_unica', $clave)->exists());

        return $clave;
    }

    /**
     * Asignar clave única si es la primera vez que se registra
     */
    public function asignarClaveUnicaSiNecesario(): void
    {
        if (empty($this->clave_unica)) {
            $this->clave_unica = self::generarClaveUnica();
            $this->save();
        }
    }

    /**
     * Materias en las que está inscrito
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
     * Verificar si ya está en otra materia
     */
    public function estaEnOtraMateria(string $nrcActual): bool
    {
        return $this->materias()
                    ->where('materia_nrc', '!=', $nrcActual)
                    ->exists();
    }

    /**
     * Verificar si ya está en esta materia
     */
    public function estaEnMateria(string $nrc): bool
    {
        return $this->materias()
                    ->where('materia_nrc', $nrc)
                    ->exists();
    }
}