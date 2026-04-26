<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $fillable = [
        'materia_nrc',
        'inicia_en',
        'termina_en',
        'activa'
    ];

    public function detalles()
    {
        return $this->hasMany(AsistenciaDetalle::class);
    }
}