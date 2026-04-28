<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MateriaController extends Controller
{
    public function index()
    {
        $materias = Materia::all();
        return view('materias.index', compact('materias'));
    }

    public function create()
    {
        return view('materias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|unique:materias,codigo',
        ]);

        Materia::create($request->all());
        return redirect()->route('materias.index')->with('success', 'Materia creada con éxito.');
    }

    public function edit(Materia $materia)
    {
        return view('materias.edit', compact('materia'));
    }

    public function update(Request $request, Materia $materia)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|unique:materias,codigo,' . $materia->id,
        ]);

        $materia->update($request->all());
        return redirect()->route('materias.index')->with('success', 'Materia actualizada.');
    }

    public function destroy(Materia $materia)
    {
        $materia->delete();
        return redirect()->route('materias.index')->with('success', 'Materia eliminada correctamente');
    }

    /**
     * Vista detalle del grupo — usa materia_estudiante para la lista de alumnos
     */
    public function show($nrc)
    {
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        // Estudiantes del nuevo módulo de alta
        $estudiantes = $materia->estudiantes()
                               ->wherePivot('status', 'activo')
                               ->get();

        // Actividades de la materia
        $actividades = $materia->actividades()
                               ->orderBy('created_at', 'asc')
                               ->get();

        $ponderacionTotal = $actividades->sum('ponderacion');

        return view('profesor.grupos-detalle', compact(
            'materia',
            'estudiantes',
            'actividades',
            'ponderacionTotal'
        ));
    }

    /**
     * Historial de asistencias — tabla cuadrícula
     * Filas: alumnos | Columnas: fechas de sesiones
     */
    public function historial($nrc)
    {
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        $estudiantes = $materia->estudiantes()
                               ->wherePivot('status', 'activo')
                               ->orderBy('nombre')
                               ->get();

        $sesiones = \App\Models\Asistencia::where('materia_nrc', $nrc)
                        ->orderBy('inicia_en', 'asc')
                        ->get();

        $diasUnicos = collect();
        $sesionesPorDia = [];

        foreach ($sesiones as $sesion) {
            $fecha = \Carbon\Carbon::parse($sesion->inicia_en);
            if ($fecha->dayOfWeek === 0) continue;
            $dia = $fecha->format('Y-m-d');
            if (!isset($sesionesPorDia[$dia])) {
                $sesionesPorDia[$dia] = [];
                $diasUnicos->push($fecha->startOfDay()->copy());
            }
            $sesionesPorDia[$dia][] = $sesion->id;
        }

        // Resultado: ['YYYY-MM-DD' => ['alumno_id' => 'estatus']]
        $registros = [];
        $prioridad = ['ausente' => 0, 'retardo' => 1, 'presente' => 2, 'justificado' => 3];

        foreach ($sesionesPorDia as $dia => $ids) {
            $registros[$dia] = [];
            foreach ($ids as $sesionId) {
                $detalles = \App\Models\AsistenciaDetalle::where('asistencia_id', $sesionId)
                                ->get(['alumno_id', 'estatus', 'asistio']);
                foreach ($detalles as $detalle) {
                    $nuevo = $detalle->estatus ?? ($detalle->asistio ? 'presente' : 'ausente');
                    $actual = $registros[$dia][$detalle->alumno_id] ?? 'ausente';
                    if (($prioridad[$nuevo] ?? 0) >= ($prioridad[$actual] ?? 0)) {
                        $registros[$dia][$detalle->alumno_id] = $nuevo;
                    }
                }
            }
        }

        return view('profesor.historial-asistencia', compact(
            'materia',
            'estudiantes',
            'diasUnicos',
            'sesionesPorDia',
            'registros'
        ));
    }

}