<x-guest-layout>
    {{-- Fondo con la imagen y centrado total --}}
    <div class="min-h-screen flex flex-col justify-center items-center bg-cover bg-center px-4" 
         style="background-image: url('{{ asset('img/fondo.png') }}');">
        
        <div class="mb-6">
            <img src="{{ asset('img/escudo_buap_a.png') }}" alt="BUAP" class="h-44 w-auto drop-shadow-xl">
        </div>

        <div class="w-full sm:max-w-md px-10 py-12 bg-white shadow-2xl rounded-[40px] border border-blue-50 relative">
            
            <h2 class="text-2xl font-bold text-[#002d62] text-center mb-10">Inicia Sesión</h2>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                {{-- Selector de Rol --}}
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1 ml-4">Tipo de Usuario</label>
                    <select name="role" id="role_selector" onchange="toggleFields()"
                            class="block w-full px-6 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner text-gray-600">
                        <option value="profesor">Profesor</option>
                        <option value="admin">Administrador</option>
                        <option value="alumno">Estudiante</option>
                    </select>
                </div>

                {{-- Campos para Admin y Profesor (Email y Password) --}}
                <div id="credentials_fields" class="space-y-6">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-4 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <x-text-input id="email" 
                                     class="block w-full pl-12 pr-4 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner" 
                                     type="email" name="email" :value="old('email')" 
                                     placeholder="Correo institucional" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-4 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <x-text-input id="password" 
                                     class="block w-full pl-12 pr-4 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner"
                                     type="password" name="password" 
                                     placeholder="Contraseña" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                </div>

                {{-- Campo para Alumnos (Código) --}}
                <div id="student_fields" class="relative hidden">
                    <span class="absolute inset-y-0 left-4 flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </span>
                    <x-text-input id="codigo" 
                                 class="block w-full pl-12 pr-4 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner" 
                                 type="text" name="codigo" 
                                 placeholder="Clave única (Enviada por correo)" />
                    <x-input-error :messages="$errors->get('codigo')" class="mt-2" />
                </div>

                <div class="pt-4 text-center">
                    <button type="submit" 
                            class="w-full py-3 bg-[#005c97] hover:bg-[#002d62] text-white font-bold rounded-full text-lg transition duration-300 shadow-lg active:scale-95">
                        {{ __('Acceder') }}
                    </button>
                    
                    <div id="forgot_password_link" class="mt-4">
                        @if (Route::has('password.request'))
                            <a class="text-sm text-gray-500 hover:text-[#002d62] transition duration-150 underline" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Script para cambiar campos dinámicamente --}}
    <script>
        function toggleFields() {
            const role = document.getElementById('role_selector').value;
            const credentialsFields = document.getElementById('credentials_fields');
            const studentFields = document.getElementById('student_fields');
            const forgotLink = document.getElementById('forgot_password_link');

            if (role === 'alumno') {
                credentialsFields.classList.add('hidden');
                studentFields.classList.remove('hidden');
                forgotLink.classList.add('hidden'); // Los alumnos no tienen contraseña que recuperar
            } else {
                credentialsFields.classList.remove('hidden');
                studentFields.classList.add('hidden');
                forgotLink.classList.remove('hidden');
            }
        }
    </script>
</x-guest-layout>