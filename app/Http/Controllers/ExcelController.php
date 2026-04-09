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
        // 1. Validar que venga el archivo
        $request->validate([
            'archivo' => 'required',
        ]);

        $archivo = $request->file('archivo');

        // 2. Convertir Excel a Array
        $data = Excel::toArray([], $archivo);

        // 3. Recorrer las filas
        foreach ($data[0] as $index => $fila) {
            // Saltamos encabezado (fila 0) o filas donde el NRC (Col A) esté vacío
            if ($index == 0 || empty($fila[0])) continue; 

            $nrc            = $fila[0];  // Columna A
            $clave          = $fila[1];  // Columna B 
            $materiaNombre  = $fila[2];  // Columna C
            $nombreProfesor = $fila[3];  // Columna D
            $correo         = trim($fila[4]); // Columna E (Limpiamos espacios)

            // 4. Crear o buscar al profesor en la tabla 'users'
            // IMPORTANTE: Buscamos por EMAIL, no por nombre, para evitar duplicados si cambia el nombre
            $userProfesor = User::firstOrCreate(
                ['email' => $correo], // Criterio de búsqueda
                [
                    'name'     => $nombreProfesor,
                    'password' => Hash::make('buap1234'), // CONTRASEÑA PREDETERMINADA
                    'role'     => 'profesor' 
                ]
            );

            // 5. Crear o actualizar la materia en la tabla 'materias'
            Materia::updateOrCreate(
                ['nrc' => $nrc], 
                [
                    'clave'       => $clave,
                    'Materia'     => $materiaNombre,   
                    'Profesor'    => $nombreProfesor, 
                    'profesor_id' => $userProfesor->id
                ]
            );
        }

        return back()->with('success', '¡Carga Académica importada con éxito!');
    }
}