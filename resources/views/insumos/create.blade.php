@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:rgba(255, 255, 255, 0.144) !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  .md-card{
    max-width: 720px;
    margin: 32px auto 64px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    padding:28px;
  }
  .md-title{
    font-weight:700; color:#C24242; text-align:center; margin-bottom:18px;
  }

  .form-control{
    border:none; border-bottom:2px solid #e6e6e6;
    border-radius:0; background:transparent; padding-left:0;
  }
  .form-select{
    border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent;
    padding-left:0;
  }
  .form-control:focus, .form-select:focus{
    box-shadow:none; border-color:#3f51b5;
  }
  .form-label{ font-size:.9rem; color:#6b7280; }
  .help{ font-size:.8rem; color:#9CA3AF; }

  .btn-theme{ background:#9F3B3B; border:none; color:#fff; }
  .btn-theme:hover{ background:#873131; color:#fff; }
  .btn-muted{ background:#e5e7eb; color:#111827; border:none; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">
    <h2 class="md-title">Registrar Nuevo Insumo</h2>

    {{-- Errores de validación --}}
    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('insumos.store') }}" method="POST" novalidate>
      @csrf

      <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input id="nombre" name="nombre" type="text"
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre') }}" required maxlength="50" autofocus>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label for="costo" class="form-label">Costo (Q)</label>
          <input id="costo" name="costo" type="number" step="0.01" min="0"
                 class="form-control @error('costo') is-invalid @enderror"
                 value="{{ old('costo') }}" placeholder="0.00">
        </div>

      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-4">
          <label for="stock" class="form-label">Stock</label>
          <input id="stock" name="stock" type="number" min="0"
                 class="form-control @error('stock') is-invalid @enderror"
                 value="{{ old('stock') }}" required>
        </div>
        <div class="col-md-4">
          <label for="stock_minimo" class="form-label">Stock mínimo</label>
          <input id="stock_minimo" name="stock_minimo" type="number" min="0"
                 class="form-control @error('stock_minimo') is-invalid @enderror"
                 value="{{ old('stock_minimo') }}" required>
        </div>
        <div class="col-md-4">
            <label for="type_insumo_id" class="form-label">Tipo de insumo</label>
            <select id="type_insumo_id" name="type_insumo_id"
                    class="form-select @error('type_insumo_id') is-invalid @enderror" required>
            <option value="">— Seleccione un tipo —</option>
            @foreach($tiposInsumo as $tipo)
                <option value="{{ $tipo->id }}" {{ old('type_insumo_id') == $tipo->id ? 'selected' : '' }}>
                {{ $tipo->nombre }}
                </option>
            @endforeach
            </select>
        </div>
      </div>

      <div class="mt-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3" maxlength="200" required
                  class="form-control @error('descripcion') is-invalid @enderror"
                  placeholder="Detalle breve del insumo…">{{ old('descripcion') }}</textarea>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-theme px-4">Registrar</button>
        <a href="{{ route('insumos.index') }}" class="btn btn-muted px-4">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection

