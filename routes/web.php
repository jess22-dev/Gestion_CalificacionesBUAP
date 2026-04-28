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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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

        // Ver todos los estudiantes con clave única
        Route::get('/estudiantes', function () {
            $buscar      = request('buscar');
            $estudiantes = \App\Models\Estudiante::with('materias')
                ->when($buscar, function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                      ->orWhere('codigo_estudiante', 'like', "%{$buscar}%")
                      ->orWhere('email', 'like', "%{$buscar}%");
                })
                ->orderBy('nombre')
                ->paginate(20);
            $conClave  = \App\Models\Estudiante::whereNotNull('clave_unica')->count();
            $sinClave  = \App\Models\Estudiante::whereNull('clave_unica')->count();
            return view('admin.estudiantes', compact('estudiantes', 'conClave', 'sinClave'));
        })->name('admin.estudiantes');

        // Baja de una materia específica
        Route::delete('/estudiantes/{estudiante}/baja-materia', function (\App\Models\Estudiante $estudiante) {
            $nrc = request('nrc');
            if (!$nrc) {
                return back()->with('error', 'Debes seleccionar una materia.');
            }
            $estudiante->materias()->detach($nrc);

            // También actualizar alumno_materia si existe
            $user = \App\Models\User::where('email', $estudiante->email)->first();
            if ($user) {
                $user->materias()->updateExistingPivot($nrc, [
                    'status'     => 'baja',
                    'fecha_baja' => now(),
                ]);
            }

            return back()->with('success', "Se dio de baja a {$estudiante->nombre} de la materia {$nrc}.");
        })->name('admin.baja.materia');

        // Baja total de la plataforma
        Route::delete('/estudiantes/{estudiante}/baja-total', function (\App\Models\Estudiante $estudiante) {
            $nombre = $estudiante->nombre;

            // Eliminar user asociado
            $user = \App\Models\User::where('email', $estudiante->email)->where('role', 'alumno')->first();
            if ($user) {
                $user->delete();
            }

            // Eliminar estudiante (cascade eliminará materia_estudiante)
            $estudiante->delete();

            return back()->with('success', "El estudiante {$nombre} fue eliminado completamente de la plataforma.");
        })->name('admin.baja.total');

        // Generar clave única para estudiante sin clave
        Route::post('/estudiantes/{estudiante}/generar-clave', function (\App\Models\Estudiante $estudiante) {
            if ($estudiante->clave_unica) {
                return back()->with('error', "El estudiante {$estudiante->nombre} ya tiene una clave asignada.");
            }

            // Generar clave única
            $clave = \App\Models\Estudiante::generarClaveUnica();
            $estudiante->update(['clave_unica' => $clave]);

            // Crear user con rol alumno si no existe — necesario para el login
            $user = \App\Models\User::firstOrCreate(
                ['email' => $estudiante->email],
                [
                    'name'     => $estudiante->nombre,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                    'role'     => 'alumno',
                ]
            );

            // Vincular en alumno_materia para cada materia del estudiante
            foreach ($estudiante->materias as $materia) {
                $yaVinculado = \Illuminate\Support\Facades\DB::table('alumno_materia')
                    ->where('alumno_id', $user->id)
                    ->where('materia_nrc', $materia->nrc)
                    ->exists();

                if (!$yaVinculado) {
                    // Generar clave_unica única verificando contra alumno_materia
                    do {
                        $claveMateria = strtoupper(\Illuminate\Support\Str::random(10));
                    } while (\Illuminate\Support\Facades\DB::table('alumno_materia')
                        ->where('clave_unica', $claveMateria)->exists());

                    // Generar clave_asistencia única verificando contra alumno_materia
                    do {
                        $claveAsistencia = strtoupper(\Illuminate\Support\Str::random(10));
                    } while (\Illuminate\Support\Facades\DB::table('alumno_materia')
                        ->where('clave_asistencia', $claveAsistencia)->exists());

                    \Illuminate\Support\Facades\DB::table('alumno_materia')->insert([
                        'alumno_id'        => $user->id,
                        'materia_nrc'      => $materia->nrc,
                        'clave_unica'      => $claveMateria,
                        'clave_asistencia' => $claveAsistencia,
                        'status'           => 'activo',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            return back()->with('success', "Clave generada para {$estudiante->nombre}: {$clave}");
        })->name('admin.generar.clave');
    });

    // 4. SECCIÓN PROFESOR
    Route::middleware(['can:profesor'])->prefix('profesor')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('profesor.dashboard');
        Route::get('/materias', [DashboardController::class, 'index'])->name('profesor.materias');
        Route::get('/grupo/{nrc}', [MateriaController::class, 'show'])->name('profesor.materias.show');
        Route::get('/materias/detalle/{nrc}', [MateriaController::class, 'show'])->name('materias.show');

        // Actividades
        Route::post('/grupo/{nrc}/actividades', [ActividadController::class, 'store'])->name('profesor.actividades.store');
        Route::delete('/grupo/{nrc}/actividades/{actividad}', [ActividadController::class, 'destroy'])->name('profesor.actividades.destroy');

        // Asistencia — tomar lista
        Route::get('/asistencia/{nrc}', function ($nrc) {
            $materia  = \App\Models\Materia::where('nrc', $nrc)->firstOrFail();
            $alumnos  = $materia->estudiantes()->wherePivot('status', 'activo')->get();
            $asistenciaActiva = \App\Models\Asistencia::where('materia_nrc', $nrc)
                                ->where('activa', true)
                                ->where('termina_en', '>', now())
                                ->latest()
                                ->first();
            return view('profesor.asistencia', compact('materia', 'alumnos', 'asistenciaActiva'));
        })->name('profesor.asistencia');

        Route::post('/asistencia/{nrc}/guardar', function ($nrc) {
            return back()->with('success', 'Lista de asistencia guardada correctamente.');
        })->name('asistencias.guardar');

        // Historial de asistencias
        Route::get('/grupo/{nrc}/historial', [MateriaController::class, 'historial'])->name('profesor.historial');
        Route::post('/grupo/{nrc}/historial/editar', [AsistenciaController::class, 'editarHistorial'])->name('profesor.historial.editar');

        // Calificaciones por actividad
        Route::get('/grupo/{nrc}/actividades/{actividad}/detalle', [ActividadController::class, 'detalle'])->name('profesor.actividades.detalle');
        Route::post('/grupo/{nrc}/actividades/{actividad}/calificar', [ActividadController::class, 'calificar'])->name('profesor.actividades.calificar');
    });

    // 5. SECCIÓN ALUMNO
    Route::middleware(['can:alumno'])->prefix('alumno')->group(function () {
        Route::get('/dashboard', [AlumnoController::class, 'index'])->name('alumno.dashboard');
        Route::get('/credencial/{nrc}', [AlumnoController::class, 'showCredencial'])->name('alumno.credencial');
        Route::post('/materia/{nrc}/baja', [AlumnoController::class, 'solicitarBaja'])->name('alumno.baja');
        Route::post('/asistencia/registrar', [AlumnoController::class, 'registrarAsistencia'])->name('alumno.asistencia.registrar');
        Route::get('/alumno/materia/{nrc}', [AlumnoController::class, 'show'])->name('alumno.materia.detalle');

        // Calificaciones del alumno por materia
        Route::get('/materia/{nrc}/calificaciones', [AlumnoController::class, 'calificaciones'])->name('alumno.calificaciones');

        // Subir / eliminar archivo de actividad
        Route::post('/actividad/{actividad}/subir', [ActividadController::class, 'subirArchivo'])->name('alumno.actividad.subir');
        Route::delete('/actividad/{actividad}/eliminar', [ActividadController::class, 'eliminarArchivo'])->name('alumno.actividad.eliminar');
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

// 9. API Asistencia
Route::post('/asistencia/iniciar',        [AsistenciaController::class, 'iniciar']);
Route::post('/asistencia/detener',        [AsistenciaController::class, 'detener']);
Route::post('/asistencia/qr',             [AsistenciaController::class, 'registrarQR']);
Route::post('/asistencia/registrar',      [AsistenciaController::class, 'registrar']);
Route::get('/asistencia/estado',          [AsistenciaController::class, 'estadoActual']);
Route::post('/asistencia/cambiar-estatus',[AsistenciaController::class, 'cambiarEstatus']);
Route::post('/asistencia/todos-presentes',[AsistenciaController::class, 'todosPresentes']);