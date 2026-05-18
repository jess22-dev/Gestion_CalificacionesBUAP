<x-app-layout>
    <x-slot name="header">
        Importar Estudiantes
    </x-slot>

    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

        <div class="mb-4">
            <a href="{{ route('profesor.estudiantes.index', ['nrc' => $nrc]) }}"
               class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                Volver a la lista de estudiantes
            </a>
        </div>

        @if($materia)
            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800">
                <span class="font-bold">Materia:</span> {{ $materia->Materia }}
                <span class="mx-2 text-blue-300">|</span>
                <span class="font-bold">NRC:</span> {{ $materia->nrc }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-xl">
                <strong> {{ session('success') }}</strong>
                @if(session('intercambio') && count(session('intercambio')) > 0)
                    <p class="text-sm mt-2 font-semibold">Alumnos de intercambio agregados:</p>
                    <ul class="mt-1 text-sm list-disc list-inside">
                        @foreach(session('intercambio') as $ic)
                            <li>{{ $ic['nombre'] }} <span class="bg-orange-100 text-orange-700 text-xs px-1 rounded">intercambio</span></li>
                        @endforeach
                    </ul>
                @endif
                @if(session('yaEnOtraMateria') && count(session('yaEnOtraMateria')) > 0)
                    <p class="text-sm mt-2 font-semibold text-blue-700">También inscritos en otra materia:</p>
                    <ul class="mt-1 text-sm list-disc list-inside">
                        @foreach(session('yaEnOtraMateria') as $yt)
                            <li>{{ $yt['nombre'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-xl">
                <strong> {{ session('warning') }}</strong>
                @if(session('duplicados'))
                    <p class="text-sm mt-2 font-semibold">Ya existían en esta materia:</p>
                    <ul class="mt-1 text-sm list-disc list-inside">
                        @foreach(session('duplicados') as $dup)
                            <li>{{ $dup['nombre'] }} — {{ $dup['codigo'] ?? 'sin código' }}</li>
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

        <div class="bg-white rounded-xl shadow p-6 mb-4">
            <h3 class="text-lg font-bold text-gray-800 mb-1">Importar desde Lista Oficial BUAP</h3>
            <p class="text-sm text-gray-500 mb-6">
                Sube el archivo HTM descargado del AutoServicios para importar la lista oficial de alumnos.
            </p>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800 mb-6">
                <p class="font-black mb-2"> Observaciones</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    <li>El archivo se descarga desde AutoServicios.</li>
                    <li>Se tomara de aqui a los alumnos <strong>inscritos y originarios de la BUAP. </strong></li>
                    <li>Los estudiantes extranjeros o de intercambio <strong>no se veran reflejados en dicha lista. </strong></li>
                    <li>El formato debe de ser .htm o .html.</li>
                </ul>
            </div>

            <form action="{{ route('profesor.estudiantes.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="nrc" value="{{ $nrc }}">

                {{-- HTM obligatorio --}}
                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Lista Oficial BUAP  <span class="text-red-500">*</span>
                    </label>
                    <div id="dropZoneHtm"
                         onclick="document.getElementById('archivo_htm').click()"
                         class="border-2 border-dashed border-blue-300 rounded-xl p-8 text-center cursor-pointer hover:border-[#002d62] hover:bg-blue-50 transition">
                        <p id="labelHtm" class="text-gray-500 text-sm">
                            Arrastra el archivo <strong>.htm</strong> aquí o <u>haz clic para buscar</u>
                        </p>
                    </div>
                    <input type="file" id="archivo_htm" name="archivo_htm"
                           accept=".htm,.html" class="hidden"
                           onchange="mostrarArchivo(this, 'labelHtm', 'dropZoneHtm', 'blue')">
                    @error('archivo_htm')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>



                <div class="flex justify-end gap-3">
                    <a href="{{ route('profesor.estudiantes.index', ['nrc' => $nrc]) }}"
                       class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-[#002d62] text-white rounded-lg text-sm font-semibold hover:bg-[#1e4b8a] transition">
                        ↑ Importar Lista
                    </button>
                </div>
            </form>
        </div>


    </div>

    <script>
    function mostrarArchivo(input, labelId, zoneId, color) {
        if (input.files.length > 0) {
            document.getElementById(labelId).innerHTML =
                '<strong>' + input.files[0].name + '</strong> seleccionado ✔';
        }
    }

    const zone = document.getElementById('dropZoneHtm');
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('bg-blue-50'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('bg-blue-50'));
    zone.addEventListener('drop', e => {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        if (file) {
            const input = document.getElementById('archivo_htm');
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            mostrarArchivo(input, 'labelHtm', 'dropZoneHtm', 'blue');
        }
    });
    </script>
</x-app-layout>