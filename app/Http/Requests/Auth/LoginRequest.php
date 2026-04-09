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
        // 1. Si es alumno, el email NO es obligatorio ni debe validarse como tal
        if ($this->input('role') === 'alumno') {
            return [
                'role'   => ['required', 'string'],
                'codigo' => ['required', 'string'],
            ];
        }

        // 2. Si es profesor o admin, el email SÍ es requerido
        return [
            'role'     => ['required', 'string'],
            'email'    => ['required', 'string', 'email'], // Aquí sí validamos formato email
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Intentar autenticar
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // 3. Si es alumno, salimos de aquí. La lógica vive en el controlador.
        if ($this->input('role') === 'alumno') {
            return; 
        }

        // 4. Para Profesores y Admins: Intentamos el login tradicional
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
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
        // Usamos el identificador según el rol
        $identifier = ($this->input('role') === 'alumno') 
            ? $this->input('codigo') 
            : $this->input('email');
        
        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}