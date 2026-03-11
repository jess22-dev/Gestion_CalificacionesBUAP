<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Mi Carga Académica - Alumno BUAP') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#f0f4f8] via-white to-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Sección de Bienvenida --}}
            <div class="mb-8 bg-white p-6 rounded-2xl shadow-sm border-l-8 border-[#002d62]">
                <h3 class="text-2xl font-bold text-[#1e4b8a]">¡Hola, {{ Auth::user()->name }}!</h3>
                <p class="text-gray-500">Aquí puedes consultar tus materias inscritas y tus claves únicas de seguimiento.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Usamos la relación materiasInscritas que pusimos en el User.php --}}
                @forelse(Auth::user()->materiasInscritas as $materia)
                    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
                        
                        <div class="bg-[#f8fafc] p-5 border-b border-gray-100 flex justify-between items-center">
                            <div>
                                <h4 class="text-lg font-bold text-[#1e4b8a]">{{ $materia->nombre }}</h4>
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Código: {{ $materia->codigo }}</span>
                            </div>
                            <div class="bg-[#1e4b8a] text-white px-3 py-1 rounded-full text-xs font-bold">
                                NRC: {{ $materia->nrc }}
                            </div>
                        </div>
                        
                        <div class="p-6">
                            {{-- La Clave Única resaltada --}}
                            <div class="bg-[#eef2f7] rounded-xl p-4 mb-4 border border-blue-100">
                                <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Clave Única de Inscripción</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-xl font-mono font-black text-[#002d62] tracking-wider">
                                        {{ $materia->pivot->clave_unica ?? 'PENDIENTE' }}
                                    </span>
                                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>

                            <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                {{-- Si tienes la relación con el profesor configurada --}}
                                Profesor: <span class="ml-1 font-semibold text-gray-700"> {{ $materia->profesor->name ?? 'Por asignar' }}</span>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-between items-center">
                            <span class="text-xs font-bold text-green-600 uppercase italic">
                                ● {{ $materia->pivot->status ?? 'Inscrito' }}
                            </span>
                            <button class="text-[#1e4b8a] text-xs font-bold hover:underline">Ver detalles</button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-12 rounded-2xl border-2 border-dashed border-gray-200 text-center">
                        <p class="text-gray-500 text-lg">No se encontraron materias en tu carga actual.</p>
                        <p class="text-gray-400 text-sm mt-2">Asegúrate de haber completado tu proceso de reinscripción.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>