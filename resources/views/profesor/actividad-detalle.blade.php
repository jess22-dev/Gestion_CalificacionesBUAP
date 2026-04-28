<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight italic">
            {{ $actividad->nombre }} — {{ $materia->Materia }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('materias.show', $materia->nrc) }}"
                   class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                    ← Volver a {{ $materia->Materia }}
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Info de la actividad --}}
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 mb-6 overflow-hidden">
                <div class="bg-[#1e4b8a] p-5 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black">{{ $actividad->nombre }}</h3>
                        <p class="text-blue-200 text-sm mt-1">
                            {{ $actividad->categoria }} · Ponderación: {{ $actividad->ponderacion }}%
                        </p>
                    </div>
                    <div class="text-right">
                        @php
                            $entregados = $alumnos->filter(fn($a) => $a->pivot->entregado)->count();
                            $calificados = $alumnos->filter(fn($a) => !is_null($a->pivot->calificacion))->count();
                        @endphp
                        <p class="text-blue-200 text-xs font-bold">{{ $entregados }}/{{ $alumnos->count() }} entregaron</p>
                        <p class="text-blue-200 text-xs font-bold">{{ $calificados }}/{{ $alumnos->count() }} calificados</p>
                    </div>
                </div>
            </div>

            {{-- Tabla de alumnos con calificaciones --}}
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="p-4 text-left text-xs font-black text-gray-500 uppercase">Alumno</th>
                                <th class="p-4 text-center text-xs font-black text-gray-500 uppercase">Archivo</th>
                                <th class="p-4 text-center text-xs font-black text-gray-500 uppercase">Estado</th>
                                <th class="p-4 text-center text-xs font-black text-gray-500 uppercase">Calificación</th>
                                <th class="p-4 text-center text-xs font-black text-gray-500 uppercase">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($alumnos as $alumno)
                                @php
                                    $pivot = $alumno->pivot;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4">
                                        <p class="font-bold text-gray-800">{{ $alumno->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $alumno->email }}</p>
                                    </td>

                                    {{-- Archivo --}}
                                    <td class="p-4 text-center">
                                        @if($pivot->archivo_path)
                                            <a href="{{ Storage::url($pivot->archivo_path) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-xs font-bold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                </svg>
                                                {{ Str::limit($pivot->archivo_nombre, 20) }}
                                            </a>
                                        @else
                                            <span class="text-gray-300 text-xs italic">Sin archivo</span>
                                        @endif
                                    </td>

                                    {{-- Estado entrega --}}
                                    <td class="p-4 text-center">
                                        @if($pivot->entregado)
                                            <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">Entregado</span>
                                        @else
                                            <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-full">Pendiente</span>
                                        @endif
                                    </td>

                                    {{-- Calificación actual --}}
                                    <td class="p-4 text-center">
                                        @if(!is_null($pivot->calificacion))
                                            <span class="text-2xl font-black {{ $pivot->calificacion >= 6 ? 'text-green-600' : 'text-red-500' }}">
                                                {{ $pivot->calificacion }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-sm italic">—</span>
                                        @endif
                                    </td>

                                    {{-- Formulario calificar --}}
                                    <td class="p-4 text-center">
                                        <form method="POST"
                                              action="{{ route('profesor.actividades.calificar', [$materia->nrc, $actividad->id]) }}"
                                              class="flex items-center gap-2 justify-center">
                                            @csrf
                                            <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                                            <input type="number" name="calificacion"
                                                value="{{ $pivot->calificacion }}"
                                                min="0" max="10" step="0.1"
                                                placeholder="0-10"
                                                class="w-20 rounded-lg border-gray-200 text-sm text-center focus:ring-[#002d62]">
                                            <button type="submit"
                                                class="bg-[#002d62] text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-[#1e4b8a] transition">
                                                Guardar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-10 text-center text-gray-400 italic">
                                        No hay alumnos vinculados a esta actividad.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
