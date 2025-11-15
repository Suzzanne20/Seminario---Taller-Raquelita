<table class="table table-hover align-middle mb-0">
  <thead class="table-dark">
    <tr>
      <th class="text-center" style="width:42px">
        <input type="checkbox" id="selectAll">
      </th>
      <th>ID</th>
      <th>Nombre</th>
      <th>Costo</th>
      <th>Stock</th>
      <th>Stock Mínimo</th>
      <th>Descripción</th>
      <th>Tipo Insumo</th>
      <th>Precio</th>
      <th class="text-center" style="width:130px">Acciones</th>
    </tr>
  </thead>
  <tbody>
    @forelse($insumos as $i)
      <tr class="{{ $i->stock <= $i->stock_minimo ? 'table-warning' : '' }} {{ $i->stock == 0 ? 'table-danger' : '' }}">
        <td class="text-center">
          <input type="checkbox" name="ids[]" value="{{ $i->id }}" class="row-checkbox">
        </td>
        <td class="fw-semibold">{{ $i->id }}</td>
        <td>
          {{ $i->nombre }}
          @if($i->stock <= $i->stock_minimo && $i->stock > 0)
            <span class="badge bg-warning ms-1" title="Stock bajo">
              <i class="bi bi-exclamation-triangle"></i>
            </span>
          @endif
          @if($i->stock == 0)
            <span class="badge bg-danger ms-1" title="Sin stock">
              <i class="bi bi-x-circle"></i>
            </span>
          @endif
        </td>
        <td>{{ number_format($i->costo, 2) }}</td>
        <td>
          <span class="{{ $i->stock <= $i->stock_minimo ? 'fw-bold text-warning' : '' }} {{ $i->stock == 0 ? 'fw-bold text-danger' : '' }}">
            {{ $i->stock }}
          </span>
        </td>
        <td>{{ $i->stock_minimo }}</td>
        <td>{{ $i->descripcion }}</td>
        <td>
          <span class="badge bg-secondary">{{ $i->tipoInsumo->nombre ?? '—' }}</span>
        </td>
        <td>{{ number_format($i->precio, 2) }}</td>
        <td class="text-center">
          <div class="d-inline-flex gap-2">
            <a href="{{ route('insumos.edit', $i->id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
              <i class="bi bi-pencil-square"></i>
            </a>
              @role('admin')
            <form action="{{ route('insumos.destroy', $i->id) }}" method="POST">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                <i class="bi bi-trash"></i>
              </button>
            </form>
              @endrole
          </div>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="10" class="text-center py-4">
          @if(request('tipo_insumo') || request('stock') || request('q'))
            No se encontraron insumos con los filtros aplicados.
            <div class="mt-2">
              <a href="{{ route('insumos.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-clockwise me-1"></i> Ver todos los insumos
              </a>
            </div>
          @else
            No hay insumos registrados.
          @endif
        </td>
      </tr>
    @endforelse
  </tbody>
</table>