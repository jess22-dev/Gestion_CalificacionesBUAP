<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Materia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CREAR AL PROFESOR
        $profesor = User::create([
            'name'     => 'Gustavo Olguin',
            'email'    => 'profe@buap.mx',
            'password' => Hash::make('password'),
            'role'     => 'profesor',
        ]);

        // 2. CREAR LA MATERIA (Usando tus columnas reales: nombre_materia, grupo, seccion...)
        $materia = Materia::create([
            'nombre_materia' => 'Modelos de Desarrollo Web',
            'grupo'          => 'ITI-101',
            'seccion'        => '001',
            'dias'           => 'Lunes, Miércoles',
            'horario'        => '10:00 - 12:00',
            'user_id'        => $profesor->id, // El ID del profe que creamos arriba
        ]);

        // 3. CREAR AL ALUMNO
        $alumno = User::create([
            'name'     => 'Estudiante BUAP',
            'email'    => 'alumno@buap.mx',
            'password' => Hash::make('password'),
            'role'     => 'alumno',
        ]);

        // 4. VINCULAR ALUMNO CON MATERIA (En tu tabla alumno_materia)
        DB::table('alumno_materia')->insert([
            'alumno_id'          => $alumno->id,
            'materia_id'         => $materia->id,
            'clave_unica'        => 'BUAP-ITI-2026', // Requerimiento 7
            'promedio_real'      => 9.55,           // Requerimiento 4
            'promedio_redondeado' => 10,             // Requerimiento 4
            'status'             => 'activo',       // Requerimiento 8
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // 5. CREAR AL ADMIN
        User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@buap.mx',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);
    }
}