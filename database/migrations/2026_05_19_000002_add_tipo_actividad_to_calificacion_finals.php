<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calificacion_finals', function (Blueprint $table) {
            if (!Schema::hasColumn('calificacion_finals', 'tipo_actividad')) {
                $table->string('tipo_actividad')->nullable()->after('actividad_nombre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calificacion_finals', function (Blueprint $table) {
            $table->dropColumn('tipo_actividad');
        });
    }
};
