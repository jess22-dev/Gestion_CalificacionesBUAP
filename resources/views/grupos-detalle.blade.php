<x-app-layout>
    <x-slot name="header">
        {{ __('Gestión de Grupo') }}
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('dashboard') }}" class="text-[#1e4b8a] font-bold hover:underline flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver al Dashboard
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-xl rounded-2xl border border-gray-100">
                <div class="bg-[#1e4b8a] p-6 text-white">
                    <h3 class="text-2xl font-bold">Detalles del Grupo: {{ $id }}</h3>
                    <p class="opacity-80">Aquí podrás gestionar las calificaciones y asistencias.</p>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <h4 class="text-lg font-bold text-[#002d62] border-b-2 border-blue-100 pb-2">Información General</h4>
                            <div class="bg-blue-50 p-4 rounded-xl">
                                <p class="text-sm text-gray-600 font-semibold uppercase">Estatus del Periodo</p>
                                <p class="text-green-600 font-bold">Activo - Primavera 2026</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-2xl border-2 border-dashed border-gray-300">
                            <h4 class="text-lg font-bold text-[#002d62] mb-4">Cargar Calificaciones (Excel)</h4>
                            
                            <form action="#" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="flex flex-col items-center justify-center">
                                    <label class="w-full flex flex-col items-center px-4 py-6 bg-white text-blue rounded-lg shadow-lg tracking-wide uppercase border border-blue cursor-pointer hover:bg-blue-50 transition-colors">
                                        <svg class="w-8 h-8 text-blue-500" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M16.88 9.1L13.47 5.69C13.28 5.5 13.03 5.4 12.78 5.4H3.5C2.67 5.4 2 6.07 2 6.9V17.1C2 17.93 2.67 18.6 3.5 18.6H16.5C17.33 18.6 18 17.93 18 17.1V10.72C18 10.47 17.9 10.22 17.71 10.03L16.88 9.1ZM12.12 6.84L15.28 10H12.12V6.84ZM16.5 17.1H3.5V6.9H10.62V11.5H15.28V17.1H16.5Z" />
                                        </svg>
                                        <span class="mt-2 text-sm leading-normal font-semibold text-gray-600">Seleccionar archivo .xlsx</span>
                                        <input type='file' class="hidden" />
                                    </label>
                                    <button type="submit" class="mt-4 w-full bg-[#002d62] text-white font-bold py-2 rounded-lg hover:bg-[#1e4b8a] transition-colors shadow-md">
                                        Subir y Procesar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>