@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  .btn-theme{ background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
  .btn-theme:hover{ background:#873131; border-color:#873131; color:#fff; }

  .pagination .page-link{ color:#1d1d1d; border-color:#e9ecef; }
  .pagination .page-link:hover{ color:#1d1d1d; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d; color:#fff; }
  .pagination .page-item.disabled .page-link{ color:#adb5bd; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-link:focus{ box-shadow:0 0 0 .15rem rgba(159,59,59,.15); }
</style>
@endpush

@section('content')
<div class="container py-4">

  <div class="container"><br><br>
    <h1 class="text-center mb-4" style="color:#C24242;">Órdenes de Trabajo</h1>
  </div>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <a href="{{ route('ordenes.create') }}"
     class="btn"
     style="background:#9F3B3B; border-color:#9F3B3B; color:#fff; border-radius:12px; padding:.55rem 1rem;">
    <i class="bi bi-plus-lg me-1"></i> Nueva Orden
  </a>

  <form action="{{ route('ordenes.index') }}" method="GET" class="d-flex align-items-center gap-2">
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" name="q" class="form-control" placeholder="Buscar por placa…"
             value="{{ request('q') }}">
    </div>
    <button class="btn btn-dark" type="submit" style="border-radius:12px;">Buscar</button>
  </form>
</div>

  {{-- Mensaje de éxito --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm rounded-3">
      {{ session('success') }}
    </div>
  @endif

  <div class="table-responsive shadow-sm rounded-3">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Fecha</th>
          <th>Placa</th>
          <th>Tipo de Servicio</th>
          <th>Kilometraje</th>
          <th>Próx. Servicio</th>
          <th>Estado</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
      @forelse ($ordenes as $ot)
        <tr>
          <td>{{ $ot->id }}</td>
          <td>
            @php
              $fc = $ot->fecha_creacion ? \Illuminate\Support\Carbon::parse($ot->fecha_creacion)->format('d/m/Y H:i') : '—';
            @endphp
            {{ $fc }}
          </td>

          <td>{{ $ot->vehiculo->placa ?? '—' }}</td>
          <td>{{ $ot->servicio->descripcion ?? '—' }}</td>
          <td>{{ $ot->kilometraje ?? '—' }}</td>
          <td>{{ $ot->proximo_servicio ?? '—' }}</td>

          <td>
            <span class="badge bg-{{ $ot->estado->badge_class ?? 'dark' }}"> 
              {{ $ot->estado->nombre ?? '—' }}
            </span>
          </td>

          <td class="text-center">
            <div class="d-inline-flex gap-2">
              <a href="{{ route('ordenes.edit', $ot->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil-square"></i>
              </a>
              <form action="{{ route('ordenes.destroy', $ot->id) }}" method="POST"
                    onsubmit="return confirm('¿Eliminar la orden #{{ $ot->id }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-4">No hay órdenes registradas.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- paginación --}}
  <div class="mt-3">
    {{ $ordenes->links() }}
  </div>
</div>
@endsection

