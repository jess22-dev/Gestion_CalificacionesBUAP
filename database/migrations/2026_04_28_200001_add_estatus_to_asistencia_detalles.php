<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencia_detalles', function (Blueprint $table) {
            // Estatus con 4 opciones — reemplaza el boolean asistio
            $table->enum('estatus', ['ausente', 'presente', 'retardo', 'justificado'])
                  ->default('ausente')
                  ->after('asistio');
        });

        // Migrar datos existentes: asistio=true → presente, asistio=false → ausente
        \Illuminate\Support\Facades\DB::statement("
            UPDATE asistencia_detalles
            SET estatus = CASE WHEN asistio = 1 THEN 'presente' ELSE 'ausente' END
        ");
    }

    public function down(): void
    {
        Schema::table('asistencia_detalles', function (Blueprint $table) {
            $table->dropColumn('estatus');
        });
    }
};
