<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\Estudiante;
use App\Models\Actividad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notificacion;

class AlumnoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Materias activas del alumno
        $materias = $user->materias()
            ->wherePivot('status', 'activo')
            ->get();

        // Datos del estudiante por email
        $estudiante = Estudiante::where('email', $user->email)->first();

        return view('alumno.dashboard', compact('materias', 'estudiante'));
    }

    public function show($nrc)
    {
        $user = Auth::user();

        $materia = $user->materias()
            ->where('materias.nrc', $nrc)
            ->wherePivot('status', 'activo')
            ->firstOrFail();

        // Historial de asistencias del alumno en esta materia
        $asistencias = DB::table('asistencia_detalles as ad')
            ->join('asistencias as a', 'ad.asistencia_id', '=', 'a.id')
            ->where('ad.alumno_id', $user->id)
            ->where('a.materia_nrc', $nrc)
            ->select(
                'a.id',
                'a.materia_nrc',
                'a.created_at as fecha_sesion',
                'ad.asistio',
                'ad.hora_registro'
            )
            ->orderByDesc('a.created_at')
            ->get();

        $totalSesiones = $asistencias->count();
        $totalPresentes = $asistencias->where('asistio', 1)->count();
        $porcentajeAsistencia = $totalSesiones > 0
            ? round(($totalPresentes / $totalSesiones) * 100, 2)
            : 0;

        $estudiante = Estudiante::where('email', $user->email)->first();

        return view('alumno.show', compact(
            'materia',
            'asistencias',
            'totalSesiones',
            'totalPresentes',
            'porcentajeAsistencia',
            'estudiante'
        ));
    }

    public function solicitarBaja(Request $request, $nrc)
    {
        $user = Auth::user();

        $estudiante = Estudiante::where('email', $user->email)->first();

        $request->validate([
            'codigo_estudiante' => 'required|string',
            'nrc_confirmacion' => 'required|string',
            'confirmacion' => 'required|string',
        ]);

        if (!$estudiante) {
            return back()->with('error', 'No se encontró la información del estudiante.');
        }

        if ($request->codigo_estudiante !== $estudiante->codigo_estudiante) {
            return back()->with('error', 'La matrícula no coincide.');
        }

        if ($request->nrc_confirmacion !== $nrc) {
            return back()->with('error', 'El NRC no coincide.');
        }

        if (strtoupper(trim($request->confirmacion)) !== 'SI') {
            return back()->with('error', 'Debes confirmar escribiendo SI.');
        }

        $materia = $user->materias()
            ->where('materias.nrc', $nrc)
            ->first();

        if (!$materia) {
            return back()->with('error', 'No se encontró la materia.');
        }

        if ($materia->pivot->status === 'baja') {
            return back()->with('error', 'Ya te encuentras dado de baja en esta materia.');
        }




        $profesorId = $materia->profesor_id ?? null;

        if ($profesorId) {
            Notificacion::create([
                'user_id' => $profesorId,
                'titulo' => 'Baja de alumno',
                'mensaje' => 'El alumno ' . $user->name . ' se dio de baja de la materia ' . $materia->Materia . ' (NRC ' . $nrc . ').',
                'tipo' => 'baja',
                'url' => route('profesor.materias.show', ['nrc' => $nrc]),
                'leida' => false,
            ]);
        }







        $user->materias()->updateExistingPivot($nrc, [
            'status' => 'baja',
            'fecha_baja' => now(),
            'updated_at' => now(),

        ]);

        // Aquí después conectamos la notificación al profesor
        return redirect()->route('alumno.dashboard')
            ->with('success', 'La baja se procesó correctamente.');
    }

    /**
     * Calificaciones del alumno en una materia
     */
    public function calificaciones($nrc)
    {
        $user = Auth::user();

        $materia = $user->materias()
            ->where('materias.nrc', $nrc)
            ->wherePivot('status', 'activo')
            ->firstOrFail();

        $actividades = Actividad::where('materia_nrc', $nrc)
            ->with(['alumnos' => function ($q) use ($user) {
                $q->where('users.id', $user->id);
            }])
            ->get()
            ->map(function ($actividad) {
                $actividad->nota_alumno = optional($actividad->alumnos->first())->pivot?->calificacion;
                return $actividad;
            });

        // Promedio simple por ahora
        $calificadas = $actividades->filter(function ($actividad) {
            return !is_null($actividad->nota_alumno);
        });

        $promedio = $calificadas->count() > 0
            ? round($calificadas->avg('nota_alumno'), 2)
            : 0;

        return view('alumno.calificaciones', compact('materia', 'actividades', 'promedio'));
    }
}