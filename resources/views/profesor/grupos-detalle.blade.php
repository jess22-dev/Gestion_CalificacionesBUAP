<x-app-layout>
    <x-slot name="header">
        {{ __('Gestión de Grupo') }}
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alertas --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-800 rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl">
                     {{ session('error') }}
                </div>
            @endif

            {{-- Barra superior --}}
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('dashboard') }}" class="text-[#1e4b8a] font-bold hover:underline flex items-center">
                     Volver
                </a>
                <a href="{{ route('profesor.estudiantes.index', ['nrc' => $materia->nrc]) }}"
                   class="inline-flex items-center gap-2 bg-[#002d62] text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-[#1e4b8a] transition shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Alta de Estudiantes
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100">

                {{-- HEADER --}}
                <div class="bg-[#1e4b8a] p-6 text-white">
                    <h3 class="text-2xl font-bold">
                        {{ $materia->Materia }} ({{ $materia->nrc }})
                    </h3>
                    <p class="opacity-80">Gestión de calificaciones y asistencia</p>
                </div>

                <div class="p-8">

                    {{-- RESUMEN DEL GRUPO --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                        {{-- Total alumnos --}}
                        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 flex flex-col items-center text-center">
                            <div class="bg-[#002d62] p-2 rounded-xl mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <p class="text-3xl font-black text-[#002d62]">{{ $estudiantes->count() }}</p>
                            <p class="text-xs text-gray-500 font-semibold mt-1 uppercase tracking-wide">Alumnos activos</p>
                        </div>

                        {{-- Actividades creadas --}}
                        <div class="bg-yellow-50 border border-yellow-100 rounded-2xl p-5 flex flex-col items-center text-center">
                            <div class="bg-yellow-500 p-2 rounded-xl mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-3xl font-black text-yellow-600">{{ $actividades->count() }}</p>
                            <p class="text-xs text-gray-500 font-semibold mt-1 uppercase tracking-wide">Actividades</p>
                        </div>

                        {{-- Ponderación usada --}}
                        <div class="bg-green-50 border border-green-100 rounded-2xl p-5 flex flex-col items-center text-center">
                            <div class="bg-green-600 p-2 rounded-xl mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <p class="text-3xl font-black {{ $ponderacionTotal == 100 ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ $ponderacionTotal }}%
                            </p>
                            <p class="text-xs text-gray-500 font-semibold mt-1 uppercase tracking-wide">Ponderación usada</p>
                        </div>

                        {{-- Sesiones de asistencia --}}
                        <div class="bg-purple-50 border border-purple-100 rounded-2xl p-5 flex flex-col items-center text-center">
                            <div class="bg-purple-600 p-2 rounded-xl mb-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-3xl font-black text-purple-600">{{ $totalSesiones }}</p>
                            <p class="text-xs text-gray-500 font-semibold mt-1 uppercase tracking-wide">Sesiones registradas</p>
                        </div>

                    </div>

                </div>
            </div>

            {{-- Módulo de Asistencia extendido --}}
            <div class="mt-8 mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Tomar Asistencia --}}
                <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-[#002d62]"> Tomar Asistencia</h3>
                        <p class="text-gray-500 text-sm">Inicia una sesión y escanea QR</p>
                    </div>
                    <a href="{{ route('profesor.asistencia', $materia->nrc) }}"
                       class="bg-[#002d62] text-white px-5 py-2.5 rounded-xl font-bold hover:bg-[#1e4b8a] transition shadow-lg text-sm">
                        Iniciar 
                    </a>
                </div>
                {{-- Ver Historial --}}
                <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-[#002d62]"> Historial de Asistencia</h3>
                        <p class="text-gray-500 text-sm">Registro día por día de todos los alumnos</p>
                    </div>
                    <a href="{{ route('profesor.historial', $materia->nrc) }}"
                       class="bg-[#1e4b8a] text-white px-5 py-2.5 rounded-xl font-bold hover:bg-[#002d62] transition shadow-lg text-sm">
                        Ver 
                    </a>
                </div>
            </div>

            {{-- ACCESO AL GENERADOR DE ACTAS Y ESTADÍSTICAS --}}
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Generador de Actas --}}
                <div class="bg-gradient-to-r from-[#1e4b8a] to-[#002d62] p-6 rounded-3xl shadow-2xl text-white flex flex-col justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="bg-white/10 p-3 rounded-2xl">
                            <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-black">Generador de Actas</h2>
                            <p class="text-blue-100 text-sm">Valida datos y genera el acta final.</p>
                        </div>
                    </div>
                    <a href="{{ route('profesor.actas.index', $materia->nrc) }}"
                       class="w-full bg-white text-[#002d62] px-6 py-3 rounded-2xl font-black text-center hover:bg-blue-50 transition-all transform hover:scale-105 shadow-xl text-sm">
                        INGRESAR
                    </a>
                </div>

                {{-- Estadísticas --}}
                <div class="bg-gradient-to-r from-[#0f766e] to-[#134e4a] p-6 rounded-3xl shadow-2xl text-white flex flex-col justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="bg-white/10 p-3 rounded-2xl">
                            <svg class="w-8 h-8 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-black">Estadísticas</h2>
                            <p class="text-teal-100 text-sm">Rendimiento grupal e individual.</p>
                        </div>
                    </div>
                    <a href="{{ route('profesor.estadisticas', $materia->nrc) }}"
                       class="w-full bg-white text-[#0f766e] px-6 py-3 rounded-2xl font-black text-center hover:bg-teal-50 transition-all transform hover:scale-105 shadow-xl text-sm">
                        VER ESTADÍSTICAS
                    </a>
                </div>

            </div>

        </div>
    </div>

</x-app-layout>