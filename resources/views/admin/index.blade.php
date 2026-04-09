<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Panel de Administración') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Resumen de Estadísticas --}}
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
                            <p class="text-gray-500">Gestión de control escolar y carga académica.</p>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-100">

                    {{-- SECCIÓN NUEVA: ACCIONES DE GESTIÓN --}}
                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Herramientas de Control</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        {{-- BOTÓN NUEVO: IMPORTAR EXCEL --}}
                        <a href="{{ route('admin.importar') }}" class="group p-6 border-2 border-blue-50 rounded-2xl hover:border-[#1e4b8a] hover:bg-blue-50 transition-all flex items-center justify-between shadow-sm">
                            <div class="flex items-center space-x-4">
                                <div class="bg-[#002d62] p-3 rounded-xl text-white group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 10-8 0v2m8-2v2m7-2s0 0 0 0m-7 2h10m-10-6V7a4 4 0 00-8 0v4m8 6v-6m10 6V11a4 4 0 00-8 0v6"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-[#002d62] text-lg uppercase italic">Carga Académica</span>
                                    <span class="text-sm text-gray-500 font-medium">Importar horarios y profesores desde Excel</span>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-300 group-hover:text-[#1e4b8a] transform group-hover:translate-x-2 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>

                        {{-- BOTÓN PERFIL --}}
                        <a href="{{ route('profile.edit') }}" class="group p-6 border-2 border-gray-50 rounded-2xl hover:border-gray-200 hover:bg-gray-50 transition-all flex items-center justify-between shadow-sm">
                            <div class="flex items-center space-x-4">
                                <div class="bg-gray-100 p-3 rounded-xl text-gray-500 group-hover:bg-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-gray-700 uppercase italic">Mi Cuenta</span>
                                    <span class="text-sm text-gray-400">Configurar perfil y seguridad</span>
                                </div>
                            </div>
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>

                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>