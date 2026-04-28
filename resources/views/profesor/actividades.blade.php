<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Gestión de Evaluaciones') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
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
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-8">

                {{-- COLUMNA IZQUIERDA --}}
                <div class="lg:w-1/3 space-y-6">

                    {{-- Formulario Crear Actividad --}}
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100">
                        <h3 class="text-lg font-bold text-[#002d62] mb-6 flex items-center">
                            <span class="bg-[#cfe2f3] p-2 rounded-lg mr-2">🎯</span> Definir Actividad
                        </h3>

                        <form action="{{ route('profesor.actividades.store', $materia->nrc) }}" method="POST" class="space-y-4">
                            @csrf

                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase">Nombre</label>
                                <input type="text" name="nombre" value="{{ old('nombre') }}"
                                    class="w-full mt-1 rounded-xl border-gray-200 focus:ring-[#1e4b8a] @error('nombre') border-red-400 @enderror"
                                    placeholder="Ej: Reporte 1">
                                @error('nombre')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-[10px] font-black text-gray-400 uppercase">Categoría</label>
                                <select name="categoria" class="w-full mt-1 rounded-xl border-gray-200 @error('categoria') border-red-400 @enderror">
                                    <option value="">-- Selecciona --</option>
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
                                <label class="text-[10px] font-black text-gray-400 uppercase italic">Ponderación (%)</label>
                                <input type="number" name="ponderacion" value="{{ old('ponderacion') }}"
                                    min="1" max="100"
                                    class="w-full mt-1 rounded-xl border-gray-200 focus:ring-[#1e4b8a] @error('ponderacion') border-red-400 @enderror"
                                    placeholder="Ej: 20">
                                @error('ponderacion')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Indicador de ponderación disponible --}}
                            <div class="bg-blue-50 rounded-xl p-3 text-sm">
                                <span class="text-blue-600 font-bold">Ponderación usada: {{ $ponderacionTotal }}%</span>
                                <span class="text-gray-500"> / Disponible: {{ 100 - $ponderacionTotal }}%</span>
                            </div>

                            <button type="submit"
                                class="w-full bg-[#1e4b8a] text-white py-3 rounded-xl font-bold hover:bg-[#002d62] transition-all shadow-md
                                {{ $ponderacionTotal >= 100 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $ponderacionTotal >= 100 ? 'disabled' : '' }}>
                                Crear Actividad
                            </button>
                        </form>
                    </div>

                    {{-- Resumen de Ponderación --}}
                    <div class="bg-white p-6 rounded-2xl shadow-md border border-gray-100">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Resumen de Ponderación</h4>
                        @php
                            $categorias = $actividades->groupBy('categoria');
                            $colores = [
                                'Prácticas'     => ['bg' => 'bg-blue-50',   'border' => 'border-blue-100',   'text' => 'text-blue-700',   'val' => 'text-blue-800'],
                                'Tareas'        => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-100', 'text' => 'text-indigo-700', 'val' => 'text-indigo-800'],
                                'Examen'        => ['bg' => 'bg-purple-50', 'border' => 'border-purple-100', 'text' => 'text-purple-700', 'val' => 'text-purple-800'],
                                'Proyecto Final'=> ['bg' => 'bg-green-50',  'border' => 'border-green-100',  'text' => 'text-green-700',  'val' => 'text-green-800'],
                            ];
                        @endphp

                        <div class="space-y-3">
                            @forelse($categorias as $cat => $acts)
                                @php $color = $colores[$cat] ?? ['bg' => 'bg-gray-50', 'border' => 'border-gray-100', 'text' => 'text-gray-700', 'val' => 'text-gray-800']; @endphp
                                <div class="flex justify-between items-center p-3 {{ $color['bg'] }} rounded-xl border {{ $color['border'] }}">
                                    <span class="text-sm font-bold {{ $color['text'] }}">{{ $cat }}</span>
                                    <span class="text-lg font-black {{ $color['val'] }}">{{ $acts->sum('ponderacion') }}%</span>
                                </div>
                            @empty
                                <p class="text-gray-400 text-sm text-center italic">Sin actividades creadas aún.</p>
                            @endforelse

                            {{-- Total --}}
                            @if($ponderacionTotal > 0)
                                <div class="flex justify-between items-center p-3 bg-gray-800 rounded-xl mt-2">
                                    <span class="text-sm font-black text-white">Total</span>
                                    <span class="text-lg font-black {{ $ponderacionTotal == 100 ? 'text-green-400' : 'text-yellow-400' }}">
                                        {{ $ponderacionTotal }}%
                                        {{ $ponderacionTotal == 100 ? '✅' : '' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: Tabla de Actividades --}}
                <div class="lg:w-2/3">
                    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                        <div class="p-6 bg-white border-b border-gray-50 flex justify-between items-center">
                            <div>
                                <h3 class="font-black text-xl text-[#002d62]">Actividades para Calificar</h3>
                                <p class="text-xs text-gray-400 mt-1 italic">{{ $materia->Materia }} — NRC: {{ $materia->nrc }}</p>
                            </div>
                            <span class="text-[10px] font-black bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full uppercase">
                                {{ $actividades->count() }} actividad(es)
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <tr>
                                        <th class="px-6 py-4 text-left">Actividad</th>
                                        <th class="px-6 py-4 text-left">Categoría</th>
                                        <th class="px-6 py-4 text-center">Ponderación</th>
                                        <th class="px-6 py-4 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-sm">
                                    @forelse($actividades as $actividad)
                                        @php
                                            $badgeColor = match($actividad->categoria) {
                                                'Prácticas'      => 'bg-blue-100 text-blue-600',
                                                'Tareas'         => 'bg-indigo-100 text-indigo-600',
                                                'Examen'         => 'bg-purple-100 text-purple-600',
                                                'Proyecto Final' => 'bg-green-200 text-green-700',
                                                default          => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <tr class="hover:bg-blue-50/40 transition">
                                            <td class="px-6 py-5 font-bold text-gray-700">{{ $actividad->nombre }}</td>
                                            <td class="px-6 py-5">
                                                <span class="{{ $badgeColor }} text-[10px] font-black px-2 py-1 rounded uppercase">
                                                    {{ $actividad->categoria }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 text-center font-black text-[#002d62]">
                                                {{ $actividad->ponderacion }}%
                                            </td>
                                            <td class="px-6 py-5 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <button class="px-3 py-1.5 border-2 border-[#1e4b8a] text-[#1e4b8a] font-bold rounded-lg text-xs hover:bg-[#1e4b8a] hover:text-white transition">
                                                        Calificar
                                                    </button>
                                                    <form action="{{ route('profesor.actividades.destroy', [$materia->nrc, $actividad->id]) }}"
                                                          method="POST" class="inline"
                                                          onsubmit="return confirm('¿Eliminar esta actividad?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="px-3 py-1.5 bg-red-100 text-red-600 font-bold rounded-lg text-xs hover:bg-red-600 hover:text-white transition">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">
                                                No hay actividades creadas aún. Usa el formulario para agregar la primera.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>