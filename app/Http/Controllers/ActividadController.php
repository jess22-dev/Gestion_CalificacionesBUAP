<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActividadController extends Controller
{
    /**
     * Crear una nueva actividad para una materia
     */
    public function store(Request $request, $nrc)
    {
        // Verificar que la materia pertenece al profesor logueado
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        $request->validate([
            'nombre'      => ['required', 'string', 'max:255'],
            'categoria'   => ['required', 'string'],
            'ponderacion' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'nombre.required'      => 'El nombre de la actividad es obligatorio.',
            'categoria.required'   => 'Selecciona una categoría.',
            'ponderacion.required' => 'La ponderación es obligatoria.',
            'ponderacion.max'      => 'La ponderación no puede superar 100.',
        ]);

        // Verificar que la ponderación total no supere 100
        $ponderacionActual = $materia->actividades()->sum('ponderacion');
        if ($ponderacionActual + $request->ponderacion > 100) {
            return back()
                ->withInput()
                ->with('error', "No puedes agregar esta actividad. La ponderación total sería " . ($ponderacionActual + $request->ponderacion) . "% (máximo 100%).");
        }

        Actividad::create([
            'nombre'      => $request->nombre,
            'categoria'   => $request->categoria,
            'ponderacion' => $request->ponderacion,
            'materia_nrc' => $nrc,
        ]);

        return back()->with('success', 'Actividad "' . $request->nombre . '" creada correctamente.');
    }

    /**
     * Eliminar una actividad
     */
    public function destroy($nrc, Actividad $actividad)
    {
        $actividad->delete();
        return back()->with('success', 'Actividad eliminada correctamente.');
    }
}
