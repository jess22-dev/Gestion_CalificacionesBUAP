<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación dinámicas
     */
    public function rules(): array
    {
        // Si el usuario seleccionó 'alumno' en el select del login
        if ($this->role === 'alumno') {
            return [
                'role'   => ['required', 'string'],
                'codigo' => ['required', 'string'],
            ];
        }

        // Si es profesor o admin, pedimos los datos tradicionales
        return [
            'role'     => ['required', 'string'],
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Intentar autenticar
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Si es alumno, NO intentamos Auth::attempt porque no usa password tradicional.
        // La lógica de búsqueda por código ya la pusimos en el AuthenticatedSessionController.
        if ($this->role === 'alumno') {
            return; 
        }

        // Para Profesores y Admins:
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Llave para el límite de intentos (Throttling)
     */
    public function throttleKey(): string
    {
        // Si es alumno usamos su código, si no, su email para bloquear intentos fallidos
        $identifier = $this->role === 'alumno' ? $this->string('codigo') : $this->string('email');
        
        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}