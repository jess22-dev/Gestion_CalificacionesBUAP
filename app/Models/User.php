<?php

namespace App\Models;

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
     * Añadimos 'role' para que el Seeder y el Registro funcionen.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // <-- IMPORTANTE: Añade esto para evitar errores de asignación
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
     * Un usuario (profesor) tiene muchos grupos asignados.
     */
    public function grupos()
    {
        // Esto asume que en tu tabla 'grupos' hay una columna 'profesor_id'
        return $this->hasMany(Grupo::class, 'profesor_id');
    }

    /**
     * Relación para el ALUMNO:
     * Un usuario (alumno) pertenece a muchas materias/grupos.
     */
    public function materiasInscritas()
    {
    // CAMBIA Grupo::class por Materia::class
    return $this->belongsToMany(Materia::class, 'alumno_materia', 'alumno_id', 'materia_id')
                ->withPivot('clave_unica', 'status', 'promedio_real', 'promedio_redondeado')
                ->withTimestamps();
    }
}