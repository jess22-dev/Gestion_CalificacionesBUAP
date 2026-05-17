<x-app-layout>
    <x-slot name="header">
        Agregar Estudiante
    </x-slot>

    <div class="max-w-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        {{-- Botón de regreso --}}
        <div class="mb-4">
            <a href="{{ route('profesor.estudiantes.index', ['nrc' => $nrc]) }}"
               class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                ← Volver a la lista de estudiantes
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

        {{-- Alerta de estudiante en otra materia --}}
        @if(session('info'))
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-800 rounded-xl">
                <p class="font-semibold mb-3"> {{ session('info') }}</p>
                <form action="{{ route('profesor.estudiantes.agregar.existente') }}" method="POST" class="flex gap-3">
                    @csrf
                    <input type="hidden" name="nrc" value="{{ session('nrc') ?? $nrc }}">
                    <input type="hidden" name="estudiante_id" value="{{ session('estudiante_existente_id') }}">
                    <button type="submit"
                        class="px-4 py-2 bg-[#002d62] text-white rounded-lg font-bold text-sm hover:bg-[#1e4b8a] transition">
                        Sí, agregar a esta materia
                    </button>
                    <a href="{{ route('profesor.estudiantes.index', ['nrc' => $nrc]) }}"
                       class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg font-bold text-sm hover:bg-gray-50 transition">
                        No, cancelar
                    </a>
                </form>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Registro Manual</h3>

            <form action="{{ route('profesor.estudiantes.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="nrc" value="{{ $nrc }}">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           placeholder="Ej: Ana García López"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002d62] @error('nombre') border-red-500 @enderror">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="Ej: ana.garcia@correo.buap.mx"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002d62] @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Matricula <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="codigo_estudiante" value="{{ old('codigo_estudiante') }}"
                           placeholder="9 dígitos — Ej: 202312345"
                           maxlength="9"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002d62] @error('codigo_estudiante') border-red-500 @enderror">
                    @error('codigo_estudiante')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('profesor.estudiantes.index', ['nrc' => $nrc]) }}"
                       class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-[#002d62] text-white rounded-lg text-sm font-semibold hover:bg-[#1e4b8a] transition">
                        Guardar Estudiante
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>