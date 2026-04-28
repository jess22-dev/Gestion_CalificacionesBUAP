<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight italic">
            Historial de Asistencia — {{ $materia->Materia }} ({{ $materia->nrc }})
        </h2>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Botón de regreso --}}
            <div class="mb-4">
                <a href="{{ route('materias.show', $materia->nrc) }}"
                   class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                    ← Volver a {{ $materia->Materia }}
                </a>
            </div>

            @if(empty($diasUnicos) || count($diasUnicos) === 0)
                <div class="bg-white rounded-2xl shadow-xl p-12 text-center border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-600 mb-2">Sin sesiones registradas</h3>
                    <p class="text-gray-400 text-sm">Aún no se ha tomado ninguna asistencia en esta materia.</p>
                    <a href="{{ route('profesor.asistencia', $materia->nrc) }}"
                       class="inline-block mt-6 bg-[#002d62] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#1e4b8a] transition">
                        Tomar primera asistencia →
                    </a>
                </div>
            @else

                {{-- Resumen general --}}
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-2xl p-5 shadow border border-gray-100 text-center">
                        <p class="text-3xl font-black text-[#002d62]">{{ count($diasUnicos) }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase mt-1">Sesiones</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow border border-gray-100 text-center">
                        <p class="text-3xl font-black text-[#002d62]">{{ $estudiantes->count() }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase mt-1">Alumnos</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow border border-gray-100 text-center">
                        @php
                            $totalAsistencias = collect($registros)->sum(fn($r) => collect($r)->filter()->count());
                            $totalPosible     = count($diasUnicos) * $estudiantes->count();
                            $porcentaje       = $totalPosible > 0 ? round(($totalAsistencias / $totalPosible) * 100) : 0;
                        @endphp
                        <p class="text-3xl font-black text-[#002d62]">{{ $porcentaje }}%</p>
                        <p class="text-xs font-bold text-gray-400 uppercase mt-1">Asistencia Global</p>
                    </div>
                </div>

                {{-- Tabla cuadrícula --}}
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-[#1e4b8a] p-4 text-white flex justify-between items-center">
                        <h3 class="font-bold text-lg">Registro de Asistencias</h3>
                        <span class="text-blue-200 text-xs"> Presente &nbsp;  Ausente</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    {{-- Columna nombre --}}
                                    <th class="p-4 text-left text-xs font-black text-gray-600 uppercase sticky left-0 bg-gray-50 z-10 min-w-[200px] border-r border-gray-200">
                                        Alumno
                                    </th>
                                    {{-- Columnas por día único --}}
                                    @foreach($diasUnicos as $dia)
                                        <th class="p-3 text-center text-xs font-bold text-gray-500 min-w-[80px] border-r border-gray-100">
                                            <div class="text-[#002d62] font-black">
                                                {{ $dia->format('d/m/Y') }}
                                            </div>
                                            <div class="text-gray-400 font-normal text-[10px]">
                                                {{ $dia->locale('es')->isoFormat('ddd') }}
                                            </div>
                                        </th>
                                    @endforeach
                                    {{-- Columna total --}}
                                    <th class="p-3 text-center text-xs font-black text-gray-600 uppercase min-w-[80px] bg-gray-50">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($estudiantes as $estudiante)
                                    @php
                                        $asistenciasAlumno = 0;
                                    @endphp
                                    <tr class="hover:bg-blue-50/30 transition">
                                        {{-- Nombre del alumno --}}
                                        <td class="p-4 sticky left-0 bg-white z-10 border-r border-gray-200">
                                            <p class="font-bold text-gray-800 text-sm">{{ $estudiante->nombre }}</p>
                                            <p class="text-blue-600 font-mono text-xs">{{ $estudiante->codigo_estudiante }}</p>
                                        </td>

                                        {{-- Una celda por día único --}}
                                        @foreach($diasUnicos as $dia)
                                            @php
                                                $fechaDia = $dia->format('Y-m-d');
                                                $asistio  = $registros[$fechaDia][$estudiante->id] ?? false;
                                                if ($asistio) $asistenciasAlumno++;
                                            @endphp
                                            <td class="p-3 text-center border-r border-gray-100">
                                                @if($asistio)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100 border-2 border-green-400"
                                                          title="Presente">
                                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100 border-2 border-red-300"
                                                          title="Ausente">
                                                        <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Total del alumno --}}
                                        <td class="p-3 text-center bg-gray-50">
                                            @php
                                                $pct = count($diasUnicos) > 0
                                                    ? round(($asistenciasAlumno / count($diasUnicos)) * 100)
                                                    : 0;
                                            @endphp
                                            <div class="font-black text-sm {{ $pct >= 75 ? 'text-green-600' : ($pct >= 50 ? 'text-yellow-600' : 'text-red-500') }}">
                                                {{ $asistenciasAlumno }}/{{ count($diasUnicos) }}
                                            </div>
                                            <div class="text-[10px] font-bold {{ $pct >= 75 ? 'text-green-500' : ($pct >= 50 ? 'text-yellow-500' : 'text-red-400') }}">
                                                {{ $pct }}%
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Fila de totales por sesión --}}
                                <tr class="bg-[#002d62] text-white font-bold">
                                    <td class="p-4 text-xs uppercase tracking-wide sticky left-0 bg-[#002d62] z-10 border-r border-blue-700">
                                        Presentes
                                    </td>
                                    @foreach($diasUnicos as $dia)
                                        @php
                                            $fechaDia = $dia->format('Y-m-d');
                                            $presentesDia = collect($registros[$fechaDia] ?? [])->filter()->count();
                                        @endphp
                                        <td class="p-3 text-center text-sm border-r border-blue-700">
                                            {{ $presentesDia }}/{{ $estudiantes->count() }}
                                        </td>
                                    @endforeach
                                    <td class="p-3 text-center text-sm bg-[#001d3d]">
                                        {{ $totalAsistencias }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>