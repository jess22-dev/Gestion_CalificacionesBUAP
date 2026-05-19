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
        .table-container { overflow-x: auto; scrollbar-width: thin; scrollbar-color: #002d62 #f1f1f1; }
        .sticky-column { position: sticky; left: 0; z-index: 20; border-right: 2px solid #e5e7eb; }
        .input-calif { width: 60px !important; height: 35px; font-size: 15px !important; font-weight: bold; border-radius: 6px; border: 1px solid #d1d5db; text-align: center; transition: all 0.2s; }
        .input-calif:focus { border-color: #f39c12; ring: 2px; outline: none; }
        .th-teams { min-width: 130px; max-width: 200px; white-space: normal; word-wrap: break-word; }
        [x-cloak] { display: none !important; }
    </style>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="actaApp()">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('profesor.materias.show', $materia->nrc) }}" class="text-[#1e4b8a] font-bold hover:underline flex items-center">
                    Volver
            </a>
            {{-- SECCIÓN CARGA DE ARCHIVOS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                {{-- BOTÓN TAREAS --}}
                <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-dashed border-blue-200 hover:border-blue-400 transition-colors">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-blue-50 rounded-2xl mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-800 uppercase">Cargar Tareas</h3>
                            <p class="text-[10px] text-gray-500">Asigna automáticamente como TAREA</p>
                        </div>
                    </div>
                    <form action="{{ route('profesor.actas.importar', $materia->nrc) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="tipo" value="tarea">
                        <input type="file" name="archivo" accept=".xlsx, .xls" required 
                            class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <button type="submit" class="bg-blue-600 text-white p-2 rounded-xl hover:bg-blue-700 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </button>
                    </form>
                </div>

                {{-- BOTÓN PRÁCTICAS --}}
                <div class="bg-white p-6 rounded-3xl shadow-sm border-2 border-dashed border-indigo-200 hover:border-indigo-400 transition-colors">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-indigo-50 rounded-2xl mr-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.628.251a2 2 0 01-1.285 0l-.628-.251a6 6 0 00-3.86-.517l-2.387.477a2 2 0 00-1.022.547l-.34.34a2 2 0 000 2.829l1.245 1.245a2 2 0 002.829 0l.143-.143a2 2 0 011.285 0l.143.143a2 2 0 002.829 0l1.245-1.245a2 2 0 000-2.829l-.34-.34z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-800 uppercase">Cargar Prácticas</h3>
                            <p class="text-[10px] text-gray-500">Asigna automáticamente como PRÁCTICA</p>
                        </div>
                    </div>
                    <form action="{{ route('profesor.actas.importar', $materia->nrc) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="tipo" value="practica">
                        <input type="file" name="archivo" accept=".xlsx, .xls" required 
                            class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
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

        {{-- TABLA --}}
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-200">
                <div class="table-container">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#002d62] text-white">
                                <th class="px-4 py-4 font-bold uppercase text-[11px] sticky-column bg-[#002d62] shadow-md">Estudiante</th>
                                <th class="px-2 py-4 font-bold uppercase text-[11px] text-center bg-yellow-600 border-l border-yellow-500">Part</th>
                                
                                @foreach($actividades as $act)
                                    @php
                                        // 1. Limpiamos el nombre de la actividad actual
                                        $actKey = trim($act);
                                        
                                        // 2. Normalizamos el array $tipos para que no fallen las comparaciones por espacios o mayúsculas
                                        // Esto busca el valor real guardado cuando el profesor subió el archivo
                                        $tiposNormalizados = array_change_key_case(array_map('trim', $tipos), CASE_LOWER);
                                        $llaveBusqueda = strtolower($actKey);
                                        
                                        $tipoAct = $tiposNormalizados[$llaveBusqueda] ?? 'otra';
                                        
                                        $colorBg = ($tipoAct == 'tarea') ? 'bg-blue-700' : 
                                                (($tipoAct == 'practica') ? 'bg-indigo-700' : 'bg-gray-600');
                                    @endphp
                                    <th class="th-teams px-3 py-4 font-bold uppercase text-[10px] text-center border-l border-white/10 {{ $colorBg }}" x-data="{ open: false }">
                                        <div class="flex flex-col items-center cursor-pointer group" @click="open = !open">
                                            <span>{{ $act }}</span>
                                            <div class="flex items-center gap-1 mt-1">
                                                <span class="text-[8px] opacity-70 italic">({{ $tipoAct }})</span>
                                                <svg class="w-3 h-3 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                        </div>

                                        <div x-show="open" @click.away="open = false" x-cloak
                                            class="absolute mt-2 w-48 bg-white shadow-2xl rounded-lg py-2 border border-gray-200 text-gray-800 text-left z-50">
                                            <button type="button" 
                                                @click="$dispatch('abrir-modal-eliminar', { 
                                                    nombre: '{{ $act }}', 
                                                    action: '{{ route('profesor.actas.eliminarActividad', [$materia->nrc, $act]) }}' 
                                                })"
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
                                <tr class="alumno-fila hover:bg-blue-50/50 transition">
                                    <td class="px-4 py-3 sticky-column bg-white">
                                        <div class="font-bold text-gray-900 text-xs uppercase nombre-alumno">{{ $datos['nombre'] }}</div>
                                        <div class="text-[10px] text-blue-600 font-medium">{{ $correo }}</div>
                                    </td>

                                    <td class="px-2 py-2 text-center bg-yellow-50/30">
                                        <input type="number" step="0.1" class="input-calif input-part" value="{{ $datos['manual']->participacion ?? 0 }}" data-email="{{ $correo }}" data-campo="participacion" onblur="guardarDatoManual(this)" @input="recalcularTodo()">
                                    </td>

                                    @foreach($actividades as $actividad)
                                        @php
                                            // Aplicamos la misma lógica de normalización en el cuerpo para el JS
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
                                <tr><td colspan="50" class="py-24 text-center text-gray-400 italic">Sube archivos de Tareas o Prácticas para ver resultados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ACCIONES --}}
            <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4 px-2">
                <div x-data>
                    <button type="button" 
                        @click="$dispatch('abrir-modal-eliminar', { 
                            nombre: 'TODOS LOS DATOS DEL NRC {{ $materia->nrc }}', 
                            action: '{{ route('profesor.actas.eliminar', $materia->nrc) }}' 
                        })"
                        class="flex items-center px-6 py-3 bg-white border-2 border-red-100 text-red-600 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-red-50 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Reiniciar Acta
                    </button>
                </div>

                <form action="{{ route('profesor.actas.exportar', $materia->nrc) }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center px-8 py-3 bg-green-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-green-700 hover:scale-105 transition-all shadow-lg shadow-green-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Exportar Acta Final
                    </button>
                </form>
            </div>
        </div>

        {{-- MODAL DE ELIMINACIÓN --}}
        <div x-data="{ showModal: false, itemEliminar: '', formAction: '' }"
            @abrir-modal-eliminar.window="showModal = true; itemEliminar = $event.detail.nombre; formAction = $event.detail.action"
            x-show="showModal" class="fixed inset-0 z-[100] overflow-y-auto" x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div @click.away="showModal = false" class="relative bg-white rounded-3xl shadow-2xl sm:w-full sm:max-w-lg overflow-hidden">
                    <div class="p-8 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50 mb-4">
                            <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 mb-2">Confirmar acción</h3>
                        <p class="text-sm text-gray-500">¿Estás seguro de eliminar <span class="font-bold text-red-600" x-text="itemEliminar"></span>?</p>
                    </div>
                    <div class="bg-gray-50 px-8 py-6 flex flex-row-reverse gap-3">
                        <form :action="formAction" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-xl bg-red-600 px-6 py-3 text-sm font-bold text-white hover:bg-red-700 uppercase tracking-widest">Sí, eliminar</button>
                        </form>
                        <button type="button" @click="showModal = false" class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-700 border border-gray-300 hover:bg-gray-50 uppercase tracking-widest">Cancelar</button>
                    </div>
                </div>
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
            }).then(() => console.log('Guardado'));
        }

        function actaApp() {
            return {
                recalcularTodo() {
                    const getW = (id) => (parseFloat(document.getElementById(id).value) || 0) / 100;
                    
                    const wPart   = getW('w_part');
                    const wTareas = getW('w_tareas');
                    const wPrac   = getW('w_prac');
                    const wProy   = getW('w_proy');
                    const wExam   = getW('w_exam');

                    document.querySelectorAll('.alumno-fila').forEach(fila => {
                        const part = parseFloat(fila.querySelector('.input-part').value) || 0;
                        const proy = parseFloat(fila.querySelector('.input-proy').value) || 0;
                        const u2u3 = parseFloat(fila.querySelector('.input-u2').value) || 0;
                        
                        const valRecup   = fila.querySelector('.input-recup').value.trim();
                        const u1Original = parseFloat(fila.querySelector('.input-u1').value) || 0;
                        const u1 = (valRecup !== "") ? parseFloat(valRecup) : u1Original;

                        // Promedio Tareas (clase .nota-tarea)
                        let sumaT = 0, contT = 0;
                        fila.querySelectorAll('.nota-tarea').forEach(td => {
                            const nota = parseFloat(td.innerText.trim());
                            if (!isNaN(nota)) { sumaT += nota; contT++; }
                        });
                        const promTareas = contT > 0 ? (sumaT / contT) : 0;

                        // Promedio Prácticas (clase .nota-practica)
                        let sumaP = 0, contP = 0;
                        fila.querySelectorAll('.nota-practica').forEach(td => {
                            const nota = parseFloat(td.innerText.trim());
                            if (!isNaN(nota)) { sumaP += nota; contP++; }
                        });
                        const promPrac = contP > 0 ? (sumaP / contP) : 0;

                        const promExam = (u1 + u2u3) / 2;

                        // Cálculo Final (Lógica BUAP)
                        let final = (part * wPart) + 
                                    (promTareas * wTareas) + 
                                    (promPrac * wPrac) + 
                                    (proy * wProy) + 
                                    (promExam * wExam);
                        
                        final = Math.round(final * 100) / 100;

                        // Regla de redondeo específica: 5.5 a 5.9 -> 5
                        let redondeado;
                        if (final >= 5.5 && final < 6.0) {
                            redondeado = 5;
                        } else {
                            redondeado = Math.round(final);
                        }

                        const span = fila.querySelector('.final-span');
                        if (span) {
                            span.innerText = redondeado;
                            span.classList.toggle('text-red-600', redondeado < 6);
                            span.classList.toggle('text-blue-900', redondeado >= 6);
                        }
                    });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { 
                const app = actaApp();
                app.recalcularTodo(); 
            }, 500);
        });
    </script>
</x-app-layout>