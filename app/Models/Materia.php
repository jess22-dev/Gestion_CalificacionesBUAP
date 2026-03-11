<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Materia extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden llenar masivamente.
     * Añadimos 'nrc' (Req 2) y 'profesor_id' para la asignación.
     */
    protected $fillable = [
        'nombre', 
        'codigo', 
        'nrc', 
        'profesor_id'
    ];

    /**
     * RELACIÓN CON EL PROFESOR (DUEÑO DEL GRUPO)
     * Una materia pertenece a un profesor (usuario con rol 'profesor').
     */
    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    /**
     * RELACIÓN CON LOS ALUMNOS (INSCRITOS)
     * Una materia tiene muchos alumnos inscritos.
     */
    public function alumnos()
    {
        // Conecta con la tabla intermedia 'alumno_materia'
        return $this->belongsToMany(User::class, 'alumno_materia', 'materia_id', 'alumno_id')
                    ->withPivot('clave_unica', 'status')
                    ->withTimestamps();
    }
}