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

                    {{-- ========================= --}}
                    {{-- CALIFICACIONES --}}
                    {{-- ========================= --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        {{-- Formulario Crear Actividad --}}
                        <div class="bg-white p-6 rounded-2xl shadow border border-gray-100">
                            <h3 class="font-bold text-lg mb-4 text-[#002d62]">Definir Actividad</h3>

                            <form action="{{ route('profesor.actividades.store', $materia->nrc) }}" method="POST" class="space-y-3">
                                @csrf

                                <div>
                                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                                        placeholder="Nombre actividad"
                                        class="w-full rounded-xl border-gray-300 focus:ring-[#1e4b8a] @error('nombre') border-red-400 @enderror">
                                    @error('nombre')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <select name="categoria" class="w-full rounded-xl border-gray-300 @error('categoria') border-red-400 @enderror">
                                        <option value="">-- Selecciona categoría --</option>
                                        <option value="Prácticas" {{ old('categoria') == 'Prácticas' ? 'selected' : '' }}>Prácticas (20%)</option>
                                        <option value="Tareas" {{ old('categoria') == 'Tareas' ? 'selected' : '' }}>Tareas (20%)</option>
                                        <option value="Examen" {{ old('categoria') == 'Examen' ? 'selected' : '' }}>Examen (20%)</option>
                                        <option value="Proyecto Final" {{ old('categoria') == 'Proyecto Final' ? 'selected' : '' }}>Proyecto Final (40%)</option>
                                    </select>
                                    @error('categoria')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <input type="number" name="ponderacion" value="{{ old('ponderacion') }}"
                                        placeholder="Ponderación (%) Ej: 20"
                                        min="1" max="100"
                                        class="w-full rounded-xl border-gray-300 @error('ponderacion') border-red-400 @enderror">
                                    @error('ponderacion')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Indicador ponderación --}}
                                <div class="bg-blue-50 rounded-xl p-3 text-sm">
                                    <span class="text-blue-600 font-bold">Usado: {{ $ponderacionTotal }}%</span>
                                    <span class="text-gray-500"> / Disponible: {{ 100 - $ponderacionTotal }}%</span>
                                </div>

                                <button type="submit"
                                    class="w-full bg-[#002d62] text-white py-2 rounded-xl font-bold hover:bg-[#1e4b8a] transition
                                    {{ $ponderacionTotal >= 100 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $ponderacionTotal >= 100 ? 'disabled' : '' }}>
                                    Crear Actividad
                                </button>
                            </form>
                        </div>

                        {{-- Lista de Actividades --}}
                        <div class="bg-white p-6 rounded-2xl shadow border border-gray-100">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-lg text-[#002d62]">Actividades</h3>
                                <span class="text-xs font-black bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                                    {{ $actividades->count() }} creada(s)
                                </span>
                            </div>

                            @if($actividades->isEmpty())
                                <p class="text-gray-400 text-sm italic text-center py-6">
                                    No hay actividades aún. Crea la primera usando el formulario.
                                </p>
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
                                                    <span class="{{ $badgeColor }} text-[10px] font-black px-2 py-0.5 rounded uppercase">
                                                        {{ $actividad->categoria }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 font-bold">{{ $actividad->ponderacion }}%</span>
                                                </div>
                                            </div>
                                            <form action="{{ route('profesor.actividades.destroy', [$materia->nrc, $actividad->id]) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('¿Eliminar esta actividad?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-400 hover:text-red-600 transition text-xs font-bold">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>

                                {{-- Total ponderación --}}
                                <div class="mt-4 flex justify-between items-center p-3 bg-gray-800 rounded-xl">
                                    <span class="text-sm font-black text-white">Total</span>
                                    <span class="font-black {{ $ponderacionTotal == 100 ? 'text-green-400' : 'text-yellow-400' }}">
                                        {{ $ponderacionTotal }}% {{ $ponderacionTotal == 100 ? '' : '' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- ========================= --}}
                    {{-- CONTROL DE ASISTENCIA --}}
                    {{-- ========================= --}}
                    <div class="mt-10 bg-white p-6 rounded-2xl shadow-xl border">
                        <h3 class="text-xl font-bold text-[#002d62] mb-4">
                            📋 Control de Asistencia
                        </h3>
                        <div class="grid md:grid-cols-3 gap-6">
                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase">Duración</label>
                                <select class="w-full mt-2 rounded-xl border-gray-200">
                                    <option>5 minutos</option>
                                    <option>10 minutos</option>
                                    <option>15 minutos</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <button class="bg-green-600 text-white px-4 py-2 rounded-xl font-bold">
                                    Iniciar 
                                </button>
                                <button class="bg-red-500 text-white px-4 py-2 rounded-xl font-bold">
                                    Detener 
                                </button>
                            </div>
                            <div class="flex items-end">
                                <button class="bg-[#002d62] text-white px-4 py-2 rounded-xl font-bold">
                                    Escanear QR 
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= --}}
                    {{-- LISTA DE ALUMNOS --}}
                    {{-- ========================= --}}
                    <div class="mt-8 bg-white p-6 rounded-2xl shadow-xl border border-gray-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-[#002d62]">
                                Lista de Alumnos
                            </h3>
                            <span class="text-xs font-black bg-blue-100 text-blue-700 px-3 py-1 rounded-full">
                                {{ $alumnos->count() }} alumno(s)
                            </span>
                        </div>

                        @if($alumnos->isEmpty())
                            <div class="text-center py-10 text-gray-400">
                         
                                <p class="italic text-sm">No hay alumnos inscritos en este grupo aún.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-[#002d62] text-white">
                                        <tr>
                                            <th class="p-3 text-left text-xs uppercase">Matrícula</th>
                                            <th class="p-3 text-left text-xs uppercase">Nombre</th>
                                            <th class="p-3 text-left text-xs uppercase">Email</th>
                                            <th class="p-3 text-center text-xs uppercase">Estado</th>
                                            <th class="p-3 text-center text-xs uppercase">Asistencia</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($alumnos as $alumno)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="p-3 font-mono text-blue-700 font-bold">
                                                {{ $alumno->pivot->clave_unica ?? '—' }}
                                            </td>
                                            <td class="p-3 font-semibold text-gray-800">{{ $alumno->name }}</td>
                                            <td class="p-3 text-gray-500">{{ $alumno->email }}</td>
                                            <td class="p-3 text-center">
                                                @if(($alumno->pivot->status ?? 'activo') === 'activo')
                                                    <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">Activo</span>
                                                @else
                                                    <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-full">Baja</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-center">
                                                <input type="checkbox" class="w-5 h-5 accent-[#002d62]">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Módulo de Asistencia --}}
            <div class="mt-8 mb-4">
                <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-[#002d62]"> Módulo de Asistencia</h3>
                        <p class="text-gray-500 text-sm">Gestiona la asistencia del grupo mediante código QR</p>
                    </div>
                    <a href="#"
                       class="bg-[#002d62] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#1e4b8a] transition shadow-lg">
                        Tomar Asistencia →
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>