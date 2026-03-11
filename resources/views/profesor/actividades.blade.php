<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Gestión de Evaluaciones') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                
                {{-- COLUMNA IZQUIERDA: Formulario y Metas (Más angosta) --}}
                <div class="lg:w-1/3 space-y-6">
                    
                    {{-- Formulario de Captura --}}
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                        <h3 class="text-lg font-bold text-[#002d62] mb-6 flex items-center">
                            <span class="bg-[#cfe2f3] p-2 rounded-lg mr-2">🎯</span> Definir Actividad
                        </h3>
                        <form class="space-y-4">
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase">Nombre</label>
                                <input type="text" class="w-full mt-1 rounded-xl border-gray-200 focus:ring-[#1e4b8a]" placeholder="Ej: Reporte 1">
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase">Categoría</label>
                                <select class="w-full mt-1 rounded-xl border-gray-200">
                                    <option>Prácticas (20%)</option>
                                    <option>Tareas (20%)</option>
                                    <option>Examen (20%)</option>
                                    <option>Proyecto Final (40%)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase italic">Puntos Base</label>
                                <input type="text" value="100 Puntos" disabled class="w-full mt-1 rounded-xl border-gray-200 bg-gray-50 font-bold text-gray-500">
                            </div>
                            <button type="button" class="w-full bg-[#1e4b8a] text-white py-3 rounded-xl font-bold hover:bg-[#002d62] transition-all shadow-md">
                                Crear Actividad
                            </button>
                        </form>
                    </div>

                    {{-- Metas de Ponderación (Ahora en tarjetas verticales pequeñas) --}}
                    <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Resumen de Ponderación</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-xl border border-blue-100">
                                <span class="text-sm font-bold text-blue-700">Prácticas</span>
                                <span class="text-lg font-black text-blue-800">20%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                                <span class="text-sm font-bold text-indigo-700">Tareas</span>
                                <span class="text-lg font-black text-indigo-800">20%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-purple-50 rounded-xl border border-purple-100">
                                <span class="text-sm font-bold text-purple-700">Examen</span>
                                <span class="text-lg font-black text-purple-800">20%</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-xl border border-green-100">
                                <span class="text-sm font-bold text-green-700">Proyecto Final</span>
                                <span class="text-lg font-black text-green-800">40%</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: Tabla de Actividades (Más ancha) --}}
                <div class="lg:w-1/3">
                    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                        <div class="p-6 bg-white border-b border-gray-50 flex justify-between items-center">
                            <h3 class="font-black text-xl text-[#002d62]">Actividades para Calificar</h3>
                            <span class="text-[10px] font-black bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full uppercase">Puntos base: 100</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-6 py-4 text-left">Actividad</th>
                                        <th class="px-6 py-4 text-left">Categoría</th>
                                        <th class="px-6 py-4 text-right">Acciones de Calificación</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-sm">
                                    <tr class="hover:bg-blue-50/40 transition">
                                        <td class="px-6 py-5 font-bold text-gray-700">Configuración de Laragon</td>
                                        <td class="px-6 py-5">
                                            <span class="bg-blue-100 text-blue-600 text-[10px] font-black px-2 py-1 rounded uppercase">Prácticas</span>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button class="px-3 py-1.5 border-2 border-[#1e4b8a] text-[#1e4b8a] font-bold rounded-lg text-xs hover:bg-[#1e4b8a] hover:text-white transition">Directa</button>
                                                <button class="px-3 py-1.5 bg-green-600 text-white font-bold rounded-lg text-xs hover:bg-green-700 flex items-center shadow-sm">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    Excel
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-blue-50/40 transition">
                                        <td class="px-6 py-5 font-bold text-gray-700">Examen Primer Parcial</td>
                                        <td class="px-6 py-5">
                                            <span class="bg-purple-100 text-purple-600 text-[10px] font-black px-2 py-1 rounded uppercase">Examen</span>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button class="px-3 py-1.5 border-2 border-[#1e4b8a] text-[#1e4b8a] font-bold rounded-lg text-xs">Directa</button>
                                                <button class="px-3 py-1.5 bg-green-600 text-white font-bold rounded-lg text-xs">Excel</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="bg-green-50/40">
                                        <td class="px-6 py-5 font-bold text-green-800 italic">Proyecto Final: Dashboard</td>
                                        <td class="px-6 py-5">
                                            <span class="bg-green-200 text-green-700 text-[10px] font-black px-2 py-1 rounded uppercase">Proyecto Final</span>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <span class="text-green-600 font-black text-[10px] border border-green-200 bg-white px-3 py-1 rounded-full uppercase">Completado ✅</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>