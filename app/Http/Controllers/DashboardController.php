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
        // Obtenemos las materias asignadas al profesor logueado
        $materias = Materia::where('profesor_id', Auth::id())->get();

        return view('profesor.dashboard', compact('materias'));
    }

    /**
     * Muestra el detalle de una materia específica (Actividades y Asistencias).
     * Vinculado a la ruta: /profesor/materias/{nrc}
     */
    public function showGrupo($nrc)
    {
        // 1. Buscamos la materia por NRC y verificamos que pertenezca al profesor logueado
        // Esto evita que un profesor vea grupos de otros.
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        // 2. Cargamos los alumnos inscritos (asegúrate de tener la relación en el modelo Materia)
        // Si aún no tienes la relación, puedes usar: $alumnos = \App\Models\User::where('role', 'alumno')->get(); para pruebas.
        $alumnos = $materia->alumnos; 

        // 3. Cargamos actividades (para el control de entregas Req. 5)
        $actividades = $materia->actividades;

        // 4. Retornamos la vista de actividades con toda la información
        return view('profesor.actividades', compact('materia', 'alumnos', 'actividades'));
    }
}