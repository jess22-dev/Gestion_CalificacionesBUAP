<div class="max-w-2xl mx-auto p-8">
    <h1 class="text-2xl font-bold mb-4">Editar Materia</h1>

    <form action="{{ route('materias.update', $materia->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- ¡Vital para que Laravel sepa que es una edición! --}}
        
        <div class="mb-4">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="{{ $materia->nombre }}" class="border w-full p-2" required>
        </div>

        <div class="mb-4">
            <label>Código:</label>
            <input type="text" name="codigo" value="{{ $materia->codigo }}" class="border w-full p-2" required>
        </div>

        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Actualizar</button>
        <a href="{{ route('materias.index') }}" class="ml-2 text-gray-600">Cancelar</a>
    </form>
</div>