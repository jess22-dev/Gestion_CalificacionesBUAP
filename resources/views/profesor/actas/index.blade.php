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
        .table-container { overflow-x: auto; scrollbar-width: thin; scrollbar-color: #002d62 #f1f1f1; }
        .sticky-column { position: sticky; left: 0; z-index: 20; box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1); }
        .input-calif { width: 60px !important; height: 35px; font-size: 15px !important; font-weight: bold; border-radius: 6px; border: 1px solid #d1d5db; text-align: center; transition: all 0.2s; }
        .input-calif:focus { border-color: #f39c12; ring: 2px; outline: none; }
        .th-teams { min-width: 130px; max-width: 200px; white-space: normal; word-wrap: break-word; }
    </style>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="actaApp()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            {{-- Botón volver --}}
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('materias.show', $materia->nrc) }}" 
                   class="text-[#1e4b8a] font-bold hover:underline flex items-center">
                    ← Volver a {{ $materia->Materia }}
                </a>
            </div>


            {{-- ADVERTENCIA: Alumnos en HTM no encontrados en Excel Ver --}}
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
                                                {{-- Campo oculto con decisión (vacío hasta que elija) --}}
                                                <input type="hidden" name="decisiones[{{ $faltante['codigo'] }}]" id="decision-{{ $loop->index }}" value="">
                                                <div class="flex justify-center gap-2">
                                                    <button type="button"
                                                            onclick="elegirDecision({{ $loop->index }}, 'mantener', '{{ addslashes($faltante['nombre']) }}')"
                                                            id="btn-mantener-{{ $loop->index }}"
                                                            class="btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-green-700 bg-white border-green-300 hover:bg-green-100">
                                                         Mantener
                                                    </button>
                                                    <button type="button"
                                                            onclick="elegirDecision({{ $loop->index }}, 'baja', '{{ addslashes($faltante['nombre']) }}')"
                                                            id="btn-baja-{{ $loop->index }}"
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

                        {{-- Botón proceder (oculto hasta que todos tengan decisión) --}}
                        <div class="mt-5 flex justify-end" id="contenedor-proceder" hidden>
                            <button type="button"
                                    onclick="procederCambios()"
                                    class="bg-[#002d62] text-white px-8 py-3 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-[#1e4b8a] transition shadow-lg">
                                Proceder con los cambios →
                            </button>
                        </div>
                    </form>
                </div>
            @endif


            {{-- ALUMNOS SIN MATRÍCULA DETECTADOS --}}
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
                                                    <button type="button"
                                                            onclick="elegirDecisionMatricula({{ $loop->index }}, 'asignar', '{{ addslashes($alumno['nombre']) }}')"
                                                            id="btn-asignar-{{ $loop->index }}"
                                                            class="text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-blue-700 bg-white border-blue-300 hover:bg-blue-100">
                                                         Asignar matrícula
                                                    </button>
                                                    <button type="button"
                                                            onclick="elegirDecisionMatricula({{ $loop->index }}, 'ignorar', '{{ addslashes($alumno['nombre']) }}')"
                                                            id="btn-ignorar-{{ $loop->index }}"
                                                            class="text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-gray-500 bg-white border-gray-300 hover:bg-gray-100">
                                                         Ignorar
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="text"
                                                       name="decisiones[{{ $alumno['email'] }}][codigo]"
                                                       id="input-mat-{{ $loop->index }}"
                                                       placeholder="000000000"
                                                       maxlength="9"
                                                       disabled
                                                       class="w-32 text-center rounded-lg border-gray-300 text-sm font-mono focus:ring-blue-400 disabled:bg-gray-100 disabled:text-gray-400 transition">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Botón proceder --}}
                        <div class="mt-5 flex justify-end" id="contenedor-proceder-matriculas" hidden>
                            <button type="button"
                                    onclick="procederMatriculas()"
                                    class="bg-blue-600 text-white px-8 py-3 rounded-xl font-black text-sm uppercase tracking-widest hover:bg-blue-700 transition shadow-lg">
                                Proceder con los cambios 
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- SECCIÓN 1: SUBIDA --}}
            <div class="bg-white rounded-3xl shadow-sm border border-dashed border-blue-300 p-6 mb-6 text-center">
                <h3 class="text-md font-bold text-gray-700 mb-3">Sube tus archivos</h3>
                <form action="{{ route('profesor.actas.procesar', $materia->nrc) }}" method="POST" enctype="multipart/form-data" class="flex flex-col items-center">
                    @csrf
                    <input type="file" name="archivos_teams[]" multiple accept=".xlsx, .xls, .csv" required 
                        class="block w-full max-w-xs text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-4">
                    <button type="submit" class="bg-[#002d62] text-white px-8 py-2 rounded-xl font-bold hover:bg-blue-800 transition shadow-md">Cargar Actividades</button>
                </form>
            </div>

            {{-- SECCIÓN 2: INFO Y PESOS --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex flex-col lg:flex-row justify-between items-center gap-6">
                    <div>
                        <h1 class="text-2xl font-black text-[#002d62] uppercase leading-tight">{{ $materia->Materia }}</h1>
                        <p class="text-gray-500 text-sm font-medium">Clave: {{ $materia->clave }} | <span class="text-blue-600">Primavera 2026</span></p>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-blue-50 p-4 rounded-2xl border border-blue-100">
                        <div class="text-center">
                            <label class="block text-[10px] font-black text-blue-800 uppercase">PART %</label>
                            <input type="number" id="w_part" value="10" @input="recalcularTodo()" class="w-16 p-1 rounded-lg border-gray-300 text-center font-bold text-sm">
                        </div>
                        <div class="text-center">
                            <label class="block text-[10px] font-black text-blue-800 uppercase">TEAMS %</label>
                            <input type="number" id="w_teams" value="30" @input="recalcularTodo()" class="w-16 p-1 rounded-lg border-gray-300 text-center font-bold text-sm">
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

            {{-- SECCIÓN 3: TABLA --}}
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-200">
                <div class="table-container">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#002d62] text-white">
                                <th class="px-4 py-4 font-bold uppercase text-[11px] sticky-column bg-[#002d62] shadow-md">Estudiante</th>
                                <th class="px-2 py-4 font-bold uppercase text-[11px] text-center bg-yellow-600 border-l border-yellow-500">Part</th>
                                
                                {{-- ENCABEZADOS DE ACTIVIDADES (Aquí sí va el menú) --}}
                                @foreach($actividades ?? [] as $actividad)
                                    <th class="th-teams px-3 py-4 font-bold uppercase text-[10px] text-center border-l border-blue-800/30 relative" 
                                        style="overflow: visible !important;" 
                                        x-data="{ open: false }">
                                        
                                        <div class="flex flex-col items-center cursor-pointer group" @click="open = !open">
                                            <span class="group-hover:text-yellow-400 transition-colors">{{ $actividad }}</span>
                                            <svg class="w-3 h-3 mt-1 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>

                                        {{-- MENÚ DESPLEGABLE --}}
                                        <div x-show="open" @click.away="open = false" x-cloak
                                            class="absolute left-0 mt-2 w-48 bg-white shadow-2xl rounded-lg py-2 border border-gray-200 text-gray-800"
                                            style="z-index: 9999; top: 100%;">
                                            
                                            <div class="px-4 py-1 border-b border-gray-100 text-[9px] text-gray-400 uppercase font-black">Opciones</div>
                                            
                                            <form action="{{ route('profesor.actas.eliminarActividad', [$materia->nrc, $actividad]) }}" 
                                                method="POST" 
                                                onsubmit="return confirm('¿Eliminar {{ $actividad }}?');">
                                                @csrf @method('DELETE')
                                                {{-- Nuevo Botón de Eliminar que activa el modal lindo --}}
                                                <button type="button" 
                                                        @click="$dispatch('abrir-modal-eliminar', { 
                                                            nombre: '{{ $actividad }}', 
                                                            action: '{{ route('profesor.actas.eliminarActividad', [$materia->nrc, $actividad]) }}' 
                                                        })"
                                                        class="flex items-center w-full px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-bold transition">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Eliminar columna
                                                </button>
                                            </form>
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
                                <tr class="alumno-fila hover:bg-blue-50/50 transition">
                                    <td class="px-4 py-3 sticky-column bg-white border-r">
                                        <div class="font-bold text-gray-900 text-xs uppercase nombre-alumno">{{ $datos['nombre'] }}</div>
                                        <div class="text-[10px] text-blue-600 font-medium">{{ $correo }}</div>
                                    </td>

                                    <td class="px-2 py-2 text-center bg-yellow-50/30">
                                        <input type="number" step="0.1" class="input-calif input-part" value="{{ $datos['manual']->participacion ?? 0 }}" data-email="{{ $correo }}" data-campo="participacion" onblur="guardarDatoManual(this)" @input="recalcularTodo()">
                                    </td>

                                    {{-- CELDAS DE CALIFICACIONES (Aquí solo va el número) --}}
                                    @foreach($actividades ?? [] as $actividad)
                                        <td class="nota-teams px-2 py-4 text-center text-sm font-bold text-gray-700 border-l border-gray-100">
                                            {{ $datos['notas'][$actividad] ?? 0 }}
                                        </td>
                                    @endforeach

                                    <td class="px-2 py-2 text-center bg-orange-50/30">
                                        <input type="number" step="0.1" class="input-calif input-proy" value="{{ $datos['manual']->proyecto ?? 0 }}" data-email="{{ $correo }}" data-campo="proyecto" onblur="guardarDatoManual(this)" @input="recalcularTodo()">
                                    </td>
                                    <td class="px-2 py-2 text-center bg-blue-50/30">
                                        <input type="number" step="0.1" class="input-calif input-u1" value="{{ $datos['manual']->examen_u1 ?? 0 }}" data-email="{{ $correo }}" data-campo="examen_u1" onblur="guardarDatoManual(this)" @input="recalcularTodo()">
                                    </td>
                                    <td class="px-2 py-2 text-center bg-blue-50/30 border-l border-blue-100">
                                        <input type="number" step="0.1" class="input-calif input-u2" value="{{ $datos['manual']->examen_u2_u3 ?? 0 }}" data-email="{{ $correo }}" data-campo="examen_u2_u3" onblur="guardarDatoManual(this)" @input="recalcularTodo()">
                                    </td>
                                    <td class="px-2 py-2 text-center bg-red-50/30 border-l border-red-100">
                                        <input type="number" step="0.1" class="input-calif input-recup" value="{{ $datos['manual']->recuperacion_u1 ?? '' }}" placeholder="-" data-email="{{ $correo }}" data-campo="recuperacion_u1" onblur="guardarDatoManual(this)" @input="recalcularTodo()">
                                    </td>
                                    <td class="px-4 py-2 text-center bg-gray-50 font-black text-lg sticky right-0 shadow-sm border-l">
                                        <span class="final-span">0</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="50" class="py-24 text-center text-gray-400 italic">Sin datos aún. Sube un archivo de Teams para comenzar.</td></tr>
                            @endforelse
                        </tbody>
                        
                        <div x-data="{ showModal: false, actividadEliminar: '', formAction: '' }"
                            @abrir-modal-eliminar.window="showModal = true; actividadEliminar = $event.detail.nombre; formAction = $event.detail.action"
                            x-show="showModal"
                            class="fixed inset-0 z-[10000] overflow-y-auto"
                            x-cloak>
                            
                            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

                            <div class="flex min-h-full items-center justify-center p-4">
                                <div @click.away="showModal = false" 
                                    class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg border border-gray-100">
                                    
                                    <div class="bg-white px-8 py-10">
                                        <div class="flex items-center justify-center mb-6">
                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50">
                                                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                                </svg>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center">
                                            <h3 class="text-xl font-black text-gray-900 mb-2">¿Confirmar eliminación?</h3>
                                            <p class="text-sm text-gray-500">
                                                Estás a punto de borrar la tarea <span class="font-bold text-red-600" x-text="actividadEliminar"></span>. 
                                                Esta acción no se puede deshacer y afectará el promedio final.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 px-8 py-6 flex flex-row-reverse gap-3">
                                        <form :action="formAction" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex w-full justify-center rounded-xl bg-red-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition-all uppercase tracking-widest">
                                                Sí, deseo eliminarla
                                            </button>
                                        </form>
                                        <button type="button" @click="showModal = false"
                                                class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-all uppercase tracking-widest">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </table>
                </div>
            </div>

                {{-- SECCIÓN DE ACCIONES FINAL (DEBAJO DE LA TABLA) --}}
                <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4 px-2">
                    
                    {{-- BOTÓN LIMPIAR (Lado Izquierdo) --}}
                    <div x-data>
                        <button type="button" 
                                @click="$dispatch('abrir-modal-eliminar', { 
                                    nombre: 'TODOS LOS DATOS DEL ACTA', 
                                    action: '{{ route('profesor.actas.eliminar', $materia->nrc) }}' 
                                })"
                                class="flex items-center px-6 py-3 bg-white border-2 border-red-100 text-red-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-red-50 hover:border-red-200 transition-all shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Limpiar Acta Completa
                        </button>
                    </div>

                    {{-- BOTÓN EXPORTAR (Lado Derecho) --}}
                    <form action="{{ route('profesor.actas.exportar', $materia->nrc) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="flex items-center px-8 py-3 bg-green-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-green-700 hover:scale-105 transition-all shadow-lg shadow-green-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar a Excel (.xlsx)
                        </button>
                    </form>
                </div>
        </div>
    </div>

    <script>
        function guardarDatoManual(input) {
            const url = "{{ route('profesor.actas.guardar_manual', ['nrc' => $materia->nrc]) }}";
            const datos = {
                _token: '{{ csrf_token() }}',
                email: input.getAttribute('data-email'),
                campo: input.getAttribute('data-campo'),
                valor: input.value,
                nombre: input.closest('tr').querySelector('.nombre-alumno').innerText
            };

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(datos)
            }).then(() => console.log('✓'));
        }

        // ── Lógica de decisiones para alumnos sin matrícula ──
        const totalSinMatricula = document.querySelectorAll('[id^="accion-mat-"]').length;
        let decisionesMatricula = {};

        function elegirDecisionMatricula(index, tipo, nombre) {
            decisionesMatricula[index] = tipo;

            const btnAsignar = document.getElementById('btn-asignar-' + index);
            const btnIgnorar = document.getElementById('btn-ignorar-' + index);
            const inputMat   = document.getElementById('input-mat-' + index);
            const accionHid  = document.getElementById('accion-mat-' + index);
            const fila       = document.getElementById('fila-mat-' + index);

            // Resetear estilos
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
            // Validar que los que eligieron asignar tienen matrícula de 9 dígitos
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

            const msg = '¿Confirmas los siguientes cambios?\n\n' +
                        ' Asignar matrícula: ' + asignar + ' alumno(s)\n' +
                        ' Ignorar: ' + ignorar + ' alumno(s)\n\n' +
                        'Esta acción no se puede deshacer.';

            if (confirm(msg)) {
                document.getElementById('form-matriculas').submit();
            }
        }

        // ── Lógica de decisiones para alumnos faltantes ──
        const totalFaltantes = document.querySelectorAll('[id^="decision-"]').length;
        let decisiones = {};

        function elegirDecision(index, tipo, nombre) {
            decisiones[index] = tipo;

            const btnMantener = document.getElementById('btn-mantener-' + index);
            const btnBaja     = document.getElementById('btn-baja-' + index);
            const input       = document.getElementById('decision-' + index);
            const fila        = document.getElementById('fila-' + index);

            // Resetear estilos
            btnMantener.className = 'btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-green-700 bg-white border-green-300 hover:bg-green-100';
            btnBaja.className     = 'btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-red-700 bg-white border-red-300 hover:bg-red-100';
            fila.classList.remove('bg-green-50', 'bg-red-50');

            // Aplicar selección
            if (tipo === 'mantener') {
                btnMantener.className = 'btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-green-800 bg-green-200 border-green-400 ring-2 ring-green-400';
                fila.classList.add('bg-green-50');
                input.value = 'mantener';
            } else {
                btnBaja.className = 'btn-decision text-xs px-3 py-1 rounded-lg font-bold border transition cursor-pointer text-red-800 bg-red-200 border-red-400 ring-2 ring-red-400';
                fila.classList.add('bg-red-50');
                input.value = 'baja';
            }

            // Mostrar botón proceder cuando todos tienen decisión
            if (Object.keys(decisiones).length === totalFaltantes) {
                document.getElementById('contenedor-proceder').removeAttribute('hidden');
            }
        }

        function procederCambios() {
            const bajas = Object.values(decisiones).filter(d => d === 'baja').length;
            const mantener = Object.values(decisiones).filter(d => d === 'mantener').length;

            const msg = '¿Confirmas los siguientes cambios?\n\n' +
                        ' Mantener: ' + mantener + ' alumno(s)\n' +
                        ' Dar de baja: ' + bajas + ' alumno(s)\n\n' +
                        'Esta acción no se puede deshacer.';

            if (confirm(msg)) {
                document.getElementById('form-faltantes').submit();
            }
        }

        function actaApp() {
            return {
                recalcularTodo() {
                    const wPart = (parseFloat(document.getElementById('w_part').value) || 0) / 100;
                    const wTeams = (parseFloat(document.getElementById('w_teams').value) || 0) / 100;
                    const wProy = (parseFloat(document.getElementById('w_proy').value) || 0) / 100;
                    const wExam = (parseFloat(document.getElementById('w_exam').value) || 0) / 100;

                    document.querySelectorAll('.alumno-fila').forEach(fila => {
                        const part = parseFloat(fila.querySelector('.input-part').value) || 0;
                        const proy = parseFloat(fila.querySelector('.input-proy').value) || 0;
                        const u2u3 = parseFloat(fila.querySelector('.input-u2').value) || 0;
                        
                        const valRecup = fila.querySelector('.input-recup').value;
                        const u1Original = parseFloat(fila.querySelector('.input-u1').value) || 0;
                        const u1 = (valRecup !== "" && valRecup !== null) ? parseFloat(valRecup) : u1Original;

                        let sumaT = 0, contT = 0;
                        fila.querySelectorAll('.nota-teams').forEach(td => {
                            sumaT += parseFloat(td.innerText) || 0;
                            contT++;
                        });
                        const promTeams = contT > 0 ? (sumaT / contT) : 0;
                        const promExam = (u1 + u2u3) / 2;

                        let final = (part * wPart) + (promTeams * wTeams) + (proy * wProy) + (promExam * wExam);
                        
                        // Redondeo BUAP: 5.5 a 5.9 es 5.
                        let redondeado;
                        if (final >= 5.5 && final < 6.0) {
                            redondeado = 5;
                        } else {
                            redondeado = Math.round(final);
                        }

                        const span = fila.querySelector('.final-span');
                        span.innerText = redondeado;
                        span.classList.toggle('text-red-600', redondeado < 6);
                        span.classList.toggle('text-blue-900', redondeado >= 6);
                    });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { actaApp().recalcularTodo(); }, 500);
        });
    </script>
</x-app-layout>