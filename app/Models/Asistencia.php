<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $fillable = [
        'materia_nrc',
        'inicia_en',
        'termina_en',
        'activa',
    ];

    protected $casts = [
        'inicia_en'  => 'datetime',
        'termina_en' => 'datetime',
        'activa'     => 'boolean',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_nrc', 'nrc');
    }

    public function detalles()
    {
        return $this->hasMany(AsistenciaDetalle::class);
    }
}