<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\Estudiante;
use App\Models\Actividad;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    /**
     * Dashboard del alumno — muestra sus materias activas
     */
    public function index()
    {
        $user = Auth::user();

        $materias   = $user->materias()->wherePivot('status', 'activo')->get();
        $estudiante = Estudiante::where('email', $user->email)->first();

        return view('alumno.dashboard', compact('materias', 'estudiante'));
    }

    /**
     * Detalle de una materia — historial de asistencias del alumno
     */
    public function show($nrc)
    {
        $user = Auth::user();

        $materia = $user->materias()
            ->where('materias.nrc', $nrc)
            ->wherePivot('status', 'activo')
            ->firstOrFail();

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

        $totalSesiones        = $asistencias->count();
        $totalPresentes       = $asistencias->where('asistio', 1)->count();
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

    /**
     * Solicitar baja de una materia
     */
    public function solicitarBaja(Request $request, $nrc)
    {
        $user       = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        $request->validate([
            'codigo_estudiante' => 'required|string',
            'nrc_confirmacion'  => 'required|string',
            'confirmacion'      => 'required|string',
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

        $materia = $user->materias()->where('materias.nrc', $nrc)->first();

        if (!$materia) {
            return back()->with('error', 'No se encontró la materia.');
        }

        if ($materia->pivot->status === 'baja') {
            return back()->with('error', 'Ya te encuentras dado de baja en esta materia.');
        }

        // Notificar al profesor
        $profesorId = $materia->profesor_id ?? null;
        if ($profesorId) {
            Notificacion::create([
                'user_id' => $profesorId,
                'titulo'  => 'Baja de alumno',
                'mensaje' => 'El alumno ' . $user->name . ' se dio de baja de la materia ' . $materia->Materia . ' (NRC ' . $nrc . ').',
                'tipo'    => 'baja',
                'url'     => route('profesor.materias.show', ['nrc' => $nrc]),
                'leida'   => false,
            ]);
        }

        $user->materias()->updateExistingPivot($nrc, [
            'status'     => 'baja',
            'fecha_baja' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('alumno.dashboard')
            ->with('success', 'La baja se procesó correctamente.');
    }

    /**
     * Calificaciones del alumno en una materia (solo lectura)
     */
    public function calificaciones($nrc)
    {
        $user = Auth::user();

        $materia = $user->materias()
            ->where('materias.nrc', $nrc)
            ->wherePivot('status', 'activo')
            ->firstOrFail();

        // Buscar estudiante por email para usar su ID en actividad_user
        $estudiante = Estudiante::where('email', $user->email)->first();

        $actividades = Actividad::where('materia_nrc', $nrc)
            ->with(['alumnos' => function ($q) use ($user) {
                $q->where('users.id', $user->id);
            }])
            ->get()
            ->map(function ($actividad) {
                $actividad->nota_alumno = optional($actividad->alumnos->first())->pivot?->calificacion;
                $actividad->entregado   = optional($actividad->alumnos->first())->pivot?->entregado ?? false;
                $actividad->archivo     = optional($actividad->alumnos->first())->pivot?->archivo_nombre;
                return $actividad;
            });

        $calificadas = $actividades->filter(fn($a) => !is_null($a->nota_alumno));

        $promedio = $calificadas->count() > 0
            ? round($calificadas->avg('nota_alumno'), 2)
            : 0;

        return view('alumno.calificaciones', compact('materia', 'actividades', 'promedio', 'estudiante'));
    }
}
