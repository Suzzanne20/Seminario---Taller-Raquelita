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
<div class="container">
    <h2>Registrar Vehículo</h2>
    <form action="{{ route('vehiculos.store') }}" method="POST">
        @csrf

        <div class="form-group mb-3">
            <label>Placa</label>
            <input type="text" name="placa" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Modelo</label>
            <input type="number" name="modelo" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Linea</label>
            <input type="text" name="linea" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Motor</label>
            <input type="text" name="motor" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Cilindraje</label>
            <input type="number" name="cilindraje" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Marca</label>
            <select id="marcaSelect" name="marca" class="form-control">
                <option value="">Seleccione una marca</option>
                @foreach($marcas as $marca)
                    <option value="{{ $marca->nombre }}">{{ $marca->nombre }}</option>
                @endforeach
                <option value="nueva">-- Nueva marca --</option>
            </select>

            <input type="text" id="nuevaMarca" name="marca" class="form-control mt-2 d-none" placeholder="Ingrese nueva marca">
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>

<script>
    document.getElementById('marcaSelect').addEventListener('change', function () {
        let nuevaMarcaInput = document.getElementById('nuevaMarca');
        if (this.value === 'nueva') {
            nuevaMarcaInput.classList.remove('d-none');
            nuevaMarcaInput.value = '';
        } else {
            nuevaMarcaInput.classList.add('d-none');
            nuevaMarcaInput.value = this.value; // pasa la marca seleccionada
        }
    });
</script>
@endsection
