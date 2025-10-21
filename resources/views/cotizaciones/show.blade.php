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

  /* Estilos para impresión */
  @media print {
    body * {
      visibility: hidden;
    }
    .printable-area, .printable-area * {
      visibility: visible;
    }
    .printable-area {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      background: white;
      box-shadow: none;
      margin: 0;
      padding: 20px;
    }
    .no-print {
      display: none !important;
    }
    .table thead th {
      background: #9F3B3B !important;
      color: white !important;
      -webkit-print-color-adjust: exact;
    }
    .tfoot-total {
      background: #9F3B3B !important;
      color: white !important;
      -webkit-print-color-adjust: exact;
    }
    .badge-pill {
      border: 1px solid #ccc !important;
    }
  }
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

  <div class="md-card mt-3 printable-area">
    {{-- Encabezado / acciones --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
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

        {{-- Botón Imprimir con funcionalidad --}}
        <button type="button" class="btn btn-outline-success" onclick="imprimirCotizacion()" title="Imprimir cotización">
          <i class="bi bi-printer me-1"></i> Imprimir
        </button>

        <a href="{{ route('cotizaciones.index') }}" class="btn btn-darksoft">
          <i class="bi bi-arrow-left-short me-1"></i> Volver
        </a>
      </div>
    </div>

{{-- Encabezado para impresión con tabla --}}
<div class="print-only" style="display: none; margin-bottom: 25px;">
  <table style="width: 100%; border-collapse: collapse;">
    <tr>
      {{-- Columna izquierda: Información de contacto --}}
      <td style="width: 33%; vertical-align: top; text-align: left;">
        <h5 style="color: #9F3B3B; font-size: 14px; font-weight: bold; margin: 0 0 5px 0;">
          CENTRO DE SERVICIO RAQUELITA
        </h5>
        <p style="font-size: 11px; line-height: 1.2; margin: 0 0 3px 0;">
          Calle Principal, Colonia 15 de Abril<br>
          Santo Tomás de Castilla, Puerto Barrios<br>
          Guatemala
        </p>
        <p style="font-size: 11px; line-height: 1.2; margin: 0;">
          <strong>Teléfono:</strong> (502) 7945-3982
        </p>
      </td>
      
      {{-- Columna central: Información de cotización --}}
      <td style="width: 34%; vertical-align: top; text-align: center;">
        <h2 style="font-size: 18px; font-weight: bold; margin: 0 0 5px 0;">
          Cotización #{{ $cotizacione->id }}
        </h2>
        <p style="font-size: 12px; margin: 0 0 2px 0;">
          <strong>Fecha:</strong> {{ optional($cotizacione->fecha_creacion)->format('Y-m-d H:i') ?? '—' }}
        </p>
        <p style="font-size: 12px; margin: 0;">
          <strong>Estado:</strong> 
          @if($cotizacione->estado_id == 6)
            Aprobada
          @elseif($cotizacione->estado_id == 7)
            Rechazada
          @elseif($cotizacione->estado_id == 4)
            Pendiente
          @else
            —
          @endif
        </p>
      </td>
      
      {{-- Columna derecha: Logo --}}
      <td style="width: 33%; vertical-align: top; text-align: right;">
        @if(file_exists(public_path('img/logo-raquelita.png')))
          <img src="{{ asset('img/logo-raquelita.png') }}" 
               alt="Logo" 
               style="max-height: 80px; max-width: 120px;">
        @else
          <div style="border: 1px dashed #ccc; padding: 8px; display: inline-block; text-align: center;">
            <small style="font-size: 10px;">Logo</small>
          </div>
        @endif
      </td>
    </tr>
  </table>
  <hr style="margin: 10px 0; border-top: 2px solid #9F3B3B;">
</div>
    {{-- Acciones (solo pendiente) --}}
    @if($cotizacione->estado_id == 4)
      <div class="mb-3 no-print">
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

    {{-- Pie de página para impresión --}}
    <div class="mt-5 pt-4 border-top print-only" style="display: none;">
      <div class="row">
        <div class="col-6 text-center">
          <p><strong>Observaciones</strong></p>
          <p>_____________________________________________________________________________________________</p>
          <p>_____________________________________________________________________________________________</p>
          <p>_____________________________________________________________________________________________</p>
        </div>
      </div>
      <div class="text-center mt-3 text-muted">
        <small>Cotización generada el <span id="fecha-local"></span></small>
      </div>
    </div>

    <script>
    function imprimirCotizacion() {
      // Obtener fecha y hora local del usuario
      const ahora = new Date();
      const fechaLocal = ahora.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      });
      const horaLocal = ahora.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
      });
      
      // Actualizar el elemento con la fecha local
      document.getElementById('fecha-local').textContent = `${fechaLocal} ${horaLocal}`;

      // Mostrar elementos de impresión
      const printElements = document.querySelectorAll('.print-only');
      printElements.forEach(el => {
        el.style.display = 'block';
      });

      // Ocultar elementos que no se deben imprimir
      const noPrintElements = document.querySelectorAll('.no-print');
      noPrintElements.forEach(el => {
        el.style.display = 'none';
      });

      // Ejecutar la impresión
      window.print();

      // Restaurar la vista normal después de imprimir
      setTimeout(() => {
        printElements.forEach(el => {
          el.style.display = 'none';
        });
        noPrintElements.forEach(el => {
          el.style.display = '';
        });
      }, 500);
    }

    // También actualizar para los eventos de impresión del navegador
    window.addEventListener('beforeprint', () => {
      const ahora = new Date();
      const fechaLocal = ahora.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      });
      const horaLocal = ahora.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
      });
      
      document.getElementById('fecha-local').textContent = `${fechaLocal} ${horaLocal}`;

      const printElements = document.querySelectorAll('.print-only');
      printElements.forEach(el => {
        el.style.display = 'block';
      });
      const noPrintElements = document.querySelectorAll('.no-print');
      noPrintElements.forEach(el => {
        el.style.display = 'none';
      });
    });
    </script>
@endsection