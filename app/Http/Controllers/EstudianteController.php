<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Materia;
use App\Imports\EstudiantesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class EstudianteController extends Controller
{
    /**
     * Listar estudiantes de una materia específica del profesor
     */
    public function index(Request $request)
    {
        $nrc = $request->query('nrc');
        $materia = null;

        if ($nrc) {
            $materia = Materia::where('nrc', $nrc)
                              ->where('profesor_id', Auth::id())
                              ->firstOrFail();

            // Solo estudiantes de esta materia dados de alta por este profesor
            $estudiantes = $materia->estudiantes()
                                   ->wherePivot('profesor_id', Auth::id())
                                   ->wherePivot('status', 'activo')
                                   ->paginate(15);
        } else {
            $estudiantes = collect()->paginate(15);
        }

        return view('profesor.estudiantes.index', compact('estudiantes', 'materia', 'nrc'));
    }

    /**
     * Formulario para agregar manualmente
     */
    public function create(Request $request)
    {
        $nrc = $request->query('nrc');
        $materia = null;

        if ($nrc) {
            $materia = Materia::where('nrc', $nrc)
                              ->where('profesor_id', Auth::id())
                              ->firstOrFail();
        }

        return view('profesor.estudiantes.create', compact('materia', 'nrc'));
    }

    /**
     * Guardar estudiante manual y vincularlo a la materia
     */
    public function store(Request $request)
    {
        $nrc = $request->input('nrc');

        $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255'],
            'codigo_estudiante' => ['required', 'digits:9'],
            'nrc'               => ['required', 'string'],
        ], [
            'nombre.required'            => 'El nombre es obligatorio.',
            'email.required'             => 'El email es obligatorio.',
            'email.email'                => 'El formato del email no es válido.',
            'codigo_estudiante.required' => 'El código es obligatorio.',
            'codigo_estudiante.digits'   => 'El código debe tener exactamente 9 dígitos.',
        ]);

        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        // Verificar si el estudiante ya existe
        $existente = Estudiante::where('email', $request->email)
                               ->orWhere('codigo_estudiante', $request->codigo_estudiante)
                               ->first();

        if ($existente) {
            // Ya está en ESTA materia
            if ($existente->estaEnMateria($nrc)) {
                return back()->withInput()
                    ->with('error', "El estudiante {$existente->nombre} ya está registrado en esta materia.");
            }

            // Está en otra materia — preguntar si quiere agregarlo
            if ($existente->estaEnOtraMateria($nrc)) {
                return back()->withInput()
                    ->with('info', "El estudiante {$existente->nombre} (código: {$existente->codigo_estudiante}) ya está registrado en otra materia. ¿Deseas agregarlo también a esta materia?")
                    ->with('estudiante_existente_id', $existente->id)
                    ->with('nrc', $nrc);
            }

            // Vincularlo a esta materia
            $existente->materias()->attach($nrc, [
                'profesor_id' => Auth::id(),
                'status'      => 'activo',
            ]);

            return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
                ->with('success', "Estudiante {$existente->nombre} agregado a la materia correctamente.");
        }

        // Nuevo estudiante
        $estudiante = Estudiante::create($request->only('nombre', 'email', 'codigo_estudiante'));

        $estudiante->materias()->attach($nrc, [
            'profesor_id' => Auth::id(),
            'status'      => 'activo',
        ]);

        return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
            ->with('success', 'Estudiante agregado correctamente.');
    }

    /**
     * Confirmar agregar estudiante existente a esta materia
     */
    public function agregarExistente(Request $request)
    {
        $nrc            = $request->input('nrc');
        $estudianteId   = $request->input('estudiante_id');

        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        $estudiante = Estudiante::findOrFail($estudianteId);

        if (!$estudiante->estaEnMateria($nrc)) {
            $estudiante->materias()->attach($nrc, [
                'profesor_id' => Auth::id(),
                'status'      => 'activo',
            ]);
        }

        return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
            ->with('success', "Estudiante {$estudiante->nombre} agregado a esta materia.");
    }

    /**
     * Formulario de importación
     */
    public function showImport(Request $request)
    {
        $nrc = $request->query('nrc');
        $materia = null;

        if ($nrc) {
            $materia = Materia::where('nrc', $nrc)
                              ->where('profesor_id', Auth::id())
                              ->firstOrFail();
        }

        return view('profesor.estudiantes.import', compact('materia', 'nrc'));
    }

    /**
     * Procesar archivo Excel/CSV
     */
    public function import(Request $request)
    {
        $nrc = $request->input('nrc');

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'nrc'     => ['required', 'string'],
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes'    => 'El archivo debe ser .xlsx, .xls o .csv.',
            'archivo.max'      => 'El archivo no debe superar los 5MB.',
        ]);

        $materia = Materia::where('nrc', $nrc)
                          ->where('profesor_id', Auth::id())
                          ->firstOrFail();

        try {
            $import = new EstudiantesImport($nrc);
            Excel::import($import, $request->file('archivo'));

            $duplicados      = $import->getDuplicados();
            $yaEnOtraMateria = $import->getYaEnOtraMateria();
            $importados      = $import->getImportados();

            if ($importados === 0 && count($duplicados) > 0) {
                return redirect()->route('profesor.estudiantes.import', ['nrc' => $nrc])
                    ->with('warning', 'No se agregó ningún estudiante. Todos ya están en esta materia.')
                    ->with('duplicados', $duplicados);
            }

            $msg = "Se importaron {$importados} estudiante(s) correctamente.";

            if (count($duplicados) > 0) {
                return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
                    ->with('warning', $msg . ' ' . count($duplicados) . ' ya existían en esta materia.')
                    ->with('duplicados', $duplicados)
                    ->with('yaEnOtraMateria', $yaEnOtraMateria);
            }

            return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
                ->with('success', $msg)
                ->with('yaEnOtraMateria', $yaEnOtraMateria);

        } catch (\Exception $e) {
            return redirect()->route('profesor.estudiantes.import', ['nrc' => $nrc])
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de un estudiante
     */
    public function show(Estudiante $estudiante, Request $request)
    {
        $nrc = $request->query('nrc');
        return view('profesor.estudiantes.show', compact('estudiante', 'nrc'));
    }
}