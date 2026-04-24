<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight italic">
            {{ __('Pase de Lista - ') }} {{ $materia->nombre_materia ?? $materia->Materia }} ({{ $materia->nrc }})
        </h2>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
                
                <form action="{{ route('asistencias.guardar', $materia->nrc) }}" method="POST">
                    @csrf
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-[#002d62]">Lista de Alumnos</h3>
                            <p class="text-gray-500">Fecha: {{ date('d/m/Y') }}</p>
                        </div>
                        {{-- Botón para resetear (Req. 12) --}}
                        <button type="reset" class="text-red-500 font-bold text-sm hover:underline italic flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Reiniciar Lista (Limpiar Selección)
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="w-full">
                            <thead class="bg-gray-50 text-gray-400 text-xs uppercase">
                                <tr>
                                    <th class="p-4 text-left">ID / Matrícula</th>
                                    <th class="p-4 text-left">Nombre del Alumno</th>
                                    <th class="p-4 text-center">Asistencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($alumnos as $alumno)
                                    <tr class="hover:bg-blue-50/50 transition">
                                        <td class="p-4 font-mono text-xs">{{ $alumno->id }}</td>
                                        <td class="p-4 font-bold text-gray-700 italic">{{ $alumno->name }}</td>
                                        <td class="p-4 text-center">
                                            {{-- Guardamos el ID del alumno en un array para el controlador --}}
                                            <input type="checkbox" name="asistencias[{{ $alumno->id }}]" value="P" checked 
                                                class="w-5 h-5 text-blue-600 rounded focus:ring-[#002d62]">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="p-8 text-center text-gray-500 italic">
                                            No hay alumnos inscritos en este grupo todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="bg-[#002d62] text-white px-8 py-3 rounded-xl font-black shadow-lg hover:scale-105 transition-transform">
                            CONFIRMAR LISTA OFICIAL ✅
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>