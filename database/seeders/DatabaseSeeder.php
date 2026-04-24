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
    // 1. Crear Administrador
    \App\Models\User::create([
        'name' => 'Admin Sistema',
        'email' => 'admin@admin.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
    ]);

    // 2. Crear Profesor de Prueba (Jessica)
    $profesor = \App\Models\User::create([
        'name' => 'Jessica Juarez Alameda',
        'email' => 'jessica.profesora@buap.mx',
        'password' => Hash::make('profe123'),
        'role' => 'profesor',
    ]);

    // 3. Crear un par de Alumnos de Prueba
    $alumno1 = \App\Models\User::create([
        'name' => 'Alumno Prueba Uno',
        'email' => 'alumno1@alumno.buap.mx',
        'password' => Hash::make('alumno123'),
        'role' => 'alumno',
    ]);

    
    \App\Models\Materia::create([
        'nrc' => '46473',
        'clave' => 'ITI-101',
        'Materia' => 'Modelos de Desarrollo Web',
        'Profesor' => 'Jessica Juarez Alameda',
        'profesor_id' => $profesor->id,
    ]);
}
}