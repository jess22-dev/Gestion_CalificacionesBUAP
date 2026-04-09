<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia; 
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
    $materias = Materia::where('profesor_id', auth()->id())->get();

    return view('profesor.dashboard', compact('materias')); // <--- Agregamos 'profesor.'
    }
}