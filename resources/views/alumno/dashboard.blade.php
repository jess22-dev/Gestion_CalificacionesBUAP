<x-app-layout>
    {{-- Header Institucional --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <h2 class="font-bold text-2xl text-white tracking-tight italic ml-3">
                    <span class="text-blue-200 font-light ml-2">Primavera 2026</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Encabezado de bienvenida --}}
            <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between px-4 lg:px-0 gap-4">
                <div>
                    <h3 class="text-4xl font-black text-[#002d62] tracking-tighter uppercase italic leading-none">Mi Carga Académica</h3>
                    <p class="text-gray-500 font-medium italic mt-2">Gestiona tus clases y registra tu asistencia.</p>
                </div>

            </div>

            {{-- Grid de Materias --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($materias as $materia)
                <div class="bg-white rounded-[2.8rem] shadow-xl shadow-blue-900/5 border border-white overflow-hidden transform hover:scale-[1.03] transition-all duration-500 group relative">
                    
                    {{-- Indicador de estatus (No clicable) --}}
                    <div class="absolute top-6 right-6 z-20 pointer-events-none">
                        <span class="bg-emerald-100 text-emerald-600 text-[9px] font-black px-3 py-1.5 rounded-full uppercase tracking-tighter flex items-center shadow-sm border border-emerald-200">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                            Activa
                        </span>
                    </div>

                    {{-- CABECERA CLICABLE: Al dar click en el nombre entra a detalles --}}
                    <a href="{{ route('alumno.materia.detalle', $materia->nrc) }}" class="block">
                        <div class="bg-gradient-to-br from-[#1e4b8a] to-[#002d62] p-8 h-44 flex items-start justify-between relative overflow-hidden group-hover:from-[#1a4178] group-hover:to-[#001d3d] transition-all duration-500">
                            <div class="relative z-10">
                                <p class="text-blue-200 text-[10px] font-black uppercase tracking-widest mb-1 opacity-80">Asignatura</p>
                                <h4 class="text-2xl font-black text-white leading-tight italic uppercase drop-shadow-md group-hover:text-blue-100 transition-colors">
                                    {{ $materia->Materia }}
                                </h4>
                                <p class="text-blue-300/60 text-[9px] font-bold mt-2 uppercase tracking-widest">Click para ver más detalles →</p>
                            </div>
                            <svg class="absolute -right-6 -bottom-6 w-32 h-32 text-white/10 transform rotate-12 group-hover:rotate-45 group-hover:scale-110 transition-transform duration-700" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.827a1 1 0 00-.788 0l-7 3a1 1 0 000 1.848l7 3a1 1 0 00.788 0l7-3a1 1 0 000-1.848l-7-3zM14 9.528c.538.303.812.646.812.972 0 .326-.274.669-.812.972l-3.394 1.912a1 1 0 01-.788 0L6.414 11.472c-.538-.303-.812-.646-.812-.972 0-.326.274-.669.812-.972L10 11.528l4-2z"></path>
                            </svg>
                        </div>
                    </a>
                    
                    <div class="p-8 -mt-10 relative z-10 bg-white rounded-t-[2.8rem] space-y-6">
                        {{-- Stats Dinámicos: NRC y Sección --}}
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
                        
                        {{-- Información de la Materia --}}
                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-blue-50/50 rounded-2xl border border-blue-100/50 group-hover:bg-blue-50 transition-colors">
                                <div class="w-10 h-10 bg-[#002d62] rounded-xl flex items-center justify-center text-white mr-4 shadow-lg shadow-blue-900/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-blue-400 uppercase leading-none mb-1">Clave de Asistencia</p>
                                    <p class="text-base font-black text-[#002d62] tracking-wider uppercase">
                                        {{ $materia->pivot->clave_asistencia }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center px-2">
                                <div class="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                                <p class="text-gray-500 text-xs font-bold uppercase italic tracking-tight">{{ $materia->Profesor }}</p>
                            </div>
                        </div>
                        
                        {{-- Botón Único de Acción Rápida (Pase QR) --}}
                        <button onclick="abrirModalQR('{{ $materia->nrc }}', '{{ $materia->pivot->qr_path }}', '{{ $materia->Materia }}')" 
                            class="w-full flex items-center justify-center py-4 bg-[#002d62] text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-blue-800 transition-all shadow-lg shadow-blue-900/20 active:scale-95 group/btn">
                            <svg class="w-4 h-4 mr-2 group-hover/btn:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            Pase de Lista QR
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Modal QR Premium --}}
    <div id="modalQR" class="fixed inset-0 bg-[#001a35]/90 hidden backdrop-blur-xl z-50 flex items-center justify-center p-6 transition-all duration-500">
        <div class="bg-white rounded-[3.5rem] max-w-sm w-full overflow-hidden shadow-2xl transform transition-all">
            <div class="bg-gradient-to-b from-blue-50 to-white p-10 text-center">
                <div class="mb-6 flex justify-center">
                    <div class="w-16 h-1 bg-[#002d62] rounded-full opacity-20"></div>
                </div>
                
                <h3 id="modalMateria" class="text-xl font-black text-[#002d62] uppercase italic leading-tight mb-1">MATERIA</h3>
                <p id="modalNRC" class="text-blue-400 text-xs font-black tracking-[0.3em] uppercase mb-8">NRC: 00000</p>
                
                <div class="bg-white p-6 rounded-[2.5rem] shadow-inner border border-gray-100 inline-block mb-8 group">
                    <img id="imgQR" src="" alt="QR Asistencia" class="w-48 h-48 mx-auto group-hover:scale-105 transition-transform duration-500">
                </div>

                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-10 px-4 leading-relaxed">
                    Presenta este código al docente para registrar tu asistencia en aula
                </p>

                <button onclick="cerrarModal()" class="w-full py-5 bg-[#002d62] text-white rounded-3xl font-black text-xs uppercase tracking-widest hover:bg-blue-800 shadow-xl shadow-blue-900/20 transition-all active:scale-95">
                    Terminar
                </button>
            </div>
        </div>
    </div>

    <script>
        function abrirModalQR(nrc, path, materia) {
            document.getElementById('modalMateria').innerText = materia;
            document.getElementById('modalNRC').innerText = "NRC: " + nrc;
            document.getElementById('imgQR').src = "{{ asset('storage') }}/" + path;
            
            const modal = document.getElementById('modalQR');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModal() {
            const modal = document.getElementById('modalQR');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-app-layout>