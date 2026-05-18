<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Estudiante;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $role = $request->input('role');

        if ($role === 'alumno') {
            // Buscar el estudiante por su clave_unica (la del admin)
            $estudiante = Estudiante::where('clave_unica', $request->input('codigo'))->first();

            if (!$estudiante) {
                return back()->withErrors(['error' => 'Credenciales incorrectas. Verifica tus datos.']);
            }

            // Buscar el User asociado por email
            $user = \App\Models\User::where('email', $estudiante->email)
                        ->where('role', 'alumno')
                        ->first();

            if (!$user) {
                return back()->withErrors(['error' => 'Tu cuenta aún no ha sido activada. Contacta al administrador.']);
            }

            Auth::login($user);
            return redirect()->route('alumno.dashboard');

        } else {
            // Acceso para Profesor / Admin
            $credentials = [
                'email'    => $request->input('email'),
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