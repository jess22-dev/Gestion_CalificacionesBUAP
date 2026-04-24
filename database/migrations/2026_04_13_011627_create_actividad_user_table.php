<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
    Schema::create('actividad_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('actividad_id');
        $table->unsignedBigInteger('alumno_id');
        $table->decimal('calificacion', 5, 2)->default(0); // Para guardar ej: 9.50
        
        $table->foreign('actividad_id')->references('id')->on('actividads')->onDelete('cascade');
        $table->foreign('alumno_id')->references('id')->on('users')->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_user');
    }
};
