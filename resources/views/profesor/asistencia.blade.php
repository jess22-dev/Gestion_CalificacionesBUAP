<script src="https://unpkg.com/html5-qrcode"></script>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight italic">
            Control de Asistencia — {{ $materia->Materia }} ({{ $materia->nrc }})
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

            {{-- CONTROL DE ASISTENCIA --}}
            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 mb-6">
                <h3 class="text-xl font-bold text-[#002d62] mb-5"> Control de Asistencia</h3>

                <div class="grid md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Duración</label>
                        <select id="duracion" class="w-full rounded-xl border-gray-200 text-sm">
                            <option value="5">5 minutos</option>
                            <option value="10">10 minutos</option>
                            <option value="15">15 minutos</option>
                            <option value="30">30 minutos</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button id="btnIniciar" class="bg-green-600 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-green-700 transition text-sm">
                            Iniciar 
                        </button>
                        <button id="btnDetener" class="bg-red-500 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-red-600 transition text-sm">
                            Detener 
                        </button>
                    </div>
                    <div class="flex items-end">
                        <button id="btnQR" disabled
                            class="w-full bg-gray-300 text-gray-500 px-4 py-2.5 rounded-xl font-bold cursor-not-allowed transition text-sm"
                            title="Primero inicia la asistencia">
                            Escanear QR 
                        </button>
                    </div>
                    <div class="flex items-end">
                        <button id="btnTodos"
                            onclick="todosPresentes()"
                            class="w-full bg-indigo-600 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition text-sm">
                             Todos vinieron
                        </button>
                    </div>
                </div>

                <div id="contador" class="text-lg font-bold text-green-600 mb-2"></div>

                <div id="estado_asistencia" class="hidden mb-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-semibold">
                     Asistencia activa — Los alumnos pueden presentar su QR
                </div>

                <div id="qr_resultado" class="hidden p-3 rounded-xl text-sm font-bold mt-2"></div>
                <div id="reader" class="hidden mt-4" style="width:300px;"></div>

                {{-- Leyenda de estatus --}}
                <div class="mt-4 flex flex-wrap gap-3 text-xs font-bold">
                    <span class="flex items-center gap-1"><span class="w-5 h-5 rounded-full bg-red-200 border-2 border-red-400 inline-block"></span> Ausente</span>
                    <span class="flex items-center gap-1"><span class="w-5 h-5 rounded-full bg-green-400 border-2 border-green-600 inline-block"></span> Presente</span>
                    <span class="flex items-center gap-1"><span class="w-5 h-5 rounded-full bg-yellow-400 border-2 border-yellow-600 inline-block"></span> Retardo</span>
                    <span class="flex items-center gap-1"><span class="w-5 h-5 rounded-full bg-blue-400 border-2 border-blue-600 inline-block"></span> Justificado</span>
                    <span class="text-gray-400 italic">← Click en el círculo para cambiar</span>
                </div>
            </div>

            {{-- LISTA DE ASISTENCIA --}}
            <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100">
                <div class="flex justify-between items-center mb-5">
                    <div>
                        <h3 class="text-xl font-bold text-[#002d62]">Lista de Asistencia</h3>
                        <p class="text-gray-400 text-sm">Fecha: {{ date('d/m/Y') }} — {{ $alumnos->count() }} alumno(s)</p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-black bg-green-100 text-green-700 px-3 py-1 rounded-full">
                            <span id="presentes_count">0</span> presentes
                        </span>
                        <span class="text-xs font-black bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full">
                            <span id="retardos_count">0</span> retardos
                        </span>
                        <span class="text-xs font-black bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                            <span id="justificados_count">0</span> justificados
                        </span>
                        <span class="text-xs font-black bg-red-100 text-red-600 px-3 py-1 rounded-full">
                            <span id="ausentes_count">{{ $alumnos->count() }}</span> ausentes
                        </span>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-[#002d62] text-white text-xs uppercase">
                            <tr>
                                <th class="p-4 text-left">Código</th>
                                <th class="p-4 text-left">Nombre del Alumno</th>
                                <th class="p-4 text-center">Estatus</th>
                                <th class="p-4 text-center">Hora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="tabla_body">
                            @forelse($alumnos as $alumno)
                                <tr class="hover:bg-gray-50 transition" id="fila_{{ $alumno->codigo_estudiante }}">
                                    <td class="p-4 font-mono text-blue-700 font-bold text-xs">
                                        {{ $alumno->codigo_estudiante }}
                                    </td>
                                    <td class="p-4 font-semibold text-gray-700">
                                        {{ $alumno->nombre }}
                                    </td>
                                    <td class="p-4 text-center">
                                        {{-- Círculo clicable — cicla entre 4 estados --}}
                                        <button
                                            id="status_{{ $alumno->codigo_estudiante }}"
                                            data-alumno-id="{{ $alumno->id }}"
                                            data-codigo="{{ $alumno->codigo_estudiante }}"
                                            data-estatus="ausente"
                                            onclick="ciclarEstatus(this)"
                                            class="w-7 h-7 rounded-full bg-red-200 border-2 border-red-400 inline-block cursor-pointer hover:scale-110 transition-transform"
                                            title="Ausente — Click para cambiar">
                                        </button>
                                    </td>
                                    <td class="p-4 text-center text-xs text-gray-400" id="hora_{{ $alumno->codigo_estudiante }}">
                                        —
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-8 text-center text-gray-500 italic">
                                        No hay alumnos inscritos en este grupo todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($alumnos->count() > 0)
                    <div class="mt-6 flex justify-end">
                        <form action="{{ route('asistencias.guardar', $materia->nrc) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="bg-[#002d62] text-white px-8 py-3 rounded-xl font-black shadow-lg hover:bg-[#1e4b8a] transition">
                                CONFIRMAR LISTA OFICIAL 
                            </button>
                        </form>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        let intervalo        = null;
        let asistenciaActiva = false;
        const materia_nrc    = "{{ $materia->nrc }}";
        const totalAlumnos   = {{ $alumnos->count() }};
        const csrf           = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf
        };

        // ─── CONFIGURACIÓN DE ESTATUS ────────────────────────────
        const ESTATUS = {
            ausente:     { next: 'presente',    cls: 'bg-red-200 border-red-400',    title: 'Ausente' },
            presente:    { next: 'retardo',     cls: 'bg-green-400 border-green-600', title: 'Presente' },
            retardo:     { next: 'justificado', cls: 'bg-yellow-400 border-yellow-600', title: 'Retardo' },
            justificado: { next: 'ausente',     cls: 'bg-blue-400 border-blue-600',  title: 'Justificado' },
        };

        // ─── RESTAURAR ESTADO AL CARGAR ─────────────────────────
        fetch(`/asistencia/estado?materia_nrc=${materia_nrc}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        })
        .then(res => res.json())
        .then(data => {
            if (data.detalles && data.detalles.length > 0) {
                data.detalles.forEach(d => {
                    if (d.codigo && d.estatus && d.estatus !== 'ausente') {
                        aplicarEstatus(d.codigo, d.estatus, d.hora ?? '');
                    }
                });
                actualizarContadores();
            }
        });

        // Restaurar contador si hay asistencia activa
        @if($asistenciaActiva)
        (function () {
            const fin = new Date("{{ $asistenciaActiva->termina_en->toIso8601String() }}");
            if (fin > new Date()) {
                activarUI();
                iniciarContador(fin);
            }
        })();
        @endif

        // ─── INICIAR ────────────────────────────────────────────
        document.getElementById('btnIniciar').addEventListener('click', function () {
            const duracion = document.getElementById('duracion').value;
            fetch('/asistencia/iniciar', {
                method: 'POST', headers,
                body: JSON.stringify({ materia_nrc, duracion })
            })
            .then(async res => { const d = await res.json(); if (!res.ok) throw new Error(d.error); return d; })
            .then(data => { activarUI(); iniciarContador(new Date(data.fin)); })
            .catch(e => alert(' ' + e.message));
        });

        // ─── DETENER ────────────────────────────────────────────
        document.getElementById('btnDetener').addEventListener('click', function () {
            fetch('/asistencia/detener', {
                method: 'POST', headers,
                body: JSON.stringify({ materia_nrc })
            })
            .then(async res => { const d = await res.json(); if (!res.ok) throw new Error(d.error); return d; })
            .then(() => {
                desactivarUI();
                if (intervalo) clearInterval(intervalo);
                document.getElementById('contador').innerHTML = 'Asistencia detenida';
            })
            .catch(e => alert(' ' + e.message));
        });

        // ─── TODOS PRESENTES ────────────────────────────────────
        window.todosPresentes = function () {
            if (!confirm('¿Marcar a TODOS los alumnos como presentes?')) return;

            fetch('/asistencia/todos-presentes', {
                method: 'POST', headers,
                body: JSON.stringify({ materia_nrc })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Marcar visualmente a todos
                    document.querySelectorAll('[id^="status_"]').forEach(btn => {
                        const codigo = btn.dataset.codigo;
                        aplicarEstatus(codigo, 'presente', new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }));
                    });
                    actualizarContadores();
                } else {
                    alert('❌ ' + (data.error ?? 'Error'));
                }
            });
        };

        // ─── ESCANEAR QR ────────────────────────────────────────
        document.getElementById('btnQR').addEventListener('click', function () {
            if (!asistenciaActiva) return;
            const readerDiv = document.getElementById('reader');
            const resultado = document.getElementById('qr_resultado');
            readerDiv.classList.remove('hidden');

            const html5QrCode = new Html5Qrcode('reader');
            Html5Qrcode.getCameras().then(devices => {
                if (!devices || !devices.length) { alert('No se encontró cámara'); return; }
                html5QrCode.start(devices[0].id, { fps: 10, qrbox: 250 }, (decodedText) => {
                    html5QrCode.stop();
                    readerDiv.classList.add('hidden');

                    fetch('/asistencia/qr', {
                        method: 'POST', headers,
                        body: JSON.stringify({ qr_data: decodedText, materia_nrc })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            resultado.className = 'p-3 rounded-xl text-sm font-bold bg-green-100 text-green-800 mt-2';
                            resultado.innerHTML = ` Presente: ${data.nombre ?? ''}`;
                            aplicarEstatus(data.codigo, 'presente',
                                new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' }));
                            actualizarContadores();
                        } else {
                            resultado.className = 'p-3 rounded-xl text-sm font-bold bg-red-100 text-red-700 mt-2';
                            resultado.innerHTML = ` ${data.error ?? 'Error desconocido'}`;
                        }
                        resultado.classList.remove('hidden');
                    });
                });
            }).catch(() => alert('No se pudo acceder a la cámara'));
        });

        // ─── CICLAR ESTATUS AL HACER CLICK ──────────────────────
        window.ciclarEstatus = function(btn) {
            const actual  = btn.dataset.estatus;
            const sig     = ESTATUS[actual].next;
            const alumnoId = btn.dataset.alumnoId;
            const codigo   = btn.dataset.codigo;

            fetch('/asistencia/cambiar-estatus', {
                method: 'POST', headers,
                body: JSON.stringify({ materia_nrc, alumno_id: parseInt(alumnoId), estatus: sig })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const hora = sig !== 'ausente'
                        ? new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
                        : '—';
                    aplicarEstatus(codigo, sig, hora);
                    actualizarContadores();
                } else {
                    alert(' ' + (data.error ?? 'Error'));
                }
            });
        };

        // ─── APLICAR ESTATUS VISUALMENTE ────────────────────────
        function aplicarEstatus(codigo, estatus, hora) {
            const btn   = document.getElementById('status_' + codigo);
            const horaEl = document.getElementById('hora_' + codigo);
            const filaEl = document.getElementById('fila_' + codigo);

            if (!btn) return;

            const cfg = ESTATUS[estatus];
            if (!cfg) return;

            // Limpiar clases de color anteriores
            btn.className = `w-7 h-7 rounded-full border-2 inline-block cursor-pointer hover:scale-110 transition-transform ${cfg.cls}`;
            btn.dataset.estatus = estatus;
            btn.title = cfg.title;

            if (horaEl) horaEl.textContent = hora || '—';
            if (filaEl) {
                filaEl.className = filaEl.className.replace(/bg-\w+-\d+/g, '').trim();
                const bgMap = { presente: 'bg-green-50', retardo: 'bg-yellow-50', justificado: 'bg-blue-50', ausente: '' };
                if (bgMap[estatus]) filaEl.classList.add(bgMap[estatus]);
            }
        }

        // ─── ACTUALIZAR CONTADORES ───────────────────────────────
        function actualizarContadores() {
            let presentes = 0, retardos = 0, justificados = 0, ausentes = 0;
            document.querySelectorAll('[id^="status_"]').forEach(btn => {
                const est = btn.dataset.estatus;
                if (est === 'presente')    presentes++;
                else if (est === 'retardo')     retardos++;
                else if (est === 'justificado') justificados++;
                else ausentes++;
            });
            document.getElementById('presentes_count').textContent   = presentes;
            document.getElementById('retardos_count').textContent    = retardos;
            document.getElementById('justificados_count').textContent = justificados;
            document.getElementById('ausentes_count').textContent    = ausentes;
        }

        // ─── ACTIVAR / DESACTIVAR UI ────────────────────────────
        function activarUI() {
            asistenciaActiva = true;
            const b = document.getElementById('btnQR');
            b.disabled = false;
            b.className = 'w-full bg-[#002d62] text-white px-4 py-2.5 rounded-xl font-bold hover:bg-[#1e4b8a] transition text-sm cursor-pointer';
            b.removeAttribute('title');
            document.getElementById('estado_asistencia').classList.remove('hidden');
        }

        function desactivarUI() {
            asistenciaActiva = false;
            const b = document.getElementById('btnQR');
            b.disabled = true;
            b.className = 'w-full bg-gray-300 text-gray-500 px-4 py-2.5 rounded-xl font-bold cursor-not-allowed transition text-sm';
            b.title = 'Primero inicia la asistencia';
            document.getElementById('estado_asistencia').classList.add('hidden');
        }

        // ─── CONTADOR ───────────────────────────────────────────
        function iniciarContador(fin) {
            if (intervalo) clearInterval(intervalo);
            intervalo = setInterval(() => {
                const diff = Math.floor((fin - new Date()) / 1000);
                if (diff <= 0) {
                    clearInterval(intervalo);
                    document.getElementById('contador').innerHTML = ' Tiempo finalizado';
                    desactivarUI();
                    fetch('/asistencia/detener', { method: 'POST', headers, body: JSON.stringify({ materia_nrc }) });
                    return;
                }
                const min = Math.floor(diff / 60), seg = diff % 60;
                document.getElementById('contador').innerHTML = ` ${min}:${seg.toString().padStart(2,'0')} restantes`;
            }, 1000);
        }
    });
    </script>

</x-app-layout>