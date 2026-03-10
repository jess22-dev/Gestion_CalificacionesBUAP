<div class="container">
    <h1>Crear Materia</h1>
    <form action="{{ route('materias.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nombre de la Materia</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Código de la Materia</label>
            <input type="text" name="codigo" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar Materia</button>
        <a href="{{ route('materias.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
    @csrf  ```
</div>
