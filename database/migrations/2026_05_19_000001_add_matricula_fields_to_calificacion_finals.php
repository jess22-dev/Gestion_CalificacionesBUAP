<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calificacion_finals', function (Blueprint $table) {
            $table->string('codigo_estudiante', 9)->nullable()->after('nombre_alumno');
            $table->boolean('sin_matricula')->default(false)->after('codigo_estudiante');
        });
    }

    public function down(): void
    {
        Schema::table('calificacion_finals', function (Blueprint $table) {
            $table->dropColumn(['codigo_estudiante', 'sin_matricula']);
        });
    }
};
