<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-white tracking-tight italic ml-3">
                <span class="text-blue-200 font-light ml-2">Primavera 2026</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between px-4 lg:px-0 gap-4">
                <div>
                    <h3 class="text-4xl font-black text-[#002d62] tracking-tighter uppercase italic leading-none">Mi Carga Académica</h3>
                    <p class="text-gray-500 font-medium italic mt-2">Consulta tus materias y presenta tu QR de asistencia.</p>
                </div>
            </div>

            {{-- Grid de Materias --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($materias as $materia)
                <div class="bg-white rounded-[2.8rem] shadow-xl shadow-blue-900/5 border border-white overflow-hidden transform hover:scale-[1.03] transition-all duration-500 group relative">

                    <div class="absolute top-6 right-6 z-20 pointer-events-none">
                        <span class="bg-emerald-100 text-emerald-600 text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-tighter flex items-center shadow-sm border border-emerald-200">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                            Activa
                        </span>
                    </div>

                    <div>
                        <div class="bg-gradient-to-br from-[#1e4b8a] to-[#002d62] p-8 h-44 flex items-start justify-between relative overflow-hidden group-hover:from-[#1a4178] group-hover:to-[#001d3d] transition-all duration-500">
                            <div class="relative z-10">
                                <p class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-1 opacity-80">Asignatura</p>
                                <h4 class="text-2xl font-black text-white leading-tight italic uppercase drop-shadow-md">
                                    {{ $materia->Materia }}
                                </h4>
                                
                            </div>
                            <svg class="absolute -right-6 -bottom-6 w-32 h-32 text-white/10 transform rotate-12 group-hover:rotate-45 transition-transform duration-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.827a1 1 0 00-.788 0l-7 3a1 1 0 000 1.848l7 3a1 1 0 00.788 0l7-3a1 1 0 000-1.848l-7-3zM14 9.528c.538.303.812.646.812.972 0 .326-.274.669-.812.972l-3.394 1.912a1 1 0 01-.788 0L6.414 11.472c-.538-.303-.812-.646-.812-.972 0-.326.274-.669.812-.972L10 11.528l4-2z"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="p-8 -mt-10 relative z-10 bg-white rounded-t-[2.8rem] space-y-6">
                        <div class="bg-slate-50 rounded-3xl p-5 flex justify-between items-center border border-gray-100 shadow-inner">
                            <div class="text-center flex-1">
                                <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">NRC</span>
                                <p class="text-xl font-black text-[#002d62] italic">{{ $materia->nrc }}</p>
                            </div>
                            <div class="h-8 w-px bg-gray-200"></div>
                            <div class="text-center flex-1">
                                <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">Sección</span>
                                <p class="text-xl font-black text-[#002d62] italic">{{ $materia->seccion ?? '001' }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">


                            <div class="flex items-center px-2">
                                <div class="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                                <p class="text-gray-500 text-xs font-bold uppercase italic tracking-tight">{{ $materia->Profesor }}</p>
                            </div>
                        </div>

                        {{-- Ver calificaciones --}}
                        <a href="{{ route('alumno.calificaciones', $materia->nrc) }}"
                            class="w-full flex items-center justify-center py-3 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-lg active:scale-95 mb-3">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Ver Calificaciones
                        </a>

                        {{-- Botón QR --}}
                        <button
                            onclick="abrirModalQR('{{ $materia->nrc }}', '{{ addslashes($materia->Materia) }}')"
                            class="w-full flex items-center justify-center py-4 bg-[#002d62] text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-blue-800 transition-all shadow-lg active:scale-95 group/btn">
                            <svg class="w-4 h-4 mr-2 group-hover/btn:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            Pase de Lista QR
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Modal QR --}}
    <div id="modalQR" class="fixed inset-0 bg-[#001a35]/90 hidden backdrop-blur-xl z-50 flex items-center justify-center p-6">
        <div class="bg-white rounded-[3.5rem] max-w-sm w-full overflow-hidden shadow-2xl">
            <div class="bg-gradient-to-b from-blue-50 to-white p-10 text-center">

                <div class="mb-4 flex justify-center">
                    <div class="w-16 h-1 bg-[#002d62] rounded-full opacity-20"></div>
                </div>

                <h3 id="modalMateria" class="text-xl font-black text-[#002d62] uppercase italic leading-tight mb-1">MATERIA</h3>
                <p id="modalNRC" class="text-blue-400 text-xs font-black tracking-[0.3em] uppercase mb-2">NRC: 00000</p>
                <p id="modalFecha" class="text-gray-400 text-[10px] mb-6"></p>

                {{-- QR generado dinámicamente --}}
                <div class="flex justify-center mb-6">
                    <div id="modalQRCode" class="p-4 border-4 border-[#002d62] rounded-2xl inline-block"></div>
                </div>

                <div class="bg-blue-50 rounded-xl p-3 text-left text-xs space-y-1 mb-6 border border-blue-100">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-semibold">Nombre:</span>
                        <span class="font-bold text-gray-800" id="modalNombre">—</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-semibold">Código:</span>
                        <span class="font-bold text-gray-800" id="modalCodigo">—</span>
                    </div>
                </div>

                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-8 px-4 leading-relaxed">
                    Presenta este código al docente para registrar tu asistencia
                </p>

                <button onclick="cerrarModal()"
                    class="w-full py-5 bg-[#002d62] text-white rounded-3xl font-black text-xs uppercase tracking-widest hover:bg-blue-800 shadow-xl transition-all active:scale-95">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Datos del estudiante desde Blade
        const estudianteNombre = "{{ addslashes($estudiante->nombre ?? Auth::user()->name) }}";
        const estudianteCodigo = "{{ $estudiante->codigo_estudiante ?? '' }}";

        let qrInstance = null;

        function abrirModalQR(nrc, materiaNombre) {
            const hoy = new Date().toISOString().split('T')[0];
            const fechaLabel = new Date().toLocaleDateString('es-MX', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });

            // Contenido del QR — formato: codigo|nrc|fecha
            const contenido = estudianteCodigo + '|' + nrc + '|' + hoy;

            // Actualizar modal  
            document.getElementById('modalMateria').textContent = materiaNombre;
            document.getElementById('modalNRC').textContent     = 'NRC: ' + nrc;
            document.getElementById('modalFecha').textContent   = 'Válido para: ' + fechaLabel;
            document.getElementById('modalNombre').textContent  = estudianteNombre;
            document.getElementById('modalCodigo').textContent  = estudianteCodigo;

            // Limpiar QR anterior
            const qrDiv = document.getElementById('modalQRCode');
            qrDiv.innerHTML = '';

            // Generar QR
            new QRCode(qrDiv, {
                text:         contenido,
                width:        256,
                height:       256,
                colorDark:    '#002d62',
                colorLight:   '#ffffff',
                correctLevel: QRCode.CorrectLevel.L,
            });

            // Mostrar modal
            const modal = document.getElementById('modalQR');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModal() {
            const modal = document.getElementById('modalQR');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('modalQRCode').innerHTML = '';
        }
    </script>

    {{-- QRCode.js al final para asegurar que el DOM esté cargado --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</x-app-layout>