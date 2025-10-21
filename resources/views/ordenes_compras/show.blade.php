@extends('layouts.app')

@push('styles')
    <style>
        html, body { height:100%; background:#f0f0f0 !important; }

        .md-card {
            max-width: 920px;
            margin: 32px auto 64px;
            background:#fff;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            padding:28px;
        }

        .md-title {
            font-weight: 700;
            color: #C24242;
            text-align: center;
            margin-bottom: 18px;
        }

        .label { font-size: .9rem; color: #6b7280; }
        .value { font-size: 1rem; font-weight: 600; color: #1f2937; }

        /*  Título sección detalles */
        h5.fw-bold {
            color: #1f2937 !important;
            font-weight: 700 !important;
            opacity: 1 !important;
        }

        /*  Encabezados tabla */
        .table-header,
        .table-header div {
            color: #1f2937 !important;
            font-weight: 600 !important;
            opacity: 1 !important;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        /*  Filas de detalle */
        .detalle-row {
            border-bottom: 1px solid #e5e7eb;
            padding: 8px 0;
            color: #1f2937 !important;
            opacity: 1 !important;
            font-size: 0.95rem;
        }

        /*  Total */
        .total-box {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            border: 2px solid #e6e6e6;
            text-align: right;
            font-weight: 700;
            font-size: 1.2rem;
            color: #9F3B3B !important;  /* Color corporativo */
            opacity: 1 !important;
        }

        .btn-muted {
            background:#e5e7eb;
            color:#111827;
            border:none;
            border-radius:12px;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="md-card">
            {{-- Encabezado --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <h2 class="md-title">Orden de Compra #{{ $orden->id }}</h2>
                <div class="d-flex gap-2">
                    <a href="javascript:window.print()" class="btn btn-dark">
                        <i class="bi bi-printer"></i> Imprimir
                    </a>
                    <a href="{{ route('ordenes_compras.index') }}" class="btn btn-muted">Volver</a>
                </div>
            </div>

            {{-- Información general --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="label">Fecha de Orden</div>
                    <div class="value">{{ \Carbon\Carbon::parse($orden->fecha_orden)->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-6">
                    <div class="label">Fecha de Entrega Esperada</div>
                    <div class="value">
                        {{ $orden->fecha_entrega_esperada ? \Carbon\Carbon::parse($orden->fecha_entrega_esperada)->format('d/m/Y') : '—' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="label">Proveedor</div>
                    <div class="value">{{ $orden->proveedor->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="label">Estado</div>
                    <div class="value">{{ ucfirst($orden->estado) }}</div>
                </div>
                <div class="col-12">
                    <div class="label">Observaciones</div>
                    <div class="value">{{ $orden->observaciones ?? '—' }}</div>
                </div>
            </div>

            {{-- Detalle de Insumos --}}
            <h5 class="fw-bold mb-2">Detalle de Insumos</h5>
            <div class="row table-header border-bottom pb-2 mb-2">
                <div class="col-5">Insumo</div>
                <div class="col-2 text-end">Cantidad</div>
                <div class="col-2 text-end">Precio Unit.</div>
                <div class="col-3 text-end">Subtotal</div>
            </div>
            @foreach($orden->detalles as $detalle)
                <div class="row detalle-row">
                    <div class="col-5">{{ $detalle->insumo->nombre ?? '—' }}</div>
                    <div class="col-2 text-end">{{ number_format($detalle->cantidad, 2) }}</div>
                    <div class="col-2 text-end">Q {{ number_format($detalle->precio_unitario, 2) }}</div>
                    <div class="col-3 text-end">Q {{ number_format($detalle->subtotal, 2) }}</div>
                </div>
            @endforeach

            {{-- Total --}}
            <div class="total-box mt-4">
                <div>Total: Q {{ number_format($orden->total, 2) }}</div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection
