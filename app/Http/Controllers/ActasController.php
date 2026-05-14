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
        
        // 1. Traemos TODAS las notas
        $notasTodas = CalificacionFinal::where('materia_nrc', (string)$nrc)->get();

        // 2. Filtramos actividades de Teams (Dinámicas)
        $actividades = $notasTodas->where('actividad_nombre', '!=', 'DATOS_MANUALES')
                                  ->pluck('actividad_nombre')
                                  ->unique()
                                  ->values();

        // 3. Organizamos los datos por alumno
        $alumnosData = [];
        $notasAgrupadas = $notasTodas->groupBy('email_alumno');

        foreach ($notasAgrupadas as $correo => $items) {
            $alumnosData[$correo] = [
                'nombre' => $items->first()->nombre_alumno,
                'notas'  => [],
                'manual' => $items->firstWhere('actividad_nombre', 'DATOS_MANUALES')
            ];

            // Llenamos solo las que son de Teams
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

        if ($request->hasFile('archivos_teams')) {
            foreach ($request->file('archivos_teams') as $archivo) {
                $data = Excel::toArray([], $archivo);
                $filas = collect($data[0]);

                // Buscamos la fila que contiene los encabezados reales
                $encabezados = $filas->first(function ($item) {
                    return is_array($item) && in_array('Dirección de correo', $item);
                });

                if (!$encabezados) continue; 

                $colCorreo     = array_search('Dirección de correo', $encabezados);
                $colTarea      = array_search('Tareas', $encabezados);
                $colNombre     = array_search('Nombre completo', $encabezados);
                $colPorcentaje = array_search('Porcentaje', $encabezados);

                // Limpiamos la data para quitar encabezados y filas vacías
                $datosLimpios = $filas->filter(function ($fila) use ($colCorreo) {
                    return isset($fila[$colCorreo]) && str_contains($fila[$colCorreo], '@'); 
                });

                foreach ($datosLimpios as $fila) {
                    $correo       = trim($fila[$colCorreo]);
                    $actividad    = trim($fila[$colTarea]);
                    $nombreAlumno = trim($fila[$colNombre]);
                    
                    // Lógica de calificación: Teams da decimal (0.8 = 80%)
                    $valorCelda   = $fila[$colPorcentaje] ?? 0;
                    $puntajeTeams = is_numeric($valorCelda) ? floatval($valorCelda) : 0;
                    
                    // IMPORTANTE: Aquí guardamos con un decimal para que el promedio 
                    // de la tabla web sea exacto antes del redondeo final del acta.
                    $notaFinal = round($puntajeTeams * 10, 1);

                    CalificacionFinal::updateOrCreate(
                        [
                            'materia_nrc'      => (string)$nrc,
                            'email_alumno'     => $correo,
                            'actividad_nombre' => $actividad,
                        ],
                        [
                            'nombre_alumno'    => $nombreAlumno,
                            'puntaje'          => $notaFinal,
                            'fecha_actividad'  => now()->format('d/m/Y'), 
                        ]
                    );
                }
            }
        }

        return redirect()->route('profesor.actas.index', $nrc)
                         ->with('success', 'Calificaciones actualizadas.');
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