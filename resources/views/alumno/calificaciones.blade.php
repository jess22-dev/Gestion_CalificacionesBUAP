<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight italic">
            Mis Calificaciones — {{ $materia->Materia }}
        </h2>
    </x-slot>

    <div class="py-12 bg-[#f0f4f8] min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-4">
                <a href="{{ route('alumno.dashboard') }}"
                   class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                    ← Volver a mis materias
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
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl">
                     {{ session('error') }}
                </div>
            @endif





            <div class="mb-4">
                <a href="{{ route('alumno.materia.detalle', $materia->nrc) }}"
                class="inline-flex items-center gap-2 text-[#002d62] font-bold hover:underline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver al detalle de la materia
                </a>
            </div>












            {{-- Resumen de promedio --}}
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 mb-6 overflow-hidden">
                <div class="bg-gradient-to-br from-[#1e4b8a] to-[#002d62] p-6 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-black">{{ $materia->Materia }}</h3>
                        <p class="text-blue-200 text-sm">NRC: {{ $materia->nrc }} · {{ $materia->Profesor }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-blue-200 text-xs uppercase font-bold mb-1">Promedio Actual</p>
                        <p class="text-4xl font-black {{ $promedio >= 6 ? 'text-green-300' : 'text-red-300' }}">
                            {{ $promedio > 0 ? number_format($promedio, 1) : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Lista de actividades --}}
            @if($actividades->isEmpty())
                <div class="bg-white rounded-2xl shadow p-10 text-center text-gray-400">
                    <p class="font-medium">No hay actividades registradas aún.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($actividades as $actividad)
                        @php
                            $pivot      = $actividad->pivot ?? null;
                            $entregado  = $pivot?->entregado ?? false;
                            $calificacion = $pivot?->calificacion ?? null;
                            $archivo    = $pivot?->archivo_nombre ?? null;
                        @endphp
                        <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
                            {{-- Header actividad --}}
                            <div class="flex justify-between items-center p-5 border-b border-gray-100">
                                <div>
                                    <h4 class="font-black text-gray-800">{{ $actividad->nombre }}</h4>
                                    <div class="flex gap-2 mt-1">
                                        <span class="bg-blue-100 text-blue-700 text-[10px] font-black px-2 py-0.5 rounded uppercase">
                                            {{ $actividad->categoria }}
                                        </span>
                                        <span class="text-xs text-gray-400 font-bold">{{ $actividad->ponderacion }}%</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if(!is_null($calificacion))
                                        <p class="text-3xl font-black {{ $calificacion >= 6 ? 'text-green-600' : 'text-red-500' }}">
                                            {{ $calificacion }}
                                        </p>
                                        <p class="text-xs text-gray-400">/ 10</p>
                                    @else
                                        <p class="text-gray-300 text-sm italic">Sin calificar</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Estado y subida de archivo --}}
                            <div class="p-5 bg-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="flex items-center gap-3">
                                    @if($entregado)
                                        <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">
                                             Entregado
                                        </span>
                                        @if($archivo)
                                            <span class="text-xs text-gray-500 italic">{{ $archivo }}</span>
                                        @endif
                                    @else
                                        <span class="bg-red-100 text-red-600 text-xs font-bold px-3 py-1 rounded-full">
                                             Pendiente
                                        </span>
                                    @endif
                                </div>

                                <div class="flex gap-2">
                                    {{-- Subir / reemplazar archivo --}}
                                    <form method="POST"
                                          action="{{ route('alumno.actividad.subir', $actividad->id) }}"
                                          enctype="multipart/form-data"
                                          class="flex items-center gap-2">
                                        @csrf
                                        <input type="file" name="archivo"
                                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                            class="text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-[#002d62] file:text-white file:text-xs file:font-bold file:cursor-pointer hover:file:bg-[#1e4b8a]">
                                        <button type="submit"
                                            class="bg-[#002d62] text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-[#1e4b8a] transition whitespace-nowrap">
                                            {{ $entregado ? 'Reemplazar' : 'Subir archivo' }}
                                        </button>
                                    </form>

                                    {{-- Eliminar archivo --}}
                                    @if($entregado)
                                        <form method="POST"
                                              action="{{ route('alumno.actividad.eliminar', $actividad->id) }}"
                                              onsubmit="return confirm('¿Eliminar el archivo entregado?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="bg-red-100 text-red-600 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                                Quitar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Barra de progreso de ponderación --}}
                <div class="mt-6 bg-white rounded-2xl shadow border border-gray-100 p-5">
                    <h4 class="font-bold text-gray-700 mb-3 text-sm">Resumen por categoría</h4>
                    @php
                        $categorias = $actividades->groupBy('categoria');
                    @endphp
                    <div class="space-y-2">
                        @foreach($categorias as $cat => $acts)
                            @php
                                $ponderacionCat = $acts->sum('ponderacion');
                                $calificadasCat = $acts->filter(fn($a) => !is_null($a->pivot?->calificacion));
                                $promCat = $calificadasCat->isNotEmpty()
                                    ? $calificadasCat->avg(fn($a) => $a->pivot->calificacion * ($a->ponderacion / 100))
                                    : null;
                            @endphp
                            <div class="flex justify-between items-center text-sm">
                                <span class="font-semibold text-gray-600">{{ $cat }}</span>
                                <span class="text-gray-400">{{ $ponderacionCat }}% ponderación</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
