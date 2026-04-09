<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // IMPORTANTE: Faltaba esta línea
use Illuminate\Support\Facades\DB;   // IMPORTANTE: Faltaba esta línea

class AuthController extends Controller
{
    public function login(Request $request) 
    {
    $role = $request->input('role');

    if ($role === 'alumno') {
        // Acceso para alumnos
        $registro = DB::table('alumno_materia')
                        ->where('clave_unica', $request->input('codigo'))
                        ->first();

        if ($registro) {
            Auth::loginUsingId($registro->alumno_id);
            return redirect()->route('alumno.dashboard');
        }
    } else {
        // Acceso para Profesor / Admin
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if (strtolower($user->role) === strtolower($role)) {
                return redirect()->intended($role . '/dashboard');
            }
            Auth::logout();
            return back()->withErrors(['error' => 'El rol seleccionado no coincide con tu cuenta.']);
        }
    }

    return back()->withErrors(['error' => 'Credenciales incorrectas. Verifica tus datos.']);
    }
}