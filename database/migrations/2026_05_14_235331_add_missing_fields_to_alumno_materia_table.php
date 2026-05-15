<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_materia', function (Blueprint $table) {
            $table->string('clave_asistencia')->nullable()->after('clave_unica');
            $table->string('qr_path')->nullable()->after('status');
            $table->timestamp('fecha_baja')->nullable()->after('qr_path');
        });
    }

    public function down(): void
    {
        Schema::table('alumno_materia', function (Blueprint $table) {
            $table->dropColumn([
                'clave_asistencia',
                'qr_path',
                'fecha_baja',
            ]);
        });
    }
};