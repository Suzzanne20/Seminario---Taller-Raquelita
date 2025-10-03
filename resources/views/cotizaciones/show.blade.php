@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  .md-card{
    max-width: 980px;
    margin: 32px auto 64px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    padding:24px;
  }

  .btn-theme{ background:#9F3B3B; color:#fff; border:none; }
  .btn-theme:hover{ background:#873131; color:#fff; }
  .btn-darksoft{ background:#1E1E1E; color:#fff; border:none; }
  .btn-darksoft:hover{ background:#111; color:#fff; }
  .btn-outline-soft{ border-color:#d1d5db; color:#374151; }
  .btn-outline-soft:hover{ background:#f3f4f6; color:#111827; }

  .badge-pill{ border-radius:999px; padding:.45rem .8rem; font-weight:600; }
  .badge-pend{ background:#fff7cd; color:#8a6d3b; border:1px solid #f1e0a6; }
  .badge-aprob{ background:#dcfce7; color:#166534; border:1px solid #b7f0c8; }
  .badge-rech{ background:#fee2e2; color:#991b1b; border:1px solid #f5baba; }

  .table thead th{
    background:#9F3B3B; color:#fff; border:0;
  }
  .table tfoot th{
    border-top:0; font-weight:700;
  }
  .tfoot-sub{ background:#f6f3f2; }
  .tfoot-total{ background:#9F3B3B; color:#fff; }
</style>
@endpush

@section('content')
<div class="container">
  {{-- Flash de éxito --}}
  @if(session('ok'))
    <div class="alert alert-success shadow-sm mt-3" style="border-radius:12px;">
      {{ session('ok') }}
    </div>
  @endif

  <div class="md-card mt-3">
    {{-- Encabezado / acciones --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div class="d-flex align-items-center gap-3">
        <h3 class="m-0 fw-bold">Cotización #{{ $cotizacione->id }}</h3>

        {{-- Estado --}}
        @if($cotizacione->estado_id)
          @if($cotizacione->estado_id == 6)
            <span class="badge badge-pill badge-aprob">Aprobada</span>
          @elseif($cotizacione->estado_id == 7)
            <span class="badge badge-pill badge-rech">Rechazada</span>
          @elseif($cotizacione->estado_id == 4)
            <span class="badge badge-pill badge-pend">Pendiente</span>
          @endif
        @endif
      </div>

      <div class="d-flex gap-2">
        <a href="{{ route('cotizaciones.edit',$cotizacione->id) }}" class="btn btn-outline-soft">
          <i class="bi bi-pencil-square me-1"></i> Editar
        </a>

        {{-- Botón “Imprimir” aún sin funcionalidad --}}
        <button type="button" class="btn btn-outline-soft" title="Imprimir (próximamente)" disabled>
          <i class="bi bi-printer me-1"></i> Imprimir
        </button>

        <a href="{{ route('cotizaciones.index') }}" class="btn btn-darksoft">
          <i class="bi bi-arrow-left-short me-1"></i> Volver
        </a>
      </div>
    </div>

    {{-- Acciones (solo pendiente) --}}
    @if($cotizacione->estado_id == 4)
      <div class="mb-3">
        <form action="{{ route('cotizaciones.aprobar',$cotizacione->id) }}" method="POST" class="d-inline">
          @csrf
          <button class="btn btn-success" style="border-radius:999px; padding:.45rem 1rem;">
            <i class="bi bi-check2-circle me-1"></i> Aprobar y generar OT
          </button>
        </form>
        <form action="{{ route('cotizaciones.rechazar',$cotizacione->id) }}" method="POST" class="d-inline">
          @csrf
          <button class="btn btn-danger" style="border-radius:999px; padding:.45rem 1rem;">
            <i class="bi bi-x-circle me-1"></i> Rechazar
          </button>
        </form>
      </div>
    @endif

    {{-- Datos generales --}}
    <div class="row g-3 mb-3">
      <div class="col-md-4">
        <div class="small text-muted">Fecha</div>
        <div class="fw-semibold">
          {{ optional($cotizacione->fecha_creacion)->format('Y-m-d H:i') ?? '—' }}
        </div>
      </div>
      <div class="col-md-4">
        <div class="small text-muted">Servicio</div>
        <div class="fw-semibold">
          {{ $cotizacione->servicio?->descripcion ?? '—' }}
        </div>
      </div>
      <div class="col-12">
        <div class="small text-muted">Descripción</div>
        <div class="fw-semibold">{{ $cotizacione->descripcion ?: '—' }}</div>
      </div>
    </div>

    {{-- Detalle de insumos --}}
    <h5 class="fw-bold mb-2">Detalle de insumos</h5>
    <div class="table-responsive shadow-sm rounded">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Insumo</th>
            <th class="text-center">Cantidad</th>
            <th class="text-end">P. Unit. (Q)</th>
            <th class="text-end">Subtotal (Q)</th>
          </tr>
        </thead>
        <tbody>
          @php $sub = 0; @endphp
          @forelse($cotizacione->insumos as $insumo)
            @php
              $line = (float)($insumo->precio ?? 0) * (float)($insumo->pivot->cantidad ?? 0);
              $sub += $line;
            @endphp
            <tr>
              <td>{{ $insumo->nombre }}</td>
              <td class="text-center">{{ $insumo->pivot->cantidad }}</td>
              <td class="text-end">{{ number_format($insumo->precio ?? 0, 2) }}</td>
              <td class="text-end">{{ number_format($line, 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted">Sin insumos asociados.</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr class="tfoot-sub">
            <th colspan="3" class="text-end">Subtotal insumos</th>
            <th class="text-end">Q {{ number_format($sub, 2) }}</th>
          </tr>
          <tr class="tfoot-sub">
            <th colspan="3" class="text-end">Mano de obra</th>
            <th class="text-end">Q {{ number_format($cotizacione->costo_mo ?? 0, 2) }}</th>
          </tr>
          <tr class="tfoot-total">
            <th colspan="3" class="text-end">TOTAL</th>
            <th class="text-end">Q {{ number_format($cotizacione->total ?? ($sub + ($cotizacione->costo_mo ?? 0)), 2) }}</th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>
@endsection

