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
    <div class="container" style="padding: 20px;">
        <br><br>
        <div class="card">
            <div class="card-header">
                <h4>Registrar Nuevo Insumo</h4>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div style="background:#F8D7DA; color:#721C24; padding:10px; border-radius:8px; margin:10px 0;">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('insumos.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="50">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="costo" class="form-label">Costo</label>
                            <input type="number" step="0.01" class="form-control" id="costo" name="costo" value="{{ old('costo') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" value="{{ old('stock_minimo') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required maxlength="200">{{ old('descripcion') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type_insumo_id" class="form-label">Tipo de Insumo</label>
                            <select class="form-control" id="type_insumo_id" name="type_insumo_id" required>
                                <option value="">-- Seleccione un tipo --</option>
                                @foreach($tiposInsumo as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('type_insumo_id') == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio" value="{{ old('precio') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="background-color: #C24242; border-color: #C24242;">Registrar Insumo</button>
                    <a href="{{ route('insumos.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
@endsection
