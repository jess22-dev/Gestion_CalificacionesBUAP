<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\Estudiante;
use App\Models\Actividad;
use Illuminate\Support\Facades\Auth;

class AlumnoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Materias del alumno con datos del pivot
        $materias = $user->materias()
            ->withPivot('promedio_real', 'promedio_redondeado', 'status', 'qr_path', 'clave_asistencia')
            ->where('alumno_materia.status', 'activo')
            ->get();

        // Buscar datos del estudiante por email para el QR
        $estudiante = Estudiante::where('email', $user->email)->first();

        return view('alumno.dashboard', compact('materias', 'estudiante'));
    }

    public function solicitarBaja($nrc)
    {
        $user    = Auth::user();
        $materia = $user->materias()->where('materia_nrc', $nrc)->first();

        if ($materia && $materia->pivot->status !== 'baja') {
            $user->materias()->updateExistingPivot($nrc, [
                'status'     => 'baja',
                'fecha_baja' => now(),
            ]);

            return back()->with('success', 'Baja procesada correctamente. Se ha notificado al docente.');
        }

        return back()->with('error', 'No se pudo procesar la baja.');
    }

    public function show($nrc)
    {
        $user    = Auth::user();
        $materia = $user->materias()->where('materias.nrc', $nrc)->firstOrFail();

        return view('alumno.show', compact('materia'));
    }

    /**
     * Calificaciones del alumno en una materia
     */
    public function calificaciones($nrc)
    {
        $user    = Auth::user();
        $materia = $user->materias()->where('materias.nrc', $nrc)->firstOrFail();

        // Actividades de la materia con el pivot del alumno
        $actividades = Actividad::where('materia_nrc', $nrc)
            ->with(['alumnos' => function ($q) use ($user) {
                $q->where('alumno_id', $user->id);
            }])
            ->get()
            ->map(function ($actividad) {
                $actividad->pivot = $actividad->alumnos->first()?->pivot;
                return $actividad;
            });

        // Calcular promedio ponderado
        $promedio = 0;
        $totalPonderacion = 0;
        foreach ($actividades as $actividad) {
            if (!is_null($actividad->pivot?->calificacion)) {
                $promedio += $actividad->pivot->calificacion * ($actividad->ponderacion / 100);
                $totalPonderacion += $actividad->ponderacion;
            }
        }
        if ($totalPonderacion > 0) {
            $promedio = $promedio / ($totalPonderacion / 100);
        }

        return view('alumno.calificaciones', compact('materia', 'actividades', 'promedio'));
    }
}