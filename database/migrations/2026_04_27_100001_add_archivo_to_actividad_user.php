<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividad_user', function (Blueprint $table) {
            $table->string('archivo_path')->nullable()->after('calificacion');
            $table->string('archivo_nombre')->nullable()->after('archivo_path');
            $table->boolean('entregado')->default(false)->after('archivo_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('actividad_user', function (Blueprint $table) {
            $table->dropColumn(['archivo_path', 'archivo_nombre', 'entregado']);
        });
    }
};
