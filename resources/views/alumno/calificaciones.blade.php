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