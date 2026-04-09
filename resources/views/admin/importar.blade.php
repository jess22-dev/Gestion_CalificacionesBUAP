<x-app-layout>
    <x-slot name="header">
        {{-- Header Limpio --}}
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-white tracking-tight italic">
                {{ __('Importar Carga Académica') }}
                <span class="text-blue-200 font-light ml-2">| Administrador</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- 1. BOTÓN VOLVER (Ahora en la sección clara, con mejor espacio) --}}
            <div class="mb-6">
                <a href="{{ route('admin.dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-bold text-xs text-[#002d62] uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-all duration-200 group">
                    <svg class="w-4 h-4 mr-2 text-[#1e4b8a] transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Volver al Panel Administrativo
                </a>
            </div>

            {{-- Mensajes de Feedback --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 shadow-sm rounded-r-xl">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-bold text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- 2. TARJETA DE SUBIDA --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-[2.5rem] border border-gray-100 mb-8">
                <div class="p-10">
                    <div class="flex items-center space-x-3 mb-8">
                        <div class="bg-[#002d62] p-3 rounded-2xl text-white shadow-lg shadow-blue-900/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-[#002d62] tracking-tight">Subir Carga Académica</h3>
                            <p class="text-gray-400 text-sm font-medium italic">Sincroniza materias y profesores mediante Excel</p>
                        </div>
                    </div>

                    <form action="{{ route('excel.importar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-end">
                            <div class="md:col-span-2">
                                <label for="archivo" class="block text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] mb-3 ml-1">Seleccionar Documento (.xlsx, .xls)</label>
                                <div class="relative">
                                    <input id="archivo" type="file" name="archivo" required 
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:tracking-widest file:bg-blue-50 file:text-[#1e4b8a] hover:file:bg-blue-100 border-2 border-dashed border-gray-200 rounded-2xl p-4 transition-all hover:border-blue-300" />
                                </div>
                            </div>
                            
                            <div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-8 py-4 bg-[#002d62] hover:bg-[#1e4b8a] text-white font-black rounded-2xl transition-all shadow-xl shadow-blue-900/20 uppercase tracking-[0.15em] text-xs active:scale-95">
                                    {{ __('Procesar Carga') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 3. TABLA DE RESULTADOS --}}
            @if(isset($materias) && $materias->count() > 0)
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-[2.5rem] border border-gray-100">
                <div class="p-10">
                    <h3 class="text-xl font-black text-[#1e4b8a] mb-8 flex items-center">
                        <span class="bg-blue-600 w-1.5 h-8 rounded-full mr-4"></span>
                        Credenciales Generadas
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">NRC</th>
                                    <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Materia</th>
                                    <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Profesor</th>
                                    <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Email (Usuario)</th>
                                    <th class="px-6 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Contraseña</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                @foreach($materias as $materia)
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-[#002d62]">{{ $materia->nrc }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">{{ $materia->Materia }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">{{ $materia->Profesor }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $materia->profesorRelacion->email ?? 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <code class="text-[11px] font-black bg-gray-100 px-2 py-1 rounded text-gray-500">buap1234</code>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>