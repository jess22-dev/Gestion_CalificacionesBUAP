<x-app-layout>
    <x-slot name="header">
        Alta de Estudiantes
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        {{-- Botón de regreso --}}
        <div class="mb-4">
            <a href="{{ route('materias.show', $nrc) }}"
               class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                ← Volver a {{ $materia->Materia ?? 'la materia' }}
            </a>
        </div>

        {{-- Info de la materia --}}
        @if($materia)
            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800">
                <span class="font-bold">Materia:</span> {{ $materia->Materia }}
                <span class="mx-2 text-blue-300">|</span>
                <span class="font-bold">NRC:</span> {{ $materia->nrc }}
            </div>
        @endif

        {{-- Alertas --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-xl flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif


        {{-- Clave única generada --}}
        @if(session('clave_generada'))
            <div class="mb-4 p-5 bg-[#002d62] text-white rounded-xl shadow-lg">
                <div class="flex items-center gap-3 mb-3">
                    <svg class="w-6 h-6 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <div>
                        <p class="font-black text-sm uppercase tracking-wide">Clave de acceso generada</p>
                        <p class="text-blue-200 text-xs">Para: {{ session('nombre_alumno') }}</p>
                    </div>
                </div>
                <div class="bg-white/10 rounded-xl p-4 text-center">
                    <p class="text-xs text-blue-200 uppercase tracking-widest mb-1">Clave única de acceso</p>
                    <p class="text-3xl font-black tracking-[0.3em] text-yellow-400">{{ session('clave_generada') }}</p>
                </div>
                <p class="text-xs text-blue-200 mt-3 text-center italic">
                     ADVERTENCIA: COMPARTE ESTA CLAVE CON EL ALUMNO. SOLO SE MUESTRA UNA VEZ.
                </p>
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-xl">
                <strong>{{ session('warning') }}</strong>
                @if(session('duplicados'))
                    <p class="text-sm mt-2 font-semibold">Ya existían en esta materia:</p>
                    <ul class="mt-1 text-sm list-disc list-inside">
                        @foreach(session('duplicados') as $dup)
                            <li>{{ $dup['nombre'] }} — {{ $dup['codigo'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if(session('yaEnOtraMateria') && count(session('yaEnOtraMateria')) > 0)
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-800 rounded-xl">
                <strong>ADVERTENCIA: Estos alumnos ya estaban en otra materia y fueron agregados a esta:</strong>
                <ul class="mt-2 text-sm list-disc list-inside">
                    @foreach(session('yaEnOtraMateria') as $e)
                    <li>{{ $dup['nombre'] }} — {{ $e['codigo'] ?? 'sin código' }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Aviso de alumnos faltantes en el nuevo HTM --}}
        @if(session('faltantes') && count(session('faltantes')) > 0)
            <div class="mb-4 p-5 bg-orange-50 border-2 border-orange-400 text-orange-900 rounded-xl">
                <div class="flex items-start gap-3 mb-3">

                    <div>
                        <p class="font-black text-base">Alumnos que ya no aparecen en la lista oficial</p>
                        <p class="text-sm mt-1 text-orange-700">
                            Los siguientes {{ count(session('faltantes')) }} alumno(s) estaban registrados en esta materia
                            pero <strong>no aparecen</strong> en la nueva lista que acabas de subir.
                            ¿Deseas darlos de baja de la materia?
                        </p>
                    </div>
                </div>

                <ul class="mb-4 space-y-1 text-sm list-disc list-inside ml-8">
                    @foreach(session('faltantes') as $f)
                        <li>{{ $f['nombre'] }} <span class="text-orange-500 font-mono text-xs">({{ $f['codigo'] }})</span></li>
                    @endforeach
                </ul>

                <div class="flex gap-3 ml-8">
                    {{-- Sí, dar de baja -- }}
                    <form method="POST" action="{{ route('profesor.estudiantes.baja.faltantes') }}">
                        @csrf
                        <input type="hidden" name="nrc" value="{{ session('nrc_import') }}">
                        @foreach(session('faltantes') as $f)
                            <input type="hidden" name="ids[]" value="{{ $f['id'] }}">
                        @endforeach
                        <button type="submit"
                            onclick="return confirm('¿Confirmas dar de baja a {{ count(session('faltantes')) }} alumno(s)?')"
                            class="px-5 py-2 bg-red-600 text-white text-sm font-bold rounded-xl hover:bg-red-700 transition">
                            Sí, dar de baja
                        </button>
                    </form>

                    {{-- No, mantener --}}
                    <a href="{{ route('profesor.estudiantes.index', ['nrc' => session('nrc_import')]) }}"
                       class="px-5 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-200 transition">
                        No, mantenerlos
                    </a>
                </div>
            </div>
        @endif

        {{-- Aviso de más/menos alumnos --}}
        @if(session('total_antes') !== null && session('total_despues') !== null)
            @php
                $antes   = session('total_antes');
                $despues = session('total_despues');
                $diff    = $despues - $antes;
            @endphp
            @if($diff > 0)
                <div class="mb-4 p-3 bg-blue-50 border border-blue-300 text-blue-800 rounded-xl text-sm">
                     Se agregaron <strong>{{ $diff }}</strong> los nuevos alumno(s) en la lista anterior
                    ({{ $antes }} → {{ $despues }}).
                </div>
            @elseif($diff < 0)
                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-300 text-yellow-800 rounded-xl text-sm">
                     La nueva lista tiene <strong>{{ abs($diff) }}</strong> alumno(s) menos que la anterior
                    ({{ $antes }} → {{ $despues }}).
                </div>
            @endif
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        {{-- Encabezado --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-700">Estudiantes en esta materia</h3>
                <p class="text-sm text-gray-500">Total: {{ $estudiantes->total() }} registros</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('profesor.estudiantes.create', ['nrc' => $nrc]) }}"
                   class="inline-flex items-center px-4 py-2 bg-[#002d62] text-white text-sm font-semibold rounded-lg hover:bg-[#1e4b8a] transition">
                     Agregar manualmente
                </a>
                <a href="{{ route('profesor.estudiantes.import', ['nrc' => $nrc]) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition">
                     Importar HTM
                </a>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            @if($estudiantes->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <p class="font-medium">No hay estudiantes registrados en esta materia aún.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#002d62] text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">Nombre</th>
                                <th class="px-4 py-3 text-left">Código</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($estudiantes as $estudiante)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $estudiante->nombre }}</td>
                                <td class="px-4 py-3">
                                    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded">
                                        {{ $estudiante->codigo_estudiante }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($estudiante->pivot->status === 'activo')
                                        <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">Activo</span>
                                    @else
                                        <span class="bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-full">Baja</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $estudiantes->appends(['nrc' => $nrc])->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>