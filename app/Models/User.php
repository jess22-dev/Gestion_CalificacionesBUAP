<?php

namespace App\Models;

use App\Models\Notificacion;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden llenar masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
    ];

    /**
     * Atributos ocultos para la serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de atributos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- RELACIONES PARA EL SISTEMA BUAP ---

    /**
     * Relación para el PROFESOR:
     * Un usuario (profesor) tiene muchas materias asignadas.
     */
    public function grupos()
    {
        // Se asume que en la tabla 'materias' hay una columna 'profesor_id'
        return $this->hasMany(Materia::class, 'profesor_id');
    }





    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class)->latest();
    }








    /**
     * Relación para el ALUMNO:
     * Un usuario (alumno) pertenece a muchas materias a través de la tabla pivote.
     * Cambiamos el nombre a 'materias' para que coincida con el AlumnoController.
     */
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
            'promedio_real',
            'promedio_redondeado',
            'status',
            'qr_path',
            'fecha_baja'
        )->withTimestamps();
    }
}