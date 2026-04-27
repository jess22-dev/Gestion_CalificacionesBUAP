<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes - SISTEMA BUAP
|--------------------------------------------------------------------------
*/

// 1. Redirección inicial
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // 2. Redirección Inteligente
    Route::get('/dashboard', function () {
        $user = Auth::user();

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
        Route::get('/materias/detalle/{nrc}', [MateriaController::class, 'show'])->name('materias.show');

        // Actividades
        Route::post('/grupo/{nrc}/actividades', [ActividadController::class, 'store'])->name('profesor.actividades.store');
        Route::delete('/grupo/{nrc}/actividades/{actividad}', [ActividadController::class, 'destroy'])->name('profesor.actividades.destroy');

        // Asistencia
        Route::get('/asistencia/{nrc}', function ($nrc) {
            $materia = \App\Models\Materia::where('nrc', $nrc)->firstOrFail();
            $alumnos = $materia->estudiantes()->wherePivot('status', 'activo')->get();
            return view('profesor.asistencia', compact('materia', 'alumnos'));
        })->name('profesor.asistencia');

        Route::post('/asistencia/{nrc}/guardar', function ($nrc) {
            return back()->with('success', 'Lista de asistencia guardada correctamente.');
             })->name('asistencias.guardar');
    });

    // 5. SECCIÓN ALUMNO
    Route::middleware(['can:alumno'])->prefix('alumno')->group(function () {
        Route::get('/dashboard', [AlumnoController::class, 'index'])->name('alumno.dashboard');
        Route::get('/credencial/{nrc}', [AlumnoController::class, 'showCredencial'])->name('alumno.credencial');
        Route::post('/materia/{nrc}/baja', [AlumnoController::class, 'solicitarBaja'])->name('alumno.baja');
        Route::post('/asistencia/registrar', [AlumnoController::class, 'registrarAsistencia'])->name('alumno.asistencia.registrar');
        Route::get('/alumno/materia/{nrc}', [AlumnoController::class, 'show'])->name('alumno.materia.detalle');
    });

    // 6. Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 7. Autenticación
require __DIR__.'/auth.php';

// 8. Módulo Alta de Estudiantes
require __DIR__.'/estudiantes.php';

// 9. API Asistencia (del compañero)
Route::post('/asistencia/iniciar',    [AsistenciaController::class, 'iniciar']);
Route::post('/asistencia/detener',    [AsistenciaController::class, 'detener']);
Route::post('/asistencia/qr',         [AsistenciaController::class, 'registrarQR']);
Route::post('/asistencia/registrar',  [AsistenciaController::class, 'registrar']);