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

  {{-- Título --}}
  <div class="container"><br><br>
    <h1 class="text-center mb-4" style="color:#C24242;">Cotizaciones</h1>
  </div>

  {{-- Toolbar: botón + buscador --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <a href="{{ route('cotizaciones.create') }}"
       class="btn btn-theme"
       style="border-radius:12px; padding:.55rem 1rem;">
      <i class="bi bi-plus-lg me-1"></i> Nueva cotización
    </a>

    <form action="{{ route('cotizaciones.index') }}" method="GET" class="d-flex align-items-center gap-2">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control"
               placeholder="Buscar por descripción o # de cotización…"
               value="{{ request('q') }}">
      </div>
      <button class="btn btn-dark" type="submit" style="border-radius:12px;">Buscar</button>
    </form>
  </div>

  {{-- Mensaje --}}
  @if(session('ok'))
    <div class="alert alert-success shadow-sm rounded-3">
      {{ session('ok') }}
    </div>
  @endif

  {{-- Tabla --}}
  <div class="table-responsive shadow-sm rounded-3">
    <table class="table table-hover align-middle mb-0 text-center">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Fecha</th>
          <th class="text-start">Descripción</th>
          <th>Servicio</th>
          <th>Total (Q)</th>
          <th>Estado</th>   {{-- ⬅ NUEVO --}}
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($cotizaciones as $c)
          <tr>
            <td class="fw-semibold">{{ $c->id }}</td>
            <td>{{ $c->fecha_creacion ? \Illuminate\Support\Carbon::parse($c->fecha_creacion)->format('d/m/Y H:i') : '—' }}</td>
            <td class="text-start">{{ $c->descripcion ?? '—' }}</td>
            <td>{{ $c->servicio?->descripcion ?? '—' }}</td>
            <td><strong>Q {{ number_format($c->total ?? 0, 2) }}</strong></td>
            <td>
              <span class="badge bg-{{ $c->estado?->badge_class ?? 'secondary' }}">
                {{ $c->estado?->nombre ?? '—' }}
              </span>
            </td>
            <td class="text-center">
              <div class="d-inline-flex gap-2">
                <a href="{{ route('cotizaciones.show', $c->id) }}" class="btn btn-sm btn-outline-secondary" title="Ver">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('cotizaciones.edit', $c->id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <form action="{{ route('cotizaciones.destroy', $c->id) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar la cotización #{{ $c->id }}?')">
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
            <td colspan="7" class="text-center py-4 text-muted">Sin cotizaciones registradas.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Paginación --}}
  <div class="mt-3 ">
    {{ $cotizaciones->links() }}
  </div>
</div>
{{-- Íconos Bootstrap --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
