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
        $materia    = Materia::where('nrc', $nrc)->firstOrFail();
        $notasTodas = CalificacionFinal::where('materia_nrc', (string) $nrc)->get();

        // CORRECCIÓN: Mapear correctamente cada actividad con el tipo exacto que guardó el profesor
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
            'tipos'       => $tipos, // Enviamos el array con los tipos reales indexados por nombre
        ]);
    }

    public function procesar(Request $request, $nrc)
    {
        // 1. Forzar limpieza y captura del tipo del Request
        $tipo = strtolower(trim((string) $request->input('tipo')));
        $archivo = $request->file('archivo');
        $materia = Materia::where('nrc', $nrc)->firstOrFail();

        if (!$tipo || $tipo === '-- selecciona el tipo --' || $tipo === '') {
            return redirect()->back()->with('error', 'Por favor, selecciona un tipo de actividad válido en el menú antes de cargar el archivo.');
        }

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
        $colNombre         = array_search('Nombre completo',     $encabezados);
        $colCorreo         = array_search('Dirección de correo', $encabezados);
        $colTarea          = array_search('Tareas',              $encabezados);
        $colPorcentaje     = array_search('Porcentaje',          $encabezados);

        $datosAlumnos = $filas->slice($indiceEncabezados + 1);

        $emailsEnExcel = [];
        $actividad     = null;

        foreach ($datosAlumnos as $fila) {
            if (!isset($fila[$colCorreo]) || !str_contains($fila[$colCorreo], '@')) continue;

            $correo       = strtolower(trim($fila[$colCorreo]));
            $nombreAlumno = trim($fila[$colNombre]);
            
            // Conservamos el nombre descriptivo que viene de Teams para la cabecera
            $actividad    = isset($fila[$colTarea]) ? trim($fila[$colTarea]) : 'Actividad sin nombre';

            $emailsEnExcel[] = $correo;

            $valorRaw      = $fila[$colPorcentaje] ?? 0;
            $puntajeLimpio = str_replace('%', '', $valorRaw);
            $puntajeTeams  = is_numeric($puntajeLimpio) ? floatval($puntajeLimpio) : 0;
            $notaFinal     = ($puntajeTeams > 1) ? ($puntajeTeams / 10) : ($puntajeTeams * 10);
            $notaFinal     = round($notaFinal, 1);

            $codigoEstudiante = $indiceMatriculas[$correo] ?? null;

            if (!$codigoEstudiante) {
                $codigoEstudiante = CalificacionFinal::where('email_alumno', $correo)
                    ->whereNotNull('codigo_estudiante')
                    ->value('codigo_estudiante');
            }

            $sinMatricula = is_null($codigoEstudiante);

            // 2. BLINDAJE EXPLICITO: Separamos la asignación de búsqueda de la actualización masiva
            $registro = CalificacionFinal::where('materia_nrc', (string) $nrc)
                ->where('email_alumno', $correo)
                ->where('actividad_nombre', $actividad)
                ->first();

            if (!$registro) {
                $registro = new CalificacionFinal();
                $registro->materia_nrc      = (string) $nrc;
                $registro->email_alumno     = $correo;
                $registro->actividad_nombre = $actividad;
            }

            // Asignación directa e inflexible del tipo
            $registro->nombre_alumno     = $nombreAlumno;
            $registro->puntaje           = $notaFinal;
            $registro->tipo_actividad    = $tipo; // <── AQUÍ SE FUERZA EL VALOR REAL EN CADA ITERACIÓN
            $registro->codigo_estudiante = $codigoEstudiante;
            $registro->sin_matricula     = $sinMatricula;
            $registro->fecha_actividad   = now()->format('d/m/Y H:i');
            
            $registro->save();
        }

        $emailsEnExcel    = array_unique($emailsEnExcel);
        $faltantesEnExcel = $alumnosHtm->filter(fn($a) => !in_array($a['email'], $emailsEnExcel))->values()->toArray();

        $sinMatriculaList = CalificacionFinal::where('materia_nrc', (string) $nrc)
            ->where('sin_matricula', true)
            ->select('email_alumno', 'nombre_alumno')
            ->distinct()
            ->get()
            ->map(fn($r) => ['email' => $r->email_alumno, 'nombre' => $r->nombre_alumno])
            ->toArray();

        $this->notificarCalificacionesPublicadas($nrc, $actividad);

        $tipoLabel = match ($tipo) {
            'tarea'         => 'Tareas',
            'practica'      => 'Prácticas',
            'participacion' => 'Participaciones',
            'examen'        => 'Exámenes',
            'recuperacion'  => 'Recuperaciones',
            'proyecto'      => 'Proyectos',
            default         => ucfirst($tipo),
        };

        // 3. OBLIGAR a que la búsqueda posterior use el tipo actual y no basura histórica
        $actividadCargada = $actividad; 

        $htmSinCalificacion = [];
        if ($actividadCargada) {
            $emailsConNota = CalificacionFinal::where('materia_nrc', (string) $nrc)
                ->where('actividad_nombre', $actividadCargada)
                ->pluck('email_alumno')
                ->map(fn($e) => strtolower(trim($e)))
                ->toArray();

            foreach ($alumnosHtm as $alumnoHtm) {
                if (!in_array($alumnoHtm['email'], $emailsConNota)) {
                    $htmSinCalificacion[] = $alumnoHtm;
                }
            }
        }

        // ── Aplicar recuperación B+D ──
        $examenRecupera = $request->input('examen_recupera');
        if ($tipo === 'recuperacion' && $examenRecupera) {

            $notasRecuperacion = CalificacionFinal::where('materia_nrc', (string) $nrc)
                ->where('actividad_nombre', $actividadCargada)
                ->whereIn('email_alumno', $emailsEnExcel)
                ->pluck('puntaje', 'email_alumno');

            $notesExamen = CalificacionFinal::where('materia_nrc', (string) $nrc)
                ->where('actividad_nombre', $examenRecupera)
                ->whereIn('email_alumno', $emailsEnExcel)
                ->pluck('puntaje', 'email_alumno');

            foreach ($emailsEnExcel as $emailRec) {
                $notaRecup  = $notasRecuperacion[$emailRec] ?? null;
                $notaExamen = $notesExamen[$emailRec]       ?? null;

                if ($notaExamen !== null && $notaExamen < 6 && $notaRecup !== null) {
                    CalificacionFinal::where('materia_nrc', (string) $nrc)
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
            $valor = $request->valor === '' ? null : $request->valor;

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

        $matriculasHtm = DB::table('materia_estudiante')
            ->join('estudiantes', 'materia_estudiante.estudiante_id', '=', 'estudiantes.id')
            ->where('materia_estudiante.materia_nrc', $nrc)
            ->select('estudiantes.codigo_estudiante', 'estudiantes.email')
            ->get()
            ->pluck('codigo_estudiante', 'email')
            ->mapWithKeys(fn($item, $key) => [strtolower(trim($key)) => $item])
            ->toArray();

        $listaActividades = $todasLasNotas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
            ->pluck('actividad_nombre')->unique()->values()->toArray();

        $datosAlumnosFlat = [];
        foreach ($todasLasNotas->groupBy('email_alumno') as $correo => $items) {
            $correoLimpio = strtolower(trim($correo));
            $manual = $items->firstWhere('actividad_nombre', 'DATOS_MANUALES');
            
            $fila   = [
                'matricula'   => $matriculasHtm[$correoLimpio] ?? $items->first()->codigo_estudiante ?? 'SIN MATRÍCULA', // ── Matrícula del HTM
                'nombre'      => $items->first()->nombre_alumno,
                'email'       => $correo,
                'notas_teams' => [],
                'manual'      => [
                    'participacion'   => $manual->participacion   ?? 0,
                    'proyecto'        => $manual->proyecto        ?? 0,
                    'examen_u1'       => $manual->examen_u1       ?? 0,
                    'examen_u2_u3'    => $manual->examen_u2_u3    ?? 0,
                    'recuperacion_u1' => $manual->recuperacion_u1 ?? null,
                ],
            ];

            // Corrección de typo en tu arreglo original: tenías 'notes_teams' en la definición y 'notas_teams' en el ciclo
            foreach ($listaActividades as $act) {
                $fila['notas_teams'][$act] = $items->firstWhere('actividad_nombre', $act)->puntaje ?? 0;
            }
            $datosAlumnosFlat[] = $fila;
        }

        // Asegurar el mapa de tipos con consistencia absoluta basados en lo guardado
        $tiposActividades = [];
        foreach ($todasLasNotas->where('actividad_nombre', '!=', 'DATOS_MANUALES') as $nota) {
            $tiposActividades[trim($nota->actividad_nombre)] = $nota->tipo_actividad ?? 'tarea';
            $tiposActividades[strtolower(trim($nota->actividad_nombre))] = $nota->tipo_actividad ?? 'tarea';
        }

        $ponderaciones = [
            'participacion' => (float) ($request->input('w_part',   10)),
            'tarea'         => (float) ($request->input('w_tareas', 10)),
            'practica'      => (float) ($request->input('w_prac',   20)),
            'proyecto'      => (float) ($request->input('w_proy',   40)),
            'examen'        => (float) ($request->input('w_exam',   20)),
            'recuperacion'  => (float) ($request->input('w_exam',   20)),
        ];

        return Excel::download(
            new ActaExport($datosAlumnosFlat, $listaActividades, $materia, $tiposActividades, $ponderaciones),
            "Acta_Final_{$nrc}.xlsx"
        );
    }

    public function exportarOficial(Request $request, $nrc)
    {
        $todasLasNotas = CalificacionFinal::where('materia_nrc', (string) $nrc)->get();
        
        $matriculasHtm = DB::table('materia_estudiante')
            ->join('estudiantes', 'materia_estudiante.estudiante_id', '=', 'estudiantes.id')
            ->where('materia_estudiante.materia_nrc', $nrc)
            ->select('estudiantes.codigo_estudiante', 'estudiantes.email')
            ->get()
            ->pluck('codigo_estudiante', 'email')
            ->mapWithKeys(fn($item, $key) => [strtolower(trim($key)) => $item])
            ->toArray();

        $listaActividades = $todasLasNotas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
            ->pluck('actividad_nombre')->unique()->values()->toArray();

        $datosAlumnosFlat = [];
        
        foreach ($todasLasNotas->groupBy('email_alumno') as $correo => $items) {
            $correoLimpio = strtolower(trim($correo));
            
            // Buscamos el registro manual ignorando espacios extras o diferencias de caja
            $manual = $items->first(function($item) {
                return trim(strtoupper($item->actividad_nombre)) === 'DATOS_MANUALES';
            });
            
            $fila = [
                'matricula'   => $matriculasHtm[$correoLimpio] ?? $items->first()->codigo_estudiante ?? 'SIN MATRÍCULA',
                'nombre'      => $items->first()->nombre_alumno,
                'email'       => $correo,
                'notas_teams' => [],
                'manual'      => [
                    // Forzamos la conversión a flotante pura para que el Excel lea números y no ceros o nulos
                    'participacion'   => $manual ? (float)($manual->participacion ?? 0) : 0.0,
                    'proyecto'        => $manual ? (float)($manual->proyecto ?? 0) : 0.0,
                    'examen_u1'       => $manual ? (float)($manual->examen_u1 ?? 0) : 0.0,
                    'examen_u2_u3'    => $manual ? (float)($manual->examen_u2_u3 ?? 0) : 0.0,
                    'recuperacion_u1' => ($manual && $manual->recuperacion_u1 !== null && $manual->recuperacion_u1 !== '') ? (float)$manual->recuperacion_u1 : null,
                ],
            ];
            
            // Poblamos las actividades de Teams asegurando valor numérico
            foreach ($listaActividades as $act) {
                $actividadReg = $items->firstWhere('actividad_nombre', $act);
                $fila['notas_teams'][$act] = $actividadReg ? (float)($actividadReg->puntaje ?? 0) : 0.0;
            }
            
            $datosAlumnosFlat[] = $fila;
        }
        return Excel::download(new ActaOficialExport($datosAlumnosFlat), "ACTA_OFICIAL_{$nrc}.xlsx");
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
            ->distinct()
            ->get()
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
                $alumno['clave_unica']       = $est->clave_unica       ?? null;
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
            $actividad = $request->input('actividad'); // El identificador que usas (ej: 'examen', 'tarea')
            $valor     = $request->input('valor');
            $nombre    = $request->input('nombre');
            
            // RECUPERAR EL TIPO: Si no viene explícito, lo igualamos a $actividad 
            // para mantener la concordancia de que donde se asigna la columna manda el tipo
            $tipo      = $request->input('tipo', $actividad); 

            $registro = CalificacionFinal::firstOrNew([
                'materia_nrc'      => (string) $nrc,
                'email_alumno'     => $email,
                'actividad_nombre' => $actividad,
            ]);

            $registro->puntaje        = is_numeric($valor) ? round((float) $valor, 2) : 0;
            $registro->nombre_alumno  = $nombre ?? $email;
            
            // ASIGNACIÓN DEL TIPO DE ACTIVIDAD PARA MANTENER LA CONSISTENCIA
            $registro->tipo_actividad = $tipo; 

            // Recuperar matrícula si el registro es nuevo y ya existe en la base de datos
            if (!$registro->exists && !$registro->codigo_estudiante) {
                $codigo = DB::table('materia_estudiante')
                    ->join('estudiantes', 'materia_estudiante.estudiante_id', '=', 'estudiantes.id')
                    ->where('materia_estudiante.materia_nrc', $nrc)
                    ->where(DB::raw('lower(trim(estudiantes.email))'), $email)
                    ->value('estudiantes.codigo_estudiante');

                if (!$codigo) {
                    $codigo = CalificacionFinal::where('email_alumno', $email)
                        ->whereNotNull('codigo_estudiante')
                        ->value('codigo_estudiante');
                }

                $registro->codigo_estudiante = $codigo;
                $registro->sin_matricula     = is_null($codigo);
            }

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
            $email    = strtolower(trim($email));
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
            ->with('success', 'Se asignó 0 a ' . count($alumnos) . " alumno(s) en '{$actividad}'. Puedes modificar las calificaciones en la tabla.");
    }

    private function notificarCalificacionesPublicadas($nrc, $actividadNombre = null)
    {
        $materia = Materia::where('nrc', $nrc)->first();
        if (!$materia) return;

        $alumnosIds = DB::table('materia_estudiante')
            ->where('materia_nrc', $nrc)
            ->where('status', 'activo')
            ->pluck('estudiante_id');

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
        $materia = Materia::where('nrc', $nrc)->first();
        if (!$materia) return;

        $alumnoId = DB::table('users')
            ->where('email', $emailAlumno)
            ->where('role', 'alumno')
            ->value('id');

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