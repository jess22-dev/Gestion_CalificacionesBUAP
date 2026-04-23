<x-app-layout>
    <x-slot name="header">
        Alta de Estudiantes
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        {{-- Éxito --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Warning con duplicados --}}
        @if(session('warning'))
            <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-lg">
                <div class="flex items-center gap-2 font-semibold mb-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('warning') }}
                </div>
                @if(session('duplicados'))
                    <p class="text-sm mb-2 font-medium">Registros omitidos por duplicado:</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm bg-white rounded border border-yellow-300">
                            <thead class="bg-yellow-200">
                                <tr>
                                    <th class="px-3 py-2 text-left">Nombre</th>
                                    <th class="px-3 py-2 text-left">Email</th>
                                    <th class="px-3 py-2 text-left">Código</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(session('duplicados') as $dup)
                                <tr class="border-t border-yellow-200">
                                    <td class="px-3 py-2">{{ $dup['nombre'] }}</td>
                                    <td class="px-3 py-2">{{ $dup['email'] }}</td>
                                    <td class="px-3 py-2">{{ $dup['codigo'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- Encabezado --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-bold text-gray-700">Lista de Estudiantes</h3>
                <p class="text-sm text-gray-500">Total: {{ $estudiantes->total() }} registros</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('profesor.estudiantes.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-[#002d62] text-white text-sm font-semibold rounded-lg hover:bg-[#1e4b8a] transition">
                    + Agregar manual
                </a>
                <a href="{{ route('profesor.estudiantes.import') }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition">
                    ↑ Importar Excel / CSV
                </a>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            @if($estudiantes->isEmpty())
                <div class="text-center py-16 text-gray-400">
                    <p class="text-4xl mb-3">📭</p>
                    <p class="font-medium">No hay estudiantes registrados aún.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-[#002d62] text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Nombre</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Código</th>
                                <th class="px-4 py-3 text-left">Registrado</th>
                                <th class="px-4 py-3 text-center">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($estudiantes as $estudiante)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-gray-400">{{ $estudiante->id }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $estudiante->nombre }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $estudiante->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-1 rounded">
                                        {{ $estudiante->codigo_estudiante }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $estudiante->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('profesor.estudiantes.show', $estudiante) }}"
                                       class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition text-xs font-medium">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $estudiantes->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
