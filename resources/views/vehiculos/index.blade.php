@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  .btn-theme{ background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
  .btn-theme:hover{ background:#873131; border-color:#873131; color:#fff; }
</style>
@endpush

@section('content')
<div class="container py-4">

  {{-- Título --}}
  <div class="container"><br><br>
    <h1 class="text-center mb-4" style="color:#C24242;">Gestión de Vehículos</h1>
  </div>

  {{-- Toolbar: botón + buscador --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="{{ route('vehiculos.create') }}"
       class="btn btn-theme"
       style="border-radius:12px; padding:.55rem 1rem;">
      <i class="bi bi-plus-lg me-1"></i> Agregar Vehículo
    </a>

    <form action="{{ route('vehiculos.index') }}" method="GET" class="d-flex align-items-center gap-2">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Buscar por placa, línea o marca…"
               value="{{ request('q') }}">
      </div>
      <button class="btn btn-dark" type="submit" style="border-radius:12px;">Buscar</button>
    </form>
  </div>

  {{-- Mensaje --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- Tabla --}}
  <div class="table-responsive shadow-sm rounded-3">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>Placa</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Línea</th>
          <th>Motor</th>
          <th>Cilindraje</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($vehiculos as $v)
          <tr>
            <td class="fw-semibold">{{ $v->placa }}</td>
            <td>{{ $v->marca->nombre ?? '—' }}</td>
            <td>{{ $v->modelo ?? '—' }}</td>
            <td>{{ $v->linea ?? '—' }}</td>
            <td>{{ $v->motor ?? '—' }}</td>
            <td>{{ $v->cilindraje ?? '—' }}</td>
            <td class="text-center">
              <div class="d-inline-flex gap-2">
                <a href="{{ route('vehiculos.edit', $v->placa) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <form action="{{ route('vehiculos.destroy', $v->placa) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar el vehículo {{ $v->placa }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-4">No hay vehículos registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Paginación --}}
  <div class="mt-3">
    {{ $vehiculos->links() }}
  </div>
</div>
@endsection



