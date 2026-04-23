<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Materia;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls',
        ]);

        $data = Excel::toArray([], $request->file('archivo'));

        foreach ($data[0] as $index => $fila) {
            // Saltamos encabezado y filas sin NRC
            if ($index == 0 || empty($fila[0])) continue; 

            // Mapeo de columnas según tu migración y archivos
            $nrc      = $fila[0]; // Col A
            $clave    = $fila[1]; // Col B 
            $nombreM  = $fila[2]; // Col C
            $nombreP  = $fila[3]; // Col D
            $emailP   = isset($fila[4]) ? trim($fila[4]) : null; // Col E

            if ($emailP) {
                // Crear/Actualizar Profesor
                $user = User::updateOrCreate(
                    ['email' => $emailP],
                    [
                        'name'     => $nombreP,
                        'password' => Hash::make('buap1234'),
                        'role'     => 'profesor'
                    ]
                );

                // Crear/Actualizar Materia vinculada
                Materia::updateOrCreate(
                    ['nrc' => $nrc],
                    [
                        'clave'       => $clave,
                        'Materia'     => $nombreM,   
                        'Profesor'    => $nombreP, 
                        'profesor_id' => $user->id
                    ]
                );
            }
        }

        // Recuperamos todas las materias con su relación para la tabla
        $materias = Materia::with('profesorRelacion')->get();

        // IMPORTANTE: Regresamos a la ruta que definiste en web.php
        return redirect()->route('admin.importar')->with([
            'success'  => '¡Carga Académica importada con éxito!',
            'materias' => $materias
        ]);
    }
}