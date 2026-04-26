<script src="https://unpkg.com/html5-qrcode"></script>


<x-app-layout>
    <x-slot name="header">
        {{ __('Gestión de Grupo') }}
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6">
                <a href="{{ route('dashboard') }}" class="text-[#1e4b8a] font-bold hover:underline">
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
                    {{--  CALIFICACIONES --}}
                    {{-- ========================= --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

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
                    {{--  ASISTENCIA --}}
                    {{-- ========================= --}}
                    <div class="mt-12 bg-white p-6 rounded-2xl shadow-xl border">

                        <h3 class="text-xl font-bold text-[#002d62] mb-4">
                             Control de Asistencia
                        </h3>

                        <div class="grid md:grid-cols-3 gap-6">

                            <div>
                                <label class="text-xs font-bold text-gray-500 uppercase">Duración</label>
                                <select class="w-full mt-2 rounded-xl border-gray-200" id="duracion">
                                    <option value="5">5 minutos</option>
                                    <option value="10">10 minutos</option>
                                    <option value="15">15 minutos</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button id="btnIniciar" class="bg-green-600 text-white px-4 py-2 rounded-xl font-bold">
                                    Iniciar 
                                </button>

                                <button id="btnDetener" class="bg-red-500 text-white px-4 py-2 rounded-xl font-bold">
                                    Detener 
                                </button>
                            </div>

                            <button id="btnQR" class="bg-[#002d62] text-white px-4 py-2 rounded-xl font-bold">
                                Escanear QR
                            </button>

                            <div id="reader" style="width:300px; margin-top:10px;"></div>

                        </div>

                        <!-- CONTADOR -->
                        <div id="contador" class="mt-4 text-lg font-bold text-green-600"></div>

                    </div>

                    {{-- ========================= --}}
                    {{--  LISTA DE ALUMNOS --}}
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
    </div>

   


    <script>
document.addEventListener("DOMContentLoaded", function () {

    let intervalo = null;

    const btnIniciar = document.getElementById('btnIniciar');
    const btnDetener = document.getElementById('btnDetener');
    const contador = document.getElementById('contador');

    // =========================
    // INICIAR
    // =========================
    btnIniciar.addEventListener('click', function () {

        const duracion = document.getElementById('duracion').value;
        const materia_nrc = "{{ $materia->nrc }}";

        fetch('/asistencia/iniciar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                materia_nrc: materia_nrc,
                duracion: duracion
            })
        })
        .then(async res => {
            let data = await res.json();
            if (!res.ok) throw new Error(data.error || "Error");
            return data;
        })
        .then(data => {

            alert("✅ Asistencia iniciada");

            let fin = new Date(data.fin);

            // limpiar intervalo anterior si existe
            if (intervalo) clearInterval(intervalo);

            intervalo = setInterval(() => {

                let ahora = new Date();
                let diff = Math.floor((fin - ahora) / 1000);

                if (diff <= 0) {
                    clearInterval(intervalo);
                    contador.innerHTML = "⛔ Finalizada";

                    // cerrar en backend automáticamente
                    fetch('/asistencia/detener', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            materia_nrc: materia_nrc
                        })
                    });

                    return;
                }

                let min = Math.floor(diff / 60);
                let seg = diff % 60;

                contador.innerHTML = `⏱️ ${min}:${seg.toString().padStart(2,'0')}`;

            }, 1000);

        })
        .catch(error => {
            console.error(error);
            alert("❌ " + error.message);
        });

    });

    // =========================
    // DETENER
    // =========================
    btnDetener.addEventListener('click', function () {

        const materia_nrc = "{{ $materia->nrc }}";

        fetch('/asistencia/detener', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                materia_nrc: materia_nrc
            })
        })
        .then(async res => {
            let data = await res.json();
            if (!res.ok) throw new Error(data.error || "Error");
            return data;
        })
        .then(data => {

            alert("⛔ Asistencia detenida");

            if (intervalo) {
                clearInterval(intervalo);
            }

            contador.innerHTML = "⛔ Finalizada";

        })
        .catch(error => {
            console.error(error);
            alert("❌ " + error.message);
        });

    });

});
</script>






<script>
document.getElementById('btnQR').addEventListener('click', function () {

    const html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(devices => {

        if (devices && devices.length) {

            html5QrCode.start(
                devices[0].id,
                {
                    fps: 10,
                    qrbox: 250
                },
                (decodedText) => {

                    console.log("QR leído:", decodedText);

                    // 🔥 AQUÍ ESTÁ LA MAGIA (ENVÍA AL BACKEND)
                    fetch('/asistencia/registrar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            clave: decodedText,
                            materia_nrc: "{{ $materia->nrc }}"
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert("✅ Asistencia registrada");
                    })
                    .catch(err => {
                        console.error(err);
                        alert("❌ Error al registrar");
                    });

                    // 🔥 detener cámara
                    html5QrCode.stop();
                }
            );

        }

    }).catch(err => {
        console.error("Error cámara:", err);
        alert("No se pudo acceder a la cámara");
    });

});
</script>




</x-app-layout>