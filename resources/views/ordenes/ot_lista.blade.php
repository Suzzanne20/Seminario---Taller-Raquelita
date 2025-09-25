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
    <div class="container" style="padding:20px;"> <br><br>

        <h1 style="color:#C24242;">Ordenes de Trabajo</h1>

        <!-- Mensaje de éxito -->
        @if(session('success'))
            <div style="background:#D4EDDA; color:#155724; padding:10px; border-radius:8px; margin:10px 0;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Botón para registrar -->
        <div style="margin:15px 0;">
            <a href="{{ route('clientes.create') }}"
               style="background:#9F3B3B; color:white; padding:8px 15px; border-radius:6px; text-decoration:none;">
                + Nueva Orden
            </a>
        </div>

        <!-- Tabla de clientes -->
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <thead style="background:#1E1E1E; color:white;">
            <tr>
                <th style="padding:10px; text-align:left;">ID</th>
                <th style="padding:10px; text-align:left;">Fecha</th>
                <th style="padding:10px; text-align:left;">Placa</th>
                <th style="padding:10px; text-align:left;">Tipo de Servicio</th>
                <th style="padding:10px; text-align:left;">Kilometraje</th>
                <th style="padding:10px; text-align:left;">Prox. Servicio</th>
                <th style="padding:10px; text-align:left;">Estado</th>
                <th style="padding:10px; text-align:center;">Acciones</th>
            </tr>
            </thead>
<tbody>
@forelse($ordenes as $orden)
    <tr>
        <td>{{ $orden->id }}</td>
        <td>{{ $orden->fecha_creacion->format('d/m/Y') }}</td>
        <td>{{ $orden->descripcion }}</td>
        <td>{{ $orden->costo_mo }}</td>
        <td>{{ $orden->total }}</td>
        <td>{{ $orden->servicio->descripcion ?? 'N/A' }}</td>
        <td>{{ $orden->cotizacion->descripcion ?? 'N/A' }}</td>
        <td>
            <a href="{{ route('ordenes.edit', $orden->id) }}">Editar</a>
            <form action="{{ route('ordenes.destroy', $orden->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('¿Eliminar orden?')">Eliminar</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8">No hay órdenes registradas.</td>
    </tr>
@endforelse
</tbody>
        </table>
    </div>
@endsection
