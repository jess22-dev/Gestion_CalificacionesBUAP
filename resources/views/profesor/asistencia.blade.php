<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight italic">
            Pase de Lista — {{ $materia->Materia }} ({{ $materia->nrc }})
        </h2>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Botón de regreso --}}
            <div class="mb-4">
                <a href="{{ route('materias.show', $materia->nrc) }}"
                   class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                    ← Volver a {{ $materia->Materia }}
                </a>
            </div>

            {{-- Alertas --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-8 rounded-2xl shadow-xl border border-gray-100">

                <form action="{{ route('asistencias.guardar', $materia->nrc) }}" method="POST">
                    @csrf

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-[#002d62]">Lista de Alumnos</h3>
                            <p class="text-gray-500 text-sm">Fecha: {{ date('d/m/Y') }} — {{ $alumnos->count() }} alumno(s)</p>
                        </div>
                        <button type="reset"
                            class="text-red-500 font-bold text-sm hover:underline italic flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reiniciar Lista
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="w-full text-sm">
                            <thead class="bg-[#002d62] text-white text-xs uppercase">
                                <tr>
                                    <th class="p-4 text-left">Código</th>
                                    <th class="p-4 text-left">Nombre del Alumno</th>
                                    <th class="p-4 text-center">Asistencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($alumnos as $alumno)
                                    <tr class="hover:bg-blue-50/50 transition">
                                        <td class="p-4 font-mono text-blue-700 font-bold text-xs">
                                            {{ $alumno->codigo_estudiante }}
                                        </td>
                                        <td class="p-4 font-semibold text-gray-700">
                                            {{ $alumno->nombre }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <input type="checkbox"
                                                name="asistencias[{{ $alumno->codigo_estudiante }}]"
                                                value="P" checked
                                                class="w-5 h-5 accent-[#002d62] rounded focus:ring-[#002d62]">
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

                    @if($alumnos->count() > 0)
                        <div class="mt-8 flex justify-end">
                            <button type="submit"
                                class="bg-[#002d62] text-white px-8 py-3 rounded-xl font-black shadow-lg hover:bg-[#1e4b8a] transition">
                                CONFIRMAR LISTA OFICIAL 
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>