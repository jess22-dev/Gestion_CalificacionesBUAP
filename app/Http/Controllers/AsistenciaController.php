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
        try {
            $request->validate([
                'qr_data'     => 'required|string',
                'materia_nrc' => 'required|string',
            ]);

            $qrData     = trim($request->qr_data);
            $materiaNrc = trim($request->materia_nrc);
            $hoy        = now()->toDateString();

            // Formato: "codigo|nrc|fecha"  ej: "456123879|10001|2026-04-27"
            $partes = explode('|', $qrData);

            if (count($partes) !== 3) {
                return response()->json(['error' => 'Formato de QR inválido'], 400);
            }

            [$codigo, $nrc, $fecha] = $partes;

            \Log::info('QR leído', compact('codigo', 'nrc', 'materiaNrc', 'fecha', 'hoy'));

            if ($fecha !== $hoy) {
                return response()->json(['error' => 'El QR ha expirado. No corresponde al día de hoy.'], 400);
            }

            if ($nrc !== $materiaNrc) {
                return response()->json(['error' => 'El QR no corresponde a esta materia.'], 400);
            }

            $asistencia = Asistencia::where('materia_nrc', $materiaNrc)
                ->where('activa', true)
                ->latest()
                ->first();

            if (!$asistencia) {
                return response()->json(['error' => 'No hay asistencia activa para esta materia.'], 400);
            }

            $estudiante = Estudiante::where('codigo_estudiante', $codigo)->first();

            if (!$estudiante) {
                return response()->json(['error' => 'Estudiante no encontrado.'], 404);
            }

            $existe = AsistenciaDetalle::where('asistencia_id', $asistencia->id)
                ->where('alumno_id', $estudiante->id)
                ->exists();

            if ($existe) {
                return response()->json(['error' => 'Asistencia ya registrada para ' . $estudiante->nombre], 400);
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
                'codigo'  => $estudiante->codigo_estudiante,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error registrarQR: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

        /**
     * Registrar asistencia manual (por clave) datos_m
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