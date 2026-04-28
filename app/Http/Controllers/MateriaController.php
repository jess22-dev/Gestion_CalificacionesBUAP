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
     * Historial de asistencias — tabla cuadrícula agrupada por DÍA
     * Filas: alumnos | Columnas: días únicos (sin repetir, sin domingos)
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

        // Todas las sesiones de la materia
        $sesiones = \App\Models\Asistencia::where('materia_nrc', $nrc)
                        ->orderBy('inicia_en', 'asc')
                        ->get();

        // Agrupar sesiones por día (YYYY-MM-DD) — sin domingos
        // Si hubo varias sesiones en el mismo día, se unifican en una sola columna
        $diasUnicos = collect();
        $sesionesPorDia = []; // ['2026-04-27' => [sesion_id1, sesion_id2, ...]]

        foreach ($sesiones as $sesion) {
            $fecha = \Carbon\Carbon::parse($sesion->inicia_en);

            // Saltar domingos (0 = domingo en dayOfWeek)
            if ($fecha->dayOfWeek === 0) continue;

            $dia = $fecha->format('Y-m-d');

            if (!isset($sesionesPorDia[$dia])) {
                $sesionesPorDia[$dia] = [];
                $diasUnicos->push($fecha->startOfDay()->copy());
            }

            $sesionesPorDia[$dia][] = $sesion->id;
        }

        // Para cada día, marcar si el alumno asistió en ALGUNA sesión de ese día
        // Resultado: ['YYYY-MM-DD' => ['alumno_id' => true/false]]
        $registros = [];
        foreach ($sesionesPorDia as $dia => $ids) {
            $registros[$dia] = [];
            foreach ($ids as $sesionId) {
                $detalles = \App\Models\AsistenciaDetalle::where('asistencia_id', $sesionId)
                                ->where('asistio', true)
                                ->pluck('alumno_id')
                                ->toArray();
                foreach ($detalles as $alumnoId) {
                    $registros[$dia][$alumnoId] = true;
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