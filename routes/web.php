<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelController; 
use Illuminate\Support\Facades\Auth;

// 1. Redirección inicial: Si ya está logueado, mandarlo a su dashboard
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // 2. Redirección Inteligente (Punto de entrada único)
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $role = strtolower($user->role);

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'profesor') {
            return redirect()->route('profesor.dashboard');
        } elseif ($role === 'alumno') {
            return redirect()->route('alumno.dashboard');
        }
        
        Auth::logout();
        return redirect('/login')->withErrors(['role' => 'Rol no reconocido.']);
    })->name('dashboard');

    // 3. SECCIÓN ADMINISTRADOR
    Route::middleware(['can:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            $materias = \App\Models\Materia::all();
            return view('admin.index', compact('materias')); 
        })->name('admin.dashboard');

        Route::get('/importar', function () {
            $materias = \App\Models\Materia::all();
            return view('admin.importar', compact('materias')); 
        })->name('admin.importar');

        Route::post('/importar-excel', [ExcelController::class, 'importar'])->name('excel.importar');
    });

    // 4. SECCIÓN PROFESOR (Corregido: llaves cerradas correctamente)
    Route::middleware(['can:profesor'])->prefix('profesor')->group(function () {
        // Llama al DashboardController que arreglamos (carga la vista profesor.dashboard)
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('profesor.dashboard');
        Route::get('/profesor/materias', [DashboardController::class, 'index'])->name('profesor.materias');
        
        Route::get('/materias/{nrc}', function($nrc) {
            return view('profesor.actividades', compact('nrc'));
        })->name('profesor.materias.show');
    });

    // 5. SECCIÓN ALUMNO
    Route::middleware(['can:alumno'])->prefix('alumno')->group(function () {
        Route::get('/dashboard', function () {
            return view('alumno.dashboard'); 
        })->name('alumno.dashboard');
    });

    // 6. Perfil (Común para todos los logueados)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Importante: Rutas de autenticación (Login/Logout)
require __DIR__.'/auth.php';