<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Materia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
    'name' => 'Administrador BUAP',
    'email' => 'admin@buap.mx',
    'password' => Hash::make('admin123'),
    'role' => 'admin', // <--- Asegúrate de que esto esté aquí
]);
    }
}