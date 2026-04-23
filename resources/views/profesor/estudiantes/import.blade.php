<x-app-layout>
    <x-slot name="header">
        Importar Estudiantes
    </x-slot>

    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        {{-- Alertas --}}
        @if(session('warning'))
            <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-lg">
                <p class="font-semibold mb-2">{{ session('warning') }}</p>
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

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">

            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('profesor.estudiantes.index') }}"
                   class="text-gray-500 hover:text-gray-700 transition">
                    ← Volver
                </a>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Importar desde Excel / CSV</h3>
                    <p class="text-sm text-gray-500">Carga masiva de estudiantes</p>
                </div>
            </div>

            {{-- Info --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-blue-800">
                <p class="font-semibold mb-1">Columnas requeridas en la primera fila:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><code class="bg-blue-100 px-1 rounded">nombre</code> — Nombre completo</li>
                    <li><code class="bg-blue-100 px-1 rounded">email</code> — Correo electrónico</li>
                    <li><code class="bg-blue-100 px-1 rounded">codigo_estudiante</code> — 9 dígitos</li>
                </ul>
                <p class="mt-2">Formatos: <strong>.xlsx</strong>, <strong>.xls</strong>, <strong>.csv</strong> — Máx. 5MB</p>
            </div>

            {{-- Formulario --}}
            <form action="{{ route('profesor.estudiantes.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Seleccionar archivo <span class="text-red-500">*</span>
                    </label>

                    <div id="dropZone"
                         onclick="document.getElementById('archivo').click()"
                         class="border-2 border-dashed border-gray-300 rounded-xl p-10 text-center cursor-pointer hover:border-[#002d62] hover:bg-blue-50 transition">
                        <p class="text-3xl mb-2">📂</p>
                        <p id="dropLabel" class="text-gray-500 text-sm">
                            Arrastra tu archivo aquí o <u>haz clic para buscar</u>
                        </p>
                    </div>

                    <input type="file" id="archivo" name="archivo"
                           accept=".xlsx,.xls,.csv"
                           class="hidden"
                           onchange="mostrarArchivo(this)">

                    @error('archivo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('profesor.estudiantes.index') }}"
                       class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                        ↑ Importar
                    </button>
                </div>
            </form>
        </div>

        {{-- Ejemplo --}}
        <div class="mt-4 bg-gray-50 rounded-xl p-4">
            <p class="text-sm font-semibold text-gray-600 mb-2">📋 Ejemplo de estructura del archivo:</p>
            <div class="overflow-x-auto">
                <table class="w-full text-sm bg-white rounded border border-gray-200">
                    <thead class="bg-green-100">
                        <tr>
                            <th class="px-3 py-2 text-left border-r border-gray-200">nombre</th>
                            <th class="px-3 py-2 text-left border-r border-gray-200">email</th>
                            <th class="px-3 py-2 text-left">codigo_estudiante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t border-gray-100">
                            <td class="px-3 py-2 border-r border-gray-100">Ana García López</td>
                            <td class="px-3 py-2 border-r border-gray-100">ana.garcia@correo.buap.mx</td>
                            <td class="px-3 py-2">202312345</td>
                        </tr>
                        <tr class="border-t border-gray-100">
                            <td class="px-3 py-2 border-r border-gray-100">Luis Ramírez Torres</td>
                            <td class="px-3 py-2 border-r border-gray-100">luis.ramirez@correo.buap.mx</td>
                            <td class="px-3 py-2">202312346</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
    function mostrarArchivo(input) {
        const label = document.getElementById('dropLabel');
        const zone  = document.getElementById('dropZone');
        if (input.files.length > 0) {
            label.innerHTML = '<strong>' + input.files[0].name + '</strong> seleccionado ✔';
            zone.classList.add('border-green-400', 'bg-green-50');
            zone.classList.remove('border-gray-300');
        }
    }
    const zone = document.getElementById('dropZone');
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('bg-blue-50'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('bg-blue-50'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('archivo');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            mostrarArchivo(input);
        }
    });
    </script>
</x-app-layout>
