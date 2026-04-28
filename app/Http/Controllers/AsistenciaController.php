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
                'success'      => true,
                'fin'          => $fin->toIso8601String(),
                'asistencia_id'=> $asistencia->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
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
     * Obtener estado actual de la asistencia activa (para restaurar al recargar)
     */
    public function estadoActual(Request $request)
    {
        $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
            ->where('activa', true)
            ->where('termina_en', '>', now())
            ->latest()
            ->first();

        if (!$asistencia) {
            // Buscar la última sesión del día aunque ya no esté activa
            $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
                ->whereDate('inicia_en', today())
                ->latest()
                ->first();
        }

        if (!$asistencia) {
            return response()->json(['detalles' => []]);
        }

        $detalles = AsistenciaDetalle::where('asistencia_id', $asistencia->id)
            ->with('alumno')
            ->get()
            ->map(fn($d) => [
                'alumno_id' => $d->alumno_id,
                'codigo'    => $d->alumno?->codigo_estudiante,
                'estatus'   => $d->estatus,
                'hora'      => $d->hora_registro?->format('H:i'),
            ]);

        return response()->json([
            'asistencia_id' => $asistencia->id,
            'detalles'      => $detalles,
        ]);
    }

    /**
     * Registrar asistencia por QR — formato: codigo|nrc|fecha
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

            $partes = explode('|', $qrData);
            if (count($partes) !== 3) {
                return response()->json(['error' => 'Formato de QR inválido'], 400);
            }

            [$codigo, $nrc, $fecha] = $partes;

            if ($fecha !== $hoy) {
                return response()->json(['error' => 'El QR ha expirado. No corresponde al día de hoy.'], 400);
            }

            if ($nrc !== $materiaNrc) {
                return response()->json(['error' => 'El QR no corresponde a esta materia.'], 400);
            }

            $asistencia = Asistencia::where('materia_nrc', $materiaNrc)
                ->where('activa', true)->latest()->first();

            if (!$asistencia) {
                return response()->json(['error' => 'No hay asistencia activa para esta materia.'], 400);
            }

            $estudiante = Estudiante::where('codigo_estudiante', $codigo)->first();
            if (!$estudiante) {
                return response()->json(['error' => 'Estudiante no encontrado.'], 404);
            }

            $existe = AsistenciaDetalle::where('asistencia_id', $asistencia->id)
                ->where('alumno_id', $estudiante->id)->exists();

            if ($existe) {
                return response()->json(['error' => 'Asistencia ya registrada para ' . $estudiante->nombre], 400);
            }

            AsistenciaDetalle::create([
                'asistencia_id' => $asistencia->id,
                'alumno_id'     => $estudiante->id,
                'clave_unica'   => $estudiante->codigo_estudiante,
                'asistio'       => true,
                'estatus'       => 'presente',
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
     * Cambiar estatus de un alumno (click en celda)
     * Ciclo: ausente → presente → retardo → justificado → ausente
     */
    public function cambiarEstatus(Request $request)
    {
        $request->validate([
            'materia_nrc' => 'required|string',
            'alumno_id'   => 'required|integer',
            'estatus'     => 'required|in:ausente,presente,retardo,justificado',
        ]);

        // Buscar la sesión más reciente del día
        $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
            ->whereDate('inicia_en', today())
            ->latest()
            ->first();

        if (!$asistencia) {
            return response()->json(['error' => 'No hay sesión de asistencia para hoy.'], 400);
        }

        AsistenciaDetalle::updateOrCreate(
            [
                'asistencia_id' => $asistencia->id,
                'alumno_id'     => $request->alumno_id,
            ],
            [
                'clave_unica'   => Estudiante::find($request->alumno_id)?->codigo_estudiante,
                'asistio'       => in_array($request->estatus, ['presente', 'retardo']),
                'estatus'       => $request->estatus,
                'hora_registro' => $request->estatus !== 'ausente' ? now() : null,
            ]
        );

        return response()->json(['success' => true, 'estatus' => $request->estatus]);
    }

    /**
     * Marcar todos los alumnos como presentes
     */
    public function todosPresentes(Request $request)
    {
        $request->validate(['materia_nrc' => 'required|string']);

        $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
            ->whereDate('inicia_en', today())
            ->latest()
            ->first();

        if (!$asistencia) {
            return response()->json(['error' => 'No hay sesión de asistencia para hoy.'], 400);
        }

        // Obtener todos los estudiantes de la materia
        $materia     = \App\Models\Materia::where('nrc', $request->materia_nrc)->first();
        $estudiantes = $materia->estudiantes()->wherePivot('status', 'activo')->get();

        foreach ($estudiantes as $estudiante) {
            AsistenciaDetalle::updateOrCreate(
                [
                    'asistencia_id' => $asistencia->id,
                    'alumno_id'     => $estudiante->id,
                ],
                [
                    'clave_unica'   => $estudiante->codigo_estudiante,
                    'asistio'       => true,
                    'estatus'       => 'presente',
                    'hora_registro' => now(),
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * Registrar asistencia manual (por clave)
     */
    public function registrar(Request $request)
    {
        $asistencia = Asistencia::where('materia_nrc', $request->materia_nrc)
            ->where('activa', true)->latest()->first();

        if (!$asistencia) {
            return response()->json(['error' => 'No hay asistencia activa'], 400);
        }

        $estudiante = Estudiante::where('codigo_estudiante', $request->clave)
            ->orWhere('clave_unica', $request->clave)->first();

        if (!$estudiante) {
            return response()->json(['error' => 'Estudiante no encontrado'], 404);
        }

        AsistenciaDetalle::updateOrCreate(
            ['asistencia_id' => $asistencia->id, 'alumno_id' => $estudiante->id],
            [
                'clave_unica'   => $estudiante->codigo_estudiante,
                'asistio'       => true,
                'estatus'       => 'presente',
                'hora_registro' => now(),
            ]
        );

        return response()->json(['success' => true, 'nombre' => $estudiante->nombre]);
    }
}