<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Materia extends Model
{
    use HasFactory;

    // 1. Configuramos el NRC como llave primaria (como en tu migración)
    protected $primaryKey = 'nrc';
    public $incrementing = false; // Ya que el NRC no es autoincremental
    protected $keyType = 'string';

    // 2. Definimos los campos que se pueden llenar (Exactamente como tu migración)
    protected $fillable = [
        'nrc',        // Columna A
        'clave',      // Columna B
        'Materia',    // Columna C (con Mayúscula)
        'Profesor',   // Columna D (con Mayúscula)
        'profesor_id' // El ID del usuario que se crea/busca
    ];

    /**
     * RELACIÓN CON EL PROFESOR (Modelo User)
     * Usamos 'profesor_id' porque así lo nombraste en la migración
     */
    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    /**
     * RELACIÓN CON LOS ALUMNOS
     * Por si la necesitas más adelante para inscripciones
     */
    public function alumnos()
    {
        return $this->belongsToMany(User::class, 'alumno_materia', 'materia_nrc', 'alumno_id')
                    ->withTimestamps();
    }
    public function profesorRelacion()
    {
        // Esto le dice a Laravel: "Busca en la tabla de Usuarios al que tenga el ID que yo tengo en profesor_id"
        return $this->belongsTo(User::class, 'profesor_id');
    }
}