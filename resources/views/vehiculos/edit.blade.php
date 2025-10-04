@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background:#f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width: 576px){ .page-body{ min-height: calc(100vh - 64px);} }

  .md-card{
    max-width: 620px;
    margin: 32px auto 64px;
    background:#fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    padding: 28px;
  }
  .md-title{
    font-weight:700; color:#C24242; text-align:center; margin-bottom:18px;
  }

  .form-control, .form-select{
    border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent; padding-left:0;
  }
  .form-control:focus, .form-select:focus{
    box-shadow:none; border-color:#3f51b5;
  }
  .form-label{ font-size:.9rem; color:#6b7280; }

  .btn-md-primary{ background:#9F3B3B; border:none; }
  .btn-md-secondary{ background:#e5e7eb; color:#111827; border:none; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">
    <h2 class="md-title"><i class="bi bi-pencil-square"></i> {{ $vehiculo->placa }}</h2>

    {{-- Errores de validación --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('vehiculos.update', $vehiculo->placa) }}" method="POST" novalidate>
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label">Placa</label>
        <input type="text" class="form-control" value="{{ $vehiculo->placa }}" disabled>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Marca</label>
          {{-- IMPORTANTE: enviar marca_id --}}
          <select name="marca_id" class="form-select" required>
            <option value="" disabled>Seleccione una marca</option>
            @foreach ($marcas as $m)
              <option value="{{ $m->id }}"
                @selected(old('marca_id', $vehiculo->marca_id) == $m->id)>
                {{ $m->nombre }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Modelo</label>
          <input type="number" name="modelo"
                 value="{{ old('modelo', $vehiculo->modelo) }}"
                 class="form-control" required>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label">Línea</label>
          <input type="text" name="linea"
                 value="{{ old('linea', $vehiculo->linea) }}"
                 class="form-control" maxlength="45" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Motor</label>
          <input type="text" name="motor"
                 value="{{ old('motor', $vehiculo->motor) }}"
                 class="form-control" maxlength="45" required>
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label">Cilindraje</label>
        <input type="number" step="0.01" name="cilindraje"
               value="{{ old('cilindraje', $vehiculo->cilindraje) }}"
               class="form-control" required>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-md-primary px-4 text-white">Actualizar</button>
        <a href="{{ route('vehiculos.index') }}" class="btn btn-md-secondary px-4">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
