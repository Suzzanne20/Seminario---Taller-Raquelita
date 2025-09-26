@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background: #f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background: #f0f0f0 !important; color: #212529; }
  @media (max-width: 576px) {
    .page-body { min-height: calc(100vh - 64px); }
  }
</style>
@endpush

@section('content')
<div class="container mt-4">
    <h2>Editar Vehículo: {{ $vehiculo->placa }}</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vehiculos.update', $vehiculo->placa) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label>Placa</label>
            <input type="text" class="form-control" value="{{ $vehiculo->placa }}" disabled>
        </div>

        <div class="form-group mb-3">
            <label>Marca</label>
            <select name="marca" id="marca" class="form-control" required>
                <option value="">Seleccione una marca</option>
                @foreach ($marcas as $marca)
                    <option value="{{ $marca->nombre }}"
                        {{ $vehiculo->marca && $vehiculo->marca->nombre == $marca->nombre ? 'selected' : '' }}>
                        {{ $marca->nombre }}
                    </option>
                @endforeach
                <option value="nueva">Nueva marca...</option>
            </select>
        </div>

        <div class="form-group mb-3" id="nuevaMarcaDiv" style="display:none;">
            <label>Nombre de nueva marca</label>
            <input type="text" name="nueva_marca" id="nueva_marca" class="form-control" maxlength="45">
        </div>

        <div class="form-group mb-3">
            <label>Modelo</label>
            <input type="number" name="modelo" class="form-control" value="{{ $vehiculo->modelo }}" required>
        </div>

        <div class="form-group mb-3">
            <label>Línea</label>
            <input type="text" name="linea" class="form-control" value="{{ $vehiculo->linea }}" required maxlength="45">
        </div>

        <div class="form-group mb-3">
            <label>Motor</label>
            <input type="text" name="motor" class="form-control" value="{{ $vehiculo->motor }}" required maxlength="45">
        </div>

        <div class="form-group mb-3">
            <label>Cilindraje</label>
            <input type="number" name="cilindraje" class="form-control" value="{{ $vehiculo->cilindraje }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.getElementById('marca').addEventListener('change', function () {
    let nuevaMarcaDiv = document.getElementById('nuevaMarcaDiv');
    if (this.value === 'nueva') {
        nuevaMarcaDiv.style.display = 'block';
    } else {
        nuevaMarcaDiv.style.display = 'none';
    }
});
</script>
@endsection
