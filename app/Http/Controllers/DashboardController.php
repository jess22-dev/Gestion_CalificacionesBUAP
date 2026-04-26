<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Muestra el panel principal del profesor con sus materias.
     */
    public function index()
    {
        $materias = Materia::where('profesor_id', Auth::id())->get();
        return view('profesor.dashboard', compact('materias'));
    }

    /**
     * Muestra el detalle de una materia — vista actividades
     */
    public function showGrupo($nrc)
    {
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        $alumnos     = $materia->alumnos;
        $actividades = $materia->actividades()->orderBy('created_at', 'asc')->get();

        // Calcular ponderación total usada
        $ponderacionTotal = $actividades->sum('ponderacion');

        return view('profesor.actividades', compact('materia', 'alumnos', 'actividades', 'ponderacionTotal'));
    }
}