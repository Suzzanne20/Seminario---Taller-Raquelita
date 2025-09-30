@extends('layouts.app')

@section('title', 'Editar Cotización')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-4">Editar Cotización</h1>

        <form action="{{ route('cotizaciones.update', $cotizacione->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Descripción -->
            <div class="mb-4">
                <label class="block font-semibold">Descripción</label>
                <input type="text" name="descripcion"
                       value="{{ old('descripcion', $cotizacione->descripcion) }}"
                       class="w-full border rounded px-3 py-2 text-black">
                @error('descripcion')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Costo mano de obra -->
            <div class="mb-4">
                <label class="block font-semibold">Costo de Mano de Obra (Q)</label>
                <input type="number" step="0.01" name="costo_mo"
                       value="{{ old('costo_mo', $cotizacione->costo_mo) }}"
                       class="w-full border rounded px-3 py-2 text-black">
                @error('costo_mo')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Servicio -->
            <div class="mb-4">
                <label class="block font-semibold">Servicio</label>
                <select name="type_service_id" class="w-full border rounded px-3 py-2 text-black">
                    <option value="">-- Seleccione un servicio --</option>
                    @foreach($servicios as $servicio)
                        <option value="{{ $servicio->id }}"
                            {{ old('type_service_id', $cotizacione->type_service_id) == $servicio->id ? 'selected' : '' }}>
                            {{ $servicio->descripcion }}
                        </option>
                    @endforeach
                </select>
                @error('type_service_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Insumos dinámicos con precio y cantidad -->
            <div class="mb-4">
                <label class="block font-semibold">Insumos</label>
                <div id="insumos-container">
                    @foreach($cotizacione->insumos as $i => $insumoSeleccionado)
                        <div class="flex items-center gap-2 mb-2">
                            <select name="insumos[{{ $i }}][id]"
                                    class="border rounded px-3 py-2 text-black insumo-select"
                                    onchange="actualizarPrecio(this)">
                                <option value="">-- Seleccione insumo --</option>
                                @foreach($insumos as $insumo)
                                    <option value="{{ $insumo->id }}"
                                            data-precio="{{ $insumo->precio }}"
                                        {{ $insumoSeleccionado->id == $insumo->id ? 'selected' : '' }}>
                                        {{ $insumo->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- precio unitario -->
                            <span class="precio">Q{{ $insumoSeleccionado->precio ?? 0 }}</span>

                            <!-- cantidad -->
                            <input type="number" name="insumos[{{ $i }}][cantidad]"
                                   value="{{ $insumoSeleccionado->pivot->cantidad }}"
                                   class="cantidad border rounded px-2 py-1 w-24 text-black"
                                   min="0" oninput="calcularTotal()">

                            <button type="button" onclick="this.parentElement.remove(); calcularTotal();"
                                    class="bg-red-500 text-white px-2 py-1 rounded">X</button>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="agregarInsumo()"
                        class="bg-red-600 text-white px-3 py-1 rounded mt-2">+ Agregar Insumo</button>
            </div>

            <!-- Total estimado -->
            <div class="mt-4 font-bold">
                Total estimado: <span id="total">Q{{ $cotizacione->total }}</span>
            </div>

            <!-- Botón -->
            <div class="flex justify-end mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <script>
        function agregarInsumo() {
            let index = document.querySelectorAll('#insumos-container > div').length;
            let html = `
        <div class="flex items-center gap-2 mb-2">
            <select name="insumos[${index}][id]"
                    class="border rounded px-3 py-2 text-black insumo-select"
                    onchange="actualizarPrecio(this)">
                <option value="">-- Seleccione insumo --</option>
                @foreach($insumos as $insumo)
            <option value="{{ $insumo->id }}" data-precio="{{ $insumo->precio }}">
                        {{ $insumo->nombre }}
            </option>
@endforeach
            </select>

            <span class="precio">Q0.00</span>

            <input type="number" name="insumos[${index}][cantidad]"
                   value="0"
                   class="cantidad border rounded px-2 py-1 w-24 text-black"
                   min="0" oninput="calcularTotal()">

            <button type="button" onclick="this.parentElement.remove(); calcularTotal();"
                    class="bg-red-500 text-white px-2 py-1 rounded">X</button>
        </div>
    `;
            document.getElementById('insumos-container').insertAdjacentHTML('beforeend', html);
        }

        function actualizarPrecio(select) {
            let precio = select.options[select.selectedIndex].getAttribute('data-precio') || 0;
            select.parentElement.querySelector('.precio').textContent = "Q" + precio;
            calcularTotal();
        }

        function calcularTotal() {
            let total = parseFloat(document.querySelector('input[name="costo_mo"]').value) || 0;
            document.querySelectorAll('#insumos-container > div').forEach(div => {
                let select = div.querySelector('.insumo-select');
                let precio = select.options[select.selectedIndex]?.getAttribute('data-precio') || 0;
                let cantidad = div.querySelector('.cantidad').value || 0;
                total += parseFloat(precio) * parseFloat(cantidad);
            });
            document.getElementById('total').textContent = "Q" + total.toFixed(2);
        }
    </script>
@endsection
