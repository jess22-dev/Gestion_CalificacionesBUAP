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
    </style>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="actaApp()" x-init="recalcularTodo()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 transition-all duration-300" :class="alumnoEnfocado ? 'blur-content' : ''">

            {{-- Botón volver --}}
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('materias.show', $materia->nrc) }}"
                   class="text-[#1e4b8a] font-bold hover:underline flex items-center">
                    ← Volver a {{ $materia->Materia }}
                </a>
            </div>

            {{-- ADVERTENCIA: Alumnos en HTM no encontrados en Excel --}}
            @if(session('advertencia_faltantes'))
                <div class="mb-6 bg-amber-50 border-2 border-amber-400 rounded-2xl p-6" id="bloque-faltantes">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="bg-amber-400 p-2 rounded-xl flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-black text-amber-800 text-base uppercase tracking-wide">
                                 {{ count(session('advertencia_faltantes')) }} alumno(s) del HTM no aparecen en el Excel
                            </h3>
                            <p class="text-amber-700 text-sm mt-1">
                                Elige una acción para cada alumno y luego presiona <strong>"Proceder con los cambios"</strong>.
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('profesor.estudiantes.baja.faltantes') }}" method="POST" id="form-faltantes">
                        @csrf
                        <input type="hidden" name="nrc" value="{{ session('nrc_faltantes') }}">
                        <div class="overflow-x-auto rounded-xl border border-amber-200">
                            <table class="w-full text-sm">
                                <thead class="bg-amber-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-black text-amber-800 uppercase text-xs">Nombre</th>
                                        <th class="px-4 py-2 text-left font-black text-amber-800 uppercase text-xs">Matrícula</th>
                                        <th class="px-4 py-2 text-left font-black text-amber-800 uppercase text-xs">Correo</th>
                                        <th class="px-4 py-2 text-center font-black text-amber-800 uppercase text-xs">Decisión</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-amber-100 bg-white" id="tabla-faltantes">
                                    @foreach(session('advertencia_faltantes') as $faltante)
                                        <tr class="transition" id="fila-{{ $loop->index }}">
                                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $faltante['nombre'] }}</td>
                                            <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $faltante['codigo'] }}</td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $faltante['email'] }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="hidden" name="decisiones[{{ $faltante['codigo'] }}]" id="decision-{{ $loop->index }}" value="">
                                                <div class="flex justify-center gap-2">
                                                    <button type="button" onclick="elegirDecision({{ $loop->index }}, 'mantener', '{{ addslashes($faltante['nombre']) }}')" id="btn-mantener-{{ $loop->index }}"
                                                            class="btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-green-700 bg-white border-green-300 hover:bg-green-100">
                                                         Mantener
                                                    </button>
                                                    <button type="button" onclick="elegirDecision({{ $loop->index }}, 'baja', '{{ addslashes($faltante['nombre']) }}')" id="btn-baja-{{ $loop->index }}"
                                                            class="btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-red-700 bg-white border-red-300 hover:bg-red-100">
                                                         Dar de baja
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-5 flex justify-end" id="contenedor-proceder" hidden>
                            <button type="button" onclick="procederCambios()"
                                    class="bg-[#002d62] text-white px-8 py-3 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-[#1e4b8a] transition shadow-lg">
                                Proceder con los cambios →
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- ALUMNOS SIN MATRÍCULA --}}
            @if(session('sin_matricula_list') && count(session('sin_matricula_list')) > 0)
                <div class="mb-6 bg-blue-50 border-2 border-blue-400 rounded-2xl p-6" id="bloque-sin-matricula">
                    <div class="flex items-start gap-4 mb-4">
                        <div class="bg-blue-500 p-2 rounded-xl flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-black text-blue-800 text-base uppercase tracking-wide">
                                {{ count(session('sin_matricula_list')) }} alumno(s) sin matrícula vinculada
                            </h3>
                            <p class="text-blue-700 text-sm mt-1">
                                Elige una acción para cada alumno y luego presiona <strong>"Proceder con los cambios"</strong>.
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('profesor.actas.procesar_matriculas', $materia->nrc) }}" method="POST" id="form-matriculas">
                        @csrf
                        <div class="overflow-x-auto rounded-xl border border-blue-200">
                            <table class="w-full text-sm">
                                <thead class="bg-blue-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-black text-blue-800 uppercase text-xs">Nombre</th>
                                        <th class="px-4 py-2 text-left font-black text-blue-800 uppercase text-xs">Correo</th>
                                        <th class="px-4 py-2 text-center font-black text-blue-800 uppercase text-xs">Decisión</th>
                                        <th class="px-4 py-2 text-center font-black text-blue-800 uppercase text-xs">Matrícula</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-100 bg-white" id="tabla-matriculas">
                                    @foreach(session('sin_matricula_list') as $alumno)
                                        <tr class="transition" id="fila-mat-{{ $loop->index }}">
                                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $alumno['nombre'] }}</td>
                                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $alumno['email'] }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="hidden" name="decisiones[{{ $alumno['email'] }}][accion]" id="accion-mat-{{ $loop->index }}" value="">
                                                <input type="hidden" name="decisiones[{{ $alumno['email'] }}][email]" value="{{ $alumno['email'] }}">
                                                <div class="flex justify-center gap-2">
                                                    <button type="button" onclick="elegirDecisionMatricula({{ $loop->index }}, 'asignar', '{{ addslashes($alumno['nombre']) }}')" id="btn-asignar-{{ $loop->index }}"
                                                            class="text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-blue-700 bg-white border-blue-300 hover:bg-blue-100">
                                                         Asignar matrícula
                                                    </button>
                                                    <button type="button" onclick="elegirDecisionMatricula({{ $loop->index }}, 'ignorar', '{{ addslashes($alumno['nombre']) }}')" id="btn-ignorar-{{ $loop->index }}"
                                                            class="text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-gray-500 bg-white border-gray-300 hover:bg-gray-100">
                                                         Ignorar
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="text" name="decisiones[{{ $alumno['email'] }}][codigo]" id="input-mat-{{ $loop->index }}"
                                                       placeholder="000000000" maxlength="9" disabled
                                                       class="w-32 text-center rounded-lg border-gray-300 text-sm font-mono focus:ring-blue-400 disabled:bg-gray-100 disabled:text-gray-400 transition">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-5 flex justify-end" id="contenedor-proceder-matriculas" hidden>
                            <button type="button" onclick="procederMatriculas()"
                                    class="bg-blue-600 text-white px-8 py-3 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-blue-700 transition shadow-lg">
                                Proceder con los cambios →
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- SECCIÓN CARGA DE ARCHIVOS (versión compañero: Tareas y Prácticas) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-dashed border-blue-200 hover:border-blue-400 transition-colors">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-blue-50 rounded-2xl mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-800 uppercase">Cargar Tareas</h3>
                            <p class="text-[10px] text-gray-500">Asigna automáticamente como TAREA</p>
                        </div>
                    </div>
                    <form action="{{ route('profesor.actas.importar', $materia->nrc) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf <input type="hidden" name="tipo" value="tarea">
                        <input type="file" name="archivo" accept=".xlsx, .xls" required class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <button type="submit" class="bg-blue-600 text-white p-2 rounded-xl hover:bg-blue-700 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-dashed border-indigo-200 hover:border-indigo-400 transition-colors">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-indigo-50 rounded-2xl mr-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.251a2 2 0 01-1.285 0l-.628-.251a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.34.34a2 2 0 000 2.829l1.245 1.245a2 2 0 002.829 0l.143-.143a2 2 0 011.285 0l.143.143a2 2 0 002.829 0l1.245-1.245a2 2 0 000-2.829l-.34-.34z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-800 uppercase">Cargar Prácticas</h3>
                            <p class="text-[10px] text-gray-500">Asigna automáticamente como PRÁCTICA</p>
                        </div>
                    </div>
                    <form action="{{ route('profesor.actas.importar', $materia->nrc) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf <input type="hidden" name="tipo" value="practica">
                        <input type="file" name="archivo" accept=".xlsx, .xls" required class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <button type="submit" class="bg-indigo-600 text-white p-2 rounded-xl hover:bg-indigo-700 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </button>
                    </form>
                </div>
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
                                    <th class="th-teams px-3 py-4 font-bold uppercase text-[10px] text-center border-l border-blue-800/30 relative {{ $tipo === 'practica' ? 'bg-indigo-800' : '' }}"
                                        style="overflow: visible !important;"
                                        x-data="{ open: false }">
                                        <div class="flex flex-col items-center cursor-pointer group" @click="open = !open">
                                            <span class="group-hover:text-yellow-400 transition-colors">{{ $actividad }}</span>
                                            <span class="text-[8px] opacity-60 mt-0.5">{{ $tipo === 'practica' ? 'PRÁCTICA' : 'TAREA' }}</span>
                                            <svg class="w-3 h-3 mt-1 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                        <div x-show="open" @click.away="open = false" x-cloak
                                             class="absolute left-0 mt-2 w-48 bg-white shadow-2xl rounded-lg py-2 border border-gray-200 text-gray-800"
                                             style="z-index: 9999; top: 100%;">
                                            <div class="px-4 py-1 border-b border-gray-100 text-[9px] text-gray-400 uppercase font-black">Opciones</div>
                                            <button type="button"
                                                    @click="$dispatch('abrir-modal-eliminar', { nombre: '{{ $actividad }}', action: '{{ route('profesor.actas.eliminarActividad', [$materia->nrc, $actividad]) }}' })"
                                                    class="flex items-center w-full px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-bold transition">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Eliminar columna
                                            </button>
                                        </div>
                                    </th>
                                @endforeach

                                <th class="px-2 py-4 font-bold uppercase text-[11px] text-center bg-orange-600 border-l border-orange-500">Proyecto</th>
                                <th class="px-2 py-4 font-bold uppercase text-[11px] text-center bg-blue-800 border-l border-blue-700">U1</th>
                                <th class="px-2 py-4 font-bold uppercase text-[11px] text-center bg-blue-800 border-l border-blue-700">U2-U3</th>
                                <th class="px-2 py-4 font-bold uppercase text-[11px] text-center bg-red-700 border-l border-red-600">Recup</th>
                                <th class="px-4 py-4 font-bold uppercase text-[11px] text-center bg-gray-900 sticky right-0 z-10">Final</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($alumnos ?? [] as $correo => $datos)
                                <tr class="alumno-fila hover:bg-blue-50/50 transition cursor-pointer"
                                    @click="enfocarAlumno({{ json_encode($datos) }}, '{{ $correo }}')">
                                    <td class="px-4 py-3 sticky-column bg-white border-r">
                                        <div class="font-bold text-gray-900 text-xs uppercase nombre-alumno">{{ $datos['nombre'] }}</div>
                                        <div class="text-[10px] text-blue-600 font-medium">{{ $correo }}</div>
                                    </td>
                                    <td class="px-2 py-2 text-center bg-yellow-50/30">
                                        <input type="number" step="0.1" class="input-calif input-part" value="{{ $datos['manual']->participacion ?? 0 }}" data-email="{{ $correo }}" data-campo="participacion" onblur="guardarDatoManual(this)" @input.stop="recalcularTodo()">
                                    </td>

                                    @foreach($actividades ?? [] as $actividad)
                                        @php $tipo = $tipos[$actividad] ?? 'tarea'; @endphp
                                        <td class="px-2 py-4 text-center text-sm font-bold text-gray-700 border-l border-gray-100 {{ $tipo === 'practica' ? 'nota-practica bg-indigo-50/30' : 'nota-tarea bg-blue-50/20' }}">
                                            {{ $datos['notas'][$actividad] ?? 0 }}
                                        </td>
                                    @endforeach

                                    <td class="px-2 py-2 text-center bg-orange-50/30">
                                        <input type="number" step="0.1" class="input-calif input-proy" value="{{ $datos['manual']->proyecto ?? 0 }}" data-email="{{ $correo }}" data-campo="proyecto" onblur="guardarDatoManual(this)" @input.stop="recalcularTodo()">
                                    </td>
                                    <td class="px-2 py-2 text-center bg-blue-50/30">
                                        <input type="number" step="0.1" class="input-calif input-u1" value="{{ $datos['manual']->examen_u1 ?? 0 }}" data-email="{{ $correo }}" data-campo="examen_u1" onblur="guardarDatoManual(this)" @input.stop="recalcularTodo()">
                                    </td>
                                    <td class="px-2 py-2 text-center bg-blue-50/30 border-l border-blue-100">
                                        <input type="number" step="0.1" class="input-calif input-u2" value="{{ $datos['manual']->examen_u2_u3 ?? 0 }}" data-email="{{ $correo }}" data-campo="examen_u2_u3" onblur="guardarDatoManual(this)" @input.stop="recalcularTodo()">
                                    </td>
                                    <td class="px-2 py-2 text-center bg-red-50/30 border-l border-red-100">
                                        <input type="number" step="0.1" class="input-calif input-recup" value="{{ $datos['manual']->recuperacion_u1 ?? '' }}" placeholder="-" data-email="{{ $correo }}" data-campo="recuperacion_u1" onblur="guardarDatoManual(this)" @input.stop="recalcularTodo()">
                                    </td>
                                    <td class="px-4 py-2 text-center bg-gray-50 font-black text-lg sticky right-0 shadow-sm border-l">
                                        <span class="final-span">0</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="50" class="py-24 text-center text-gray-400 italic">Sin datos aún. Sube un archivo de Teams para comenzar.</td></tr>
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

        {{-- MODAL ENFOQUE ALUMNO --}}
        <div x-show="alumnoEnfocado" x-cloak class="focus-overlay" style="position:fixed;inset:0;z-index:100;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.2);">
            <div @click.away="alumnoEnfocado = null" class="cloned-row-container">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-black text-[#002d62]" x-text="alumnoEnfocado?.nombre ?? ''"></h3>
                    <button @click="alumnoEnfocado = null" class="text-gray-400 hover:text-gray-600 font-black text-xl">✕</button>
                </div>
                <table class="cloned-table">
                    <thead>
                        <tr>
                            <th>Part</th>
                            @foreach($actividades ?? [] as $act)
                                <th>{{ $act }}</th>
                            @endforeach
                            <th>Proyecto</th><th>U1</th><th>U2-U3</th><th>Recup</th><th>Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td x-text="getValorEnfocado('participacion')"></td>
                            @foreach($actividades ?? [] as $act)
                                <td x-text="getNotaEnfocada('{{ $act }}')"></td>
                            @endforeach
                            <td x-text="getValorEnfocado('proyecto')"></td>
                            <td x-text="getValorEnfocado('examen_u1')"></td>
                            <td x-text="getValorEnfocado('examen_u2_u3')"></td>
                            <td x-text="getValorEnfocado('recuperacion_u1') || '-'"></td>
                            <td x-text="getFinalEnfocado()"></td>
                        </tr>
                    </tbody>
                </table>
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

    <script>
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
                alumnoEnfocado: null,
                correoEnfocado: '',
                actividades: @js($actividades),
                tipos: @js($tipos),

                enfocarAlumno(datos, correo) {
                    this.alumnoEnfocado = datos;
                    this.correoEnfocado = correo;
                },

                getValorEnfocado(campo) {
                    if (!this.correoEnfocado) return 0;
                    const input = document.querySelector(`.alumno-fila [data-email="${this.correoEnfocado}"][data-campo="${campo}"]`);
                    if (!input) return this.alumnoEnfocado?.manual?.[campo] ?? 0;
                    const val = input.value.trim();
                    return val !== '' ? val : 0;
                },

                getNotaEnfocada(act) {
                    if (!this.alumnoEnfocado) return 0;
                    return this.alumnoEnfocado.notas?.[act] ?? 0;
                },

                getFinalEnfocado() {
                    if (!this.correoEnfocado) return 0;
                    const input = document.querySelector(`.alumno-fila [data-email="${this.correoEnfocado}"][data-campo="participacion"]`);
                    if (!input) return 0;
                    return input.closest('tr')?.querySelector('.final-span')?.innerText ?? 0;
                },

                recalcularTodo() {
                    const getW = (id) => (parseFloat(document.getElementById(id)?.value) || 0) / 100;
                    const wPart   = getW('w_part');
                    const wTareas = getW('w_tareas');
                    const wPrac   = getW('w_prac');
                    const wProy   = getW('w_proy');
                    const wExam   = getW('w_exam');

                    document.querySelectorAll('.alumno-fila').forEach(fila => {
                        const part = parseFloat(fila.querySelector('.input-part').value)  || 0;
                        const proy = parseFloat(fila.querySelector('.input-proy').value)  || 0;
                        const u2u3 = parseFloat(fila.querySelector('.input-u2').value)    || 0;
                        const valRec = fila.querySelector('.input-recup').value.trim();
                        const u1 = (valRec !== '') ? parseFloat(valRec) : (parseFloat(fila.querySelector('.input-u1').value) || 0);

                        let sumaT = 0, contT = 0;
                        fila.querySelectorAll('.nota-tarea').forEach(td => { sumaT += parseFloat(td.innerText) || 0; contT++; });
                        const pT = contT > 0 ? (sumaT / contT) : 0;

                        let sumaP = 0, contP = 0;
                        fila.querySelectorAll('.nota-practica').forEach(td => { sumaP += parseFloat(td.innerText) || 0; contP++; });
                        const pP = contP > 0 ? (sumaP / contP) : 0;

                        let final = (part * wPart) + (pT * wTareas) + (pP * wPrac) + (proy * wProy) + (((u1 + u2u3) / 2) * wExam);
                        final = Math.round(final * 100) / 100;
                        let red = (final >= 5.5 && final < 6.0) ? 5 : Math.round(final);

                        const span = fila.querySelector('.final-span');
                        if (span) {
                            span.innerText = red;
                            span.className = red < 6 ? 'final-span text-red-600' : 'final-span text-blue-900';
                        }
                    });
                }
            }
        }
    </script>
</x-app-layout>