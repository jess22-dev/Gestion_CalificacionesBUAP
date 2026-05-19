<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2>
                {{ __('Generador de Actas Finales') }}
            </h2>
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

        /* Estilos de Enfoque y Desenfoque */
        .blur-content { filter: blur(5px); pointer-events: none; opacity: 0.4; transition: all 0.3s ease; }
        .focus-overlay { position: fixed; inset: 0; z-index: 100; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.2); }
        .cloned-row-container { 
            background: white; padding: 2rem; border-radius: 24px; 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); 
            width: 95%; max-width: 1300px; overflow-x: auto; border: 4px solid #002d62; 
        }
        .cloned-table { width: 100%; border-collapse: collapse; }
        .cloned-table th { background: #002d62; color: white; padding: 12px; font-size: 10px; text-transform: uppercase; border: 1px solid rgba(255,255,255,0.1); }
        .cloned-table td { padding: 15px; text-align: center; font-weight: bold; border: 1px solid #e5e7eb; font-size: 14px; }
    </style>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="actaApp()" x-init="recalcularTodo()">
        {{-- Contenedor Principal con condicional de desenfoque --}}
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 transition-all duration-300" :class="alumnoEnfocado ? 'blur-content' : ''">
            <a href="{{ route('profesor.materias.show', $materia->nrc) }}" class="text-[#1e4b8a] font-bold hover:underline flex items-center mb-4">
                Volver
            </a>

            {{-- SECCIÓN CARGA DE ARCHIVOS --}}
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
                        <p class="text-gray-500 text-sm font-medium">Clave: {{ $materia->clave }} | <span class="text-blue-600">Periodo 2026</span></p>
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
                                @foreach($actividades as $act)
                                    @php
                                        $actKey = trim($act);
                                        $tiposNormalizados = array_change_key_case(array_map('trim', $tipos), CASE_LOWER);
                                        $tipoAct = $tiposNormalizados[strtolower($actKey)] ?? 'otra';
                                        $colorBg = ($tipoAct == 'tarea') ? 'bg-blue-700' : (($tipoAct == 'practica') ? 'bg-indigo-700' : 'bg-gray-600');
                                    @endphp
                                    <th class="th-teams px-3 py-4 font-bold uppercase text-[10px] text-center border-l border-white/10 {{ $colorBg }}" x-data="{ openMenu: false }">
                                        <div class="flex flex-col items-center cursor-pointer" @click="openMenu = !openMenu">
                                            <span>{{ $act }}</span>
                                            <span class="text-[8px] opacity-70 italic">({{ $tipoAct }})</span>
                                        </div>
                                        <div x-show="openMenu" @click.away="openMenu = false" x-cloak class="absolute mt-2 w-48 bg-white shadow-2xl rounded-lg py-2 border border-gray-200 text-gray-800 text-left z-50">
                                            <button type="button" @click="$dispatch('abrir-modal-eliminar', { nombre: '{{ $act }}', action: '{{ route('profesor.actas.eliminarActividad', [$materia->nrc, $act]) }}' })" class="flex items-center w-full px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-bold transition">
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
                                <tr class="alumno-fila hover:bg-blue-50/50">
                                    <td class="px-4 py-3 sticky-column bg-white">
                                        {{-- El clic aquí dispara el Enfoque --}}
                                        <div class="cursor-pointer group" @click="enfocarAlumno(@js($datos), @js($correo))">
                                            <div class="font-bold text-gray-900 text-xs uppercase nombre-alumno group-hover:text-blue-600">{{ $datos['nombre'] }}</div>
                                            <div class="text-[10px] text-blue-600 font-medium">{{ $correo }}</div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2 text-center bg-yellow-50/30">
                                        <input type="number" step="0.1" class="input-calif input-part" value="{{ $datos['manual']->participacion ?? 0 }}" data-email="{{ $correo }}" data-campo="participacion" onblur="guardarDatoManual(this)" @input="recalcularTodo()">
                                    </td>
                                    @foreach($actividades as $actividad)
                                        @php
                                            $actKeyBody = strtolower(trim($actividad));
                                            $tiposNormalizadosBody = array_change_key_case(array_map('trim', $tipos), CASE_LOWER);
                                            $tipoParaClase = $tiposNormalizadosBody[$actKeyBody] ?? 'otra';
                                            $claseJS = ($tipoParaClase == 'tarea') ? 'nota-tarea' : (($tipoParaClase == 'practica') ? 'nota-practica' : '');
                                        @endphp
                                        <td class="{{ $claseJS }} px-2 py-4 text-center text-sm font-bold text-gray-700 border-l border-gray-100">
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
                                <tr><td colspan="50" class="py-24 text-center text-gray-400 italic">Sube archivos para ver resultados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ACCIONES --}}
            <div class="flex flex-col md:flex-row justify-end items-center gap-4 bg-white p-6 rounded-3xl shadow-lg border border-gray-100">
                <button type="button" @click="$dispatch('abrir-modal-eliminar', { nombre: 'TODOS LOS DATOS', action: '{{ route('profesor.actas.eliminar', $materia->nrc) }}' })" class="flex items-center px-6 py-3 bg-white border-2 border-red-100 text-red-600 rounded-2xl font-black text-xs uppercase hover:bg-red-50">
                    Reiniciar Acta
                </button>
                <form action="{{ route('profesor.actas.exportar', $materia->nrc) }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center px-10 py-3 bg-[#002d62] text-white rounded-2xl font-black text-xs uppercase shadow-xl shadow-blue-200">
                        Exportar Acta
                    </button>
                </form>
                <form action="{{ route('profesor.actas.exportar_oficial', $materia->nrc) }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center px-10 py-3 bg-[#002d62] hover:bg-black text-white rounded-2xl font-black text-xs uppercase shadow-xl shadow-blue-200 transition-all transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Exportar Oficial
                    </button>
                </form>
            </div>
        </div>

        {{-- CAPA DE ENFOQUE (CLON SOBRESALIENTE) --}}
        <template x-if="alumnoEnfocado">
            <div class="focus-overlay" @click.self="alumnoEnfocado = null" x-cloak>
                <div class="cloned-row-container" x-transition>
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-2xl font-black text-[#002d62] uppercase" x-text="alumnoEnfocado.nombre"></h3>
                            <p class="text-blue-600 font-bold" x-text="correoEnfocado"></p>
                        </div>
                        <button @click="alumnoEnfocado = null" class="bg-red-600 text-white px-6 py-2 rounded-full font-black text-xs uppercase hover:bg-red-700 transition shadow-lg">
                            Cerrar
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                        <table class="cloned-table">
                            <thead>
                                <tr>
                                    <th>Part</th>
                                    <template x-for="act in actividades">
                                        <th x-text="act"></th>
                                    </template>
                                    <th>Proy</th>
                                    <th>U1</th>
                                    <th>U2-U3</th>
                                    <th>Recup</th>
                                    <th>Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    {{-- CORREGIDO: Lee valores en tiempo real del DOM --}}
                                    <td class="bg-yellow-50" x-text="getValorEnfocado('participacion')"></td>
                                    <template x-for="act in actividades">
                                        <td x-text="getNotaEnfocada(act)"></td>
                                    </template>
                                    <td class="bg-orange-50" x-text="getValorEnfocado('proyecto')"></td>
                                    <td class="bg-blue-50"   x-text="getValorEnfocado('examen_u1')"></td>
                                    <td class="bg-blue-50"   x-text="getValorEnfocado('examen_u2_u3')"></td>
                                    <td class="bg-red-50"    x-text="getValorEnfocado('recuperacion_u1') || '-'"></td>
                                    <td class="bg-green-50 font-black text-lg" x-text="getFinalEnfocado()"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-6 text-center text-gray-400 text-xs font-bold italic uppercase tracking-widest">Modo de Visualización Activo</p>
                </div>
            </div>
        </template>

        {{-- MODAL DE ELIMINACIÓN --}}
        <div x-data="{ showModal: false, itemEliminar: '', formAction: '' }" @abrir-modal-eliminar.window="showModal = true; itemEliminar = $event.detail.nombre; formAction = $event.detail.action" x-show="showModal" class="fixed inset-0 z-[110]" x-cloak>
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

                /**
                 * Lee el valor actual de un input en la fila del alumno enfocado,
                 * directo del DOM, para que refleje ediciones en tiempo real.
                 */
                getValorEnfocado(campo) {
                    if (!this.correoEnfocado) return 0;
                    const input = document.querySelector(
                        `.alumno-fila [data-email="${this.correoEnfocado}"][data-campo="${campo}"]`
                    );
                    if (!input) return this.alumnoEnfocado?.manual?.[campo] ?? 0;
                    const val = input.value.trim();
                    return val !== '' ? val : 0;
                },

                /**
                 * Lee la nota de una actividad del objeto alumnoEnfocado
                 * (estas vienen del servidor y no son editables en la tabla).
                 */
                getNotaEnfocada(act) {
                    if (!this.alumnoEnfocado) return 0;
                    return this.alumnoEnfocado.notas?.[act] ?? 0;
                },

                /**
                 * Lee el valor del .final-span de la fila del alumno enfocado,
                 * que ya fue calculado por recalcularTodo().
                 */
                getFinalEnfocado() {
                    if (!this.correoEnfocado) return 0;
                    const input = document.querySelector(
                        `.alumno-fila [data-email="${this.correoEnfocado}"][data-campo="participacion"]`
                    );
                    if (!input) return 0;
                    const fila = input.closest('tr');
                    return fila?.querySelector('.final-span')?.innerText ?? 0;
                },

                recalcularTodo() {
                    const getW = (id) => (parseFloat(document.getElementById(id).value) || 0) / 100;
                    const wPart  = getW('w_part');
                    const wTareas = getW('w_tareas');
                    const wPrac  = getW('w_prac');
                    const wProy  = getW('w_proy');
                    const wExam  = getW('w_exam');

                    document.querySelectorAll('.alumno-fila').forEach(fila => {
                        const part  = parseFloat(fila.querySelector('.input-part').value)  || 0;
                        const proy  = parseFloat(fila.querySelector('.input-proy').value)  || 0;
                        const u2u3  = parseFloat(fila.querySelector('.input-u2').value)    || 0;
                        const valRec = fila.querySelector('.input-recup').value.trim();
                        const u1    = (valRec !== '') ? parseFloat(valRec) : (parseFloat(fila.querySelector('.input-u1').value) || 0);

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