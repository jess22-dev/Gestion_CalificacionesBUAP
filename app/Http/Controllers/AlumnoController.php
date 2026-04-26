<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia;
use Illuminate\Support\Facades\Auth;

class AlumnoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Obtenemos las materias vinculadas al alumno con los datos de la tabla pivote
        $materias = $user->materias()
            ->withPivot('promedio_real', 'promedio_redondeado', 'status', 'qr_path', 'clave_asistencia')
            ->get();

        return view('alumno.dashboard', compact('materias'));
    }

    /**
     * Lógica de Baja Automática (Requerimiento 3 y 4)
     */
    public function solicitarBaja($nrc)
    {
        $user = Auth::user();

        // Buscamos la relación específica
        $materia = $user->materias()->where('materia_nrc', $nrc)->first();

        if ($materia && $materia->pivot->status !== 'baja') {
            // Actualizamos a status 'baja' y registramos la fecha
            $user->materias()->updateExistingPivot($nrc, [
                'status' => 'baja',
                'fecha_baja' => now()
            ]);
            
            return back()->with('success', 'Baja procesada correctamente. Se ha notificado al docente.');
        }

        return back()->with('error', 'No se pudo procesar la baja.');
    }

    public function show($nrc)
    {
    $user = Auth::user();
    
    // Obtenemos la materia asociada a este alumno específico
    $materia = $user->materias()->where('materias.nrc', $nrc)->firstOrFail();

    return view('alumno.show', compact('materia'));
    }
}