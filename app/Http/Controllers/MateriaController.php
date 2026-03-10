<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    // LISTAR MATERIAS
    public function index()
    {
        $materias = Materia::all();
        return view('materias.index', compact('materias'));
    }

    // VISTA PARA CREAR
    public function create()
    {
        return view('materias.create');
    }

    // FUNCION CREAR (GUARDAR EN DB)
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|unique:materias,codigo',
        ]);

        Materia::create($request->all());

        return redirect()->route('materias.index')->with('success', 'Materia creada con éxito.');
    }

    // VISTA PARA EDITAR
    public function edit(Materia $materia)
    {
        return view('materias.edit', compact('materia'));
    }

    // FUNCION EDITAR (ACTUALIZAR EN DB)
    public function update(Request $request, Materia $materia)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|unique:materias,codigo,' . $materia->id,
        ]);

        $materia->update($request->all());

        return redirect()->route('materias.index')->with('success', 'Materia actualizada.');
    }

    // FUNCION ELIMINAR
    public function destroy(Materia $materia)
    {
        $materia->delete();
        return redirect()->route('materias.index')->with('success', 'Materia eliminada correctamente');
    }
}