<table class="w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-100">
            <th class="border p-2">Nombre</th>
            <th class="border p-2">Código</th>
            <th class="border p-2">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($materias as $materia)
        <tr>
            <td class="border p-2">{{ $materia->nombre }}</td>
            <td class="border p-2">{{ $materia->codigo }}</td>
            <td class="border p-2 flex gap-2">
                <a href="{{ route('materias.edit', $materia->id) }}" 
                   class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">
                    Editar
                </a>

                <form action="{{ route('materias.destroy', $materia->id) }}" method="POST" 
                      onsubmit="return confirm('¿Segura que quieres eliminar esta materia?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                        Eliminar
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>