<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia; 
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
    
        $materias = Materia::where('user_id', Auth::id())->get();

        // 3. Enviamos 'materias' a la vista
        return view('profesor.dashboard', compact('materias'));
    }
}