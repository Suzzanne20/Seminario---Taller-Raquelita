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
@section('title','Lista de inspecciones')

@section('content')
<style>
  :root{
    --brand:#000000;        /* barra superior negra */
    --ink:#0f172a;          /* texto principal */
    --muted:#64748b;        /* texto suave */
    --line:#e5e7eb;         /* líneas */
    --panel:#ffffff;        /* tarjetas */
    --bg:#f7f8fb;           /* fondo */
    --ring:0 0 0 4px rgba(0,0,0,.08);
  }

  /* ===== Página & contenedor ===== */
  body{ background:var(--bg); }
  .page-wrap{ padding-block:26px; }
  .page-header{
    display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;
    margin-bottom:14px;
  }

  .page-title{
    margin:0;
    font-weight:800;
    letter-spacing:.2px;
    color:var(--ink);
    display:flex; align-items:center; gap:10px;
  }
  .page-title .dot{
    width:8px;height:8px;border-radius:999px;background:#111;display:inline-block;
  }

  /* ===== Tarjeta “glass” para el listado ===== */
  .card-glass{
    background:linear-gradient(180deg, rgba(255,255,255,.92), #fff);
    border:1px solid rgba(255,255,255,.8);
    border-radius:18px;
    box-shadow:0 18px 40px rgba(0,0,0,.08);
    overflow:hidden;
  }

  /* ===== Toolbar (buscador) ===== */
  .toolbar{
    display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap;
    padding:14px 16px; border-bottom:1px solid var(--line); background:#fff;
  }
  .search{
    position:relative; display:flex; align-items:center; gap:10px;
    background:#fff; border:1px solid var(--line); border-radius:999px;
    padding:8px 12px; min-width:280px; max-width:420px; width:100%;
    box-shadow:0 6px 18px rgba(0,0,0,.04);
  }
  .search input{
    border:none; outline:none; width:100%; background:transparent; color:var(--ink);
  }
  .search .ico{
    width:18px; height:18px; opacity:.65;
  }
  .toolbar .btn{
    border-radius:999px;
  }
  .btn-reset{
    color:#334155; background:#f8fafc; border:1px solid #e2e8f0;
  }
  .btn-reset:hover{ background:#eef2f7; border-color:#cbd5e1; }

  /* ===== Tabla ===== */
  .table-wrap{ padding: 10px 14px 4px; }
  .table-white{
    --bs-table-bg: #fff;
    --bs-table-striped-bg: #fafafa;
    border-radius:14px; overflow:hidden;
  }
  .table-white thead th{
    background:#fafafa!important; color:#0f172a; border-bottom:1px solid var(--line)!important;
    font-weight:800;
  }
  .table-white tbody td{
    border-bottom:1px solid #f1f5f9!important;
    color:var(--ink);
    vertical-align: middle;
  }
  .table-white tbody tr:hover td{
    background:#fdfdfd;
  }

  /* ===== Chips ===== */
  .chip{
    display:inline-block; padding:.35rem .55rem; border:1px solid var(--line);
    border-radius:999px; background:#f8fafc; font-weight:700; color:#0f172a;
  }
  .chip-blue{ background:#eef6ff; border-color:#dbeafe; color:#0a58ca; }

  /* ===== Botones de acción (pill) ===== */
  .btn-pill{ border-radius:999px !important; padding:.4rem .75rem; }
  .btn-view{ background:#fff; color:#475569; border:1px solid #e5e7eb; }
  .btn-view:hover{ background:#f8fafc; border-color:#d1d5db; color:#111827; }

  .btn-edit{ background:#fff; color:#0d6efd; border:1px solid #b6d4fe; }
  .btn-edit:hover{ background:#eef6ff; border-color:#0d6efd; color:#0a58ca; }

  .btn-delete{ background:#fff; color:#dc3545; border:1px solid #f1c0c0; }
  .btn-delete:hover{ background:#fff5f5; border-color:#dc3545; color:#b02a37; }

  /* ===== Paginación ===== */
  .pager{
    padding:10px 16px; border-top:1px solid var(--line); background:#fff;
    display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;
  }
  .small-muted{ color:var(--muted); }

  /* Acciones responsivas */
  @media (max-width:576px){
    .actions-stack{ display:flex; gap:.5rem; flex-wrap:wrap; justify-content:flex-end; }
    .actions-stack form{ display:inline; }
  }
</style>

<div class="page-wrap">
  <div class="container">

    {{-- Encabezado --}}
    <div class="page-header">
      <h2 class="page-title">
        <span class="dot"></span> Inspecciones
      </h2>
      <a href="{{ route('inspecciones.create') }}" class="btn btn-dark rounded-pill px-3 py-2">
        ➕ Nueva inspección
      </a>
    </div>

    {{-- Mensajes --}}
    @if (session('ok'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('ok') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- Tarjeta principal --}}
    <div class="card-glass">

      {{-- Toolbar / Buscador --}}
      <div class="toolbar">
        <form action="{{ route('inspecciones.index') }}" method="GET" class="d-flex flex-wrap gap-2">
          <div class="search">
            {{-- icono lupa --}}
            <svg class="ico" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M21 21l-4.35-4.35m1.1-4.9a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <input type="text"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Buscar por placa (ej. P123ABC)">
          </div>
          <button class="btn btn-dark rounded-pill px-3" type="submit">Buscar</button>
          @if(request()->filled('q'))
            <a class="btn btn-reset rounded-pill px-3" href="{{ route('inspecciones.index') }}">Limpiar</a>
          @endif
        </form>

        {{-- Acción rápida --}}
        <a href="{{ route('inspecciones.create') }}" class="btn btn-outline-dark rounded-pill px-3">
          Nueva
        </a>
      </div>

      {{-- Tabla --}}
      <div class="table-wrap">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 table-white">
            <thead>
              <tr>
                <th style="width:90px">ID</th>
                <th style="width:200px">Fecha</th>
                <th style="width:150px">Placa</th>
                <th style="width:120px">Tipo</th>
                <th>Observaciones</th>
                <th style="width:330px" class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($items as $r)
                <tr>
                  <td class="fw-bold">#{{ $r->id }}</td>
                  <td>{{ optional($r->fecha_creacion)->format('Y-m-d H:i') }}</td>
                  <td>
                    <span class="chip">{{ $r->vehiculo_placa }}</span>
                  </td>
                  <td>
                    <span class="chip chip-blue">{{ $r->type_vehiculo_id }}</span>
                  </td>
                  <td class="text-truncate" style="max-width:520px">
                    {{ \Illuminate\Support\Str::limit($r->observaciones, 140) }}
                  </td>
                  <td class="text-end">
                    {{-- Desktop --}}
                    <div class="btn-group d-none d-sm-inline-flex" role="group">
                      <a href="{{ route('inspecciones.show', $r) }}" class="btn btn-pill btn-view">
                        👁 Ver
                      </a>
                      <a href="{{ route('inspecciones.edit', $r) }}" class="btn btn-pill btn-edit">
                        ✏️ Editar
                      </a>
                      <form action="{{ route('inspecciones.destroy', $r) }}" method="POST"
                            onsubmit="return confirm('¿Eliminar la inspección #{{ $r->id }}?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-pill btn-delete">
                          🗑 Eliminar
                        </button>
                      </form>
                    </div>

                    {{-- Móvil --}}
                    <div class="actions-stack d-sm-none mt-2">
                      <a href="{{ route('inspecciones.show', $r) }}" class="btn btn-pill btn-view btn-sm">👁 Ver</a>
                      <a href="{{ route('inspecciones.edit', $r) }}" class="btn btn-pill btn-edit btn-sm">✏️ Editar</a>
                      <form action="{{ route('inspecciones.destroy', $r) }}" method="POST"
                            onsubmit="return confirm('¿Eliminar la inspección #{{ $r->id }}?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-pill btn-delete btn-sm">🗑 Eliminar</button>
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
      </div>

      {{-- Paginación --}}
      @if ($items->hasPages())
        <div class="pager">
          <small class="small-muted">
            Mostrando {{ $items->firstItem() }}–{{ $items->lastItem() }} de {{ $items->total() }}
            @if(request()->filled('q')) para la placa “{{ request('q') }}” @endif
          </small>
          {{-- Mantener el query `q` en la paginación --}}
          {{ $items->appends(['q'=>request('q')])->onEachSide(1)->links() }}
        </div>
      @endif

    </div>
  </div>
</div>
@endsection
