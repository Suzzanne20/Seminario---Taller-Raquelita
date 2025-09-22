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
    <h2 class="mb-3">Gestión de Vehículos</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('vehiculos.create') }}" class="btn btn-primary mb-3">Agregar Vehículo</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Placa</th>
                <th>Marca</th>
                <th>Modelo</th>
                <th>Línea</th>
                <th>Motor</th>
                <th>Cilindraje</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vehiculos as $vehiculo)
                <tr>
                    <td>{{ $vehiculo->placa }}</td>
                    <td>{{ $vehiculo->marca ? $vehiculo->marca->nombre : 'Sin marca' }}</td>
                    <td>{{ $vehiculo->modelo }}</td>
                    <td>{{ $vehiculo->linea }}</td>
                    <td>{{ $vehiculo->motor }}</td>
                    <td>{{ $vehiculo->cilindraje }}</td>
                    <td>
                        <a href="{{ route('vehiculos.edit', $vehiculo->placa) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('vehiculos.destroy', $vehiculo->placa) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este vehículo y su marca?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No hay vehículos registrados</td></tr>
            @endforelse
        </tbody>
    </table>
</div>


@endsection
