<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Resumen de Estadísticas (Tarjetas rápidas) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-[#1e4b8a]">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Profesores</p>
                    <p class="text-3xl font-bold text-[#002d62]">Conectado</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-[#1e4b8a]">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Materias Activas</p>
                    <p class="text-3xl font-bold text-[#002d62]">En Sistema</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm border-l-4 border-[#002d62]">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Rol Actual</p>
                    <p class="text-3xl font-bold text-[#1e4b8a]">Administrador</p>
                </div>
            </div>

            {{-- Área de Contenido Principal --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100">
                <div class="p-8 text-gray-900">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="bg-[#cfe2f3] p-3 rounded-full">
                            <svg class="w-8 h-8 text-[#1e4b8a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-[#1e4b8a]">¡Hola, {{ Auth::user()->name }}!</h3>
                            <p class="text-gray-500">Desde aquí puedes gestionar los usuarios y las materias del sistema.</p>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('profile.edit') }}" class="p-4 border rounded-xl hover:bg-gray-50 transition flex items-center justify-between">
                            <span>Configurar mi perfil</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>
                        </a>
                        {{-- Aquí podrías añadir un link a la gestión de usuarios si lo tienes --}}
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>