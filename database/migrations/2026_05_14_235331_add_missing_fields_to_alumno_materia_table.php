<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_materia', function (Blueprint $table) {
            if (!Schema::hasColumn('alumno_materia', 'clave_asistencia')) {
                $table->string('clave_asistencia')->nullable()->after('clave_unica');
            }
            if (!Schema::hasColumn('alumno_materia', 'qr_path')) {
                $table->string('qr_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('alumno_materia', 'fecha_baja')) {
                $table->timestamp('fecha_baja')->nullable()->after('qr_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alumno_materia', function (Blueprint $table) {
            $table->dropColumn(
                array_filter([
                    Schema::hasColumn('alumno_materia', 'clave_asistencia') ? 'clave_asistencia' : null,
                    Schema::hasColumn('alumno_materia', 'qr_path')          ? 'qr_path'          : null,
                    Schema::hasColumn('alumno_materia', 'fecha_baja')       ? 'fecha_baja'        : null,
                ])
            );
        });
    }
};