<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencia_detalles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('asistencia_id');
            $table->unsignedBigInteger('alumno_id');

            $table->string('clave_unica')->nullable();
            $table->boolean('asistio')->default(true);
            $table->timestamp('hora_registro')->nullable();

            $table->timestamps();

            // 🔐 evitar duplicados
            $table->unique(['asistencia_id','alumno_id']);

            // 🔗 relaciones (MUY IMPORTANTE)
            $table->foreign('asistencia_id')
                  ->references('id')
                  ->on('asistencias')
                  ->onDelete('cascade');

            $table->foreign('alumno_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_detalles');
    }
};