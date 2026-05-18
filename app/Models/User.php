<?php

namespace App\Models;

use App\Models\Notificacion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Profesor → muchas materias
    public function grupos()
    {
        return $this->hasMany(Materia::class, 'profesor_id');
    }

    // Alumno → muchas materias via alumno_materia
    public function materias()
    {
        return $this->belongsToMany(
            Materia::class,
            'alumno_materia',
            'alumno_id',
            'materia_nrc',
            'id',
            'nrc'
        )->withPivot(
            'clave_unica',
            'clave_asistencia',
            'status',
            'qr_path',
            'fecha_baja',
            'promedio_real',
            'promedio_redondeado'
        )->withTimestamps();
    }

    // Notificaciones del usuario
    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class)->latest();
    }
}
