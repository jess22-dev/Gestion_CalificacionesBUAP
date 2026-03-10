<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = [
        'nombre',
        'nrc',
        'profesor_id', // Asegúrate de que este nombre sea el mismo que usamos en la migración
    ];
}
