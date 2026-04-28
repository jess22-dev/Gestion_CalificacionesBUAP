<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $role = $request->input('role');

        // Login especial para alumnos con clave única
        if ($role === 'alumno') {
            $claveUnica = trim($request->input('codigo'));

            // Buscar estudiante por clave única
            $estudiante = Estudiante::where('clave_unica', $claveUnica)->first();

            if (!$estudiante) {
                return back()->withErrors([
                    'codigo' => 'La clave única no es válida. Verifica e intenta de nuevo.',
                ])->onlyInput('role');
            }

            // Buscar el user con rol alumno asociado a ese email
            $userAlumno = User::where('email', $estudiante->email)
                              ->where('role', 'alumno')
                              ->first();

            if (!$userAlumno) {
                return back()->withErrors([
                    'codigo' => 'No se encontró una cuenta de alumno asociada a esta clave.',
                ])->onlyInput('role');
            }

            // Autenticar al alumno usando el guard web
            Auth::login($userAlumno);
            $request->session()->regenerate();

            return redirect()->route('alumno.dashboard');
        }

        // Login normal para profesor y admin
        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}