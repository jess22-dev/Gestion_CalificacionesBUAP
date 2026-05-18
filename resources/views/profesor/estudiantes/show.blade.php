<x-app-layout>
    <x-slot name="header">
        Detalle del Estudiante
    </x-slot>

    <div class="max-w-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow p-6">

            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('profesor.estudiantes.index') }}"
                   class="text-gray-500 hover:text-gray-700 transition">
                    ← Volver
                </a>
                <h3 class="text-lg font-bold text-gray-800">Información del Estudiante</h3>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-sm font-semibold text-gray-500">Nombre</span>
                    <span class="text-sm text-gray-800 font-medium">{{ $estudiante->nombre }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-sm font-semibold text-gray-500">Email</span>
                    <span class="text-sm text-gray-800">{{ $estudiante->email }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 pb-3">
                    <span class="text-sm font-semibold text-gray-500">Matrícula</span>
                    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded">
                        {{ $estudiante->codigo_estudiante }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm font-semibold text-gray-500">Fecha de registro</span>
                    <span class="text-sm text-gray-500">{{ $estudiante->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
