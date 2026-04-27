<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelController; 
use App\Http\Controllers\AlumnoController; 
use App\Http\Controllers\MateriaController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ActividadController;

/*
|--------------------------------------------------------------------------
| Web Routes - SISTEMA BUAP
|--------------------------------------------------------------------------
*/

// 1. Redirección inicial: Si NO hay sesión, forzar LOGIN.
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Todas las rutas protegidas por autenticación
Route::middleware(['auth', 'verified'])->group(function () {

    // 2. Redirección Inteligente (Controlador de tráfico principal)
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        // Verificación de seguridad: si no hay usuario o no hay rol, cerrar sesión
        if (!$user || !$user->role) {
            Auth::logout();
            return redirect('/login')->withErrors(['role' => 'Acceso denegado o rol no asignado.']);
        }

        $role = trim(strtolower($user->role));

        return match ($role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'profesor' => redirect()->route('profesor.dashboard'),
            'alumno'   => redirect()->route('alumno.dashboard'),
            default    => Auth::logout() || redirect('/login')->withErrors(['role' => 'Rol no reconocido.']),
        };
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

    // 4. SECCIÓN PROFESOR
    Route::middleware(['can:profesor'])->prefix('profesor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('profesor.dashboard');
        Route::get('/materias', [DashboardController::class, 'index'])->name('profesor.materias');
        Route::get('/grupo/{nrc}', [DashboardController::class, 'showGrupo'])->name('profesor.materias.show');
        Route::post('/grupo/{nrc}/actividades', [ActividadController::class, 'store'])->name('profesor.actividades.store');
        Route::delete('/grupo/{nrc}/actividades/{actividad}', [ActividadController::class, 'destroy'])->name('profesor.actividades.destroy');

        Route::get('/materias/detalle/{nrc}', [MateriaController::class, 'show'])->name('materias.show');
        Route::get('/asistencia/{nrc}', function($nrc) {
            return view('profesor.asistencia', compact('nrc'));
        })->name('profesor.asistencia');
    });

    // 5. SECCIÓN ALUMNO
    Route::middleware(['can:alumno'])->prefix('alumno')->group(function () {
        Route::get('/dashboard', [AlumnoController::class, 'index'])->name('alumno.dashboard');
        Route::get('/credencial/{nrc}', [AlumnoController::class, 'showCredencial'])->name('alumno.credencial');
        Route::post('/materia/{nrc}/baja', [AlumnoController::class, 'solicitarBaja'])->name('alumno.baja');
        Route::post('/asistencia/registrar', [AlumnoController::class, 'registrarAsistencia'])->name('alumno.asistencia.registrar');
        Route::get('/alumno/materia/{nrc}', [AlumnoController::class, 'show'])->name('alumno.materia.detalle');
    });

    // 6. Configuración de Perfil 
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 7. Rutas de autenticación
require __DIR__.'/auth.php';
require __DIR__.'/estudiantes.php';