@extends('layouts.app')

@push('styles')
    <style>
        .container { max-width: 600px; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px; }
        .card-header { background-color: #C24242; color: white; }
    </style>
@endpush

@section('content')
    <div class="container" style="padding: 20px;">
        <br><br>
        <div class="card">
            <div class="card-header">
                <h4>Editar Tipo de Insumo: {{ $tiposInsumo->nombre }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('tipo-insumos.update', $tiposInsumo->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Tipo de Insumo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ $tiposInsumo->nombre }}" required maxlength="255">
                    </div>
                    <button type="submit" class="btn btn-primary" style="background-color: #C24242; border-color: #C24242;">Guardar cambios</button>
                    <a href="{{ route('tipo-insumos.index') }}" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
@endsection
