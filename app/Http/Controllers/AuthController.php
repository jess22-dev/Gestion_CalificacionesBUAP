<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request) 
{
    // 1. Recibimos el rol seleccionado en la vista
    $role = $request->input('role');

    if ($role == 'alumno') {
        // 2. Lógica para Estudiantes (Solo código/clave única)
        // Buscamos en la tabla de unión que creamos
        $registro = \DB::table('alumno_materia')
                        ->where('clave_unica', $request->codigo)
                        ->first();

        if ($registro && $registro->status == 'activo') {
            Auth::loginUsingId($registro->alumno_id);
            return redirect()->route('alumno.dashboard');
        }
    } else {
        // 3. Lógica para Admin y Profesor (Email y Password)
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // Validamos que el usuario tenga el rol que seleccionó
            if (Auth::user()->role == $role) {
                return redirect()->intended($role . '/dashboard');
            }
            Auth::logout(); // Si el rol no coincide, lo sacamos
        }
    }

    return back()->withErrors(['error' => 'Credenciales incorrectas o rol no autorizado']);
}
}
