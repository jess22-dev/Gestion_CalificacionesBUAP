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
        $this->call([
        AdminSeeder::class,
    ]);
    }
}