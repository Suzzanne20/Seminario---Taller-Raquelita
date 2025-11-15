@extends('layouts.app')

@push('styles')
    <style>
        html, body { height:100%; background:#f0f0f0 !important; }
        .page-body { min-height:calc(100vh - 72px); background:rgba(255, 255, 255, 0.144) !important; color:#212529; }
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

        const q = n => 'Q ' + (parseFloat(n||0)).toFixed(2);

        function buscarInsumos(texto){
            const t = String(texto || '').toLowerCase().trim();
            let resultados = insumos || [];
            if (t){
                resultados = resultados.filter(i =>
                    String(i.nombre || '').toLowerCase().includes(t) ||
                    String(i.costo || '').includes(t)
                );
            }
            return resultados.slice(0, 50);
        }

        document.getElementById('btnAgregarDetalle').addEventListener('click', agregarDetalle);

        function agregarDetalle() {
            const container = document.getElementById('detallesContainer');
            const div = document.createElement('div');
            div.className = 'detalle-row';
            div.dataset.index = detalleCounter;

            div.innerHTML = `
      <div class="row g-2 align-items-end">
        <div class="col-md-5" style="position:relative;">
          <label class="form-label">Insumo</label>
          <input type="text"
                 class="form-control insumo-search"
                 placeholder="Buscar insumo..." required>
          <input type="hidden"
                 name="detalles[${detalleCounter}][insumo_id]"
                 class="insumo-id">
          <div class="insumo-results"
               style="position:absolute; top:58px; left:0; right:0;
                      background:#fff; border:1px solid #e5e7eb;
                      border-radius:8px; max-height:220px;
                      overflow-y:auto; display:none; z-index:999;">
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label">Cantidad</label>
          <input type="number" name="detalles[${detalleCounter}][cantidad]"
                 class="form-control detalle-cantidad"
                 step="0.01" min="0.01" value="1" required
                 onchange="calcularSubtotal(this)">
        </div>
        <div class="col-md-2">
          <label class="form-label">Precio Unit.</label>
          <input type="number" name="detalles[${detalleCounter}][precio_unitario]"
                 class="form-control detalle-precio"
                 step="0.01" min="0" value="0" required
                 onchange="calcularSubtotal(this)">
        </div>
        <div class="col-md-2">
          <label class="form-label">Subtotal</label>
          <input type="text" class="form-control detalle-subtotal"
                 readonly value="Q 0.00" style="font-weight:600;">
          <input type="hidden" name="detalles[${detalleCounter}][subtotal]"
                 class="detalle-subtotal-value" value="0">
        </div>
        <div class="col-md-1">
          <button type="button" class="btn btn-danger btn-sm w-100"
                  onclick="eliminarDetalle(this)" title="Eliminar">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    `;

            container.appendChild(div);
            detalleCounter++;
        }

        function calcularSubtotal(input) {
            const row = input.closest('.detalle-row');
            const cantidad = parseFloat(row.querySelector('.detalle-cantidad').value) || 0;
            const precio = parseFloat(row.querySelector('.detalle-precio').value) || 0;
            const subtotal = cantidad * precio;

            row.querySelector('.detalle-subtotal').value = q(subtotal);
            row.querySelector('.detalle-subtotal-value').value = subtotal.toFixed(2);

            calcularTotal();
        }

        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('.detalle-subtotal-value').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            document.getElementById('totalGeneral').textContent = q(total);
            document.getElementById('inputTotal').value = total.toFixed(2);
        }

        function eliminarDetalle(btn) {
            btn.closest('.detalle-row').remove();
            calcularTotal();
        }

        // Delegados para búsqueda de insumos
        document.addEventListener('input', function(e){
            if(!e.target.classList.contains('insumo-search')) return;
            const row = e.target.closest('.detalle-row');
            const box = row.querySelector('.insumo-results');
            const texto = e.target.value;
            const resultados = buscarInsumos(texto);

            if(!resultados.length){
                box.style.display = 'none';
                box.innerHTML = '';
                return;
            }

            box.innerHTML = resultados.map(i => `
              <div class="insumo-option"
                   data-id="${i.id}"
                   data-precio="${i.costo || 0}"
                   data-nombre="${i.nombre}"
                   style="padding:4px 8px; cursor:pointer; font-size:0.85rem;">
                ${i.nombre} — Q${Number(i.costo || 0).toFixed(2)}
              </div>
            `).join('');
            box.style.display = 'block';
        });

        document.addEventListener('click', function(e){
            const opt = e.target.closest('.insumo-option');
            if (opt){
                const row = opt.closest('.detalle-row');
                const search = row.querySelector('.insumo-search');
                const idInput = row.querySelector('.insumo-id');
                const precioInput = row.querySelector('.detalle-precio');
                const box = row.querySelector('.insumo-results');

                search.value       = opt.dataset.nombre;
                idInput.value      = opt.dataset.id;
                precioInput.value  = parseFloat(opt.dataset.precio || 0).toFixed(2);
                box.style.display  = 'none';

                calcularSubtotal(precioInput);
                return;
            }

            // Cerrar dropdowns si se hace clic fuera
            if(!e.target.closest('.detalle-row')){
                document.querySelectorAll('.insumo-results').forEach(b=>{
                    b.style.display = 'none';
                });
            }
        });

        // Agregar primer detalle automáticamente
        agregarDetalle();
    </script>
@endsection
