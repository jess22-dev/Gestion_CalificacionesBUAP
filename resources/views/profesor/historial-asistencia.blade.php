<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight italic">
            Historial de Asistencia — {{ $materia->Materia }} ({{ $materia->nrc }})
        </h2>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4 flex justify-between items-center">
                <a href="{{ route('materias.show', $materia->nrc) }}"
                   class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                    ← Volver a {{ $materia->Materia }}
                </a>
                {{-- Leyenda --}}
                <div class="flex gap-3 text-xs font-bold">
                    <span class="flex items-center gap-1">
                        <span class="w-5 h-5 rounded-full bg-green-400 border-2 border-green-600 inline-block"></span> Presente
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-5 h-5 rounded-full bg-red-200 border-2 border-red-400 inline-block"></span> Ausente
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-5 h-5 rounded-full bg-blue-400 border-2 border-blue-600 inline-block"></span> Justificado
                    </span>
                </div>
            </div>

            {{-- Notificación de guardado --}}
            <div id="notif" class="hidden mb-4 p-3 bg-green-100 border border-green-400 text-green-800 rounded-xl text-sm font-bold text-center transition"></div>

            @if(empty($diasUnicos) || count($diasUnicos) === 0)
                <div class="bg-white rounded-2xl shadow-xl p-12 text-center border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-600 mb-2">Sin sesiones registradas</h3>
                    <p class="text-gray-400 text-sm">Aún no se ha tomado ninguna asistencia en esta materia.</p>
                    <a href="{{ route('profesor.asistencia', $materia->nrc) }}"
                       class="inline-block mt-6 bg-[#002d62] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#1e4b8a] transition">
                        Tomar primera asistencia 
                    </a>
                </div>
            @else

                {{-- Resumen --}}
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-2xl p-5 shadow border border-gray-100 text-center">
                        <p class="text-3xl font-black text-[#002d62]">{{ count($diasUnicos) }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase mt-1">Días de clase</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow border border-gray-100 text-center">
                        <p class="text-3xl font-black text-[#002d62]">{{ $estudiantes->count() }}</p>
                        <p class="text-xs font-bold text-gray-400 uppercase mt-1">Alumnos</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow border border-gray-100 text-center">
                        @php
                            $totalPresentes = 0;
                            $totalPosible   = count($diasUnicos) * $estudiantes->count();
                            foreach ($registros as $dia => $alumnos) {
                                foreach ($alumnos as $est) {
                                    if (in_array($est, ['presente', 'justificado'])) $totalPresentes++;
                                }
                            }
                            $porcentaje = $totalPosible > 0 ? round(($totalPresentes / $totalPosible) * 100) : 0;
                        @endphp
                        <p class="text-3xl font-black text-[#002d62]">{{ $porcentaje }}%</p>
                        <p class="text-xs font-bold text-gray-400 uppercase mt-1">Asistencia Global</p>
                    </div>
                </div>

                {{-- Tabla cuadrícula --}}
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-[#1e4b8a] p-4 text-white flex justify-between items-center">
                        <h3 class="font-bold text-lg">Registro de Asistencias</h3>
                        <span class="text-blue-200 text-xs italic">Haz click en cualquier celda para cambiar el estatus</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="p-4 text-left text-xs font-black text-gray-600 uppercase sticky left-0 bg-gray-50 z-10 min-w-[200px] border-r border-gray-200">
                                        Alumno
                                    </th>
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
                                    <th class="p-3 text-center text-xs font-black text-gray-600 uppercase min-w-[80px] bg-gray-50">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($estudiantes as $estudiante)
                                    @php $asistenciasAlumno = 0; @endphp
                                    <tr class="hover:bg-blue-50/30 transition">

                                        <td class="p-4 sticky left-0 bg-white z-10 border-r border-gray-200">
                                            <p class="font-bold text-gray-800 text-sm">{{ $estudiante->nombre }}</p>
                                            <p class="text-blue-600 font-mono text-xs">{{ $estudiante->codigo_estudiante }}</p>
                                        </td>

                                        @foreach($diasUnicos as $dia)
                                            @php
                                                $fechaDia = $dia->format('Y-m-d');
                                                $estatus  = $registros[$fechaDia][$estudiante->id] ?? 'ausente';
                                                if (in_array($estatus, ['presente', 'justificado'])) $asistenciasAlumno++;
                                            @endphp
                                            <td class="p-3 text-center border-r border-gray-100">
                                                <button
                                                    class="celda-estatus w-7 h-7 rounded-full border-2 inline-block cursor-pointer hover:scale-125 transition-transform"
                                                    data-alumno-id="{{ $estudiante->id }}"
                                                    data-fecha="{{ $fechaDia }}"
                                                    data-estatus="{{ $estatus }}"
                                                    data-nrc="{{ $materia->nrc }}"
                                                    onclick="ciclarEstatus(this)"
                                                    title="{{ ucfirst($estatus) }}">
                                                </button>
                                            </td>
                                        @endforeach

                                        {{-- Total --}}
                                        <td class="p-3 text-center bg-gray-50" id="total_{{ $estudiante->id }}">
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

                                {{-- Fila totales --}}
                                <tr class="bg-[#002d62] text-white font-bold">
                                    <td class="p-4 text-xs uppercase tracking-wide sticky left-0 bg-[#002d62] z-10 border-r border-blue-700">
                                        Presentes
                                    </td>
                                    @foreach($diasUnicos as $dia)
                                        @php
                                            $fechaDia     = $dia->format('Y-m-d');
                                            $presentesDia = collect($registros[$fechaDia] ?? [])
                                                ->filter(fn($e) => in_array($e, ['presente','justificado']))
                                                ->count();
                                        @endphp
                                        <td class="p-3 text-center text-sm border-r border-blue-700" id="col_total_{{ $dia->format('Ymd') }}">
                                            {{ $presentesDia }}/{{ $estudiantes->count() }}
                                        </td>
                                    @endforeach
                                    <td class="p-3 text-center text-sm bg-[#001d3d]">
                                        {{ $totalPresentes }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif

        </div>
    </div>

    <script>
    const ESTATUS_CONFIG = {
        ausente:     { next: 'presente',    cls: 'bg-red-200 border-red-400',     title: 'Ausente' },
        presente:    { next: 'justificado', cls: 'bg-green-400 border-green-600', title: 'Presente' },
        justificado: { next: 'ausente',     cls: 'bg-blue-400 border-blue-600',   title: 'Justificado' },
    };

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Aplicar colores iniciales a todas las celdas al cargar
    document.querySelectorAll('.celda-estatus').forEach(btn => {
        aplicarColor(btn, btn.dataset.estatus);
    });

    function ciclarEstatus(btn) {
        const actual    = btn.dataset.estatus;
        const siguiente = ESTATUS_CONFIG[actual]?.next ?? 'ausente';
        const alumnoId  = btn.dataset.alumnoId;
        const fecha     = btn.dataset.fecha;
        const nrc       = btn.dataset.nrc;

        fetch(`/profesor/grupo/${nrc}/historial/editar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ alumno_id: parseInt(alumnoId), fecha, estatus: siguiente })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                aplicarColor(btn, siguiente);
                btn.dataset.estatus = siguiente;
                btn.title = ESTATUS_CONFIG[siguiente]?.title ?? siguiente;
                mostrarNotif(` Cambiado a: ${ESTATUS_CONFIG[siguiente]?.title ?? siguiente}`);
            } else {
                mostrarNotif(' ' + (data.error ?? 'Error'), true);
            }
        })
        .catch(() => mostrarNotif(' Error al guardar', true));
    }

    function aplicarColor(btn, estatus) {
        const cfg = ESTATUS_CONFIG[estatus] ?? ESTATUS_CONFIG['ausente'];
        // Limpiar clases de color
        btn.className = btn.className
            .replace(/bg-\w+-\d+/g, '')
            .replace(/border-\w+-\d+/g, '')
            .trim();
        btn.className += ` ${cfg.cls}`;
    }

    function mostrarNotif(msg, error = false) {
        const n = document.getElementById('notif');
        n.textContent = msg;
        n.className = `mb-4 p-3 rounded-xl text-sm font-bold text-center ${error ? 'bg-red-100 border border-red-400 text-red-800' : 'bg-green-100 border border-green-400 text-green-800'}`;
        n.classList.remove('hidden');
        setTimeout(() => n.classList.add('hidden'), 3000);
    }
    </script>

</x-app-layout>