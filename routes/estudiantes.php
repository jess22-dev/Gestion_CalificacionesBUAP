<?php

use App\Http\Controllers\EstudianteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas del módulo: Estudiantes
| Solo accesibles por usuarios con rol 'profesor'
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'can:profesor'])
    ->prefix('profesor/estudiantes')
    ->name('profesor.estudiantes.')
    ->group(function () {

        Route::get('/',          [EstudianteController::class, 'index'])->name('index');
        Route::get('/crear',     [EstudianteController::class, 'create'])->name('create');
        Route::post('/crear',    [EstudianteController::class, 'store'])->name('store');
        Route::get('/importar',  [EstudianteController::class, 'showImport'])->name('import');
        Route::post('/importar', [EstudianteController::class, 'import'])->name('import.store');
        Route::get('/{estudiante}', [EstudianteController::class, 'show'])->name('show');
    });
