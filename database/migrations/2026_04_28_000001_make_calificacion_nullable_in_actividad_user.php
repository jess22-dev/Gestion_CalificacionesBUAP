<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividad_user', function (Blueprint $table) {
            $table->decimal('calificacion', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('actividad_user', function (Blueprint $table) {
            $table->decimal('calificacion', 5, 2)->nullable(false)->change();
        });
    }
};
