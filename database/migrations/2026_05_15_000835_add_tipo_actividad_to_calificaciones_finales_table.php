<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('calificacion_finals', function (Blueprint $table) {
            // Agregamos la columna después de 'puntaje'
            $table->string('tipo_actividad')->nullable()->after('puntaje');
        });
    }

    public function down(): void
    {
        Schema::table('calificacion_finals', function (Blueprint $table) {
            $table->dropColumn('tipo_actividad');
        });
    }
};
