<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\CalificacionFinal;
use App\Models\Notificacion;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActaExport;
use Illuminate\Support\Facades\DB;

class ActasController extends Controller
{
    public function index($nrc)
    {
        $materia = Materia::where('nrc', $nrc)->firstOrFail();

        $notasTodas = CalificacionFinal::where('materia_nrc', (string) $nrc)->get();

        $actividades = $notasTodas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
            ->pluck('actividad_nombre')
            ->unique()
            ->values();

        $alumnosData = [];
        $notasAgrupadas = $notasTodas->groupBy('email_alumno');

        foreach ($notasAgrupadas as $correo => $items) {
            $alumnosData[$correo] = [
                'nombre' => $items->first()->nombre_alumno,
                'notas'  => [],
                'manual' => $items->firstWhere('actividad_nombre', 'DATOS_MANUALES'),
            ];

            foreach ($items->where('actividad_nombre', '!=', 'DATOS_MANUALES') as $n) {
                $alumnosData[$correo]['notas'][$n->actividad_nombre] = $n->puntaje;
            }
        }

        return view('profesor.actas.index', [
            'materia'     => $materia,
            'actividades' => $actividades,
            'alumnos'     => $alumnosData,
        ]);
    }

    public function procesar(Request $request, $nrc)
    {
        $request->validate([
            'archivos_teams.*' => 'required|mimes:xlsx,xls,csv',
        ]);

        $materia = Materia::where('nrc', $nrc)->firstOrFail();

        // ── Emails de alumnos activos registrados vía HTM ──
        $alumnosHtm = DB::table('materia_estudiante')
            ->join('estudiantes', 'materia_estudiante.estudiante_id', '=', 'estudiantes.id')
            ->where('materia_estudiante.materia_nrc', $nrc)
            ->where('materia_estudiante.status', 'activo')
            ->select('estudiantes.nombre', 'estudiantes.codigo_estudiante', 'estudiantes.email')
            ->get()
            ->map(function ($e) {
                return [
                    'nombre' => $e->nombre,
                    'codigo' => $e->codigo_estudiante,
                    'email'  => strtolower(trim($e->email)),
                ];
            });

        // Índice email => codigo_estudiante para cruce rápido
        $indiceMatriculas = $alumnosHtm->pluck('codigo', 'email')->toArray();

        $seActualizo           = false;
        $actividadesProcesadas = [];
        $emailsEnExcel         = [];

        if ($request->hasFile('archivos_teams')) {
            foreach ($request->file('archivos_teams') as $archivo) {
                $data  = Excel::toArray([], $archivo);
                $filas = collect($data[0]);

                $encabezados = $filas->first(function ($item) {
                    return is_array($item) && in_array('Dirección de correo', $item);
                });

                if (!$encabezados) {
                    continue;
                }

                $colCorreo     = array_search('Dirección de correo', $encabezados);
                $colTarea      = array_search('Tareas', $encabezados);
                $colNombre     = array_search('Nombre completo', $encabezados);
                $colPorcentaje = array_search('Porcentaje', $encabezados);

                $datosLimpios = $filas->filter(function ($fila) use ($colCorreo) {
                    return isset($fila[$colCorreo]) && str_contains($fila[$colCorreo], '@');
                });

                foreach ($datosLimpios as $fila) {
                    $correo       = strtolower(trim($fila[$colCorreo] ?? ''));
                    $actividad    = trim($fila[$colTarea] ?? '');
                    $nombreAlumno = trim($fila[$colNombre] ?? '');

                    $emailsEnExcel[] = $correo;

                    $valorCelda   = $fila[$colPorcentaje] ?? 0;
                    $puntajeTeams = is_numeric($valorCelda) ? floatval($valorCelda) : 0;
                    $notaFinal    = round($puntajeTeams * 10, 1);

                    // Cruzar matrícula por email
                    $codigoEstudiante = $indiceMatriculas[$correo] ?? null;
                    $sinMatricula     = is_null($codigoEstudiante);

                    CalificacionFinal::updateOrCreate(
                        [
                            'materia_nrc'      => (string) $nrc,
                            'email_alumno'     => $correo,
                            'actividad_nombre' => $actividad,
                        ],
                        [
                            'nombre_alumno'     => $nombreAlumno,
                            'codigo_estudiante' => $codigoEstudiante,
                            'sin_matricula'     => $sinMatricula,
                            'puntaje'           => $notaFinal,
                            'fecha_actividad'   => now()->format('d/m/Y'),
                        ]
                    );

                    $seActualizo = true;

                    if ($actividad !== '') {
                        $actividadesProcesadas[] = $actividad;
                    }
                }
            }
        }

        // ── Validación: alumnos en HTM que NO están en el Excel ──
        $emailsEnExcel    = array_unique($emailsEnExcel);
        $faltantesEnExcel = $alumnosHtm->filter(function ($alumno) use ($emailsEnExcel) {
            return !in_array($alumno['email'], $emailsEnExcel);
        })->values()->toArray();

        if ($seActualizo) {
            $actividadesProcesadas = array_values(array_unique($actividadesProcesadas));

            $actividadNombre = count($actividadesProcesadas) === 1
                ? $actividadesProcesadas[0]
                : null;

            $this->notificarCalificacionesPublicadas($nrc, $actividadNombre);
        }

        // ── Detectar alumnos sin matrícula en el acta ──
        $sinMatriculaList = CalificacionFinal::where('materia_nrc', (string) $nrc)
            ->where('sin_matricula', true)
            ->select('email_alumno', 'nombre_alumno')
            ->distinct()
            ->get()
            ->map(fn($r) => [
                'email'  => $r->email_alumno,
                'nombre' => $r->nombre_alumno,
            ])->toArray();

        if (!empty($faltantesEnExcel)) {
            return redirect()
                ->route('profesor.actas.index', $nrc)
                ->with('success', 'Calificaciones actualizadas.')
                ->with('advertencia_faltantes', $faltantesEnExcel)
                ->with('nrc_faltantes', $nrc)
                ->with('sin_matricula_list', $sinMatriculaList);
        }

        if (!empty($sinMatriculaList)) {
            return redirect()
                ->route('profesor.actas.index', $nrc)
                ->with('success', 'Calificaciones actualizadas.')
                ->with('sin_matricula_list', $sinMatriculaList)
                ->with('nrc_faltantes', $nrc);
        }

        return redirect()
            ->route('profesor.actas.index', $nrc)
            ->with('success', 'Calificaciones actualizadas.');
    }

    public function guardarManual(Request $request, $nrc)
    {
        try {
            $request->validate([
                'email'  => 'required|email',
                'nombre' => 'required|string',
                'campo'  => 'required|string',
                'valor'  => 'nullable',
            ]);

            $campo = $request->campo;
            $valor = $request->valor === "" ? null : $request->valor;

            CalificacionFinal::updateOrCreate(
                [
                    'materia_nrc'      => (string) $nrc,
                    'email_alumno'     => $request->email,
                    'actividad_nombre' => 'DATOS_MANUALES',
                ],
                [
                    'nombre_alumno' => $request->nombre,
                    $campo          => $valor,
                ]
            );

            $this->notificarCalificacionManualAlumno($nrc, $request->email, $campo);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function exportar(Request $request, $nrc)
    {
        $materia       = Materia::where('nrc', $nrc)->firstOrFail();
        $todasLasNotas = CalificacionFinal::where('materia_nrc', (string) $nrc)->get();

        $listaActividades = $todasLasNotas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
            ->pluck('actividad_nombre')
            ->unique()
            ->values()
            ->toArray();

        $datosAlumnosFlat = [];
        $notasAgrupadas   = $todasLasNotas->groupBy('email_alumno');

        foreach ($notasAgrupadas as $correo => $items) {
            $manual = $items->firstWhere('actividad_nombre', 'DATOS_MANUALES');

            $fila = [
                'nombre' => $items->first()->nombre_alumno,
                'email'  => $correo,
                'notas_teams' => [],
                'manual' => [
                    'participacion'   => $manual->participacion ?? 0,
                    'proyecto'        => $manual->proyecto ?? 0,
                    'examen_u1'       => $manual->examen_u1 ?? 0,
                    'examen_u2_u3'    => $manual->examen_u2_u3 ?? 0,
                    'recuperacion_u1' => $manual->recuperacion_u1 ?? null,
                ],
            ];

            foreach ($listaActividades as $actividad) {
                $nota = $items->firstWhere('actividad_nombre', $actividad);
                $fila['notas_teams'][$actividad] = $nota->puntaje ?? 0;
            }

            $datosAlumnosFlat[] = $fila;
        }

        return Excel::download(
            new ActaExport($datosAlumnosFlat, $listaActividades, $materia),
            "Acta_Final_{$nrc}.xlsx"
        );
    }

    public function eliminarActividad($nrc, $actividad)
    {
        try {
            CalificacionFinal::where('materia_nrc', (string) $nrc)
                ->where('actividad_nombre', $actividad)
                ->delete();

            return redirect()->back()->with('success', "La actividad '{$actividad}' ha sido eliminada.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo eliminar la actividad.');
        }
    }

    public function eliminar($nrc)
    {
        try {
            CalificacionFinal::where('materia_nrc', (string) $nrc)->delete();
            return redirect()->back()->with('success', 'Se eliminaron todos los datos del acta.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo limpiar el acta.');
        }
    }

    public function procesarMatriculas(Request $request, $nrc)
    {
        $decisiones = $request->input('decisiones', []);

        $asignados = 0;
        $ignorados = 0;

        foreach ($decisiones as $email => $data) {
            $email  = strtolower(trim($email));
            $accion = $data['accion'] ?? 'ignorar';
            $codigo = $data['codigo'] ?? null;

            if ($accion === 'asignar' && $codigo && preg_match('/^\d{9}$/', $codigo)) {
                // Crear estudiante si no existe
                $existente = \App\Models\Estudiante::where('codigo_estudiante', $codigo)->first();
                if (!$existente) {
                    $nombreAlumno = CalificacionFinal::where('email_alumno', $email)
                        ->value('nombre_alumno');

                    \App\Models\Estudiante::create([
                        'nombre'            => $nombreAlumno ?? $email,
                        'email'             => $email,
                        'codigo_estudiante' => $codigo,
                        'clave_unica'       => \App\Models\Estudiante::generarClaveUnica(),
                    ]);
                }

                CalificacionFinal::where('materia_nrc', (string) $nrc)
                    ->where('email_alumno', $email)
                    ->update([
                        'codigo_estudiante' => $codigo,
                        'sin_matricula'     => false,
                    ]);

                $asignados++;
            } else {
                // Ignorar — queda en el acta sin matrícula
                CalificacionFinal::where('materia_nrc', (string) $nrc)
                    ->where('email_alumno', $email)
                    ->update(['sin_matricula' => false]);

                $ignorados++;
            }
        }

        $msg = '';
        if ($asignados > 0 && $ignorados > 0) {
            $msg = "Se asignó matrícula a {$asignados} alumno(s) y se ignoraron {$ignorados} alumno(s).";
        } elseif ($asignados > 0) {
            $msg = "Se asignó matrícula a {$asignados} alumno(s) correctamente.";
        } else {
            $msg = "Se ignoraron {$ignorados} alumno(s). Quedan en el acta sin matrícula vinculada.";
        }

        return redirect()
            ->route('profesor.actas.index', $nrc)
            ->with('success', $msg);
    }

    private function notificarCalificacionesPublicadas($nrc, $actividadNombre = null)
    {
        $materia = Materia::where('nrc', $nrc)->first();
        if (!$materia) return;

        $alumnosIds = DB::table('alumno_materia')
            ->where('materia_nrc', $nrc)
            ->where('status', 'activo')
            ->pluck('alumno_id');

        foreach ($alumnosIds as $alumnoId) {
            $yaExisteReciente = Notificacion::where('user_id', $alumnoId)
                ->where('tipo', 'calificacion')
                ->where('url', route('alumno.calificaciones', ['nrc' => $nrc]))
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if ($yaExisteReciente) continue;

            Notificacion::create([
                'user_id' => $alumnoId,
                'titulo'  => 'Nueva calificación publicada',
                'mensaje' => $actividadNombre
                    ? 'Se publicó una calificación nueva en "' . $actividadNombre . '" de la materia ' . $materia->Materia . '.'
                    : 'Se publicaron nuevas calificaciones en la materia ' . $materia->Materia . '.',
                'tipo'  => 'calificacion',
                'url'   => route('alumno.calificaciones', ['nrc' => $nrc]),
                'leida' => false,
            ]);
        }
    }

    private function notificarCalificacionManualAlumno($nrc, $emailAlumno, $campo = null)
    {
        $materia = Materia::where('nrc', $nrc)->first();
        if (!$materia) return;

        $alumnoId = DB::table('users')
            ->where('email', $emailAlumno)
            ->where('role', 'alumno')
            ->value('id');

        if (!$alumnoId) return;

        $yaExisteReciente = Notificacion::where('user_id', $alumnoId)
            ->where('tipo', 'calificacion')
            ->where('url', route('alumno.calificaciones', ['nrc' => $nrc]))
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($yaExisteReciente) return;

        $nombreCampo = $campo ? str_replace('_', ' ', $campo) : 'calificación';

        Notificacion::create([
            'user_id' => $alumnoId,
            'titulo'  => 'Calificación actualizada',
            'mensaje' => 'Tu profesor actualizó "' . $nombreCampo . '" en la materia ' . $materia->Materia . '.',
            'tipo'    => 'calificacion',
            'url'     => route('alumno.calificaciones', ['nrc' => $nrc]),
            'leida'   => false,
        ]);
    }
}