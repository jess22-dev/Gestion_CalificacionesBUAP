<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MateriaController;
use Illuminate\Support\Facades\Auth;

// 1. Redirección inicial
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // 2. Redirección Inteligente por Rol
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'profesor') {
            return redirect()->route('profesor.dashboard');
        } elseif ($user->role === 'alumno') {
            return redirect()->route('alumno.dashboard');
        }
        Auth::logout();
        return redirect('/')->withErrors(['role' => 'Rol no reconocido.']);
    })->name('dashboard');

    // 3. ADMINISTRADOR
    Route::middleware(['can:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.index'); 
        })->name('admin.dashboard');
    });

    // 4. PROFESOR (Maquetado para la Entrega 1)
    Route::middleware(['can:profesor'])->prefix('profesor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('profesor.dashboard');
        
        // --- ESTA ES LA RUTA DE LA MAQUETA ---
        // Al hacer clic en una materia, te mandará directamente a la vista de Actividades
        Route::get('/materias/{id}', function($id) {
            return view('profesor.actividades');
        })->name('profesor.materias.show');

        // Rutas adicionales para los botones de la tarjeta
        Route::get('/materias/{id}/actividades', function($id) {
            return view('profesor.actividades');
        })->name('profesor.actividades');

        Route::get('/materias/{id}/asistencia', function($id) {
            return view('profesor.asistencia');
        })->name('profesor.asistencia');
    });

    // 5. ALUMNO
    Route::middleware(['can:alumno'])->prefix('alumno')->group(function () {
        Route::get('/dashboard', function () {
            return view('alumno.dashboard'); 
        })->name('alumno.dashboard');
    });

    // 6. Perfil Común
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';