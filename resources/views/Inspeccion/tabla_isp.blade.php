@extends('layouts.app')

@section('title','Inspecciones')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  /* Paleta y componentes compartidos */
  :root{
    --brand:#9F3B3B;
  }
  .btn-theme{ background:#9F3B3B; border-color:#9F3B3B; color:#fff; border-radius:12px; padding:.55rem 1rem; }
  .btn-theme:hover{ background:#873131; border-color:#873131; color:#fff; }
  .control{ height:40px; border-radius:12px; }

  .page-header{ text-align:center; margin:64px 0 20px; }
  .page-header h1{ font-weight:800; color:#C24242; letter-spacing:.2px; }

  .toolbar{
    max-width:1150px; margin:0 auto 14px;
    display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap;
  }

  .card-shell{ max-width:1150px; margin:0 auto; border-radius:16px; overflow:hidden; }

  .table thead.table-dark th{ background:#1e1e1e; border-color:#1e1e1e; }
  .table td,.table th{ vertical-align:middle; }

  .btn-round{ width:38px; height:38px; border-radius:999px; display:inline-grid; place-items:center; }

  .pagination .page-link{ color:#1d1d1d; border-color:#e9ecef; }
  .pagination .page-link:hover{ color:#1d1d1d; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d; color:#fff; }
  .pagination .page-item.disabled .page-link{ color:#adb5bd; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-link:focus{ box-shadow:0 0 0 .15rem rgba(159,59,59,.15); }

  .chip{ display:inline-block; padding:.25rem .55rem; border-radius:999px; background:#eef2f7; color:#111; border:1px solid #e5e7eb; font-weight:600; }
</style>
@endpush

@section('content')

  {{-- Título --}}
  <div class="page-header">
    <h1>Inspecciones</h1>
  </div>

  {{-- Toolbar: botón + buscador --}}
  <div class="toolbar">
    <a href="{{ route('inspecciones.create') }}" class="btn btn-theme">
      <i class="bi bi-plus-lg me-1"></i> Nueva inspección
    </a>

    <form action="{{ route('inspecciones.index') }}" method="GET" class="d-flex align-items-center gap-2">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control control"
               placeholder="Buscar por placa (ej. P123ABC)">
      </div>
      <button class="btn btn-dark control" type="submit" style="border-radius:12px;">Buscar</button>
      @if(request()->filled('q'))
        <a href="{{ route('inspecciones.index') }}" class="btn btn-outline-secondary control" style="border-radius:12px;">
          Limpiar
        </a>
      @endif
    </form>
  </div>

  {{-- Tabla --}}
  <div class="card card-shell shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th style="width:90px">ID</th>
            <th style="width:200px">Fecha</th>
            <th style="width:150px">Placa</th>
            <th style="width:140px">Tipo</th>
            <th>Observaciones</th>
            <th class="text-end" style="width:160px">Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse($items as $r)
          <tr>
            <td class="fw-semibold">{{ $r->id }}</td>
            <td>
              @php
                $fc = $r->fecha_creacion ?? ($r->created_at ?? null);
              @endphp
              {{ $fc ? \Illuminate\Support\Carbon::parse($fc)->format('d/m/Y H:i') : '—' }}
            </td>
            <td><span class="chip">{{ $r->vehiculo_placa ?? '—' }}</span></td>
            <td><span class="chip">{{ $r->type_vehiculo_id ?? '—' }}</span></td>
            <td class="text-start">
              {{ \Illuminate\Support\Str::limit($r->observaciones ?? '—', 120) }}
            </td>
            <td class="text-end">
              <div class="d-inline-flex gap-2">
                @if(Route::has('inspecciones.show'))
                <a href="{{ route('inspecciones.show', $r) }}" class="btn btn-outline-secondary btn-round" title="Ver">
                  <i class="bi bi-eye"></i>
                </a>
                @endif
                <a href="{{ route('inspecciones.edit', $r) }}" class="btn btn-outline-primary btn-round" title="Editar">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <form action="{{ route('inspecciones.destroy', $r) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar la inspección #{{ $r->id }}?');" class="d-inline">
                  @csrf @method('DELETE')
                  <button class="btn btn-outline-danger btn-round" title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">No hay inspecciones registradas.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{-- Paginación --}}
    @if ($items instanceof \Illuminate\Contracts\Pagination\Paginator)
      <div class="card-footer bg-white">
        {{ $items->appends(['q'=>request('q')])->links() }}
      </div>
    @endif
  </div>

  {{-- Íconos Bootstrap (si aún no están globales) --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

