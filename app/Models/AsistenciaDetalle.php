<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaDetalle extends Model
{
    protected $table = 'asistencia_detalles';

    protected $fillable = [
        'asistencia_id',
        'alumno_id',
        'clave_unica',
        'asistio',
        'hora_registro',
    ];

    protected $casts = [
        'asistio'       => 'boolean',
        'hora_registro' => 'datetime',
    ];

    public function asistencia()
    {
        return $this->belongsTo(Asistencia::class);
    }

    public function alumno()
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }
}