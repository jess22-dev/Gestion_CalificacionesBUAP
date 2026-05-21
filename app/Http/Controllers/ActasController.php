<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\CalificacionFinal;
use App\Models\Notificacion;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActaExport;
use App\Exports\ActaOficialExport;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ActasController extends Controller
{
    public function index($nrc)
    {
        $materia = Materia::where('nrc', $nrc)->firstOrFail();

        $notasTodas = CalificacionFinal::where('materia_nrc', (string) $nrc)->get();

        $tipos = $notasTodas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
                            ->pluck('tipo_actividad', 'actividad_nombre')
                            ->toArray();

        $actividades = $notasTodas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
                                  ->pluck('actividad_nombre')
                                  ->unique()
                                  ->values();

        $alumnosData    = [];
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
            'tipos'       => $tipos,
        ]);
    }

    public function procesar(Request $request, $nrc)
    {
        $tipo    = $request->input('tipo');
        $archivo = $request->file('archivo');

        $materia = Materia::where('nrc', $nrc)->firstOrFail();

        // ── Emails de alumnos activos registrados vía HTM ──
        $alumnosHtm = DB::table('materia_estudiante')
            ->join('estudiantes', 'materia_estudiante.estudiante_id', '=', 'estudiantes.id')
            ->where('materia_estudiante.materia_nrc', $nrc)
            ->where('materia_estudiante.status', 'activo')
            ->select('estudiantes.nombre', 'estudiantes.codigo_estudiante', 'estudiantes.email')
            ->get()
            ->map(fn($e) => [
                'nombre' => $e->nombre,
                'codigo' => $e->codigo_estudiante,
                'email'  => strtolower(trim($e->email)),
            ]);

        $indiceMatriculas = $alumnosHtm->pluck('codigo', 'email')->toArray();

        $data  = Excel::toArray([], $archivo);
        $filas = collect($data[0]);

        $encabezados = $filas->first(fn($fila) => is_array($fila) && in_array('Dirección de correo', $fila));

        if (!$encabezados) {
            return redirect()->back()->with('error', 'No se encontró la estructura de Teams (Falta columna Dirección de correo).');
        }

        $indiceEncabezados = $filas->search($encabezados);
        $colNombre     = array_search('Nombre completo',    $encabezados);
        $colCorreo     = array_search('Dirección de correo', $encabezados);
        $colTarea      = array_search('Tareas',             $encabezados);
        $colPorcentaje = array_search('Porcentaje',         $encabezados);

        $datosAlumnos  = $filas->slice($indiceEncabezados + 1);
        $emailsEnExcel = [];

        foreach ($datosAlumnos as $fila) {
            if (!isset($fila[$colCorreo]) || !str_contains($fila[$colCorreo], '@')) continue;

            $correo       = strtolower(trim($fila[$colCorreo]));
            $nombreAlumno = trim($fila[$colNombre]);
            $actividad    = trim($fila[$colTarea]);

            $emailsEnExcel[] = $correo;

            $valorRaw      = $fila[$colPorcentaje] ?? 0;
            $puntajeLimpio = str_replace('%', '', $valorRaw);
            $puntajeTeams  = is_numeric($puntajeLimpio) ? floatval($puntajeLimpio) : 0;
            $notaFinal     = ($puntajeTeams > 1) ? ($puntajeTeams / 10) : ($puntajeTeams * 10);
            $notaFinal     = round($notaFinal, 1);

            $codigoEstudiante = $indiceMatriculas[$correo] ?? null;

            // Si no encontramos en HTM, buscar en BD por si ya fue asignado antes
            if (!$codigoEstudiante) {
                $codigoEstudiante = CalificacionFinal::where('email_alumno', $correo)
                    ->whereNotNull('codigo_estudiante')
                    ->value('codigo_estudiante');
            }

            $sinMatricula = is_null($codigoEstudiante);

            CalificacionFinal::updateOrCreate(
                [
                    'materia_nrc'      => (string) $nrc,
                    'email_alumno'     => $correo,
                    'actividad_nombre' => $actividad,
                ],
                [
                    'nombre_alumno'     => $nombreAlumno,
                    'puntaje'           => $notaFinal,
                    'tipo_actividad'    => $tipo,
                    'codigo_estudiante' => $codigoEstudiante,
                    'sin_matricula'     => $sinMatricula,
                    'fecha_actividad'   => now()->format('d/m/Y H:i'),
                ]
            );
        }

        // ── Validación: alumnos en HTM que NO están en el Excel ──
        $emailsEnExcel    = array_unique($emailsEnExcel);
        $faltantesEnExcel = $alumnosHtm->filter(fn($a) => !in_array($a['email'], $emailsEnExcel))->values()->toArray();

        // ── Detectar alumnos sin matrícula ──
        $sinMatriculaList = CalificacionFinal::where('materia_nrc', (string) $nrc)
            ->where('sin_matricula', true)
            ->select('email_alumno', 'nombre_alumno')
            ->distinct()
            ->get()
            ->map(fn($r) => ['email' => $r->email_alumno, 'nombre' => $r->nombre_alumno])
            ->toArray();

        $this->notificarCalificacionesPublicadas($nrc, $actividad ?? null);

        $tipoLabel = match($tipo) {
            'tarea'         => 'Tareas',
            'practica'      => 'Prácticas',
            'participacion' => 'Participaciones',
            'examen'        => 'Exámenes',
            'recuperacion'  => 'Recuperaciones',
            'proyecto'      => 'Proyectos',
            default         => ucfirst($tipo),
        };

        // ── Opción C+D: detectar alumnos HTM sin calificación en esta actividad ──
        $actividadCargada = null;
        foreach ($emailsEnExcel as $emailEx) {
            $reg = \App\Models\CalificacionFinal::where('materia_nrc', (string)$nrc)
                ->where('email_alumno', $emailEx)
                ->where('actividad_nombre', '!=', 'DATOS_MANUALES')
                ->orderByDesc('updated_at')->first();
            if ($reg) { $actividadCargada = $reg->actividad_nombre; break; }
        }

        $htmSinCalificacion = [];
        if ($actividadCargada) {
            $emailsConNota = \App\Models\CalificacionFinal::where('materia_nrc', (string)$nrc)
                ->where('actividad_nombre', $actividadCargada)
                ->pluck('email_alumno')->map(fn($e) => strtolower(trim($e)))->toArray();

            foreach ($alumnosHtm as $alumnoHtm) {
                if (!in_array($alumnoHtm['email'], $emailsConNota)) {
                    $htmSinCalificacion[] = $alumnoHtm;
                }
            }
        }

        // ── Aplicar recuperación B+D ──
        $examenRecupera = $request->input('examen_recupera');
        if ($tipo === 'recuperacion' && $examenRecupera) {
            foreach ($emailsEnExcel as $emailRec) {
                $notaRecup = \App\Models\CalificacionFinal::where('materia_nrc', (string)$nrc)
                    ->where('email_alumno', $emailRec)
                    ->where('actividad_nombre', $actividadCargada)
                    ->value('puntaje');

                $notaExamen = \App\Models\CalificacionFinal::where('materia_nrc', (string)$nrc)
                    ->where('email_alumno', $emailRec)
                    ->where('actividad_nombre', $examenRecupera)
                    ->value('puntaje');

                // Opción D: solo sustituir si reprobó el examen original
                if ($notaExamen !== null && $notaExamen < 6 && $notaRecup !== null) {
                    \App\Models\CalificacionFinal::where('materia_nrc', (string)$nrc)
                        ->where('email_alumno', $emailRec)
                        ->where('actividad_nombre', $examenRecupera)
                        ->update(['puntaje' => $notaRecup]);
                }
            }
        }

        $redirect = redirect()->route('profesor.actas.index', $nrc)
            ->with('success', "Se cargaron las {$tipoLabel} correctamente.");

        if (!empty($faltantesEnExcel)) {
            $redirect = $redirect->with('advertencia_faltantes', $faltantesEnExcel);
        }

        if (!empty($sinMatriculaList)) {
            $redirect = $redirect->with('sin_matricula_list', $sinMatriculaList);
        }

        if (!empty($htmSinCalificacion)) {
            $redirect = $redirect
                ->with('htm_sin_calificacion', $htmSinCalificacion)
                ->with('actividad_cargada', $actividadCargada)
                ->with('tipo_cargado', $tipo);
        }

        return $redirect;
    }

    public function guardarManual(Request $request, $nrc)
    {
        try {
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
            ->pluck('actividad_nombre')->unique()->values()->toArray();

        $datosAlumnosFlat = [];
        foreach ($todasLasNotas->groupBy('email_alumno') as $correo => $items) {
            $manual = $items->firstWhere('actividad_nombre', 'DATOS_MANUALES');
            $fila   = [
                'nombre'      => $items->first()->nombre_alumno,
                'email'       => $correo,
                'notas_teams' => [],
                'manual'      => [
                    'participacion'   => $manual->participacion ?? 0,
                    'proyecto'        => $manual->proyecto ?? 0,
                    'examen_u1'       => $manual->examen_u1 ?? 0,
                    'examen_u2_u3'    => $manual->examen_u2_u3 ?? 0,
                    'recuperacion_u1' => $manual->recuperacion_u1 ?? null,
                ],
            ];
            foreach ($listaActividades as $act) {
                $fila['notas_teams'][$act] = $items->firstWhere('actividad_nombre', $act)->puntaje ?? 0;
            }
            $datosAlumnosFlat[] = $fila;
        }

        // Construir mapa de tipos normalizando claves y valores nulos
        $tiposActividades = [];
        foreach ($todasLasNotas->where('actividad_nombre', '!=', 'DATOS_MANUALES') as $nota) {
            $clave = strtolower(trim($nota->actividad_nombre));
            $tiposActividades[$clave] = $nota->tipo_actividad ?? 'tarea';
        }
        // También mantener las claves originales para compatibilidad
        foreach ($todasLasNotas->where('actividad_nombre', '!=', 'DATOS_MANUALES') as $nota) {
            $tiposActividades[trim($nota->actividad_nombre)] = $nota->tipo_actividad ?? 'tarea';
        }

        // Ponderaciones desde la request (o valores por defecto)
        $ponderaciones = [
            'participacion' => (float)($request->input('w_part',  10)),
            'tarea'         => (float)($request->input('w_tareas', 10)),
            'practica'      => (float)($request->input('w_prac',  20)),
            'proyecto'      => (float)($request->input('w_proy',  40)),
            'examen'        => (float)($request->input('w_exam',  20)),
            'recuperacion'  => (float)($request->input('w_exam',  20)),
        ];

        return Excel::download(
            new ActaExport($datosAlumnosFlat, $listaActividades, $materia, $tiposActividades, $ponderaciones),
            "Acta_Final_{$nrc}.xlsx"
        );
    }

    public function exportarOficial(Request $request, $nrc)
    {
        $todasLasNotas    = CalificacionFinal::where('materia_nrc', (string) $nrc)->get();
        $listaActividades = $todasLasNotas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
            ->pluck('actividad_nombre')->unique()->values()->toArray();

        $datosAlumnosFlat = [];
        foreach ($todasLasNotas->groupBy('email_alumno') as $correo => $items) {
            $manual = $items->firstWhere('actividad_nombre', 'DATOS_MANUALES');
            $fila   = [
                'nombre'      => $items->first()->nombre_alumno,
                'email'       => $correo,
                'notas_teams' => [],
                'manual'      => [
                    'participacion'   => $manual->participacion ?? 0,
                    'proyecto'        => $manual->proyecto ?? 0,
                    'examen_u1'       => $manual->examen_u1 ?? 0,
                    'examen_u2_u3'    => $manual->examen_u2_u3 ?? 0,
                    'recuperacion_u1' => $manual->recuperacion_u1 ?? null,
                ],
            ];
            foreach ($listaActividades as $act) {
                $fila['notas_teams'][$act] = $items->firstWhere('actividad_nombre', $act)->puntaje ?? 0;
            }
            $datosAlumnosFlat[] = $fila;
        }

        return Excel::download(new ActaOficialExport($datosAlumnosFlat), "ACTA_OFICIAL_{$nrc}.xlsx");
    }

    public function eliminarActividad($nrc, $actividad)
    {
        try {
            CalificacionFinal::where('materia_nrc', (string) $nrc)
                ->where('actividad_nombre', $actividad)->delete();
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
        $asignados  = 0;
        $ignorados  = 0;

        foreach ($decisiones as $email => $data) {
            $email  = strtolower(trim($email));
            $accion = $data['accion'] ?? 'ignorar';
            $codigo = $data['codigo'] ?? null;

            if ($accion === 'asignar' && $codigo && preg_match('/^\d{9}$/', $codigo)) {
                $existente = \App\Models\Estudiante::where('codigo_estudiante', $codigo)->first();
                if (!$existente) {
                    $nombreAlumno = CalificacionFinal::where('email_alumno', $email)->value('nombre_alumno');
                    \App\Models\Estudiante::create([
                        'nombre'            => $nombreAlumno ?? $email,
                        'email'             => $email,
                        'codigo_estudiante' => $codigo,
                        'clave_unica'       => \App\Models\Estudiante::generarClaveUnica(),
                    ]);
                }
                CalificacionFinal::where('materia_nrc', (string) $nrc)
                    ->where('email_alumno', $email)
                    ->update(['codigo_estudiante' => $codigo, 'sin_matricula' => false]);
                $asignados++;
            } else {
                CalificacionFinal::where('materia_nrc', (string) $nrc)
                    ->where('email_alumno', $email)
                    ->update(['sin_matricula' => false]);
                $ignorados++;
            }
        }

        $msg = match (true) {
            $asignados > 0 && $ignorados > 0 => "Se asignó matrícula a {$asignados} alumno(s) y se ignoraron {$ignorados}.",
            $asignados > 0 => "Se asignó matrícula a {$asignados} alumno(s) correctamente.",
            default => "Se ignoraron {$ignorados} alumno(s). Quedan en el acta sin matrícula vinculada.",
        };

        return redirect()->route('profesor.actas.index', $nrc)->with('success', $msg);
    }

    public function descargarListaAcceso($nrc)
    {
        $materia = Materia::where('nrc', $nrc)->firstOrFail();

        $alumnosHtm = DB::table('materia_estudiante')
            ->join('estudiantes', 'materia_estudiante.estudiante_id', '=', 'estudiantes.id')
            ->where('materia_estudiante.materia_nrc', $nrc)
            ->where('materia_estudiante.status', 'activo')
            ->select('estudiantes.nombre', 'estudiantes.email', 'estudiantes.codigo_estudiante', 'estudiantes.clave_unica')
            ->get()
            ->mapWithKeys(fn($e) => [strtolower(trim($e->email)) => [
                'nombre'            => $e->nombre,
                'email'             => strtolower(trim($e->email)),
                'codigo_estudiante' => $e->codigo_estudiante,
                'clave_unica'       => $e->clave_unica,
            ]]);

        $alumnosExcel = CalificacionFinal::where('materia_nrc', (string) $nrc)
            ->select('nombre_alumno', 'email_alumno', 'codigo_estudiante')
            ->distinct()->get()
            ->mapWithKeys(fn($r) => [strtolower(trim($r->email_alumno)) => [
                'nombre'            => $r->nombre_alumno,
                'email'             => strtolower(trim($r->email_alumno)),
                'codigo_estudiante' => $r->codigo_estudiante,
                'clave_unica'       => null,
            ]]);

        $alumnos = collect();
        foreach ($alumnosHtm as $email => $alumno) {
            $alumnos->push($alumno);
        }
        foreach ($alumnosExcel as $email => $alumno) {
            if (!$alumnosHtm->has($email)) {
                $est = DB::table('estudiantes')->where('email', $email)->first();
                $alumno['clave_unica']       = $est->clave_unica ?? null;
                $alumno['codigo_estudiante'] = $alumno['codigo_estudiante'] ?? ($est->codigo_estudiante ?? null);
                $alumnos->push($alumno);
            }
        }

        $alumnos = $alumnos->sortBy('nombre')->values()->toArray();
        $pdf     = Pdf::loadView('profesor.actas.lista_acceso_pdf', compact('materia', 'alumnos'))
                      ->setPaper('letter', 'portrait');

        return $pdf->download("Lista_Acceso_{$nrc}.pdf");
    }

    public function guardarNota(Request $request, $nrc)
    {
        try {
            $email     = strtolower(trim($request->input('email')));
            $actividad = $request->input('actividad');
            $valor     = $request->input('valor');
            $nombre    = $request->input('nombre');

            $registro = CalificacionFinal::firstOrNew([
                'materia_nrc'      => (string) $nrc,
                'email_alumno'     => $email,
                'actividad_nombre' => $actividad,
            ]);

            $registro->puntaje       = is_numeric($valor) ? round((float)$valor, 2) : 0;
            $registro->nombre_alumno = $nombre ?? $email;
            if (!$registro->fecha_actividad) {
                $registro->fecha_actividad = now()->format('d/m/Y H:i');
            }
            $registro->save();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function asignarCero(Request $request, $nrc)
    {
        $actividad = $request->input('actividad');
        $tipo      = $request->input('tipo', 'tarea');
        $alumnos   = $request->input('alumnos', []);
        $nombres   = $request->input('nombres', []);

        foreach ($alumnos as $email) {
            $email = strtolower(trim($email));
            $registro = CalificacionFinal::firstOrNew([
                'materia_nrc'      => (string) $nrc,
                'email_alumno'     => $email,
                'actividad_nombre' => $actividad,
            ]);
            $registro->nombre_alumno   = $nombres[$email] ?? $email;
            $registro->puntaje         = 0;
            $registro->tipo_actividad  = $tipo;
            $registro->fecha_actividad = now()->format('d/m/Y H:i');
            if (!$registro->codigo_estudiante) {
                $registro->codigo_estudiante = \App\Models\Estudiante::where('email', $email)->value('codigo_estudiante');
            }
            $registro->sin_matricula = is_null($registro->codigo_estudiante);
            $registro->save();
        }

        return redirect()->route('profesor.actas.index', $nrc)
            ->with('success', "Se asignó 0 a " . count($alumnos) . " alumno(s) en '{$actividad}'. Puedes modificar las calificaciones en la tabla.");
    }

    private function notificarCalificacionesPublicadas($nrc, $actividadNombre = null)
    {
        $materia = Materia::where('nrc', $nrc)->first();
        if (!$materia) return;

        $alumnosIds = DB::table('alumno_materia')
            ->where('materia_nrc', $nrc)->where('status', 'activo')->pluck('alumno_id');

        foreach ($alumnosIds as $alumnoId) {
            $yaExiste = Notificacion::where('user_id', $alumnoId)
                ->where('tipo', 'calificacion')
                ->where('url', route('alumno.calificaciones', ['nrc' => $nrc]))
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();
            if ($yaExiste) continue;

            Notificacion::create([
                'user_id' => $alumnoId,
                'titulo'  => 'Nueva calificación publicada',
                'mensaje' => $actividadNombre
                    ? "Se publicó \"{$actividadNombre}\" en {$materia->Materia}."
                    : "Se publicaron calificaciones en {$materia->Materia}.",
                'tipo'  => 'calificacion',
                'url'   => route('alumno.calificaciones', ['nrc' => $nrc]),
                'leida' => false,
            ]);
        }
    }

    private function notificarCalificacionManualAlumno($nrc, $emailAlumno, $campo = null)
    {
        $materia  = Materia::where('nrc', $nrc)->first();
        if (!$materia) return;

        $alumnoId = DB::table('users')->where('email', $emailAlumno)->where('role', 'alumno')->value('id');
        if (!$alumnoId) return;

        $yaExiste = Notificacion::where('user_id', $alumnoId)
            ->where('tipo', 'calificacion')
            ->where('url', route('alumno.calificaciones', ['nrc' => $nrc]))
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
        if ($yaExiste) return;

        Notificacion::create([
            'user_id' => $alumnoId,
            'titulo'  => 'Calificación actualizada',
            'mensaje' => 'Tu profesor actualizó "' . str_replace('_', ' ', $campo ?? 'calificación') . '" en ' . $materia->Materia . '.',
            'tipo'    => 'calificacion',
            'url'     => route('alumno.calificaciones', ['nrc' => $nrc]),
            'leida'   => false,
        ]);
    }
}