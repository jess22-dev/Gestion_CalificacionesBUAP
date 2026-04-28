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
    Schema::table('asistencias', function (Blueprint $table) {
        $table->string('materia_nrc')->after('id');
        $table->timestamp('inicia_en')->nullable();
        $table->timestamp('termina_en')->nullable();
        $table->boolean('activa')->default(true);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('asistencias', function (Blueprint $table) {
        $table->dropColumn([
            'materia_nrc',
            'inicia_en',
            'termina_en',
            'activa'
        ]);
    });
}
};
