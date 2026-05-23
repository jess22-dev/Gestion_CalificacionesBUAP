<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2>{{ __('Generador de Actas Finales') }}</h2>
            <div class="flex gap-2 items-center">                
                <span class="bg-[#002d62] text-white text-sm px-4 py-1 rounded-full font-bold shadow-sm">NRC: {{ $materia->nrc }}</span>
            </div>
        </div>
    </x-slot>

    <style>
        .table-container { overflow-x: auto; scrollbar-width: thin; scrollbar-color: #002d62 #f1f1f1; position: relative; }
        .sticky-column { position: sticky; left: 0; z-index: 20; border-right: 2px solid #e5e7eb; }
        .input-calif { width: 60px !important; height: 35px; font-size: 15px !important; font-weight: bold; border-radius: 6px; border: 1px solid #d1d5db; text-align: center; transition: all 0.2s; }
        .input-calif:focus { border-color: #f39c12; ring: 2px; outline: none; }
        .th-teams { min-width: 130px; max-width: 200px; white-space: normal; word-wrap: break-word; }
        [x-cloak] { display: none !important; }
        .blur-content { filter: blur(5px); pointer-events: none; opacity: 0.4; transition: all 0.3s ease; }
        .cloned-row-container { background: white; padding: 2rem; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); width: 95%; max-width: 1300px; overflow-x: auto; border: 4px solid #002d62; }
        .cloned-table { width: 100%; border-collapse: collapse; }
        .cloned-table th { background: #002d62; color: white; padding: 12px; font-size: 10px; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.1); }
        .cloned-table td { padding: 15px; text-align: center; font-weight: bold; border: 1px solid #e5e7eb; font-size: 14px; }

        /* FIX 3: Color reactivo por tipo de actividad */
        .col-tipo-tarea      { background-color: rgba(219, 234, 254, 0.2); }
        .col-tipo-practica   { background-color: rgba(238, 242, 255, 0.3); }
        .col-tipo-examen     { background-color: rgba(254, 243, 199, 0.3); }
        .col-tipo-proyecto   { background-color: rgba(209, 250, 229, 0.2); }
        .col-tipo-recuperacion { background-color: rgba(254, 226, 226, 0.2); }

        .th-tipo-tarea      { /* hereda bg de la clase base */ }
        .th-tipo-practica   { background-color: #3730a3 !important; }
        .th-tipo-examen     { background-color: #92400e !important; }
        .th-tipo-proyecto   { background-color: #065f46 !important; }
        .th-tipo-recuperacion { background-color: #991b1b !important; }
    </style>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="actaApp()" x-init="recalcularTodo()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 transition-all duration-300" >

            {{-- Botón volver --}}
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('materias.show', $materia->nrc) }}"
                   class="text-[#1e4b8a] font-bold hover:underline flex items-center">
                    ← Volver a {{ $materia->Materia }}
                </a>
            </div>

            {{-- ADVERTENCIA: Alumnos en HTM no encontrados en Excel --}}
            @if(session('advertencia_faltantes'))
                <div class="mb-6 bg-amber-50 border-2 border-amber-400 rounded-2xl p-4 flex items-start gap-3">
                    <div class="bg-amber-400 p-2 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-black text-amber-800 text-sm uppercase tracking-wide">
                             {{ count(session('advertencia_faltantes')) }} alumno(s) del HTM no aparecen en el Excel
                        </p>
                        <ul class="mt-2 space-y-0.5">
                            @foreach(session('advertencia_faltantes') as $f)
                                <li class="text-xs text-amber-700">• {{ $f['nombre'] }} <span class="font-mono text-amber-500">({{ $f['codigo'] }})</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- ALUMNOS SIN MATRÍCULA --}}
            @if(session('sin_matricula_list') && count(session('sin_matricula_list')) > 0)
                <div class="mb-6 bg-blue-50 border-2 border-blue-400 rounded-2xl p-4 flex items-start gap-3">
                    <div class="bg-blue-500 p-2 rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-black text-blue-800 text-sm uppercase tracking-wide">
                            {{ count(session('sin_matricula_list')) }} alumno(s) sin matrícula vinculada en el sistema
                        </p>
                        <ul class="mt-2 space-y-0.5">
                            @foreach(session('sin_matricula_list') as $a)
                                <li class="text-xs text-blue-700">• {{ $a['nombre'] }} <span class="font-mono text-blue-400">{{ $a['email'] }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- OPCIÓN C+D: Alumnos del HTM sin calificación en esta actividad --}}
            @if(session('htm_sin_calificacion'))
                <div class="mb-6 bg-orange-50 border-2 border-orange-400 rounded-2xl p-5" id="bloque-htm-sin-cal">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="bg-orange-400 p-2 rounded-xl flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-black text-orange-800 text-sm uppercase tracking-wide">
                                {{ count(session('htm_sin_calificacion')) }} alumno(s) del HTM sin calificación en "{{ session('actividad_cargada') }}"
                            </p>
                            <p class="text-orange-700 text-xs mt-1">¿Deseas asignarles 0 automáticamente? Podrás modificarlo después.</p>
                        </div>
                    </div>
                    <div class="mb-3 overflow-x-auto rounded-xl border border-orange-200">
                        <table class="w-full text-xs">
                            <thead class="bg-orange-100">
                                <tr>
                                    <th class="px-3 py-2 text-left font-black text-orange-800 uppercase">Nombre</th>
                                    <th class="px-3 py-2 text-left font-black text-orange-800 uppercase">Matrícula</th>
                                    <th class="px-3 py-2 text-left font-black text-orange-800 uppercase">Correo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-orange-100 bg-white">
                                @foreach(session('htm_sin_calificacion') as $a)
                                    <tr><td class="px-3 py-2 font-semibold">{{ $a['nombre'] }}</td><td class="px-3 py-2 font-mono">{{ $a['codigo'] }}</td><td class="px-3 py-2 text-gray-500">{{ $a['email'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="flex gap-3">
                        <form action="{{ route('profesor.actas.asignar_cero', $materia->nrc) }}" method="POST">
                            @csrf
                            <input type="hidden" name="actividad" value="{{ session('actividad_cargada') }}">
                            <input type="hidden" name="tipo" value="{{ session('tipo_cargado') }}">
                            @foreach(session('htm_sin_calificacion') as $a)
                                <input type="hidden" name="alumnos[]" value="{{ $a['email'] }}">
                                <input type="hidden" name="nombres[{{ $a['email'] }}]" value="{{ $a['nombre'] }}">
                            @endforeach
                            <button type="submit" class="px-5 py-2 bg-orange-500 text-white text-xs font-bold rounded-xl hover:bg-orange-600 transition">
                                Sí, asignar 0 a todos
                            </button>
                        </form>
                        <a href="{{ route('profesor.actas.index', $materia->nrc) }}"
                           class="px-5 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                            No, dejarlos sin calificación
                        </a>
                    </div>
                </div>
            @endif

            {{-- SECCIÓN CARGA DE ARCHIVOS --}}
            <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-dashed border-blue-200 hover:border-blue-400 transition-colors mb-6">
                <h3 class="text-sm font-black text-gray-800 uppercase mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Cargar actividad
                </h3>
                <form action="{{ route('profesor.actas.importar', $materia->nrc) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

                        {{-- Selector de tipo --}}
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Tipo de actividad</label>
                            <select name="tipo" required
                                    class="w-full rounded-xl border-gray-300 font-semibold text-gray-700 text-sm focus:ring-[#1e4b8a]"
                                    onchange="mostrarRecuperacion(this.value)">
                                <option value="">-- Selecciona el tipo --</option>
                                <option value="tarea"> Tarea</option>
                                <option value="practica"> Práctica</option>
                                <option value="examen"> Examen</option>
                                <option value="recuperacion"> Recuperación</option>
                                <option value="proyecto"> Proyecto</option>
                            </select>
                        </div>

                        {{-- Campo: qué examen recupera (solo visible si tipo=recuperacion) --}}
                        <div id="campo-examen-recupera" style="display:none">
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1">¿Qué examen recupera?</label>
                            <select name="examen_recupera"
                                    class="w-full rounded-xl border-gray-300 font-semibold text-gray-700 text-sm focus:ring-[#1e4b8a]">
                                <option value="">-- Selecciona el examen --</option>
                                @foreach(($actividades ?? collect())->filter(fn($a) => ($tipos[$a] ?? '') === 'examen') as $examAct)
                                    <option value="{{ $examAct }}">{{ $examAct }}</option>
                                @endforeach
                                @if(($actividades ?? collect())->filter(fn($a) => ($tipos[$a] ?? '') === 'examen')->isEmpty())
                                    <option value="_sin_examen" disabled>No hay exámenes cargados aún</option>
                                @endif
                            </select>
                            <p class="text-xs text-gray-400 mt-1 italic">Solo se aplica si el alumno reprobó ese examen (&lt;6).</p>
                        </div>

                        {{-- Selector de archivo --}}
                        <div>
                            <label class="block text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Archivo Excel de Teams</label>
                            <input type="file" name="archivo" accept=".xlsx,.xls" required
                                   class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        {{-- Botón subir --}}
                        <div>
                            <button type="submit" id="btn-cargar-actividad"
                                    class="w-full bg-[#002d62] text-white py-2.5 px-6 rounded-xl font-black text-sm hover:bg-[#1e4b8a] transition shadow-md flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Cargar actividad
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- SECCIÓN PESOS --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
                    <div>
                        <h1 class="text-2xl font-black text-[#002d62] uppercase leading-tight">{{ $materia->Materia }}</h1>
                        <p class="text-gray-500 text-sm font-medium">Clave: {{ $materia->clave }} | <span class="text-blue-600">Primavera 2026</span></p>
                    </div>
                    <div class="grid grid-cols-3 md:grid-cols-5 gap-3 bg-blue-50 p-4 rounded-2xl border border-blue-100">
                        <div class="text-center">
                            <label class="block text-[10px] font-black text-blue-800 uppercase">PART %</label>
                            <input type="number" id="w_part" value="10" @input="recalcularTodo()" class="w-16 p-1 rounded-lg border-gray-300 text-center font-bold text-sm">
                        </div>
                        <div class="text-center">
                            <label class="block text-[10px] font-black text-blue-600 uppercase">TAREAS %</label>
                            <input type="number" id="w_tareas" value="10" @input="recalcularTodo()" class="w-16 p-1 rounded-lg border-blue-200 text-center font-bold text-sm bg-blue-100/50">
                        </div>
                        <div class="text-center">
                            <label class="block text-[10px] font-black text-indigo-700 uppercase">PRAC %</label>
                            <input type="number" id="w_prac" value="20" @input="recalcularTodo()" class="w-16 p-1 rounded-lg border-indigo-200 text-center font-bold text-sm bg-indigo-50">
                        </div>
                        <div class="text-center">
                            <label class="block text-[10px] font-black text-blue-800 uppercase">PROY %</label>
                            <input type="number" id="w_proy" value="40" @input="recalcularTodo()" class="w-16 p-1 rounded-lg border-gray-300 text-center font-bold text-sm">
                        </div>
                        <div class="text-center">
                            <label class="block text-[10px] font-black text-blue-800 uppercase">EXAM %</label>
                            <input type="number" id="w_exam" value="20" @input="recalcularTodo()" class="w-16 p-1 rounded-lg border-gray-300 text-center font-bold text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLA PRINCIPAL --}}
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-200 mb-6">
                <div class="table-container">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#002d62] text-white">
                                <th class="px-4 py-4 font-bold uppercase text-[11px] sticky-column bg-[#002d62] shadow-md">Estudiante</th>
                                <th class="px-2 py-4 font-bold uppercase text-[11px] text-center bg-yellow-600 border-l border-yellow-500">Part</th>

                                @foreach($actividades ?? [] as $actividad)
                                    @php $tipo = $tipos[$actividad] ?? 'tarea'; @endphp

                                    {{--
                                        FIX 2: Dropdown Alpine.js en el <th> para cambiar el tipo de actividad
                                        en tiempo real. Al seleccionar un tipo nuevo se llama cambiarTipoActividad()
                                        que actualiza el estado reactivo y dispara recalcularTodo().
                                    --}}
                                    <th class="th-teams px-3 py-4 font-bold uppercase text-[10px] text-center border-l border-blue-800/30 relative th-tipo-{{ $tipo }}"
                                        id="th-act-{{ Str::slug($actividad) }}"
                                        style="overflow: visible !important;"
                                        x-data="{
                                            open: false,
                                            tipoActual: '{{ $tipo }}',
                                            nombreActividad: '{{ addslashes($actividad) }}'
                                        }">
                                        <div class="flex flex-col items-center cursor-pointer group" @click="open = !open">
                                            <span class="group-hover:text-yellow-400 transition-colors">{{ $actividad }}</span>

                                            {{-- FIX 2: Etiqueta de tipo reactiva vía Alpine --}}
                                            <span class="text-[8px] opacity-60 mt-0.5 uppercase"
                                                  x-text="tipoActual === 'practica' ? 'PRÁCTICA'
                                                        : tipoActual === 'examen'    ? 'EXAMEN'
                                                        : tipoActual === 'proyecto'  ? 'PROYECTO'
                                                        : tipoActual === 'recuperacion' ? 'RECUP.'
                                                        : 'TAREA'">
                                            </span>

                                            <svg class="w-3 h-3 mt-1 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>

                                        <div x-show="open" @click.away="open = false" x-cloak
                                             class="absolute left-0 mt-2 w-52 bg-white shadow-2xl rounded-lg py-2 border border-gray-200 text-gray-800"
                                             style="z-index: 9999; top: 100%;">
                                            <div class="px-4 py-1 border-b border-gray-100 text-[9px] text-gray-400 uppercase font-black">Cambiar tipo</div>

                                            {{-- FIX 2: Opciones para cambiar el tipo de actividad dinámicamente --}}
                                            <template x-for="opcion in [
                                                { valor: 'tarea',       label: ' Tarea' },
                                                { valor: 'practica',    label: ' Práctica' },
                                                { valor: 'examen',      label: ' Examen' },
                                                { valor: 'proyecto',    label: ' Proyecto' },
                                                { valor: 'recuperacion',label: ' Recuperación' }
                                            ]" :key="opcion.valor">
                                                <button type="button"
                                                        @click.stop="
                                                            tipoActual = opcion.valor;
                                                            open = false;
                                                            $dispatch('tipo-cambiado', { actividad: nombreActividad, tipo: opcion.valor });
                                                            $nextTick(() => recalcularTodo())
                                                        "
                                                        class="flex items-center w-full px-4 py-2 text-xs hover:bg-blue-50 font-semibold transition"
                                                        :class="tipoActual === opcion.valor ? 'text-blue-700 bg-blue-50 font-black' : 'text-gray-700'"
                                                        x-text="opcion.label">
                                                </button>
                                            </template>

                                            <div class="border-t border-gray-100 mt-1 pt-1">
                                                <button type="button"
                                                        @click="$dispatch('abrir-modal-eliminar', { nombre: '{{ $actividad }}', action: '{{ route('profesor.actas.eliminarActividad', [$materia->nrc, $actividad]) }}' })"
                                                        class="flex items-center w-full px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-bold transition">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Eliminar columna
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                @endforeach

                                <th class="px-4 py-4 font-bold uppercase text-[11px] text-center bg-gray-900 sticky right-0 z-10">Final</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                                @forelse($alumnos ?? [] as $correo => $datos)
                                    <tr class="alumno-fila fila-alumno-enfoque hover:bg-blue-50/50 transition cursor-pointer"
                                        data-matricula="{{ $datos['matricula'] ?? explode('@', $correo)[0] }}"
                                        data-nombre="{{ mb_strtoupper($datos['nombre']) }}"
                                        data-correo="{{ $correo }}"
                                        data-manual="{{ json_encode([
                                            'participacion'   => (float)($datos['manual']->participacion ?? 0),
                                            'proyecto'        => (float)($datos['manual']->proyecto ?? 0),
                                            'examen_u1'       => (float)($datos['manual']->examen_u1 ?? 0),
                                            'examen_u2_u3'    => (float)($datos['manual']->examen_u2_u3 ?? 0),
                                            'recuperacion_u1' => $datos['manual']->recuperacion_u1 ?? null
                                        ]) }}"
                                        data-teams="{{ json_encode($datos['notas'] ?? []) }}">
                                        
                                        <td class="px-4 py-3 sticky-column bg-white border-r">
                                            {{-- Añadimos la clase 'nombre-clicable' para dar feedback visual al profesor --}}
                                            <div class="font-bold text-gray-900 text-xs uppercase nombre-alumno hover:text-blue-700 transition-colors">
                                                {{ $datos['nombre'] }}
                                            </div>
                                            <div class="text-[10px] text-blue-600 font-medium">{{ $correo }}</div>
                                        </td>
                                        
                                        <td class="px-2 py-2 text-center bg-yellow-50/30">
                                            <input type="number" step="0.1" class="input-calif input-part" value="{{ $datos['manual']->participacion ?? 0 }}" data-email="{{ $correo }}" data-campo="participacion" onblur="guardarDatoManual(this)" @input.stop="recalcularTodo()">
                                        </td>

                                        @foreach($actividades ?? [] as $actividad)
                                            @php $tipo = $tipos[$actividad] ?? 'tarea'; @endphp
                                            <td class="px-2 py-2 text-center border-l border-gray-100 col-tipo-{{ $tipo }}"
                                                data-tipo="{{ $tipo }}"
                                                data-actividad-col="{{ $actividad }}"
                                                @tipo-cambiado.window="
                                                    if ($event.detail.actividad === '{{ $actividad }}') {
                                                        $el.dataset.tipo = $event.detail.tipo;
                                                        $el.className = $el.className.replace(/col-tipo-\S+/, '');
                                                        $el.classList.add('px-2', 'py-2', 'text-center', 'border-l', 'border-gray-100', 'col-tipo-' + $event.detail.tipo);
                                                        $el.querySelector('.nota-act').dataset.tipo = $event.detail.tipo;
                                                    }
                                                ">
                                                <input type="number" step="0.1"
                                                    class="input-calif nota-act"
                                                    value="{{ $datos['notas'][$actividad] ?? 0 }}"
                                                    data-email="{{ $correo }}"
                                                    data-campo="nota_actividad"
                                                    data-actividad="{{ $actividad }}"
                                                    data-tipo="{{ $tipo }}"
                                                    onblur="guardarNota(this)"
                                                    @input.stop="recalcularTodo()">
                                            </td>
                                        @endforeach

                                        <td class="px-4 py-2 text-center bg-gray-50 font-black text-lg sticky right-0 shadow-sm border-l">
                                            <span class="final-span">0</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="50" class="py-24 text-center text-gray-400 italic">
                                            Sin datos aún. Sube un archivo de Teams para comenzar.
                                        </td>
                                    </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BOTONES DE EXPORTAR --}}
            <div class="flex flex-wrap gap-3 justify-end mb-6">
                <form action="{{ route('profesor.actas.exportar', $materia->nrc) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-green-700 transition shadow">
                        Exportar Acta Completa
                    </button>
                </form>
                <form action="{{ route('profesor.actas.exportar_oficial', $materia->nrc) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-[#002d62] text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-[#1e4b8a] transition shadow">
                        Exportar Acta Oficial
                    </button>
                </form>
                <form action="{{ route('profesor.actas.eliminar', $materia->nrc) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar TODOS los datos del acta? Esta acción no se puede deshacer.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 bg-red-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-red-700 transition shadow">
                        Limpiar Acta
                    </button>
                </form>
            </div>
        </div>

        {{-- MODAL ELIMINAR ACTIVIDAD --}}
        <div x-data="{ showModal: false, itemEliminar: '', formAction: '' }"
             @abrir-modal-eliminar.window="showModal = true; itemEliminar = $event.detail.nombre; formAction = $event.detail.action"
             x-show="showModal" x-cloak
             class="fixed inset-0 z-[10000] overflow-y-auto">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div @click.away="showModal = false" class="relative bg-white rounded-3xl shadow-2xl sm:w-full sm:max-w-lg overflow-hidden">
                    <div class="p-8 text-center">
                        <h3 class="text-xl font-black text-gray-900 mb-2">Confirmar acción</h3>
                        <p class="text-sm text-gray-500">¿Estás seguro de eliminar <span class="font-bold text-red-600" x-text="itemEliminar"></span>?</p>
                    </div>
                    <div class="bg-gray-50 px-8 py-6 flex flex-row-reverse gap-3">
                        <form :action="formAction" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-xl bg-red-600 px-6 py-3 text-sm font-bold text-white uppercase">Sí, eliminar</button>
                        </form>
                        <button type="button" @click="showModal = false" class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-700 border uppercase">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-enfoque" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4 animate-fade-in">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-2xl overflow-hidden border border-gray-100">
            <div class="bg-[#002d62] text-white p-6 flex justify-between items-start">
                <div>
                    <span id="enfoque-matricula" class="text-xs font-mono bg-white/20 px-2 py-0.5 rounded-md font-bold tracking-wider uppercase mb-1 inline-block"></span>
                    <h3 id="enfoque-nombre" class="text-xl font-black tracking-tight"></h3>
                    <p id="enfoque-correo" class="text-sm text-gray-300 font-medium"></p>
                </div>
                <button id="cerrar-enfoque" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition text-sm font-bold">
                    ✕ Cerrar
                </button>
            </div>

            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <div>
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Evaluaciones de Criterio (Manual)</h4>
                    <div class="grid grid-cols-2 gap-3" id="enfoque-notas-manuales">
                        </div>
                </div>

                <div>
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Actividades de Microsoft Teams</h4>
                    <div class="bg-gray-50 rounded-2xl border border-gray-100 p-4 space-y-2.5" id="enfoque-notas-teams">
                        </div>
                </div>
            </div>

            <div class="bg-gray-50 p-6 border-t border-gray-100 flex justify-between items-center">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Estado en Acta</p>
                    <div id="enfoque-estado" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black mt-1"></div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-gray-400 block">Calificación Final</span>
                    <span id="enfoque-calificacion-final" class="text-4xl font-black text-[#002d62]"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mostrarRecuperacion(valor) {
            const campo = document.getElementById('campo-examen-recupera');
            if (campo) campo.style.display = valor === 'recuperacion' ? '' : 'none';
        }

        function guardarNota(input) {
            const email     = input.getAttribute('data-email');
            const actividad = input.getAttribute('data-actividad');
            const valor     = input.value;
            const nombre    = input.closest('tr').querySelector('.nombre-alumno').innerText;

            fetch("{{ route('profesor.actas.guardar_nota', ['nrc' => $materia->nrc]) }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ email, actividad, valor, nombre })
            }).then(r => r.json()).then(d => {
                if (d.status !== 'success') console.error('Error guardando nota');
            });
        }

        function guardarDatoManual(input) {
            fetch("{{ route('profesor.actas.guardar_manual', ['nrc' => $materia->nrc]) }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    email: input.getAttribute('data-email'),
                    campo: input.getAttribute('data-campo'),
                    valor: input.value,
                    nombre: input.closest('tr').querySelector('.nombre-alumno').innerText
                })
            });
        }

        // ── Decisiones alumnos sin matrícula ──
        const totalSinMatricula = document.querySelectorAll('[id^="accion-mat-"]').length;
        let decisionesMatricula = {};

        function elegirDecisionMatricula(index, tipo, nombre) {
            decisionesMatricula[index] = tipo;
            const btnAsignar = document.getElementById('btn-asignar-' + index);
            const btnIgnorar = document.getElementById('btn-ignorar-' + index);
            const inputMat   = document.getElementById('input-mat-' + index);
            const accionHid  = document.getElementById('accion-mat-' + index);
            const fila       = document.getElementById('fila-mat-' + index);

            btnAsignar.className = 'text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-blue-700 bg-white border-blue-300 hover:bg-blue-100';
            btnIgnorar.className = 'text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-gray-500 bg-white border-gray-300 hover:bg-gray-100';
            fila.classList.remove('bg-blue-50', 'bg-gray-50');

            if (tipo === 'asignar') {
                btnAsignar.className = 'text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-blue-800 bg-blue-200 border-blue-400 ring-2 ring-blue-400';
                fila.classList.add('bg-blue-50');
                inputMat.disabled = false;
                inputMat.focus();
                accionHid.value = 'asignar';
            } else {
                btnIgnorar.className = 'text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-gray-700 bg-gray-200 border-gray-400 ring-2 ring-gray-400';
                fila.classList.add('bg-gray-50');
                inputMat.disabled = true;
                inputMat.value = '';
                accionHid.value = 'ignorar';
            }

            if (Object.keys(decisionesMatricula).length === totalSinMatricula) {
                document.getElementById('contenedor-proceder-matriculas').removeAttribute('hidden');
            }
        }

        function procederMatriculas() {
            let valido = true;
            for (const [index, tipo] of Object.entries(decisionesMatricula)) {
                if (tipo === 'asignar') {
                    const input = document.getElementById('input-mat-' + index);
                    if (!input || !/^\d{9}$/.test(input.value)) {
                        alert('El alumno en la fila ' + (parseInt(index) + 1) + ' necesita una matrícula válida de 9 dígitos.');
                        valido = false;
                        break;
                    }
                }
            }
            if (!valido) return;
            const asignar = Object.values(decisionesMatricula).filter(d => d === 'asignar').length;
            const ignorar = Object.values(decisionesMatricula).filter(d => d === 'ignorar').length;
            if (confirm('¿Confirmas los siguientes cambios?\n\n✎ Asignar matrícula: ' + asignar + ' alumno(s)\n✗ Ignorar: ' + ignorar + ' alumno(s)\n\nEsta acción no se puede deshacer.')) {
                document.getElementById('form-matriculas').submit();
            }
        }

        // ── Decisiones alumnos faltantes ──
        const totalFaltantes = document.querySelectorAll('[id^="decision-"]').length;
        let decisiones = {};

        function elegirDecision(index, tipo, nombre) {
            decisiones[index] = tipo;
            const btnMantener = document.getElementById('btn-mantener-' + index);
            const btnBaja     = document.getElementById('btn-baja-' + index);
            const input       = document.getElementById('decision-' + index);
            const fila        = document.getElementById('fila-' + index);

            btnMantener.className = 'btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-green-700 bg-white border-green-300 hover:bg-green-100';
            btnBaja.className     = 'btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-red-700 bg-white border-red-300 hover:bg-red-100';
            fila.classList.remove('bg-green-50', 'bg-red-50');

            if (tipo === 'mantener') {
                btnMantener.className = 'btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-green-800 bg-green-200 border-green-400 ring-2 ring-green-400';
                fila.classList.add('bg-green-50');
                input.value = 'mantener';
            } else {
                btnBaja.className = 'btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-red-800 bg-red-200 border-red-400 ring-2 ring-red-400';
                fila.classList.add('bg-red-50');
                input.value = 'baja';
            }

            if (Object.keys(decisiones).length === totalFaltantes) {
                document.getElementById('contenedor-proceder').removeAttribute('hidden');
            }
        }

        function procederCambios() {
            const bajas    = Object.values(decisiones).filter(d => d === 'baja').length;
            const mantener = Object.values(decisiones).filter(d => d === 'mantener').length;
            if (confirm('¿Confirmas los siguientes cambios?\n\n✓ Mantener: ' + mantener + ' alumno(s)\n✗ Dar de baja: ' + bajas + ' alumno(s)\n\nEsta acción no se puede deshacer.')) {
                document.getElementById('form-faltantes').submit();
            }
        }

        
        function actaApp() {
            return {
                // Mapa mutable tipo-actividad: se actualiza vía evento tipo-cambiado
                tiposActivos: @js($tipos),

                init() {
                    // FIX 2: Escuchar cambios de tipo desde los dropdowns Alpine de los <th>
                    window.addEventListener('tipo-cambiado', (e) => {
                        const { actividad, tipo } = e.detail;
                        this.tiposActivos[actividad] = tipo;
                        this.recalcularTodo();
                    });
                },

                recalcularTodo() {
                    const getW = (id) => (parseFloat(document.getElementById(id)?.value) || 0) / 100;
                    const wPart   = getW('w_part');
                    const wTareas = getW('w_tareas');
                    const wPrac   = getW('w_prac');
                    const wProy   = getW('w_proy');
                    const wExam   = getW('w_exam');

                    const wMap = {
                        tarea:        wTareas,
                        practica:     wPrac,    
                        proyecto:     wProy,
                        examen:       wExam,
                        recuperacion: wExam   // la recuperación comparte el peso del examen
                    };

                    document.querySelectorAll('.alumno-fila').forEach(fila => {
                        // Participación
                        const part = parseFloat(fila.querySelector('.input-part')?.value) || 0;

                        // Agrupar notas por tipo
                        const tipoSumas = {};
                        const tipoCont  = {};

                        fila.querySelectorAll('.nota-act').forEach(input => {
                            // FIX 2+1: Leer el tipo SIEMPRE desde data-tipo del input,
                            // que se actualiza en el evento @tipo-cambiado del <td>
                            const tipo = input.getAttribute('data-tipo') || 'tarea';
                            const nota = parseFloat(input.value) || 0;
                            tipoSumas[tipo] = (tipoSumas[tipo] || 0) + nota;
                            tipoCont[tipo]  = (tipoCont[tipo]  || 0) + 1;
                        });

                        // Calcular calificación final ponderada
                        let final = part * wPart;

                        for (const tipo in tipoSumas) {
                            const prom   = tipoSumas[tipo] / tipoCont[tipo];
                            const peso   = wMap[tipo] ?? 0;
                            final += prom * peso;
                        }

                        final = Math.round(final * 100) / 100;

                        // Regla de redondeo: [5.5, 6.0) → 5; resto → redondeo normal
                        let calFinal;
                        if (final >= 5.5 && final < 6.0) {
                            calFinal = 5;
                        } else {
                            calFinal = Math.round(final);
                        }

                        // FIX 3: Actualizar span con color reactivo
                        const span = fila.querySelector('.final-span');
                        if (span) {
                            span.textContent = calFinal;
                            span.className = calFinal < 6
                                ? 'final-span text-red-600'
                                : 'final-span text-blue-900';
                        }
                    });
                }
            };
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            const filas = document.querySelectorAll('.alumno-fila');

            // 1. Creamos el contenedor oscuro (Fondo de ventana) dinámicamente si no existe
            let fondoVentana = document.getElementById('fondo-ventana-enfoque');
            if (!fondoVentana) {
                fondoVentana = document.createElement('div');
                fondoVentana = Object.assign(fondoVentana, {
                    id: 'fondo-ventana-enfoque',
                    className: 'fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 hidden flex flex-col items-center justify-center p-4 transition-all duration-300'
                });
                
                // Estructura interna: Un contenedor blanco estilizado para alojar la fila flotante
                fondoVentana.innerHTML = `
                    <div class="w-full max-w-7xl bg-white rounded-3xl shadow-2xl p-6 border border-gray-100 flex flex-col gap-4 animate-scale-up">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-blue-600 animate-pulse"></span>
                                <h3 class="text-sm font-black text-slate-700 tracking-wider uppercase">Modo Enfoque: Editor de Alumno</h3>
                            </div>
                            <button id="cerrar-ventana-enfoque" class="px-4 py-1.5 bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-500 rounded-xl text-xs font-black transition-all">
                                ✕  CERRAR
                            </button>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white" id="espacio-fila-flotante"></div>
                        <p class="text-[11px] text-gray-400 italic text-center">Puedes modificar las calificaciones aquí dentro. Al cerrar, los cambios se mantendrán en la lista general.</p>
                    </div>
                `;
                document.body.appendChild(fondoVentana);
            }

            const espacioFila = document.getElementById('espacio-fila-flotante');
            const btnCerrar = document.getElementById('cerrar-ventana-enfoque');

            filas.forEach(fila => {
                const nombre = fila.querySelector('.nombre-alumno');
                
                if (nombre) {
                    nombre.addEventListener('click', function(e) {
                        e.stopPropagation();

                        // 2. Construimos una tabla miniatura idéntica para que la fila no pierda sus tamaños ni estilos
                        const tablaMiniatura = document.createElement('table');
                        tablaMiniatura.className = "w-full text-left border-collapse";
                        
                        // Clonamos la fila original con TODOS sus inputs, valores y eventos vivos
                        const filaClonada = fila.cloneNode(true);
                        
                        // Truco maestro: Al clonar con JavaScript puro, los valores de los inputs a veces no se copian. Los forzamos:
                        const inputsOriginales = fila.querySelectorAll('input');
                        const inputsClonados = filaClonada.querySelectorAll('input');
                        inputsOriginales.forEach((inputOrig, index) => {
                            inputsClonados[index].value = inputOrig.value;
                            
                            // Sincronizamos en tiempo real: lo que escribas en la ventana se escribe en la tabla de abajo
                            inputsClonados[index].addEventListener('input', function() {
                                inputOrig.value = this.value;
                                // Disparamos el recalcular de Alpine/JS que tengas en la vista principal
                                inputOrig.dispatchEvent(new Event('input', { bubbles: true }));
                            });
                            
                            inputsClonados[index].addEventListener('blur', function() {
                                inputOrig.value = this.value;
                                inputOrig.dispatchEvent(new Event('blur', { bubbles: true }));
                            });
                        });

                        // Quitamos clases molestas del clon y le damos un fondo limpio
                        filaClonada.classList.remove('hover:bg-blue-50/50');
                        filaClonada.classList.add('bg-slate-50/80');

                        // Metemos la fila clonada dentro de nuestra mini tabla ventana
                        const tbodyMini = document.createElement('tbody');
                        tbodyMini.appendChild(filaClonada);
                        tablaMiniatura.appendChild(tbodyMini);

                        // 3. Montamos la fila en el espacio de la ventana y la mostramos
                        espacioFila.innerHTML = '';
                        espacioFila.appendChild(tablaMiniatura);
                        
                        fondoVentana.classList.remove('hidden');
                    });
                }
            });

            // Función lógica para cerrar la ventana flotante
            function cerrarEnfoque() {
                fondoVentana.classList.add('hidden');
                espacioFila.innerHTML = '';
                // Forzamos un recalculo general al cerrar por si acaso
                if (typeof recalcularTodo === 'function') recalcularTodo();
            }

            if (btnCerrar) btnCerrar.addEventListener('click', cerrarEnfoque);
            fondoVentana.addEventListener('click', function(e) {
                if (e.target === fondoVentana) cerrarEnfoque();
            });
        });
    </script>
    <style>
        /* Animación sutil para que la ventana emerja con estilo */
        @keyframes scaleUp {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-scale-up {
            animation: scaleUp 0.2s ease-out forwards;
        }
    </style>
</x-app-layout>