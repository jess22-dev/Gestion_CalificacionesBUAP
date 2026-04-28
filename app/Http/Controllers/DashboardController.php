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

    // showGrupo eliminado — usar MateriaController@show
}