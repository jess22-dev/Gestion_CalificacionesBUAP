<x-app-layout>
    <x-slot name="header">
         <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Panel de Control - Profesor BUAP') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Cambiamos $grupos por $materias para que coincida con el controlador --}}
                @forelse($materias as $materia)
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transform hover:scale-105 transition-transform duration-300">
                        
                        <div class="bg-[#1e4b8a] p-5 h-20 flex items-center">
                            {{-- Mostramos el nombre de la materia --}}
                            <h4 class="text-xl font-bold text-white truncate">{{ $materia->nombre }}</h4>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <div class="bg-[#cfe2f3] rounded-full p-4 flex justify-between items-center">
                                <span class="font-semibold text-[#1e4b8a]">NRC:</span>
                                <span class="text-lg font-bold text-[#002d62]">{{ $materia->nrc }}</span>
                            </div>
                            
                            <p class="text-gray-500 text-sm italic">Código: {{ $materia->codigo }}</p>
                            <p class="text-gray-400 text-xs">Sección asignada para el periodo actual. Verifique la lista de alumnos antes de continuar.</p>
                            
                            {{-- Corregimos la ruta para que use la que definimos en web.php --}}
                            <a href="{{ route('profesor.materias.show', $materia->id) }}" 
                               class="inline-flex items-center justify-center w-full px-5 py-3 bg-[#1e4b8a] border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-[#002d62] transition duration-150 shadow-md">
                                Entrar al grupo
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    {{-- Caso por si el profesor no tiene materias asignadas --}}
                    <div class="col-span-full bg-white p-12 rounded-2xl border-2 border-dashed border-gray-300 text-center">
                        <div class="mb-4">
                            <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-lg font-bold">Aún no tienes materias registradas.</p>
                        <p class="text-gray-400 text-sm mt-2">Contacta a Secretaría Académica si crees que esto es un error.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>