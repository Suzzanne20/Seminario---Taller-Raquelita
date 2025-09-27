@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }
</style>
@endpush

@section('content')
    <div class="container">

        {{-- Mensaje de éxito --}}
        @if(session('ok'))
            <div class="alert alert-success shadow-sm" style="border-radius:12px;">
                {{ session('ok') }}
            </div>
        @endif

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold" style="color:#1E1E1E;">Cotización #{{ $cotizacion->id }}</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('cotizaciones.edit',$cotizacion->id) }}"
                   class="btn"
                   style="background-color:#B5747D; color:#fff; border-radius:10px;">
                    Editar
                </a>
                <a href="{{ route('cotizaciones.index') }}"
                   class="btn"
                   style="background-color:#1E1E1E; color:#fff; border-radius:10px;">
                    Volver
                </a>
            </div>
        </div>

        {{-- Botón aprobar (solo si está pendiente) --}}
        @if($cotizacion->estado && strtolower($cotizacion->estado->nombre) === 'pendiente')
            <form action="{{ route('cotizaciones.aprobar',$cotizacion->id) }}" method="POST" class="mb-3">
                @csrf
                <button class="btn"
                        style="background-color:#C24242; color:#fff; border-radius:20px; padding:8px 18px;">
                    Aprobar y generar OT
                </button>
            </form>
        @endif

        {{-- Estado --}}
        @if($cotizacion->estado)
            @php $estado = strtolower($cotizacion->estado->nombre); @endphp
            @if($estado === 'aprobada')
                <span class="badge bg-success">Aprobada</span>
            @elseif($estado === 'rechazada')
                <span class="badge bg-danger">Rechazada</span>
            @elseif($estado === 'pendiente')
                <span class="badge bg-warning text-dark">Pendiente</span>
            @endif
        @endif

        {{-- Datos generales --}}
        <div class="card shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body">
                <p><strong>Fecha:</strong> {{ $cotizacion->fecha_creacion?->format('Y-m-d H:i') }}</p>
                <p><strong>Servicio:</strong> {{ $cotizacion->servicio?->descripcion }}</p>
                <p><strong>Descripción:</strong> {{ $cotizacion->descripcion }}</p>
            </div>
        </div>

        {{-- Insumos --}}
        <h5 class="fw-bold mb-3" style="color:#1E1E1E;">Detalle de insumos</h5>
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle">
                <thead style="background-color:#9F3B3B; color:#fff;">
                <tr>
                    <th>Insumo</th>
                    <th>Cantidad</th>
                    <th>P. Unit. (Q)</th>
                    <th>Subtotal (Q)</th>
                </tr>
                </thead>
                <tbody>
                @php $sub=0; @endphp
                @foreach($cotizacion->insumos as $insumo)
                    @php
                        $line = (float)$insumo->precio * (float)$insumo->pivot->cantidad;
                        $sub += $line;
                    @endphp
                    <tr>
                        <td>{{ $insumo->nombre }}</td>
                        <td>{{ $insumo->pivot->cantidad }}</td>
                        <td>{{ number_format($insumo->precio,2) }}</td>
                        <td>{{ number_format($line,2) }}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                <tr style="background:#F4EFEE;">
                    <th colspan="3" class="text-end">Subtotal insumos</th>
                    <th>Q {{ number_format($sub,2) }}</th>
                </tr>
                <tr style="background:#F4EFEE;">
                    <th colspan="3" class="text-end">Mano de Obra</th>
                    <th>Q {{ number_format($cotizacion->costo_mo ?? 0,2) }}</th>
                </tr>
                <tr style="background:#9F3B3B; color:#fff;">
                    <th colspan="3" class="text-end">TOTAL</th>
                    <th>Q {{ number_format($cotizacion->total,2) }}</th>
                </tr>
                </tfoot>
            </table>
        </div>

    </div>
@endsection
