<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Materia;
use App\Models\User;
use App\Imports\EstudiantesImport;
use App\Imports\HtmImport;
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
     * Procesar importación HTM oficial BUAP
     * Detecta nuevos alumnos y alumnos que ya no aparecen en el HTM
     */
    public function import(Request $request)
    {
        $nrc = $request->input('nrc');

        $request->validate([
            'archivo_htm' => ['required', 'file', 'mimes:htm,html', 'max:10240'],
            'nrc'         => ['required', 'string'],
        ], [
            'archivo_htm.required' => 'El archivo HTM de la lista oficial es obligatorio.',
            'archivo_htm.mimes'    => 'El archivo debe ser .htm o .html (descargado del SIIAA BUAP).',
        ]);

        $materia = Materia::where('nrc', $nrc)->where('profesor_id', Auth::id())->firstOrFail();

        try {
            $htmContent = file_get_contents($request->file('archivo_htm')->getRealPath());

            // Alumnos actualmente en la materia ANTES de importar
            $activos = $materia->estudiantes()->wherePivot('status', 'activo')->get();
            $totalAntes = $activos->count();

            $import = new HtmImport($nrc);
            $import->procesar($htmContent);

            $importados      = $import->totalImportados();
            $duplicados      = $import->getDuplicados();
            $yaEnOtraMateria = $import->getYaEnOtraMateria();
            $codigosHtm      = $import->getCodigosHtm();

            // Detectar alumnos que ya no aparecen en el nuevo HTM
            $faltantes = $activos->filter(function ($estudiante) use ($codigosHtm) {
                return $estudiante->codigo_estudiante &&
                       !in_array($estudiante->codigo_estudiante, $codigosHtm);
            })->map(fn($e) => [
                'id'     => $e->id,
                'nombre' => $e->nombre,
                'codigo' => $e->codigo_estudiante,
            ])->values()->toArray();

            // Total después de importar
            $totalDespues = $materia->estudiantes()->wherePivot('status', 'activo')->count();

            // Construir mensaje
            if ($importados === 0 && count($duplicados) > 0 && count($faltantes) === 0) {
                return redirect()->route('profesor.estudiantes.import', ['nrc' => $nrc])
                    ->with('warning', 'No se agregó ningún estudiante nuevo. Todos ya estaban en esta materia.')
                    ->with('duplicados', $duplicados);
            }

            $msg = '';
            if ($importados > 0) {
                $msg = " Se agregaron {$importados} estudiante(s) nuevo(s).";
            } elseif ($totalDespues === $totalAntes) {
                $msg = "La lista está actualizada. No hubo cambios en el número de alumnos.";
            }

            return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
                ->with('success', $msg ?: "Importación completada.")
                ->with('duplicados', $duplicados)
                ->with('yaEnOtraMateria', $yaEnOtraMateria)
                ->with('faltantes', $faltantes)
                ->with('total_antes', $totalAntes)
                ->with('total_despues', $totalDespues)
                ->with('nrc_import', $nrc);

        } catch (\Exception $e) {
            return redirect()->route('profesor.estudiantes.import', ['nrc' => $nrc])
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Dar de baja a alumnos que ya no aparecen en el HTM
     */
    public function bajaFaltantes(Request $request)
    {
        $nrc = $request->input('nrc');
        $ids = $request->input('ids', []);

        $materia = Materia::where('nrc', $nrc)->where('profesor_id', Auth::id())->firstOrFail();

        $dados_baja = 0;
        foreach ($ids as $id) {
            $estudiante = Estudiante::find($id);
            if ($estudiante && $estudiante->estaEnMateria($nrc)) {
                $estudiante->materias()->updateExistingPivot($nrc, ['status' => 'baja']);
                $dados_baja++;
            }
        }

        return redirect()->route('profesor.estudiantes.index', ['nrc' => $nrc])
            ->with('success', "Se dieron de baja {$dados_baja} alumno(s) que ya no aparecen en la lista oficial.");
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