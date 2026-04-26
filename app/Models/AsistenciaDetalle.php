<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaDetalle extends Model
{
    protected $fillable = [
        'asistencia_id',
        'alumno_id',
        'estatus'
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
