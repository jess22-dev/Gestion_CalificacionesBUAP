<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\CalificacionFinal;
use App\Models\Asistencia;
use App\Models\AsistenciaDetalle;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{
    public function grupo($nrc)
    {
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        $notas = CalificacionFinal::where('materia_nrc', (string) $nrc)
                    ->where('actividad_nombre', '!=', 'DATOS_MANUALES')
                    ->get();

        // ── Actividades únicas ──
        $actividades = $notas->pluck('actividad_nombre')->unique()->values();

        // ── Promedio por actividad ──
        $promediosPorActividad = $actividades->mapWithKeys(function ($act) use ($notas) {
            $puntajes = $notas->where('actividad_nombre', $act)->pluck('puntaje')->filter(fn($v) => $v !== null);
            return [$act => $puntajes->count() ? round($puntajes->avg(), 2) : 0];
        });

        // ── Alumnos del Excel (calificacion_finals) ──
        $alumnosExcel = $notas->groupBy('email_alumno')->map(function ($items) {
            $puntajes = $items->pluck('puntaje')->filter(fn($v) => $v !== null);
            return [
                'nombre'   => $items->first()->nombre_alumno,
                'email'    => strtolower($items->first()->email_alumno),
                'promedio' => $puntajes->count() ? round($puntajes->avg(), 2) : 0,
                'fuente'   => 'excel',
            ];
        });

        // ── Alumnos del HTM (materia_estudiante) ──
        $alumnosHtm = DB::table('materia_estudiante')
            ->join('estudiantes', 'materia_estudiante.estudiante_id', '=', 'estudiantes.id')
            ->where('materia_estudiante.materia_nrc', $nrc)
            ->where('materia_estudiante.status', 'activo')
            ->select('estudiantes.nombre', 'estudiantes.email', 'estudiantes.codigo_estudiante')
            ->get();

        // ── Combinar ambas listas (HTM tiene prioridad en datos, Excel tiene calificaciones) ──
        $alumnosAgrupados = collect();

        // Índice email => codigo_estudiante para el selector
        $matriculasHtm = collect($alumnosHtm)->pluck('codigo_estudiante', 'email')
            ->mapWithKeys(fn($v, $k) => [strtolower(trim($k)) => $v]);

        foreach ($alumnosHtm as $alumnoHtm) {
            $email = strtolower(trim($alumnoHtm->email ?? ''));
            if (!$email) continue;

            if ($alumnosExcel->has($email)) {
                // Está en ambas listas — enriquecer con matrícula
                $dato = $alumnosExcel[$email];
                $dato['codigo_estudiante'] = $alumnoHtm->codigo_estudiante ?? null;
                $alumnosAgrupados->push($dato);
            } else {
                // Solo en HTM — promedio 0
                $alumnosAgrupados->push([
                    'nombre'            => $alumnoHtm->nombre,
                    'email'             => $email,
                    'promedio'          => 0,
                    'fuente'            => 'htm',
                    'codigo_estudiante' => $alumnoHtm->codigo_estudiante ?? null,
                ]);
            }
        }

        // Agregar los que solo están en Excel pero no en HTM
        foreach ($alumnosExcel as $email => $alumnoExcel) {
            if (!$alumnosAgrupados->firstWhere('email', $email)) {
                // Buscar matrícula en calificacion_finals
                $alumnoExcel['codigo_estudiante'] = \App\Models\CalificacionFinal::where('email_alumno', $email)
                    ->whereNotNull('codigo_estudiante')
                    ->value('codigo_estudiante');
                $alumnosAgrupados->push($alumnoExcel);
            }
        }

        $alumnosAgrupados = $alumnosAgrupados->sortByDesc('promedio')->values();

        // ── Promedio general del grupo ──
        $todosLosPuntajes = $notas->pluck('puntaje')->filter(fn($v) => $v !== null);
        $promedioGeneral  = $todosLosPuntajes->count() ? round($todosLosPuntajes->avg(), 2) : 0;

        $top3    = $alumnosAgrupados->take(3);
        $bottom3 = $alumnosAgrupados->sortBy('promedio')->take(3)->values();

        // ── Asistencias del grupo ──
        $sesiones      = Asistencia::where('materia_nrc', $nrc)->pluck('id');
        $totalSesiones = $sesiones->count();

        $asistenciaPorAlumno = [];
        if ($totalSesiones > 0) {
            $detalles = AsistenciaDetalle::whereIn('asistencia_id', $sesiones)->get();

            $userEmails = DB::table('users')
                ->whereIn('id', $detalles->pluck('alumno_id')->unique())
                ->pluck('email', 'id');

            foreach ($detalles->groupBy('alumno_id') as $alumnoId => $registros) {
                $email     = strtolower($userEmails[$alumnoId] ?? '');
                $presentes = $registros->whereIn('estatus', ['presente', 'justificado'])->count();
                $asistenciaPorAlumno[$email] = [
                    'presentes' => $presentes,
                    'total'     => $totalSesiones,
                    'pct'       => round(($presentes / $totalSesiones) * 100, 1),
                ];
            }
        }

        $pctAsistencias     = collect($asistenciaPorAlumno)->pluck('pct');
        $promedioAsistencia = $pctAsistencias->count() ? round($pctAsistencias->avg(), 1) : 0;

        // ── Lista unificada para el selector de alumno ──
        $listaAlumnos = $alumnosAgrupados->map(fn($a) => (object)[
            'email_alumno'      => $a['email'],
            'nombre_alumno'     => $a['nombre'],
            'codigo_estudiante' => $a['codigo_estudiante'] ?? null,
        ]);

        return view('profesor.estadisticas', compact(
            'materia',
            'actividades',
            'promediosPorActividad',
            'promedioGeneral',
            'promedioAsistencia',
            'top3',
            'bottom3',
            'totalSesiones',
            'listaAlumnos',
            'alumnosAgrupados'
        ));
    }

    public function alumno(Request $request, $nrc)
    {
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        $email = $request->input('email');

        if (!$email) {
            return response()->json(['error' => 'Email requerido'], 422);
        }

        // ── Calificaciones por actividad ──
        $notas = CalificacionFinal::where('materia_nrc', (string) $nrc)
                    ->where('email_alumno', $email)
                    ->where('actividad_nombre', '!=', 'DATOS_MANUALES')
                    ->get();

        $manual = CalificacionFinal::where('materia_nrc', (string) $nrc)
                    ->where('email_alumno', $email)
                    ->where('actividad_nombre', 'DATOS_MANUALES')
                    ->first();

        $calificaciones = $notas->map(fn($n) => [
            'actividad' => $n->actividad_nombre,
            'puntaje'   => $n->puntaje,
        ]);

        $promedio = $notas->pluck('puntaje')->filter(fn($v) => $v !== null)->avg();
        $promedio = $promedio ? round($promedio, 2) : 0;

        // ── Asistencias del alumno ──
        $user = DB::table('users')->where('email', $email)->first();
        $asistencia = ['presentes' => 0, 'total' => 0, 'pct' => 0];

        if ($user) {
            $sesiones      = Asistencia::where('materia_nrc', $nrc)->pluck('id');
            $totalSesiones = $sesiones->count();

            if ($totalSesiones > 0) {
                $registros = AsistenciaDetalle::whereIn('asistencia_id', $sesiones)
                                ->where('alumno_id', $user->id)
                                ->get();

                $presentes = $registros->whereIn('estatus', ['presente', 'justificado'])->count();
                $asistencia = [
                    'presentes' => $presentes,
                    'total'     => $totalSesiones,
                    'pct'       => round(($presentes / $totalSesiones) * 100, 1),
                ];
            }
        }

        // ── Datos manuales ──
        $datosExtra = $manual ? [
            'participacion'   => $manual->participacion ?? 0,
            'proyecto'        => $manual->proyecto ?? 0,
            'examen_u1'       => $manual->examen_u1 ?? 0,
            'examen_u2_u3'    => $manual->examen_u2_u3 ?? 0,
            'recuperacion_u1' => $manual->recuperacion_u1,
        ] : null;

        return response()->json([
            'nombre'         => $notas->first()->nombre_alumno ?? $email,
            'calificaciones' => $calificaciones,
            'promedio'       => $promedio,
            'asistencia'     => $asistencia,
            'datosExtra'     => $datosExtra,
        ]);
    }
}