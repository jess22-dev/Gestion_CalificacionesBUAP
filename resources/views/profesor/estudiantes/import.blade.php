<x-app-layout>
    <x-slot name="header">
        Importar Estudiantes
    </x-slot>

    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

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

        {{-- Alertas --}}
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

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Importar desde Excel / CSV</h3>
            <p class="text-sm text-gray-500 mb-6">Los estudiantes se vincularán automáticamente a esta materia.</p>

            {{-- Info columnas --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm text-blue-800">
                <p class="font-semibold mb-1">Columnas requeridas en la primera fila:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><code class="bg-blue-100 px-1 rounded">nombre</code></li>
                    <li><code class="bg-blue-100 px-1 rounded">email</code></li>
                    <li><code class="bg-blue-100 px-1 rounded">codigo_estudiante</code> — 9 dígitos</li>
                </ul>
                <p class="mt-2 text-xs">Si un alumno ya existe en otra materia, se agregará automáticamente a esta y se te notificará.</p>
            </div>

            <form action="{{ route('profesor.estudiantes.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="nrc" value="{{ $nrc }}">

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
                           accept=".xlsx,.xls,.csv" class="hidden"
                           onchange="mostrarArchivo(this)">
                    @error('archivo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('profesor.estudiantes.index', ['nrc' => $nrc]) }}"
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
            <p class="text-sm font-semibold text-gray-600 mb-2">📋 Ejemplo de estructura:</p>
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
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function mostrarArchivo(input) {
        if (input.files.length > 0) {
            document.getElementById('dropLabel').innerHTML =
                '<strong>' + input.files[0].name + '</strong> seleccionado ✔';
            document.getElementById('dropZone').classList.add('border-green-400', 'bg-green-50');
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