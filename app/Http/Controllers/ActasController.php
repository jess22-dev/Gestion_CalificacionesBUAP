<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\CalificacionFinal; 
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActaExport;

class ActasController extends Controller
{
    public function index($nrc)
    {
        $materia = Materia::where('nrc', $nrc)->firstOrFail();
        
        $notasTodas = CalificacionFinal::where('materia_nrc', (string)$nrc)->get();

        // --- ESTA ES LA PARTE CLAVE ---
        // Creamos un mapa de 'Nombre Actividad' => 'tipo' (tarea/practica)
        // Esto servirá para los encabezados y para las clases CSS
        $tipos = $notasTodas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
                            ->pluck('tipo_actividad', 'actividad_nombre')
                            ->toArray();
        // ------------------------------

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
                'manual' => $items->firstWhere('actividad_nombre', 'DATOS_MANUALES')
            ];

            foreach ($items->where('actividad_nombre', '!=', 'DATOS_MANUALES') as $n) {
                $alumnosData[$correo]['notas'][$n->actividad_nombre] = $n->puntaje;
            }
        }

        return view('profesor.actas.index', [
            'materia'     => $materia,
            'actividades' => $actividades,
            'alumnos'     => $alumnosData,
            'tipos'       => $tipos, // <--- ENVIAMOS LA VARIABLE AQUÍ
        ]);
    }
    public function procesar(Request $request, $nrc)
    {
        $tipo = $request->input('tipo');
        $archivo = $request->file('archivo');
        $data = Excel::toArray([], $archivo);
        $filas = collect($data[0]);

        // 1. Buscamos en qué fila están los encabezados (normalmente es la fila 2 en Teams)
        $indiceEncabezados = null;
        $encabezados = $filas->first(function ($fila, $key) {
            return is_array($fila) && in_array('Dirección de correo', $fila);
        });

        if (!$encabezados) {
            return redirect()->back()->with('error', 'No se encontró la estructura de Teams (Falta columna Dirección de correo).');
        }

        // Obtenemos el índice de la fila donde están los encabezados
        $indiceEncabezados = $filas->search($encabezados);

        // 2. Mapeamos las posiciones exactas según tu imagen
        $colNombre     = array_search('Nombre completo', $encabezados);
        $colCorreo     = array_search('Dirección de correo', $encabezados);
        $colTarea      = array_search('Tareas', $encabezados);
        // En tu imagen "Porcentaje" es la última columna, buscamos por nombre exacto
        $colPorcentaje = array_search('Porcentaje', $encabezados);

        // 3. Empezamos a leer justo después de los encabezados
        $datosAlumnos = $filas->slice($indiceEncabezados + 1);

        foreach ($datosAlumnos as $fila) {
            // Validamos que la fila tenga un correo válido de la BUAP
            if (!isset($fila[$colCorreo]) || !str_contains($fila[$colCorreo], '@')) continue;

            $correo       = trim($fila[$colCorreo]);
            $nombreAlumno = trim($fila[$colNombre]);
            $actividad    = trim($fila[$colTarea]);
            
            // El porcentaje en Teams viene como "90%", "100%" o "0.9". 
            // Vamos a limpiar el símbolo % si existe y convertir a número.
            $valorRaw = $fila[$colPorcentaje] ?? 0;
            $puntajeLimpio = str_replace('%', '', $valorRaw);
            $puntajeTeams = is_numeric($puntajeLimpio) ? floatval($puntajeLimpio) : 0;

            // Si el valor es mayor a 1 (ej: 90), lo dividimos entre 10 para escala 0-10
            // Si es menor o igual a 1 (ej: 0.9), lo multiplicamos por 10.
            $notaFinal = ($puntajeTeams > 1) ? ($puntajeTeams / 10) : ($puntajeTeams * 10);
            $notaFinal = round($notaFinal, 1);

            \App\Models\CalificacionFinal::updateOrCreate(
                [
                    'materia_nrc'      => (string)$nrc,
                    'email_alumno'     => $correo,
                    'actividad_nombre' => $actividad,
                ],
                [
                    'nombre_alumno'    => $nombreAlumno,
                    'puntaje'          => $notaFinal,
                    'tipo_actividad'   => $tipo, // 'tarea' o 'practica'
                    'fecha_actividad'  => now()->format('d/m/Y'), 
                ]
            );
        }

        return redirect()->route('profesor.actas.index', $nrc)
                        ->with('success', "Se cargaron las " . ($tipo == 'tarea' ? 'Tareas' : 'Prácticas') . " correctamente.");
    }

    public function guardarManual(Request $request, $nrc)
    {
        try {
            // El campo viene dinámico (participacion, proyecto, etc.)
            $campo = $request->campo;
            $valor = $request->valor === "" ? null : $request->valor;

            CalificacionFinal::updateOrCreate(
                [
                    'materia_nrc'      => (string)$nrc,
                    'email_alumno'     => $request->email,
                    'actividad_nombre' => 'DATOS_MANUALES',
                ],
                [
                    'nombre_alumno' => $request->nombre,
                    $campo          => $valor, // Se actualiza solo el campo enviado (U1, Proyecto, etc.)
                ]
            );
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function exportar(Request $request, $nrc)
    {
        $materia = Materia::where('nrc', $nrc)->firstOrFail();
        $todasLasNotas = CalificacionFinal::where('materia_nrc', (string)$nrc)->get();

        // Lista de actividades de Teams para las columnas del Excel
        $listaActividades = $todasLasNotas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
                                          ->pluck('actividad_nombre')
                                          ->unique()
                                          ->values()
                                          ->toArray();

        $datosAlumnosFlat = [];
        $notasAgrupadas = $todasLasNotas->groupBy('email_alumno');

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
                ]
            ];

            // Aseguramos que las notas de Teams sigan el mismo orden que los encabezados
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

    // NUEVO MÉTODO PARA ELIMINAR UNA ACTIVIDAD 
    public function eliminarActividad($nrc, $actividad)
    {
        try {
            // Borramos la actividad específica para todos los alumnos de ese NRC
            CalificacionFinal::where('materia_nrc', (string)$nrc)
                            ->where('actividad_nombre', $actividad)
                            ->delete();

            return redirect()->back()->with('success', "La actividad '{$actividad}' ha sido eliminada.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'No se pudo eliminar la actividad.');
        }
    }
}