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
     * Ver detalle del grupo — Actividades + Alumnos
     */
    public function show($nrc)
    {
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        // Alumnos inscritos con datos del pivot
        $alumnos = $materia->alumnos()->withPivot('clave_unica', 'status', 'promedio_real', 'promedio_redondeado')->get();

        // Actividades de la materia
        $actividades = $materia->actividades()->orderBy('created_at', 'asc')->get();

        // Ponderación total usada
        $ponderacionTotal = $actividades->sum('ponderacion');

        return view('profesor.grupos-detalle', compact('materia', 'alumnos', 'actividades', 'ponderacionTotal'));
    }
}