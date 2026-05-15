<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $role = $request->input('role');

        if ($role === 'alumno') {
            $request->validate([
                'codigo' => 'required|string'
            ]);

            $registro = DB::table('alumno_materia')
                ->where('clave_unica', $request->input('codigo'))
                ->where('status', 'activo')
                ->first();

            if (!$registro) {
                return back()->withErrors([
                    'codigo' => 'La clave única no es válida o el alumno no está activo.'
                ])->withInput();
            }

            Auth::loginUsingId($registro->alumno_id);
            $request->session()->regenerate();

            return redirect()->route('alumno.dashboard');
        }

        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (strtolower($user->role) === strtolower($role)) {
                return redirect()->intended(route('dashboard'));
            }

            Auth::logout();

            return back()->withErrors([
                'error' => 'El rol seleccionado no coincide con tu cuenta.'
            ])->withInput();
        }

        return back()->withErrors([
            'error' => 'Credenciales incorrectas. Verifica tus datos.'
        ])->withInput();
    }
}