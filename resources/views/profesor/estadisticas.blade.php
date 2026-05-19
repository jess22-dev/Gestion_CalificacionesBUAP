<x-app-layout>
    <x-slot name="header">
        {{ __('Estadísticas del Grupo') }}
    </x-slot>

    <div class="py-10 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Barra superior --}}
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('materias.show', $materia->nrc) }}"
                   class="text-[#1e4b8a] font-bold hover:underline flex items-center">
                    ← Volver a {{ $materia->Materia }}
                </a>
                <span class="bg-[#002d62] text-white text-sm px-4 py-1 rounded-full font-bold shadow-sm">
                    NRC: {{ $materia->nrc }}
                </span>
            </div>

            {{-- Título --}}
            <div class="bg-[#1e4b8a] p-6 text-white rounded-2xl mb-6 shadow-xl">
                <h2 class="text-2xl font-black">{{ $materia->Materia }}</h2>
                <p class="opacity-75 text-sm mt-1">Panel de estadísticas — Primavera 2026</p>
            </div>

            {{-- Pestañas --}}
            <div class="mb-6 flex gap-2 border-b-2 border-gray-200" id="pestanas">
                <button onclick="cambiarPestana('grupo')" id="tab-grupo"
                        class="tab-btn px-6 py-3 font-black text-sm uppercase tracking-wide rounded-t-xl transition border-b-4 border-[#002d62] text-[#002d62] bg-white">
                     Grupo
                </button>
                <button onclick="cambiarPestana('alumno')" id="tab-alumno"
                        class="tab-btn px-6 py-3 font-black text-sm uppercase tracking-wide rounded-t-xl transition border-b-4 border-transparent text-gray-400 hover:text-[#002d62]">
                     Alumno
                </button>
            </div>

            {{-- ══════════════ PESTAÑA GRUPO ══════════════ --}}
            <div id="panel-grupo">

                {{-- Tarjetas resumen --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 flex flex-col items-center">
                        <p class="text-xs uppercase font-black text-gray-400 tracking-widest mb-1">Promedio General</p>
                        <p class="text-5xl font-black {{ $promedioGeneral >= 6 ? 'text-green-500' : 'text-red-500' }}">
                            {{ $promedioGeneral }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">sobre 10</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 flex flex-col items-center">
                        <p class="text-xs uppercase font-black text-gray-400 tracking-widest mb-1">Asistencia Promedio</p>
                        <p class="text-5xl font-black {{ $promedioAsistencia >= 80 ? 'text-green-500' : ($promedioAsistencia >= 60 ? 'text-yellow-500' : 'text-red-500') }}">
                            {{ $promedioAsistencia }}%
                        </p>
                        <p class="text-xs text-gray-400 mt-1">{{ $totalSesiones }} sesión(es) registrada(s)</p>
                    </div>
                    <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 flex flex-col items-center">
                        <p class="text-xs uppercase font-black text-gray-400 tracking-widest mb-1">Alumnos</p>
                        <p class="text-5xl font-black text-[#002d62]">{{ $alumnosAgrupados->count() }}</p>
                        <p class="text-xs text-gray-400 mt-1">con calificaciones registradas</p>
                    </div>
                </div>

                {{-- Promedio por actividad --}}
                @if($actividades->count() > 0)
                <div class="bg-white rounded-2xl shadow border border-gray-100 p-6 mb-6">
                    <h3 class="font-black text-[#002d62] text-base uppercase tracking-wide mb-4">Promedio por Actividad</h3>
                    <div class="space-y-3">
                        @foreach($promediosPorActividad as $actividad => $promedio)
                            @php $pct = min(($promedio / 10) * 100, 100); @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-semibold text-gray-700 truncate max-w-xs">{{ $actividad }}</span>
                                    <span class="font-black {{ $promedio >= 6 ? 'text-green-600' : 'text-red-500' }}">{{ $promedio }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3">
                                    <div class="h-3 rounded-full transition-all duration-500 {{ $promedio >= 6 ? 'bg-green-400' : 'bg-red-400' }}"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Ranking --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    {{-- Top 3 --}}
                    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
                        <h3 class="font-black text-green-700 text-sm uppercase tracking-wide mb-4"> Mejor Desempeño</h3>
                        @forelse($top3 as $i => $alumno)
                            <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black
                                    {{ $i === 0 ? 'bg-yellow-100 text-yellow-700' : ($i === 1 ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-600') }}">
                                    {{ $i + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 text-sm truncate">{{ $alumno['nombre'] }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ $alumno['email'] }}</p>
                                </div>
                                <span class="font-black text-green-600 text-lg">{{ $alumno['promedio'] }}</span>
                            </div>
                        @empty
                            <p class="text-gray-400 text-sm italic">Sin datos aún.</p>
                        @endforelse
                    </div>

                    {{-- Bottom 3 --}}
                    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
                        <h3 class="font-black text-red-600 text-sm uppercase tracking-wide mb-4"> Necesitan Atención</h3>
                        @forelse($bottom3 as $alumno)
                            <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-gray-800 text-sm truncate">{{ $alumno['nombre'] }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ $alumno['email'] }}</p>
                                </div>
                                <span class="font-black text-red-500 text-lg">{{ $alumno['promedio'] }}</span>
                            </div>
                        @empty
                            <p class="text-gray-400 text-sm italic">Sin datos aún.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Tabla completa del grupo --}}
                <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-black text-[#002d62] text-sm uppercase tracking-wide">Todos los Alumnos</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-black text-gray-500 uppercase text-xs">Nombre</th>
                                    <th class="px-4 py-3 text-center font-black text-gray-500 uppercase text-xs">Promedio Actividades</th>
                                    <th class="px-4 py-3 text-center font-black text-gray-500 uppercase text-xs">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($alumnosAgrupados as $alumno)
                                    <tr class="hover:bg-blue-50 transition">
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-gray-800">{{ $alumno['nombre'] }}</p>
                                            <p class="text-xs text-gray-400">{{ $alumno['email'] }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-black text-base {{ $alumno['promedio'] >= 6 ? 'text-green-600' : 'text-red-500' }}">
                                                {{ $alumno['promedio'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button onclick="verAlumno('{{ $alumno['email'] }}', '{{ addslashes($alumno['nombre']) }}')"
                                                    class="text-xs text-[#002d62] border border-[#002d62] px-3 py-1 rounded-lg hover:bg-[#002d62] hover:text-white transition font-bold">
                                                Ver detalle
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-12 text-center text-gray-400 italic">Sin datos de calificaciones aún.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>{{-- fin panel-grupo --}}

            {{-- ══════════════ PESTAÑA ALUMNO ══════════════ --}}
            <div id="panel-alumno" style="display:none">

                {{-- Selector --}}
                <div class="bg-white rounded-2xl shadow border border-gray-100 p-6 mb-6">
                    <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-2">Seleccionar Alumno</label>
                    <select id="selector-alumno" onchange="cargarAlumno(this.value)"
                            class="w-full rounded-xl border-gray-300 focus:ring-[#1e4b8a] font-semibold text-gray-700">
                        <option value="">-- Elige un alumno --</option>
                        @foreach($listaAlumnos as $a)
                            <option value="{{ $a->email_alumno }}">
                                {{ $a->nombre_alumno }} — {{ $a->codigo_estudiante ?? $a->email_alumno }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Loading --}}
                <div id="loading-alumno" style="display:none" class="text-center py-12 text-gray-400">
                    <svg class="animate-spin w-8 h-8 mx-auto text-[#1e4b8a]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <p class="mt-2 text-sm">Cargando datos...</p>
                </div>

                {{-- Contenido del alumno --}}
                <div id="contenido-alumno" style="display:none">

                    <div class="bg-[#002d62] rounded-2xl p-5 text-white mb-6 flex items-center gap-4">
                        <div class="bg-white/10 p-3 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p id="alumno-nombre" class="text-xl font-black"></p>
                            <p id="alumno-email" class="text-blue-200 text-sm"></p>
                        </div>
                    </div>

                    {{-- Tarjetas individuales --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 flex flex-col items-center">
                            <p class="text-xs uppercase font-black text-gray-400 tracking-widest mb-1">Promedio Actividades</p>
                            <p id="alumno-promedio" class="text-5xl font-black text-[#002d62]">—</p>
                            <p class="text-xs text-gray-400 mt-1">sobre 10</p>
                        </div>
                        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100 flex flex-col items-center">
                            <p class="text-xs uppercase font-black text-gray-400 tracking-widest mb-1">Asistencia</p>
                            <p id="alumno-asistencia-pct" class="text-5xl font-black text-[#002d62]">—</p>
                            <p id="alumno-asistencia-detalle" class="text-xs text-gray-400 mt-1">— sesiones</p>
                        </div>
                    </div>

                    {{-- Calificaciones por actividad --}}
                    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6 mb-6">
                        <h3 class="font-black text-[#002d62] text-sm uppercase tracking-wide mb-4">Calificaciones por Actividad</h3>
                        <div id="alumno-actividades" class="space-y-3"></div>
                    </div>

                    {{-- Datos manuales --}}
                    <div id="bloque-manuales" class="bg-white rounded-2xl shadow border border-gray-100 p-6" style="display:none">
                        <h3 class="font-black text-[#002d62] text-sm uppercase tracking-wide mb-4">Datos Adicionales</h3>
                        <div id="alumno-manuales" class="grid grid-cols-2 sm:grid-cols-3 gap-3"></div>
                    </div>

                </div>{{-- fin contenido-alumno --}}

                {{-- Estado vacío --}}
                <div id="vacio-alumno" class="text-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p class="font-semibold">Selecciona un alumno para ver su detalle</p>
                </div>

            </div>{{-- fin panel-alumno --}}

        </div>
    </div>

    <script>
        const nrc = '{{ $materia->nrc }}';

        // ── Pestañas ──
        function cambiarPestana(tab) {
            document.getElementById('panel-grupo').style.display  = tab === 'grupo'  ? 'block' : 'none';
            document.getElementById('panel-alumno').style.display = tab === 'alumno' ? 'block' : 'none';
            document.getElementById('tab-grupo').className  = tabClass(tab === 'grupo');
            document.getElementById('tab-alumno').className = tabClass(tab === 'alumno');
        }

        function tabClass(activo) {
            return activo
                ? 'tab-btn px-6 py-3 font-black text-sm uppercase tracking-wide rounded-t-xl transition border-b-4 border-[#002d62] text-[#002d62] bg-white'
                : 'tab-btn px-6 py-3 font-black text-sm uppercase tracking-wide rounded-t-xl transition border-b-4 border-transparent text-gray-400 hover:text-[#002d62]';
        }

        // ── Ver alumno desde tabla del grupo ──
        function verAlumno(email, nombre) {
            cambiarPestana('alumno');
            const sel = document.getElementById('selector-alumno');
            sel.value = email;
            cargarAlumno(email);
        }

        // ── Cargar datos del alumno vía AJAX ──
        function cargarAlumno(email) {
            if (!email) return;

            document.getElementById('vacio-alumno').style.display    = 'none';
            document.getElementById('contenido-alumno').style.display = 'none';
            document.getElementById('loading-alumno').style.display   = 'block';

            fetch(`/profesor/estadisticas/${nrc}/alumno?email=${encodeURIComponent(email)}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('loading-alumno').style.display    = 'none';
                    document.getElementById('contenido-alumno').style.display  = 'block';

                    document.getElementById('alumno-nombre').textContent = data.nombre;
                    document.getElementById('alumno-email').textContent  = email;

                    // Promedio
                    const pEl = document.getElementById('alumno-promedio');
                    pEl.textContent  = data.promedio;
                    pEl.className    = `text-5xl font-black ${data.promedio >= 6 ? 'text-green-500' : 'text-red-500'}`;

                    // Asistencia
                    const aEl = document.getElementById('alumno-asistencia-pct');
                    aEl.textContent = data.asistencia.pct + '%';
                    aEl.className   = `text-5xl font-black ${data.asistencia.pct >= 80 ? 'text-green-500' : data.asistencia.pct >= 60 ? 'text-yellow-500' : 'text-red-500'}`;
                    document.getElementById('alumno-asistencia-detalle').textContent =
                        `${data.asistencia.presentes} presentes de ${data.asistencia.total} sesiones`;

                    // Actividades
                    const actDiv = document.getElementById('alumno-actividades');
                    if (data.calificaciones.length === 0) {
                        actDiv.innerHTML = '<p class="text-gray-400 text-sm italic">Sin actividades registradas.</p>';
                    } else {
                        actDiv.innerHTML = data.calificaciones.map(c => {
                            const pct = Math.min((c.puntaje / 10) * 100, 100);
                            const color = c.puntaje >= 6 ? 'bg-green-400' : 'bg-red-400';
                            const textColor = c.puntaje >= 6 ? 'text-green-600' : 'text-red-500';
                            return `
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-semibold text-gray-700 truncate max-w-xs">${c.actividad}</span>
                                        <span class="font-black ${textColor}">${c.puntaje}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-3">
                                        <div class="h-3 rounded-full ${color}" style="width:${pct}%"></div>
                                    </div>
                                </div>`;
                        }).join('');
                    }

                    // Datos manuales
                    const manDiv  = document.getElementById('alumno-manuales');
                    const manBloque = document.getElementById('bloque-manuales');
                    if (data.datosExtra) {
                        manBloque.style.display = 'block';
                        const labels = {
                            participacion: 'Participación', proyecto: 'Proyecto',
                            examen_u1: 'Examen U1', examen_u2_u3: 'Examen U2-U3',
                            recuperacion_u1: 'Recuperación U1'
                        };
                        manDiv.innerHTML = Object.entries(data.datosExtra)
                            .filter(([k, v]) => v !== null)
                            .map(([k, v]) => `
                                <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase font-black tracking-wide mb-1">${labels[k] ?? k}</p>
                                    <p class="text-2xl font-black text-[#002d62]">${v}</p>
                                </div>`).join('');
                    } else {
                        manBloque.style.display = 'none';
                    }
                })
                .catch(() => {
                    document.getElementById('loading-alumno').style.display = 'none';
                    document.getElementById('vacio-alumno').style.display   = 'block';
                    document.getElementById('vacio-alumno').innerHTML = '<p class="text-red-400 font-semibold">Error al cargar los datos del alumno.</p>';
                });
        }
    </script>

</x-app-layout>