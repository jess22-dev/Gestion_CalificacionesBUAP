<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asistencia_detalles', function (Blueprint $table) {
            // Eliminar FK anterior que apuntaba a users
            $table->dropForeign(['alumno_id']);

            // Crear nueva FK que apunta a estudiantes
            $table->foreign('alumno_id')
                  ->references('id')
                  ->on('estudiantes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('asistencia_detalles', function (Blueprint $table) {
            $table->dropForeign(['alumno_id']);

            $table->foreign('alumno_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
