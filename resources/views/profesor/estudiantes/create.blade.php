<x-app-layout>
    <x-slot name="header">
        Agregar Estudiante
    </x-slot>

    <div class="max-w-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow p-6">

            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('profesor.estudiantes.index') }}"
                   class="text-gray-500 hover:text-gray-700 transition">
                    ← Volver
                </a>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Registro Manual</h3>
                    <p class="text-sm text-gray-500">Agrega un estudiante individualmente</p>
                </div>
            </div>

            <form action="{{ route('profesor.estudiantes.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-1">
                        Nombre completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nombre" name="nombre"
                           value="{{ old('nombre') }}"
                           placeholder="Ej: Ana García López"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002d62] @error('nombre') border-red-500 @enderror">
                    @error('nombre')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="Ej: ana.garcia@correo.buap.mx"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002d62] @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="codigo_estudiante" class="block text-sm font-semibold text-gray-700 mb-1">
                        Código de estudiante <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="codigo_estudiante" name="codigo_estudiante"
                           value="{{ old('codigo_estudiante') }}"
                           placeholder="9 dígitos — Ej: 202312345"
                           maxlength="9"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#002d62] @error('codigo_estudiante') border-red-500 @enderror">
                    @error('codigo_estudiante')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('profesor.estudiantes.index') }}"
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
