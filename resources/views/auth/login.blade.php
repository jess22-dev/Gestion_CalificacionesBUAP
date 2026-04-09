<x-guest-layout>
    {{-- Fondo con la imagen y centrado total --}}
    <div class="min-h-screen flex flex-col justify-center items-center bg-cover bg-center px-4" 
         style="background-image: url('{{ asset('img/fondo.png') }}');">
        
        <div class="mb-6">
            <img src="{{ asset('img/escudo_buap_a.png') }}" alt="BUAP" class="h-44 w-auto drop-shadow-xl">
        </div>

        <div class="w-full sm:max-w-md px-10 py-12 bg-white shadow-2xl rounded-[40px] border border-blue-50 relative">
            
            <h2 class="text-2xl font-bold text-[#002d62] text-center mb-10">Inicia Sesión</h2>

            @if($errors->has('error'))
                <div class="mb-4 p-3 rounded-2xl bg-red-50 border border-red-200 text-red-600 text-sm font-bold text-center">
                    {{ $errors->first('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6" id="main_login_form">
                @csrf

                {{-- Selector de Rol --}}
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1 ml-4">Tipo de Usuario</label>
                    <select name="role" id="role_selector" onchange="toggleFields()"
                            class="block w-full px-6 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner text-gray-600">
                        <option value="profesor" {{ old('role') == 'profesor' ? 'selected' : '' }}>Profesor</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="alumno" {{ old('role') == 'alumno' ? 'selected' : '' }}>Estudiante</option>
                    </select>
                </div>

                <div id="credentials_fields" class="space-y-6">
                    {{-- Email --}}
                    <div class="relative">
                        <span class="absolute inset-y-0 left-4 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </span>
                        <x-text-input id="email" 
                                     class="block w-full pl-12 pr-4 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner" 
                                     type="text" name="email" :value="old('email')" 
                                     placeholder="Correo institucional" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    {{-- Password con OJITO --}}
                    <div class="relative" x-data="{ show: false }">
                        {{-- Icono Candado Izquierda --}}
                        <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>

                        {{-- Input --}}
                        <x-text-input id="password" 
                                     class="block w-full pl-12 pr-12 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner"
                                     ::type="show ? 'text' : 'password'" 
                                     name="password" 
                                     placeholder="Contraseña" />

                        {{-- Botón Ojo Derecha --}}
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-[#002d62] transition-colors">
                            <template x-if="!show">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </template>
                            <template x-if="show">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                            </template>
                        </button>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                </div>

                {{-- Campo para Alumnos --}}
                <div id="student_fields" class="relative hidden">
                    <span class="absolute inset-y-0 left-4 flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </span>
                    <x-text-input id="codigo" 
                                 class="block w-full pl-12 pr-4 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner" 
                                 type="text" name="codigo" :value="old('codigo')"
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

    <script>
        function toggleFields() {
            const role = document.getElementById('role_selector').value;
            const credentialsFields = document.getElementById('credentials_fields');
            const studentFields = document.getElementById('student_fields');
            const forgotLink = document.getElementById('forgot_password_link');
            
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const codigoInput = document.getElementById('codigo');

            if (role === 'alumno') {
                credentialsFields.classList.add('hidden');
                studentFields.classList.remove('hidden');
                forgotLink.classList.add('hidden');
                emailInput.value = "";
                passwordInput.value = "";
            } else {
                credentialsFields.classList.remove('hidden');
                studentFields.classList.add('hidden');
                forgotLink.classList.remove('hidden');
                codigoInput.value = "";
            }
        }
        window.onload = toggleFields;
    </script>
</x-guest-layout>