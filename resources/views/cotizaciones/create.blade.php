@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }
</style>
@endpush

@section('content')
    <div class="container"><br><br>
        <h3 class="fw-bold mb-4" style="color:#1E1E1E;">Nueva Cotización</h3>

        {{-- Errores --}}
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm" style="border-radius:12px;">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('cotizaciones.store') }}" method="POST">
            @csrf

            {{-- Descripción --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Descripción</label>
                <input type="text" name="descripcion" class="form-control shadow-sm" required>
            </div>

            {{-- Servicio --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Servicio</label>
                <select name="type_service_id" class="form-select shadow-sm" required>
                    <option value="">Seleccione...</option>
                    @foreach($servicios as $servicio)
                        <option value="{{ $servicio->id }}">{{ $servicio->descripcion }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mano de obra --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Costo Mano de Obra</label>
                <input type="number" step="0.01" name="costo_mo" id="costo_mo" class="form-control shadow-sm" value="0">
            </div>

            {{-- Insumos dinámicos --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Insumos</label>

                <div id="insumos-container"></div>

                <button type="button" id="add-insumo" class="btn btn-outline-primary mt-2">
                    + Agregar Insumo
                </button>
            </div>

            {{-- Total dinámico --}}
            <div class="card p-3 mt-3 shadow-sm" style="background:#f8f9fa; border-radius:12px;">
                <h5 class="fw-bold">Total estimado:
                    <span id="total-cotizacion" style="color:#C24242;">Q 0.00</span>
                </h5>
            </div>

            {{-- Botones --}}
            <div class="mt-4">
                <button type="submit" class="btn"
                        style="background-color:#C24242; color:#fff; border-radius:12px; padding:8px 18px;">
                    Guardar Cotización
                </button>
                <a href="{{ route('cotizaciones.index') }}" class="btn btn-secondary" style="border-radius:12px;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        let insumoIndex = 0;

        const insumosData = @json($insumos);

        function renderInsumoRow(index) {
            let options = `<option value="">Seleccione insumo...</option>`;
            insumosData.forEach(insumo => {
                options += `<option value="${insumo.id}" data-precio="${insumo.precio}">
                                ${insumo.nombre} (Q${insumo.precio})
                            </option>`;
            });

            return `
                <div class="row align-items-center mb-2 insumo-row" data-index="${index}">
                    <div class="col-md-6">
                        <select name="insumos[${index}][id]" class="form-select insumo-select" required>
                            ${options}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="insumos[${index}][cantidad]"
                               class="form-control cantidad-input" placeholder="Cantidad" min="1" value="1" required>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-insumo">X</button>
                    </div>
                </div>
            `;
        }

        function calcularTotal() {
            let total = 0;

            // Mano de obra
            let costoMo = parseFloat(document.getElementById('costo_mo').value) || 0;
            total += costoMo;

            // Insumos
            document.querySelectorAll('.insumo-row').forEach(row => {
                let select = row.querySelector('.insumo-select');
                let cantidad = parseFloat(row.querySelector('.cantidad-input').value) || 0;
                let precio = select.options[select.selectedIndex]?.getAttribute('data-precio');

                if (precio && cantidad > 0) {
                    total += parseFloat(precio) * cantidad;
                }
            });

            document.getElementById('total-cotizacion').innerText = "Q " + total.toFixed(2);
        }

        document.getElementById('add-insumo').addEventListener('click', function () {
            const container = document.getElementById('insumos-container');
            container.insertAdjacentHTML('beforeend', renderInsumoRow(insumoIndex));
            insumoIndex++;
        });

        // Escuchar cambios para recalcular
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('cantidad-input') || e.target.id === 'costo_mo') {
                calcularTotal();
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('insumo-select')) {
                calcularTotal();
            }
        });

        // Quitar fila de insumo
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-insumo')) {
                e.target.closest('.insumo-row').remove();
                calcularTotal();
            }
        });
    </script>
@endpush
