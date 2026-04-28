<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Gestión de Estudiantes') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gradient-to-br from-[#e0ebf8] via-white to-[#e0ebf8] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Botón de regreso --}}
            <div class="mb-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center gap-2 text-[#1e4b8a] font-bold hover:underline text-sm">
                    ← Volver al Panel
                </a>
            </div>

            {{-- Alertas --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl">
                     {{ session('error') }}
                </div>
            @endif

            {{-- Resumen --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-[#1e4b8a] text-center">
                    <p class="text-3xl font-black text-[#002d62]">{{ $estudiantes->total() }}</p>
                    <p class="text-xs font-bold text-gray-400 uppercase mt-1">Total Estudiantes</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-green-500 text-center">
                    <p class="text-3xl font-black text-green-600">{{ $conClave }}</p>
                    <p class="text-xs font-bold text-gray-400 uppercase mt-1">Con Clave Asignada</p>
                </div>
                <div class="bg-white p-5 rounded-2xl shadow-sm border-l-4 border-yellow-400 text-center">
                    <p class="text-3xl font-black text-yellow-600">{{ $sinClave }}</p>
                    <p class="text-xs font-bold text-gray-400 uppercase mt-1">Sin Clave</p>
                </div>
            </div>

            {{-- Buscador --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
                <form method="GET" action="{{ route('admin.estudiantes') }}" class="flex gap-3">
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        placeholder="Buscar por nombre, código o email..."
                        class="flex-1 rounded-xl border-gray-200 text-sm focus:ring-[#002d62] focus:border-[#002d62]">
                    <button type="submit"
                        class="bg-[#002d62] text-white px-5 py-2 rounded-xl font-bold text-sm hover:bg-[#1e4b8a] transition">
                        Buscar
                    </button>
                    @if(request('buscar'))
                        <a href="{{ route('admin.estudiantes') }}"
                           class="bg-gray-100 text-gray-600 px-5 py-2 rounded-xl font-bold text-sm hover:bg-gray-200 transition">
                            Limpiar
                        </a>
                    @endif
                </form>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-[#002d62] p-4 flex justify-between items-center">
                    <h3 class="text-white font-bold text-lg">Lista de Estudiantes</h3>
                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">
                        {{ $estudiantes->total() }} registro(s)
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="p-4 text-left text-xs font-black text-gray-500 uppercase">#</th>
                                <th class="p-4 text-left text-xs font-black text-gray-500 uppercase">Código</th>
                                <th class="p-4 text-left text-xs font-black text-gray-500 uppercase">Nombre</th>
                                <th class="p-4 text-left text-xs font-black text-gray-500 uppercase">Email</th>
                                <th class="p-4 text-center text-xs font-black text-gray-500 uppercase">Clave Única</th>
                                <th class="p-4 text-center text-xs font-black text-gray-500 uppercase">Materias</th>
                                <th class="p-4 text-center text-xs font-black text-gray-500 uppercase">Registrado</th>
                                <th class="p-4 text-center text-xs font-black text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($estudiantes as $i => $estudiante)
                                <tr class="hover:bg-blue-50/30 transition">
                                    <td class="p-4 text-gray-400 text-xs font-bold">
                                        {{ $estudiantes->firstItem() + $i }}
                                    </td>
                                    <td class="p-4">
                                        <span class="bg-blue-100 text-blue-700 font-mono text-xs font-bold px-2 py-1 rounded">
                                            {{ $estudiante->codigo_estudiante }}
                                        </span>
                                    </td>
                                    <td class="p-4 font-semibold text-gray-800">
                                        {{ $estudiante->nombre }}
                                    </td>
                                    <td class="p-4 text-gray-500 text-xs">
                                        {{ $estudiante->email }}
                                    </td>
                                    <td class="p-4 text-center">
                                        @if($estudiante->clave_unica)
                                            <span class="bg-[#002d62] text-white font-mono font-black text-sm px-3 py-1.5 rounded-lg tracking-widest">
                                                {{ $estudiante->clave_unica }}
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.generar.clave', $estudiante->id) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    onclick="return confirm('¿Generar clave única para {{ addslashes($estudiante->nombre) }}?')"
                                                    class="bg-indigo-100 text-indigo-700 hover:bg-indigo-600 hover:text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                                     Generar Clave
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-full">
                                            {{ $estudiante->materias->count() }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-center text-xs text-gray-400">
                                        {{ $estudiante->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <button
                                            onclick="abrirModalBaja(
                                                {{ $estudiante->id }},
                                                '{{ addslashes($estudiante->nombre) }}',
                                                {{ $estudiante->materias->toJson() }}
                                            )"
                                            class="bg-red-100 text-red-600 hover:bg-red-600 hover:text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                            Dar de Baja
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-10 text-center text-gray-400 italic">
                                        @if(request('buscar'))
                                            No se encontraron estudiantes con "{{ request('buscar') }}"
                                        @else
                                            No hay estudiantes registrados aún.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($estudiantes->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $estudiantes->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════ --}}
    {{-- MODAL DE BAJA --}}
    {{-- ══════════════════════════════════ --}}
    <div id="modalBaja" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center p-6 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl overflow-hidden">

            {{-- Header --}}
            <div class="bg-red-600 p-5 text-white">
                <h3 class="text-lg font-black"> Dar de Baja</h3>
                <p id="modal_nombre" class="text-red-200 text-sm mt-1"></p>
            </div>

            <div class="p-6 space-y-4">

                {{-- Opción 1: Baja de una materia --}}
                <div id="seccion_materia" class="border-2 border-gray-100 rounded-xl p-4">
                    <h4 class="font-bold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                         Dar de baja de una materia
                    </h4>
                    <p class="text-xs text-gray-400 mb-3">Selecciona la materia y confirma la baja.</p>
                    <form id="form_baja_materia" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <select id="select_materia" name="nrc"
                            onchange="toggleBtnMateria(this)"
                            class="w-full rounded-xl border-gray-200 text-sm mb-3">
                            <option value="">-- Selecciona una materia --</option>
                        </select>
                        <button type="submit"
                            id="btn_baja_materia"
                            disabled
                            onclick="return confirm('¿Seguro que deseas dar de baja a este alumno de la materia seleccionada?')"
                            class="w-full bg-gray-300 text-gray-400 font-bold py-2.5 rounded-xl text-sm cursor-not-allowed transition"
                            title="Selecciona una materia primero">
                             Confirmar baja de materia
                        </button>
                    </form>
                </div>

                {{-- Opción 2: Baja total de la plataforma --}}
                <div class="border-2 border-red-100 rounded-xl p-4 bg-red-50">
                    <h4 class="font-bold text-red-700 mb-2 text-sm uppercase tracking-wide">
                         Dar de baja de la plataforma
                    </h4>
                    <p class="text-xs text-red-500 mb-3">
                        Se eliminarán todos sus datos, materias y acceso al sistema. Esta acción no se puede deshacer.
                    </p>
                    <form id="form_baja_total" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            onclick="return confirm('¿Seguro que deseas eliminar a este estudiante completamente de la plataforma? Esta acción NO se puede deshacer.')"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-xl text-sm transition">
                            Eliminar de la plataforma
                        </button>
                    </form>
                </div>

                {{-- Cancelar --}}
                <button onclick="cerrarModal()"
                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2 rounded-xl text-sm transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script>
        function abrirModalBaja(id, nombre, materias) {
            document.getElementById('modal_nombre').textContent = nombre;

            // Rellena el select de materias
            const select = document.getElementById('select_materia');
            select.innerHTML = '<option value="">-- Selecciona una materia --</option>';

            if (materias && materias.length > 0) {
                materias.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.nrc;
                    opt.textContent = m.Materia + ' (NRC: ' + m.nrc + ')';
                    select.appendChild(opt);
                });
                document.getElementById('seccion_materia').classList.remove('hidden');
            } else {
                document.getElementById('seccion_materia').classList.add('hidden');
            }

            // Asigna acciones a los formularios
            document.getElementById('form_baja_materia').action =
                '/admin/estudiantes/' + id + '/baja-materia';
            document.getElementById('form_baja_total').action =
                '/admin/estudiantes/' + id + '/baja-total';

            // Mostrar modal
            const modal = document.getElementById('modalBaja');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModal() {
            const modal = document.getElementById('modalBaja');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Habilitar botón de baja de materia solo si hay una seleccionada
        function toggleBtnMateria(select) {
            const btn = document.getElementById('btn_baja_materia');
            if (select.value) {
                btn.disabled = false;
                btn.className = 'w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2.5 rounded-xl text-sm transition cursor-pointer';
                btn.removeAttribute('title');
            } else {
                btn.disabled = true;
                btn.className = 'w-full bg-gray-300 text-gray-400 font-bold py-2.5 rounded-xl text-sm cursor-not-allowed transition';
                btn.title = 'Selecciona una materia primero';
            }
        }

        // Cerrar al hacer click fuera
        document.getElementById('modalBaja').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });
    </script>

</x-app-layout>