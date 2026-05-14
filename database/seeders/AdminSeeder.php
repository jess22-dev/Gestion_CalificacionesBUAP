<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Materia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run()
    {
        \App\Models\User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@buap.mx',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role'     => 'admin', 
        ]);
    }
}