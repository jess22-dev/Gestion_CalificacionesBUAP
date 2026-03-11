<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Añadido para buscar el código
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. LÓGICA ESPECIAL PARA ALUMNOS (Login con Clave Única)
        if ($request->role === 'alumno') {
            // Buscamos la clave en la tabla de unión [Req 7]
            $registro = DB::table('alumno_materia')
                ->where('clave_unica', $request->codigo)
                ->where('status', 'activo')
                ->first();

            if ($registro) {
                // Si existe, buscamos al usuario en la tabla 'users' y lo logueamos
                $user = \App\Models\User::find($registro->alumno_id);
                
                if ($user) {
                    Auth::login($user);
                    $request->session()->regenerate();
                    return redirect()->route('alumno.dashboard');
                }
            }

            // Si no existe o falló algo, regresamos con error específico
            return back()->withErrors([
                'codigo' => 'La clave única no es válida o el alumno no está activo.',
            ]);
        }

        // 2. LÓGICA PARA ADMIN Y PROFESOR (Login tradicional)
        $request->authenticate(); // Valida email y password

        $request->session()->regenerate();

        // Redirección inteligente según el rol al entrar
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'profesor') {
            return redirect()->route('profesor.dashboard');
        }

        // Caso por defecto (o si falló la detección de rol)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}