@extends('layouts.app')

@push('styles')
<style>
  /* Fondo claro a pantalla completa (como en la lista) */
  html, body { height: 100%; background: #f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background: #f0f0f0 !important; color: #212529; }
  @media (max-width: 576px){ .page-body { min-height: calc(100vh - 64px); } }

  /* Card del formulario */
  .card-form {
    max-width: 560px;
    border: 1px solid #e9ecef;
    border-radius: 14px;
  }

  /* Botón con tu color de marca */
  .btn-theme { background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
  .btn-theme:hover { background:#873131; border-color:#873131; color:#fff; }

  /* Focus inputs amigable */
  .form-control:focus {
    border-color:#c24242;
    box-shadow: 0 0 0 .2rem rgba(194,66,66,.15);
  }
</style>
@endpush

@section('content')
<div class="container py-4"><br><br>
  <h1 class="text-center mb-4" style="color:#C24242;">Registrar Cliente</h1>

  {{-- Errores de validación --}}
  @if ($errors->any())
    <div class="alert alert-danger" role="alert">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Card centrada --}}
  <div class="d-flex justify-content-center">
    <div class="card card-form shadow-sm w-100">
      <div class="card-body p-4">
        <form action="{{ route('clientes.store') }}" method="POST" novalidate>
          @csrf

          <div class="mb-3">
            <label for="nombre" class="form-label fw-semibold">Nombre:</label>
            <input type="text"
                   class="form-control"
                   name="nombre" id="nombre"
                   value="{{ old('nombre') }}"
                   required autofocus>
          </div>

          <div class="mb-3">
            <label for="nit" class="form-label fw-semibold">NIT:</label>
            <input type="text"
                   class="form-control"
                   name="nit" id="nit"
                   value="{{ old('nit') }}">
          </div>

          <div class="mb-3">
            <label for="telefono" class="form-label fw-semibold">Teléfono:</label>
            <input type="text"
                   class="form-control"
                   name="telefono" id="telefono"
                   value="{{ old('telefono') }}"
                   required>
          </div>

          <div class="mb-4">
            <label for="direccion" class="form-label fw-semibold">Dirección:</label>
            <input type="text"
                   class="form-control"
                   name="direccion" id="direccion"
                   value="{{ old('direccion') }}">
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-theme">
              Guardar
            </button>
            <a href="{{ route('clientes.index') }}" class="btn btn-outline-dark">
              Cancelar
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
