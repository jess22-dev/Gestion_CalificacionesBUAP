<x-app-layout>
    <x-slot name="header">
        {{ __('Gestión de Grupo') }}
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <a href="{{ route('dashboard') }}" class="text-[#1e4b8a] font-bold hover:underline flex items-center">
                    ← Volver al Dashboard
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
                    {{-- 🔵 CALIFICACIONES --}}
                    {{-- ========================= --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        <!-- Definir Actividad -->
                        <div class="bg-white p-6 rounded-2xl shadow">
                            <h3 class="font-bold text-lg mb-4">Definir Actividad</h3>

                            <input type="text" placeholder="Nombre actividad"
                                class="w-full mb-3 rounded border-gray-300">

                            <select class="w-full mb-3 rounded border-gray-300">
                                <option>Prácticas (20%)</option>
                                <option>Examen (20%)</option>
                                <option>Proyecto (40%)</option>
                            </select>

                            <input type="number" placeholder="Puntos base"
                                class="w-full mb-3 rounded border-gray-300">

                            <button class="w-full bg-[#002d62] text-white py-2 rounded-xl font-bold">
                                Crear Actividad
                            </button>
                        </div>

                        <!-- Actividades -->
                        <div class="bg-white p-6 rounded-2xl shadow">
                            <h3 class="font-bold text-lg mb-4">Actividades</h3>

                            <ul class="space-y-3">
                                <li class="bg-gray-100 p-3 rounded">Configuración de Laravel</li>
                                <li class="bg-gray-100 p-3 rounded">Examen Primer Parcial</li>
                                <li class="bg-gray-100 p-3 rounded">Proyecto Final</li>
                            </ul>
                        </div>

                    </div>

                    {{-- ========================= --}}
                    {{-- 🟢 ASISTENCIA --}}
                    {{-- ========================= --}}
                    <div class="mt-12 bg-white p-6 rounded-2xl shadow-xl border">

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
                                    Iniciar ▶️
                                </button>

                                <button class="bg-red-500 text-white px-4 py-2 rounded-xl font-bold">
                                    Detener ⛔
                                </button>
                            </div>

                            <div class="flex items-end">
                                <button class="bg-[#002d62] text-white px-4 py-2 rounded-xl font-bold">
                                    Escanear QR 📷
                                </button>
                            </div>

                        </div>
                    </div>

                    {{-- ========================= --}}
                    {{-- 📋 LISTA DE ALUMNOS --}}
                    {{-- ========================= --}}
                    <div class="mt-8 bg-white p-6 rounded-2xl shadow-xl">

                        <h3 class="text-lg font-bold text-[#002d62] mb-4">
                            Lista de Asistencia
                        </h3>

                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-gray-400 text-xs uppercase">
                                    <th class="p-3">Matrícula</th>
                                    <th class="p-3">Alumno</th>
                                    <th class="p-3 text-center">Asistencia</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($alumnos as $alumno)
                                <tr class="border-b">
                                    <td class="p-3">{{ $alumno->clave_unica }}</td>
                                    <td class="p-3">Alumno {{ $alumno->alumno_id }}</td>
                                    <td class="p-3 text-center">
                                        <input type="checkbox" class="w-5 h-5">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>

        </div>


        {{-- ========================= --}}
{{-- 📋 ACCESO A ASISTENCIA --}}
{{-- ========================= --}}
<div class="mt-10">

    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 flex justify-between items-center">

        <div>
            <h3 class="text-xl font-bold text-[#002d62]">
                📋 Módulo de Asistencia
            </h3>
            <p class="text-gray-500 text-sm">
                Gestiona la asistencia del grupo mediante código QR
            </p>
        </div>

        <a href="{{ route('profesor.asistencia', $materia->nrc) }}"
           class="bg-[#002d62] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#1e4b8a] transition shadow-lg">

            Tomar Asistencia →
        </a>

    </div>

</div>







    </div>
</x-app-layout>