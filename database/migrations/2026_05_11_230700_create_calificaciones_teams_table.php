<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('calificaciones_teams', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('materia_nrc'); // Para saber a qué grupo pertenece
            $blueprint->string('email_alumno'); // Tu llave para identificar al alumno
            $blueprint->string('nombre_alumno'); // Solo como respaldo visual
            $blueprint->string('actividad_nombre'); // Columna H del Excel
            $blueprint->decimal('puntaje', 5, 2); // La nota ya convertida (0-10)
            $blueprint->timestamps();

            // Esto evita que se duplique la nota si suben el mismo archivo dos veces
            $blueprint->unique(['materia_nrc', 'email_alumno', 'actividad_nombre'], 'calificacion_unica');
        });
    }

    public function down()
    {
        Schema::dropIfExists('calificaciones_teams');
    }
};