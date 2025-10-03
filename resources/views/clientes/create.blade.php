@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

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

  .form-control{
    border:none; border-bottom:2px solid #e6e6e6;
    border-radius:0; background:transparent; padding-left:0;
  }
  .form-control:focus{
    box-shadow:none; border-color:#3f51b5;
  }
  .form-label{ font-size:.9rem; color:#6b7280; }
  .help{ font-size:.8rem; color:#9CA3AF; }

  .btn-md-primary{ background:#9F3B3B; border:none; }
  .btn-md-secondary{ background:#e5e7eb; color:#111827; border:none; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">
    <h2 class="md-title">Registrar Cliente</h2>

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

    <form action="{{ route('clientes.store') }}" method="POST" novalidate>
      @csrf

      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input id="nombre" name="nombre" type="text"
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre') }}" required maxlength="45" autofocus>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label for="nit" class="form-label">NIT</label>
          <input id="nit" name="nit" type="text"
                 class="form-control @error('nit') is-invalid @enderror"
                 value="{{ old('nit') }}" maxlength="20"
                 placeholder="CF o 1234567-8">

        </div>

        <div class="col-md-6">
          <label for="telefono" class="form-label">Teléfono</label>
          <input id="telefono" name="telefono" type="text"
                 class="form-control @error('telefono') is-invalid @enderror"
                 value="{{ old('telefono') }}" required maxlength="20"
                 placeholder="Ej. 5555-5555">
        </div>
      </div>

      <div class="mt-3">
        <label for="direccion" class="form-label">Dirección</label>
        <input id="direccion" name="direccion" type="text"
               class="form-control @error('direccion') is-invalid @enderror"
               value="{{ old('direccion') }}" maxlength="60"
               placeholder="Colonia, zona, municipio...">
        <div class="help">Opcional</div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-md-primary px-4 text-white">Guardar</button>
        <a href="{{ route('clientes.index') }}" class="btn btn-md-secondary px-4">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection


