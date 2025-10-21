@extends('layouts.app')

@push('styles')
    <style>
        html, body { height:100%; background:#f0f0f0 !important; }
        .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
        @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

        .md-card{
            max-width: 920px;
            margin: 32px auto 64px;
            background:#fff;
            border-radius:12px;
            box-shadow:0 10px 30px rgba(0,0,0,.08);
            padding:28px;
        }
        .md-title{
            font-weight:700; color:#C24242; text-align:center; margin-bottom:18px;
        }

        .form-control{
            border:none; border-bottom:2px solid #e6e6e6;
            border-radius:0; background:transparent; padding-left:0;
        }
        .form-select{
            border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent;
            padding-left:0;
        }
        .form-control:focus, .form-select:focus{
            box-shadow:none; border-color:#3f51b5;
        }
        .form-label{ font-size:.9rem; color:#6b7280; }
        .help{ font-size:.8rem; color:#9CA3AF; }

        .btn-theme{ background:#9F3B3B; border:none; color:#fff; }
        .btn-theme:hover{ background:#873131; color:#fff; }
        .btn-muted{ background:#e5e7eb; color:#111827; border:none; }

        .section-divider{
            border-top:2px solid #f3f4f6;
            margin:24px 0;
            padding-top:20px;
        }
        .section-title{
            font-size:1rem;
            font-weight:600;
            color:#9F3B3B;
            margin-bottom:16px;
        }

        .detalle-row {
            background:#f9fafb;
            padding:16px;
            border-radius:8px;
            margin-bottom:12px;
            border-left:3px solid #9F3B3B;
        }

        .total-box{
            background:#f9fafb;
            padding:16px;
            border-radius:8px;
            border:2px solid #e6e6e6;
            text-align:right;
        }
        .total-label{ font-size:.9rem; color:#6b7280; margin-bottom:4px; }
        #totalGeneral {
            font-size:1.8rem;
            font-weight:700;
            color:#9F3B3B;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="md-card">
            <h2 class="md-title">Registrar Nueva Orden de Compra</h2>

            {{-- Errores de validación --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('ordenes_compras.store') }}" method="POST" id="formOrdenCompra" novalidate>
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="fecha_orden" class="form-label">Fecha de Orden</label>
                        <input id="fecha_orden" name="fecha_orden" type="date"
                               class="form-control @error('fecha_orden') is-invalid @enderror"
                               value="{{ old('fecha_orden', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="fecha_entrega_esperada" class="form-label">Fecha Entrega Esperada</label>
                        <input id="fecha_entrega_esperada" name="fecha_entrega_esperada" type="date"
                               class="form-control @error('fecha_entrega_esperada') is-invalid @enderror"
                               value="{{ old('fecha_entrega_esperada') }}">
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-8">
                        <label for="proveedor_id" class="form-label">Proveedor</label>
                        <select id="proveedor_id" name="proveedor_id"
                                class="form-select @error('proveedor_id') is-invalid @enderror" required>
                            <option value="">— Seleccione un proveedor —</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                    {{ $proveedor->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" name="estado" class="form-select @error('estado') is-invalid @enderror" required>
                            <option value="pendiente" {{ old('estado', 'pendiente') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="aprobada" {{ old('estado') == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                            <option value="recibida" {{ old('estado') == 'recibida' ? 'selected' : '' }}>Recibida</option>
                            <option value="cancelada" {{ old('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3"
                              class="form-control @error('observaciones') is-invalid @enderror"
                              placeholder="Notas adicionales sobre la orden…">{{ old('observaciones') }}</textarea>
                </div>

                {{-- Detalle de Insumos --}}
                <div class="section-divider">
                    <h3 class="section-title">Detalle de Insumos</h3>

                    <div id="detallesContainer">
                        {{-- Aquí se agregarán dinámicamente los detalles --}}
                    </div>

                    <button type="button" id="btnAgregarDetalle" class="btn btn-outline-secondary btn-sm mt-2">
                        <i class="bi bi-plus-circle me-1"></i> Agregar Insumo
                    </button>
                </div>

                {{-- Total --}}
                <div class="total-box mt-3">
                    <div class="total-label">Total de la Orden</div>
                    <div id="totalGeneral">Q 0.00</div>
                    <input type="hidden" name="total" id="inputTotal" value="0">
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-theme px-4">Registrar</button>
                    <a href="{{ route('ordenes_compras.index') }}" class="btn btn-muted px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- JS Dinámico --}}
    <script>
        const insumos = @json($insumos);
        let detalleCounter = 0;

        document.getElementById('btnAgregarDetalle').addEventListener('click', agregarDetalle);

        function agregarDetalle() {
            const container = document.getElementById('detallesContainer');
            const div = document.createElement('div');
            div.className = 'detalle-row';
            div.dataset.index = detalleCounter;

            div.innerHTML = `
      <div class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label">Insumo</label>
          <select name="detalles[${detalleCounter}][insumo_id]" class="form-select detalle-insumo" required onchange="actualizarPrecio(this)">
            <option value="">— Seleccione insumo —</option>
            ${insumos.map(i => `<option value="${i.id}" data-precio="${i.costo}">${i.nombre}</option>`).join('')}
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Cantidad</label>
          <input type="number" name="detalles[${detalleCounter}][cantidad]" class="form-control detalle-cantidad"
                 step="0.01" min="0.01" value="1" required onchange="calcularSubtotal(this)">
        </div>
        <div class="col-md-2">
          <label class="form-label">Precio Unit.</label>
          <input type="number" name="detalles[${detalleCounter}][precio_unitario]" class="form-control detalle-precio"
                 step="0.01" min="0" value="0" required onchange="calcularSubtotal(this)">
        </div>
        <div class="col-md-2">
          <label class="form-label">Subtotal</label>
          <input type="text" class="form-control detalle-subtotal" readonly value="Q 0.00" style="font-weight:600;">
          <input type="hidden" name="detalles[${detalleCounter}][subtotal]" class="detalle-subtotal-value" value="0">
        </div>
        <div class="col-md-1">
          <button type="button" class="btn btn-danger btn-sm w-100" onclick="eliminarDetalle(this)" title="Eliminar">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    `;

            container.appendChild(div);
            detalleCounter++;
        }

        function actualizarPrecio(select) {
            const row = select.closest('.detalle-row');
            const precio = select.options[select.selectedIndex].dataset.precio || 0;
            const inputPrecio = row.querySelector('.detalle-precio');
            inputPrecio.value = parseFloat(precio).toFixed(2);
            calcularSubtotal(inputPrecio);
        }

        function calcularSubtotal(input) {
            const row = input.closest('.detalle-row');
            const cantidad = parseFloat(row.querySelector('.detalle-cantidad').value) || 0;
            const precio = parseFloat(row.querySelector('.detalle-precio').value) || 0;
            const subtotal = cantidad * precio;

            row.querySelector('.detalle-subtotal').value = `Q ${subtotal.toFixed(2)}`;
            row.querySelector('.detalle-subtotal-value').value = subtotal.toFixed(2);

            calcularTotal();
        }

        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('.detalle-subtotal-value').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            document.getElementById('totalGeneral').textContent = `Q ${total.toFixed(2)}`;
            document.getElementById('inputTotal').value = total.toFixed(2);
        }

        function eliminarDetalle(btn) {
            btn.closest('.detalle-row').remove();
            calcularTotal();
        }

        // Agregar primer detalle automáticamente
        agregarDetalle();
    </script>
@endsection
