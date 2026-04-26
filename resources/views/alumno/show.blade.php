<x-app-layout>
    {{-- Header Institucional --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-black text-white italic tracking-tight uppercase leading-none">
                     <span class="text-blue-200 font-light">Primavera 2026</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#f8fafc] min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- 1. BANNER PRINCIPAL --}}
            <div class="bg-[#002d62] rounded-[3.5rem] p-12 text-white shadow-2xl shadow-blue-900/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8">
                        <div class="flex-1">
                            <span class="bg-blue-500/20 text-blue-200 text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-[0.2em] mb-4 inline-block border border-blue-400/20">
                                Asignatura Vigente
                            </span>
                            <h3 class="text-4xl md:text-6xl font-black italic uppercase leading-none tracking-tighter mb-4">
                                {{ $materia->Materia }}
                            </h3>
                            <p class="text-blue-200/60 font-medium italic flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Impartido por: <span class="text-white font-bold not-italic">{{ $materia->Profesor }}</span>
                            </p>
                        </div>

                        {{-- SECCIÓN NRC --}}
                        <div class="flex items-center gap-4 bg-white/5 p-6 rounded-[2.5rem] border border-white/10 backdrop-blur-md min-w-[200px]">
                            <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg border border-white/10">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-blue-300 uppercase tracking-widest leading-none mb-1">Registro NRC</p>
                                <p class="text-2xl font-black tracking-tighter">{{ $materia->nrc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. MÉTRICAS INTERACTIVAS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Card Asistencia --}}
                <div id="openAttendanceModal" class="bg-white rounded-[3rem] p-10 shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-xl hover:shadow-blue-900/10 transition-all duration-500 cursor-pointer">
                    <div class="flex items-center gap-8">
                        <div class="relative flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <svg class="w-24 h-24 transform -rotate-90">
                                <circle cx="48" cy="48" r="42" stroke="currentColor" stroke-width="8" fill="transparent" class="text-gray-100" />
                                <circle cx="48" cy="48" r="42" stroke="currentColor" stroke-width="8" fill="transparent" stroke-dasharray="263.89" stroke-dashoffset="{{ 263.89 * (1 - 0.85) }}" class="text-blue-600 transition-all duration-1000" stroke-linecap="round" />
                            </svg>
                            <span class="absolute text-lg font-black text-[#002d62]">85%</span>
                        </div>
                        <div>
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Asistencias</p>
                            <h5 class="text-3xl font-black text-[#002d62] italic leading-none group-hover:text-blue-600 transition-colors">Ver Detalle</h5>
                        </div>
                    </div>
                    <svg class="w-6 h-6 text-blue-200 group-hover:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>

                {{-- Card Promedio --}}
                <div class="bg-white rounded-[3rem] p-10 shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-xl hover:shadow-emerald-900/5 transition-all duration-500">
                    <div class="flex items-center gap-8">
                        <div class="w-24 h-24 bg-emerald-50 rounded-[2rem] flex items-center justify-center border-4 border-emerald-500/20 group-hover:rotate-6 transition-transform duration-500">
                            <span class="text-4xl font-black text-emerald-600 italic">9.2</span>
                        </div>
                        <div>
                            <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Promedio</p>
                            <h5 class="text-3xl font-black text-[#002d62] italic leading-none uppercase">Académico</h5>
                        </div>
                    </div>
                    <svg class="w-8 h-8 text-emerald-100" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" /></svg>
                </div>
            </div>

            {{-- 3. TABLA DE ENTREGAS --}}
            <div class="bg-white rounded-[3.5rem] shadow-sm border border-white overflow-hidden">
                <div class="p-12 border-b border-gray-50 flex justify-between items-center bg-slate-50/30">
                    <div>
                        <h4 class="text-[#002d62] font-black uppercase text-base tracking-[0.2em] flex items-center">
                            <span class="w-3 h-3 bg-blue-600 rounded-full mr-4 animate-pulse"></span>
                            Concentrado de Entregas
                        </h4>
                        <p class="text-gray-400 text-xs font-medium italic mt-1 ml-7">Historial detallado de progreso</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto px-4 pb-4">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr>
                                <th class="px-10 py-6 text-left text-[11px] font-black text-gray-300 uppercase tracking-widest">Actividad</th>
                                <th class="px-10 py-6 text-center text-[11px] font-black text-gray-300 uppercase tracking-widest">Estatus</th>
                                <th class="px-10 py-6 text-center text-[11px] font-black text-gray-300 uppercase tracking-widest">Nota</th>
                                <th class="px-10 py-6 text-right text-[11px] font-black text-gray-300 uppercase tracking-widest">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 bg-white">
                            <tr class="hover:bg-blue-50/30 transition-all group">
                                <td class="px-10 py-7">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 bg-[#002d62]/5 text-[#002d62] rounded-2xl flex items-center justify-center font-black text-sm group-hover:bg-[#002d62] group-hover:text-white transition-all border border-[#002d62]/10">01</div>
                                        <p class="text-sm font-bold text-[#002d62] uppercase tracking-tight">Práctica 01: Entorno de Desarrollo</p>
                                    </div>
                                </td>
                                <td class="px-10 py-7 text-center">
                                    <span class="px-5 py-2 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-full uppercase italic border border-emerald-100">Revisado</span>
                                </td>
                                <td class="px-10 py-7 text-center">
                                    <span class="text-xl font-black text-[#002d62]">10.0</span>
                                </td>
                                <td class="px-10 py-7 text-right">
                                    <p class="text-xs font-bold text-gray-400 italic">25/Abr/2026</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- BOTÓN BAJA --}}
            <div class="flex justify-center pb-12">
                <form action="{{ route('alumno.baja', $materia->nrc) }}" method="POST">
                    @csrf
                    <button class="flex items-center gap-3 text-red-200 hover:text-red-500 transition-all duration-300 group">
                        <div class="w-10 h-10 rounded-2xl border border-red-50 flex items-center justify-center group-hover:bg-red-50 group-hover:border-red-200 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </div>
                        <span class="text-[11px] font-black uppercase tracking-[0.3em] italic">Baja definitiva de la asignatura</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DE ASISTENCIAS (VISTA DE TABLA PERSONAL) --}}
    <div id="attendanceModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div id="modalOverlay" class="fixed inset-0 bg-[#001529]/90 backdrop-blur-md transition-opacity"></div>
        <div class="flex min-h-full items-center justify-center p-4 md:p-10">
            <div class="relative w-full max-w-5xl transform overflow-hidden rounded-[3.5rem] bg-white p-8 md:p-12 shadow-2xl transition-all">
                
                {{-- Encabezado Modal --}}
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h3 class="text-3xl font-black text-[#002d62] uppercase italic">Bitácora de Asistencias</h3>
                        <p class="text-gray-400 text-xs font-bold italic mt-1 uppercase tracking-widest">Kardex de Sesiones - {{ $materia->Materia }}</p>
                    </div>
                    <button id="closeAttendanceModal" class="p-4 bg-slate-100 rounded-3xl hover:bg-red-50 hover:text-red-600 transition-all group">
                        <svg class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- Tabla de Asistencias --}}
                <div class="overflow-hidden rounded-[2.5rem] border border-gray-100 shadow-sm mb-6">
                    <div class="max-h-[50vh] overflow-y-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-slate-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Sesión</th>
                                    <th class="px-8 py-5 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Fecha</th>
                                    <th class="px-8 py-5 text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Estatus</th>
                                    <th class="px-8 py-5 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Hora de Entrada</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                {{-- Fila Ejemplo 1 --}}
                                <tr class="hover:bg-blue-50/50 transition-colors group">
                                    <td class="px-8 py-6 text-sm font-black text-blue-600">#12</td>
                                    <td class="px-8 py-6 text-sm font-bold text-[#002d62]">Sábado, 25 de Abril 2026</td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="inline-flex items-center px-4 py-1.5 bg-emerald-100 text-emerald-600 text-[9px] font-black rounded-full uppercase italic border border-emerald-200">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                                            Presente
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right font-mono text-xs font-bold text-gray-400">09:05:42 AM</td>
                                </tr>
                                {{-- Fila Ejemplo 2 (Falta) --}}
                                <tr class="hover:bg-red-50/30 transition-colors group">
                                    <td class="px-8 py-6 text-sm font-black text-red-400">#11</td>
                                    <td class="px-8 py-6 text-sm font-bold text-[#002d62]">Jueves, 23 de Abril 2026</td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="inline-flex items-center px-4 py-1.5 bg-red-50 text-red-500 text-[9px] font-black rounded-full uppercase italic border border-red-100">
                                            Falta
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right font-mono text-xs font-bold text-red-200 uppercase italic">Sin registro</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pie de Modal --}}
                <div class="pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 text-gray-400">
                    <p class="text-[10px] font-bold italic flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Los registros son generados automáticamente al escanear el código QR en el aula.
                    </p>
                    <div class="flex gap-4">
                        <span class="text-[10px] font-black uppercase tracking-widest"><span class="text-emerald-500 italic">●</span> 10 Asistencias</span>
                        <span class="text-[10px] font-black uppercase tracking-widest"><span class="text-red-400 italic">●</span> 2 Faltas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById('attendanceModal');
            const btnOpen = document.getElementById('openAttendanceModal');
            const btnClose = document.getElementById('closeAttendanceModal');
            const overlay = document.getElementById('modalOverlay');

            function toggleModal(show) {
                if (show) {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            btnOpen.onclick = () => toggleModal(true);
            btnClose.onclick = () => toggleModal(false);
            overlay.onclick = () => toggleModal(false);

            // Cerrar con tecla ESC
            document.onkeydown = (evt) => {
                if (evt.key === "Escape") toggleModal(false);
            };
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; transition: all 0.3s; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #002d62; }
    </style>
</x-app-layout>