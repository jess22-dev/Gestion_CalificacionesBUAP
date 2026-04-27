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
                    ← Volver al Dashboard
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

                    {{-- ACTIVIDADES --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        {{-- Formulario --}}
                        <div class="bg-white p-6 rounded-2xl shadow border border-gray-100">
                            <h3 class="font-bold text-lg mb-4 text-[#002d62]">Definir Actividad</h3>
                            <form action="{{ route('profesor.actividades.store', $materia->nrc) }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="text" name="nombre" value="{{ old('nombre') }}"
                                    placeholder="Nombre actividad"
                                    class="w-full rounded-xl border-gray-300 focus:ring-[#1e4b8a]">
                                <select name="categoria" class="w-full rounded-xl border-gray-300">
                                    <option value="">-- Selecciona categoría --</option>
                                    <option value="Prácticas">Prácticas (20%)</option>
                                    <option value="Tareas">Tareas (20%)</option>
                                    <option value="Examen">Examen (20%)</option>
                                    <option value="Proyecto Final">Proyecto Final (40%)</option>
                                </select>
                                <input type="number" name="ponderacion" value="{{ old('ponderacion') }}"
                                    placeholder="Ponderación (%) Ej: 20" min="1" max="100"
                                    class="w-full rounded-xl border-gray-300">
                                <div class="bg-blue-50 rounded-xl p-3 text-sm">
                                    <span class="text-blue-600 font-bold">Usado: {{ $ponderacionTotal }}%</span>
                                    <span class="text-gray-500"> / Disponible: {{ 100 - $ponderacionTotal }}%</span>
                                </div>
                                <button type="submit"
                                    class="w-full bg-[#002d62] text-white py-2 rounded-xl font-bold hover:bg-[#1e4b8a] transition {{ $ponderacionTotal >= 100 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $ponderacionTotal >= 100 ? 'disabled' : '' }}>
                                    Crear Actividad
                                </button>
                            </form>
                        </div>

                        {{-- Lista actividades --}}
                        <div class="bg-white p-6 rounded-2xl shadow border border-gray-100">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-lg text-[#002d62]">Actividades</h3>
                                <span class="text-xs font-black bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                                    {{ $actividades->count() }} creada(s)
                                </span>
                            </div>
                            @if($actividades->isEmpty())
                                <p class="text-gray-400 text-sm italic text-center py-6">No hay actividades aún.</p>
                            @else
                                <ul class="space-y-3">
                                    @foreach($actividades as $actividad)
                                        @php
                                            $badgeColor = match($actividad->categoria) {
                                                'Prácticas'      => 'bg-blue-100 text-blue-600',
                                                'Tareas'         => 'bg-indigo-100 text-indigo-600',
                                                'Examen'         => 'bg-purple-100 text-purple-600',
                                                'Proyecto Final' => 'bg-green-200 text-green-700',
                                                default          => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <li class="bg-gray-50 p-3 rounded-xl flex justify-between items-center border border-gray-100">
                                            <div>
                                                <p class="font-bold text-gray-700 text-sm">{{ $actividad->nombre }}</p>
                                                <div class="flex gap-2 mt-1">
                                                    <span class="{{ $badgeColor }} text-[10px] font-black px-2 py-0.5 rounded uppercase">{{ $actividad->categoria }}</span>
                                                    <span class="text-xs text-gray-500 font-bold">{{ $actividad->ponderacion }}%</span>
                                                </div>
                                            </div>
                                            <form action="{{ route('profesor.actividades.destroy', [$materia->nrc, $actividad->id]) }}"
                                                  method="POST" onsubmit="return confirm('¿Eliminar esta actividad?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600 text-xs font-bold">Eliminar</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-4 flex justify-between items-center p-3 bg-gray-800 rounded-xl">
                                    <span class="text-sm font-black text-white">Total</span>
                                    <span class="font-black {{ $ponderacionTotal == 100 ? 'text-green-400' : 'text-yellow-400' }}">
                                        {{ $ponderacionTotal }}% {{ $ponderacionTotal == 100 ? '' : '' }}
                                    </span>
                                </div>
                            @endif
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
                        Iniciar →
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
                        Ver →
                    </a>
                </div>
            </div>

        </div>
    </div>


</x-app-layout>