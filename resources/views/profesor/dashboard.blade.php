<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-white tracking-tight italic">
               <span class="text-blue-200 font-light ml-2"> Profesor BUAP</span>
            </h2>
            <div class="hidden md:block text-right">
                <p class="text-blue-200 font-light ml-2">Primavera 2026</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Encabezado de bienvenida --}}
            <div class="mb-10 ml-4 lg:ml-0">
                <h3 class="text-3xl font-black text-[#002d62] tracking-tighter">Materias Asignadas</h3>
                <p class="text-gray-500 font-medium italic">Selecciona un grupo para gestionar asistencia y evaluaciones.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($materias as $materia)
                    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-gray-100 overflow-hidden transform hover:scale-[1.03] transition-all duration-300 group">
                        
                        {{-- Cabecera con Nombre de Materia --}}
                        <div class="bg-gradient-to-br from-[#1e4b8a] to-[#002d62] p-8 h-32 flex items-start justify-between relative overflow-hidden">
                            <div class="relative z-10">
                                <p class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-1">Materia</p>
                                {{-- CORREGIDO: Usamos Materia con M mayúscula --}}
                                <h4 class="text-xl font-black text-white leading-tight italic">{{ $materia->Materia }}</h4>
                            </div>
                            <svg class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10 transform rotate-12" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.827a1 1 0 00-.788 0l-7 3a1 1 0 000 1.848l7 3a1 1 0 00.788 0l7-3a1 1 0 000-1.848l-7-3zM14 9.528c.538.303.812.646.812.972 0 .326-.274.669-.812.972l-3.394 1.912a1 1 0 01-.788 0L6.414 11.472c-.538-.303-.812-.646-.812-.972 0-.326.274-.669.812-.972L10 11.528l4-2z"></path>
                            </svg>
                        </div>
                        
                        <div class="p-8 space-y-6">
                            {{-- Badge de NRC --}}
                            <div class="bg-blue-50 rounded-2xl p-4 flex justify-between items-center border border-blue-100">
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">Código NRC</span>
                                    <span class="text-lg font-black text-[#002d62]">{{ $materia->nrc }}</span>
                                </div>
                                <div class="h-8 w-px bg-blue-200"></div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">Estado</span>
                                    <span class="text-lg font-black text-[#002d62] italic">Activo</span>
                                </div>
                            </div>
                            
                            <div class="space-y-1">
                                <p class="text-gray-500 text-xs font-bold flex items-center italic">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                    {{-- CORREGIDO: Usamos clave en lugar de codigo --}}
                                    Clave: {{ $materia->clave }}
                                </p>
                                <p class="text-gray-400 text-[10px] leading-relaxed">Verifique la lista de alumnos y el plan de ponderación antes de iniciar capturas.</p>
                            </div>
                            
                            {{-- Botón de Acción --}}
                            {{-- Nota: Asegúrate que la ruta profesor.materias acepte el ID --}}
                            <a href="{{ route('profesor.materias', $materia->id) }}" 
                               class="flex items-center justify-center w-full py-4 bg-[#002d62] text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-[#1e4b8a] transition-all shadow-lg shadow-blue-900/20 active:scale-95 group">
                                Entrar al grupo
                                <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-20 rounded-[3rem] border-2 border-dashed border-gray-200 text-center shadow-inner">
                        <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-black text-gray-400 uppercase tracking-widest">Sin Materias Registradas</h4>
                        <p class="text-gray-400 text-sm mt-2 italic font-medium">No se encontraron grupos asociados a tu ID de usuario.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>