@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background:#f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width: 576px){ .page-body{ min-height: calc(100vh - 64px);} }

  /* Tarjeta centrada con sombra suave */
  .md-card{
    max-width: 620px;
    margin: 32px auto 64px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    padding: 28px;
  }
  .md-title{
    font-weight: 700;
    color: #C24242;
    text-align: center;
    margin-bottom: 18px;
  }

  /* Estilo “material” para los inputs */
  .form-control, .form-select{
    border: none;
    border-bottom: 2px solid #e6e6e6;
    border-radius: 0;
    background: transparent;
    padding-left: 0;
  }
  .form-control:focus, .form-select:focus{
    box-shadow: none;
    border-color: #3f51b5; /* azul material */
  }
  .form-label{
    font-size: .9rem;
    color: #6b7280;
  }

  .btn-md-primary{
    background:#9F3B3B; border:none;
  }
  .btn-md-secondary{
    background:#e5e7eb; color:#111827; border:none;
  }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">
    <h2 class="md-title">Registrar Vehículo</h2>

    {{-- Errores de validación (importante para saber qué falló) --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('vehiculos.store') }}" method="POST" novalidate>
      @csrf

      <div class="mb-3">
        <label class="form-label">Placa</label>
        <input type="text"
               name="placa"
               value="{{ old('placa') }}"
               maxlength="7"
               class="form-control"
               placeholder="Ej: P123ABC"
               required>
        <div class="form-text">Formato: 1 letra + 3 números + 3 letras (P123ABC)</div>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Modelo</label>
          <input type="number" name="modelo" value="{{ old('modelo') }}" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Línea</label>
          <input type="text" name="linea" value="{{ old('linea') }}" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Motor</label>
          <input type="text" name="motor" value="{{ old('motor') }}" class="form-control" required>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label">Cilindraje</label>
          <input type="number" step="0.01" name="cilindraje" value="{{ old('cilindraje') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Marca</label>
          {{-- IMPORTANTE: ahora se envía marca_id --}}
          <select name="marca_id" class="form-select" required>
            <option value="" selected disabled>Seleccione una marca</option>
            @foreach($marcas as $m)
              <option value="{{ $m->id }}" @selected(old('marca_id')==$m->id)>
                {{ $m->nombre }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-md-primary px-4 text-white">Guardar</button>
        <a href="{{ route('vehiculos.index') }}" class="btn btn-md-secondary px-4">Cancelar</a>
      </div>
    </form>
  </div>
</div>

{{-- Helpers UI --}}
<script>
  // Placa en mayúsculas automáticamente
  document.querySelector('input[name="placa"]').addEventListener('input', function(){
    this.value = this.value.toUpperCase();
  });
</script>
@endsection

