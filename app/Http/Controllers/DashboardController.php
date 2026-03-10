<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo; // Importamos el modelo de Grupos
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Traemos solo los grupos que le pertenecen al profesor logueado
        // Esto cubre tu punto 4: "Visualiza los grupos asignados"
        $grupos = Grupo::where('profesor_id', Auth::id())->get();

        return view('dashboard', compact('grupos'));
    }
}