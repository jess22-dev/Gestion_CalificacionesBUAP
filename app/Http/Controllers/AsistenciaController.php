<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asistencia;
use App\Models\AsistenciaDetalle;
use App\Models\Estudiante;

class AsistenciaController extends Controller
{
    /**
     * Iniciar sesión de asistencia
     */
    public function iniciar(Request $request)
    {
        try {
            $request->validate([
                'materia_nrc' => 'required',
                'duracion'    => 'required|numeric',
            ]);

            $inicio = now();
            $fin    = now()->addMinutes((int) $request->duracion);

            $asistencia = Asistencia::create([
                'materia_nrc' => $request->materia_nrc,
                'inicia_en'   => $inicio,
                'termina_en'  => $fin,
                'activa'      => true,
            ]);

            return response()->json([
                'success' => true,
                'fin'     => $fin->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detener sesión de asistencia
     */
    public function detener(Request $request)
    {
        $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
            ->where('activa', true)
            ->latest()
            ->first();

        if (!$asistencia) {
            return response()->json(['error' => 'No hay asistencia activa'], 404);
        }

        $asistencia->update(['activa' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Registrar asistencia por QR
     * El QR contiene JSON con: nombre, codigo, materia, nrc, fecha
     */
    public function registrarQR(Request $request)
    {
        $request->validate([
            'qr_data'     => 'required|string',
            'materia_nrc' => 'required|string',
        ]);

        // Decodificar el JSON del QR
        $datos = json_decode($request->qr_data, true);

        if (!$datos || !isset($datos['codigo'])) {
            return response()->json(['error' => 'QR inválido'], 400);
        }

        // Verificar que el QR es del día de hoy
        $fechaQR  = $datos['fecha'] ?? null;
        $hoy      = now()->toDateString();

        if ($fechaQR !== $hoy) {
            return response()->json(['error' => 'El QR ha expirado. No corresponde al día de hoy.'], 400);
        }

        // Verificar que el NRC coincide
        if (($datos['nrc'] ?? '') !== $request->materia_nrc) {
            return response()->json(['error' => 'El QR no corresponde a esta materia.'], 400);
        }

        // Buscar asistencia activa
        $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
            ->where('activa', true)
            ->latest()
            ->first();

        if (!$asistencia) {
            return response()->json(['error' => 'No hay asistencia activa'], 400);
        }

        // Buscar estudiante por código
        $estudiante = Estudiante::where('codigo_estudiante', $datos['codigo'])->first();

        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        // Evitar duplicados
        $existe = AsistenciaDetalle::where('asistencia_id', $asistencia->id)
            ->where('alumno_id', $estudiante->id)
            ->exists();

        if ($existe) {
            return response()->json(['error' => 'Asistencia ya registrada para este alumno'], 400);
        }

        AsistenciaDetalle::create([
            'asistencia_id' => $asistencia->id,
            'alumno_id'     => $estudiante->id,
            'clave_unica'   => $estudiante->codigo_estudiante,
            'asistio'       => true,
            'hora_registro' => now(),
        ]);

        return response()->json([
            'success' => true,
            'nombre'  => $estudiante->nombre,
        ]);
    }

    /**
     * Registrar asistencia manual (por clave)
     */
    public function registrar(Request $request)
    {
        $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
            ->where('activa', true)
            ->latest()
            ->first();

        if (!$asistencia) {
            return response()->json(['error' => 'No hay asistencia activa'], 400);
        }

        $estudiante = Estudiante::where('codigo_estudiante', $request->clave)
            ->orWhere('clave_unica', $request->clave)
            ->first();

        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        AsistenciaDetalle::updateOrCreate(
            [
                'asistencia_id' => $asistencia->id,
                'alumno_id'     => $estudiante->id,
            ],
            [
                'clave_unica'   => $estudiante->codigo_estudiante,
                'asistio'       => true,
                'hora_registro' => now(),
            ]
        );

        return response()->json(['success' => true, 'nombre' => $estudiante->nombre]);
    }
}