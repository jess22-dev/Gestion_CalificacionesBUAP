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

                <div class="relative">
                    <span class="absolute inset-y-0 left-4 flex items-center">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </span>
                    <x-text-input id="email" 
                                 class="block w-full pl-12 pr-4 py-3 rounded-full border-blue-100 bg-gray-50 focus:border-blue-500 focus:ring-blue-500 shadow-inner" 
                                 type="email" name="email" :value="old('email')" 
                                 required autofocus placeholder="Matrícula / ID Trabajador" />
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
                                 required placeholder="Contraseña" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="pt-4 text-center">
                    <button type="submit" 
                            class="w-full py-3 bg-[#005c97] hover:bg-[#002d62] text-white font-bold rounded-full text-lg transition duration-300 shadow-lg active:scale-95">
                        {{ __('Acceder') }}
                    </button>
                    
                    @if (Route::has('password.request'))
                        <div class="mt-4">
                            <a class="text-sm text-gray-500 hover:text-[#002d62] transition duration-150 underline" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>