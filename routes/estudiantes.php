<?php

use App\Http\Controllers\EstudianteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo: Alta de Estudiantes
| Solo accesible por usuarios con rol 'profesor'
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'can:profesor'])
    ->prefix('profesor/estudiantes')
    ->name('profesor.estudiantes.')
    ->group(function () {

        // Listado de estudiantes de una materia
        Route::get('/',          [EstudianteController::class, 'index'])->name('index');

        // Agregar manualmente
        Route::get('/crear',     [EstudianteController::class, 'create'])->name('create');
        Route::post('/crear',    [EstudianteController::class, 'store'])->name('store');

        // Confirmar agregar estudiante existente a esta materia
        Route::post('/agregar-existente', [EstudianteController::class, 'agregarExistente'])->name('agregar.existente');

        // Importar Excel
        Route::get('/importar',  [EstudianteController::class, 'showImport'])->name('import');
        Route::post('/importar', [EstudianteController::class, 'import'])->name('import.store');

        // Ver detalle
        Route::get('/{estudiante}', [EstudianteController::class, 'show'])->name('show');
    });