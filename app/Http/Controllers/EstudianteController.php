<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Imports\EstudiantesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EstudianteController extends Controller
{
    /**
     * Listar todos los estudiantes
     */
    public function index()
    {
        $estudiantes = Estudiante::latest()->paginate(15);
        return view('estudiantes.index', compact('estudiantes'));
    }

    /**
     * Mostrar formulario para agregar manualmente
     */
    public function create()
    {
        return view('estudiantes.create');
    }

    /**
     * Guardar estudiante manual catch
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255', 'unique:estudiantes,email'],
            'codigo_estudiante' => ['required', 'string', 'digits:9', 'unique:estudiantes,codigo_estudiante'],
        ], [
            'nombre.required'               => 'El nombre es obligatorio.',
            'email.required'                => 'El email es obligatorio.',
            'email.email'                   => 'El email no tiene un formato válido.',
            'email.unique'                  => 'Ya existe un estudiante con ese email.',
            'codigo_estudiante.required'    => 'El código es obligatorio.',
            'codigo_estudiante.unique'      => 'Ya existe un estudiante con ese código.',
            'codigo_estudiante.digits'      => 'El código debe ser de 9 digitos.',
        ]);

        Estudiante::create($request->only('nombre', 'email', 'codigo_estudiante'));

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante agregado correctamente.');
    }

    /**
     * Mostrar formulario de importación
     */
    public function showImport()
    {
        return view('estudiantes.import');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Estudiante $estudiante)
    {
        return view('estudiantes.edit', compact('estudiante'));
    }

    /**
     * Actualizar estudiante
     */
    public function update(Request $request, Estudiante $estudiante)
    {
        $request->validate([
            'nombre'            => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255', 'unique:estudiantes,email,' . $estudiante->id],
            'codigo_estudiante' => ['required', 'digits:9', 'unique:estudiantes,codigo_estudiante,' . $estudiante->id],
        ], [
            'nombre.required'               => 'El nombre es obligatorio.',
            'email.required'                => 'El email es obligatorio.',
            'email.email'                   => 'El email no tiene un formato válido.',
            'email.unique'                  => 'Ya existe un estudiante con ese email.',
            'codigo_estudiante.required'    => 'El código es obligatorio.',
            'codigo_estudiante.digits'      => 'El código debe ser de 9 dígitos.',
            'codigo_estudiante.unique'      => 'Ya existe un estudiante con ese código.',
        ]);

        $estudiante->update($request->only('nombre', 'email', 'codigo_estudiante'));

        return redirect()->route('estudiantes.index')
            ->with('success', 'Estudiante actualizado correctamente.');
}

    /**
     * Procesar el archivo Excel/CSV importado
     */
    public function import(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'archivo.required' => 'Debes seleccionar un archivo.',
            'archivo.mimes'    => 'El archivo debe ser .xlsx, .xls o .csv.',
            'archivo.max'      => 'El archivo no debe superar los 5MB.',
        ]);

        try {
            $import = new EstudiantesImport();
            Excel::import($import, $request->file('archivo'));

            $duplicados  = $import->getDuplicados();
            $importados  = $import->getImportados();

            // Caso 1: Solo duplicados, nada nuevo
            if ($importados === 0 && count($duplicados) > 0) {
                return redirect()->route('estudiantes.import')
                    ->with('warning', 'No se agregó ningún estudiante. Todos los registros ya existen.')
                    ->with('duplicados', $duplicados);
            }

            // Caso 2: Algunos importados y algunos duplicados
            if ($importados > 0 && count($duplicados) > 0) {
                return redirect()->route('estudiantes.index')
                    ->with('warning', "Se importaron {$importados} estudiante(s). " . count($duplicados) . " registro(s) ya existían y fueron omitidos.")
                    ->with('duplicados', $duplicados);
            }

            // Caso 3: Todo importado sin duplicados
            return redirect()->route('estudiantes.index')
                ->with('success', "Se importaron {$importados} estudiante(s) correctamente.");




        } 
        catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
    $errores = [];
    foreach ($e->failures() as $failure) {
        $errores[] = "Fila {$failure->row()}: " . implode(', ', $failure->errors());
    }
    return redirect()->route('estudiantes.import')
        ->with('warning', 'Algunos registros no se importaron por datos inválidos o incompletos.')
        ->with('errores', $errores);


        } catch (\Exception $e) {
            return redirect()->route('estudiantes.import')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de un estudiante
     */
    public function show(Estudiante $estudiante)
    {
        $estudiante->load('grupos');
        return view('estudiantes.show', compact('estudiante'));
    }
}