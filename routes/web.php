<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MateriaController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Punto 3 y 4: Dashboard con la carga académica
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Punto 5: Entrar a un grupo (¡Aquí agregamos el ; que faltaba!)
Route::get('/grupos/{id}', function ($id) {
    return view('grupos-detalle', compact('id'));
})->name('grupos.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Tus materias del módulo anterior
Route::resource('materias', MateriaController::class)->middleware('auth');

require __DIR__.'/auth.php';