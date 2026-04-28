<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Materia;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ActividadController extends Controller
{
    /**
     * Crear una nueva actividad para una materia
     */
    // Ponderaciones predeterminadas por categoría
    private array $ponderacionesPredeterminadas = [
        'Prácticas'      => 20,
        'Tareas'         => 20,
        'Examen'         => 20,
        'Proyecto Final' => 40,
    ];

    public function store(Request $request, $nrc)
    {
        $request->validate([
            'nombre'      => ['required', 'string', 'max:255'],
            'categoria'   => ['required', 'string', 'max:100'],
            'ponderacion' => ['nullable', 'numeric', 'min:1', 'max:100'],
        ]);

        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        // Si no se pone ponderación, usar la predeterminada de la categoría
        $ponderacion = $request->ponderacion
            ?? ($this->ponderacionesPredeterminadas[$request->categoria] ?? 10);

        $totalActual = $materia->actividades()->sum('ponderacion');

        if ($totalActual + $ponderacion > 100) {
            return back()->withInput()
                ->with('error', "La ponderación total no puede superar 100%. Disponible: " . (100 - $totalActual) . "%");
        }

        $actividad = Actividad::create([
            'nombre'      => $request->nombre,
            'categoria'   => $request->categoria,
            'ponderacion' => $ponderacion,
            'materia_nrc' => $nrc,
        ]);

        // Vincular automáticamente todos los alumnos activos de la materia
        $estudiantes = $materia->estudiantes()->wherePivot('status', 'activo')->get();
        foreach ($estudiantes as $estudiante) {
            $user = \App\Models\User::where('email', $estudiante->email)->first();
            if ($user) {
                $actividad->alumnos()->attach($user->id, [
                    'calificacion'   => null,
                    'archivo_path'   => null,
                    'archivo_nombre' => null,
                    'entregado'      => false,
                ]);
            }
        }

        return back()->with('success', "Actividad '{$actividad->nombre}' creada correctamente.");
    }

    /**
     * Eliminar una actividad
     */
    public function destroy($nrc, Actividad $actividad)
    {
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        // Eliminar archivos subidos por alumnos
        foreach ($actividad->alumnos as $alumno) {
            if ($alumno->pivot->archivo_path) {
                Storage::disk('public')->delete($alumno->pivot->archivo_path);
            }
        }

        $actividad->delete();
        return back()->with('success', "Actividad eliminada correctamente.");
    }

    /**
     * Vista detalle de una actividad — lista de alumnos con calificaciones
     */
    public function detalle($nrc, Actividad $actividad)
    {
        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        $alumnos = $actividad->alumnos()->get();

        return view('profesor.actividad-detalle', compact('materia', 'actividad', 'alumnos'));
    }

    /**
     * Guardar calificación de un alumno (profesor)
     */
    public function calificar(Request $request, $nrc, Actividad $actividad)
    {
        $request->validate([
            'alumno_id'    => ['required', 'exists:users,id'],
            'calificacion' => ['required', 'numeric', 'min:0', 'max:10'],
        ]);

        $actividad->alumnos()->updateExistingPivot($request->alumno_id, [
            'calificacion' => $request->calificacion,
        ]);

        return back()->with('success', 'Calificación guardada correctamente.');
    }

    /**
     * Subir archivo de un alumno (alumno)
     */
    public function subirArchivo(Request $request, $actividadId)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ], [
            'archivo.mimes' => 'Solo se permiten archivos PDF, imágenes (JPG, PNG) y Word (DOC, DOCX).',
            'archivo.max'   => 'El archivo no puede superar 10MB.',
        ]);

        $user      = Auth::user();
        $actividad = Actividad::findOrFail($actividadId);

        // Verificar que el alumno pertenece a esta actividad
        $pivot = $actividad->alumnos()->where('alumno_id', $user->id)->first();
        if (!$pivot) {
            return back()->with('error', 'No tienes acceso a esta actividad.');
        }

        // Eliminar archivo anterior si existe
        if ($pivot->pivot->archivo_path) {
            Storage::disk('public')->delete($pivot->pivot->archivo_path);
        }

        // Guardar nuevo archivo
        $archivo = $request->file('archivo');
        $path    = $archivo->store('actividades/' . $actividadId, 'public');

        $actividad->alumnos()->updateExistingPivot($user->id, [
            'archivo_path'   => $path,
            'archivo_nombre' => $archivo->getClientOriginalName(),
            'entregado'      => true,
        ]);

        return back()->with('success', 'Archivo subido correctamente.');
    }

    /**
     * Eliminar archivo de un alumno (alumno)
     */
    public function eliminarArchivo($actividadId)
    {
        $user      = Auth::user();
        $actividad = Actividad::findOrFail($actividadId);

        $pivot = $actividad->alumnos()->where('alumno_id', $user->id)->first();
        if ($pivot && $pivot->pivot->archivo_path) {
            Storage::disk('public')->delete($pivot->pivot->archivo_path);

            $actividad->alumnos()->updateExistingPivot($user->id, [
                'archivo_path'   => null,
                'archivo_nombre' => null,
                'entregado'      => false,
            ]);
        }

        return back()->with('success', 'Archivo eliminado correctamente.');
    }
}