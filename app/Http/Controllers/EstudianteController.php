<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\User;
use App\Imports\EstudiantesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EstudianteController extends Controller
{
    /**
     * Listar estudiantes de una materia específica del profesor
     */
    public function index(Request $request)
    {
        $nrc     = $request->query('nrc');
        $materia = null;

        if ($nrc) {
            $materia = Materia::where('nrc', $nrc)
                              ->where('profesor_id', Auth::id())
                              ->firstOrFail();

            $estudiantes = $materia->estudiantes()
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
        $nrc     = $request->query('nrc');
        $materia = null;

        if ($nrc) {
            $materia = Materia::where('nrc', $nrc)
                              ->where('profesor_id', Auth::id())
                              ->firstOrFail();
        }

        return view('profesor.estudiantes.create', compact('materia', 'nrc'));
    }

    /**
     * Guardar estudiante manual
     * - Si es nuevo: crea en estudiantes + crea user alumno + vincula en alumno_materia
     * - Si existe: verifica si ya está en esta materia o en otra
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
            if ($existente->estaEnMateria($nrc)) {
                return back()->withInput()
                    ->with('error', "El estudiante {$existente->nombre} ya está registrado en esta materia.");
            }

            if ($existente->estaEnOtraMateria($nrc)) {
                return back()->withInput()
                    ->with('info', "El estudiante {$existente->nombre} (código: {$existente->codigo_estudiante}) ya está registrado en otra materia. ¿Deseas agregarlo también a esta materia?")
                    ->with('estudiante_existente_id', $existente->id)
                    ->with('nrc', $nrc);
            }

            // Vincular a materia_estudiante
            $existente->materias()->attach($nrc, [
                'profesor_id' => Auth::id(),
                'status'      => 'activo',
            ]);

            // También vincular en alumno_materia si tiene user
            $userAlumno = User::where('email', $existente->email)->first();
            if ($userAlumno) {
                $this->vincularAlumnoMateria($userAlumno, $nrc, $existente->codigo_estudiante);
            }

            return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
                ->with('success', "Estudiante {$existente->nombre} agregado a la materia correctamente.");
        }

        // Nuevo estudiante — generar clave única
        $claveUnica = Estudiante::generarClaveUnica();

        $estudiante = Estudiante::create([
            'nombre'            => $request->nombre,
            'email'             => $request->email,
            'codigo_estudiante' => $request->codigo_estudiante,
            'clave_unica'       => $claveUnica,
        ]);

        // Crear usuario con rol alumno si no existe
        $userAlumno = User::firstOrCreate(
            ['email' => $request->email],
            [
                'name'     => $request->nombre,
                'password' => Hash::make(Str::random(16)), // password random, no se usa
                'role'     => 'alumno',
            ]
        );

        // Vincular en materia_estudiante
        $estudiante->materias()->attach($nrc, [
            'profesor_id' => Auth::id(),
            'status'      => 'activo',
        ]);

        // Vincular en alumno_materia (sistema existente)
        $this->vincularAlumnoMateria($userAlumno, $nrc, $request->codigo_estudiante, $claveUnica);

        return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
            ->with('success', "Estudiante {$estudiante->nombre} registrado correctamente.")
            ->with('clave_generada', $claveUnica)
            ->with('nombre_alumno', $estudiante->nombre);
    }

    /**
     * Vincular alumno en alumno_materia (sistema existente del proyecto)
     * Genera claves únicas verificando contra la BD para evitar duplicados
     */
    private function vincularAlumnoMateria(User $user, string $nrc, string $codigoEstudiante, string $claveAsistencia = null): void
    {
        $yaVinculado = $user->materias()->where('materia_nrc', $nrc)->exists();

        if (!$yaVinculado) {
            // clave_unica en alumno_materia tiene índice UNIQUE — generar una nueva
            do {
                $claveUnicaMateria = strtoupper(Str::random(10));
            } while (\Illuminate\Support\Facades\DB::table('alumno_materia')
                ->where('clave_unica', $claveUnicaMateria)->exists());

            // clave_asistencia también tiene índice UNIQUE
            do {
                $claveAsistenciaFinal = strtoupper(Str::random(10));
            } while (\Illuminate\Support\Facades\DB::table('alumno_materia')
                ->where('clave_asistencia', $claveAsistenciaFinal)->exists());

            $user->materias()->attach($nrc, [
                'clave_unica'      => $claveUnicaMateria,
                'clave_asistencia' => $claveAsistencia ?? $claveAsistenciaFinal,
                'status'           => 'activo',
            ]);
        }
    }

    /**
     * Confirmar agregar estudiante existente a esta materia
     */
    public function agregarExistente(Request $request)
    {
        $nrc          = $request->input('nrc');
        $estudianteId = $request->input('estudiante_id');

        $materia    = Materia::where('nrc', $nrc)->where('profesor_id', Auth::id())->firstOrFail();
        $estudiante = Estudiante::findOrFail($estudianteId);

        if (!$estudiante->estaEnMateria($nrc)) {
            $estudiante->materias()->attach($nrc, [
                'profesor_id' => Auth::id(),
                'status'      => 'activo',
            ]);

            $userAlumno = User::where('email', $estudiante->email)->first();
            if ($userAlumno) {
                $this->vincularAlumnoMateria($userAlumno, $nrc, $estudiante->codigo_estudiante);
            }
        }

        return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
            ->with('success', "Estudiante {$estudiante->nombre} agregado a esta materia.");
    }

    /**
     * Formulario de importación
     */
    public function showImport(Request $request)
    {
        $nrc     = $request->query('nrc');
        $materia = null;

        if ($nrc) {
            $materia = Materia::where('nrc', $nrc)->where('profesor_id', Auth::id())->firstOrFail();
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
        ]);

        $materia = Materia::where('nrc', $nrc)->where('profesor_id', Auth::id())->firstOrFail();

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