<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asistencia;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    public function iniciar(Request $request)
{
    try {

        $request->validate([
            'materia_nrc' => 'required',
            'duracion' => 'required|numeric'
        ]);

        $inicio = now();
        $fin = now()->addMinutes((int)$request->duracion);

        $asistencia = Asistencia::create([
            'materia_nrc' => $request->materia_nrc,
            'inicia_en' => $inicio,
            'termina_en' => $fin,
            'activa' => true
        ]);

        return response()->json([
            'success' => true,
            'fin' => $fin->toIso8601String()
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function detener(Request $request)
{
    $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
        ->where('activa', true)
        ->latest()
        ->first();

    if (!$asistencia) {
        return response()->json([
            'error' => 'No hay asistencia activa'
        ], 404);
    }

    $asistencia->update([
        'activa' => false
    ]);

    return response()->json([
        'success' => true
    ]);
}


public function registrarQR(Request $request)
{
    $request->validate([
        'alumno_id' => 'required',
        'materia_nrc' => 'required'
    ]);

    // buscar asistencia activa
    $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
        ->where('activa', true)
        ->first();

    if (!$asistencia) {
        return response()->json([
            'error' => 'No hay asistencia activa'
        ], 400);
    }

    // evitar duplicados
    $existe = \App\Models\AsistenciaDetalle::where('asistencia_id', $asistencia->id)
        ->where('alumno_id', $request->alumno_id)
        ->exists();

    if ($existe) {
        return response()->json([
            'error' => 'Ya registrado'
        ], 400);
    }

    \App\Models\AsistenciaDetalle::create([
        'asistencia_id' => $asistencia->id,
        'alumno_id' => $request->alumno_id,
        'estatus' => 'P'
    ]);

    return response()->json([
        'success' => true
    ]);
}



public function registrar(Request $request)
{
    $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
        ->where('activa', true)
        ->latest()
        ->first();

    if (!$asistencia) {
        return response()->json(['error' => 'No hay asistencia activa'], 400);
    }

    AsistenciaDetalle::updateOrCreate(
        [
            'asistencia_id' => $asistencia->id,
            'alumno_id' => $request->clave
        ],
        [
            'hora_registro' => now(),
            'asistio' => true
        ]
    );

    return response()->json(['success' => true]);
}


}
