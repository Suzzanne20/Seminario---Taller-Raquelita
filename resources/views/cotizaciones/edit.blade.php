@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:rgba(255, 255, 255, 0.144) !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  .md-card{
    max-width: 880px;
    margin: 32px auto 64px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    padding:28px;
  }
  .md-title{
    font-weight:700; color:#C24242; text-align:center; margin-bottom:18px;
  }

  .form-control, .form-select{
    border:none; border-bottom:2px solid #e6e6e6;
    border-radius:0; background:transparent; padding-left:0;
  }
  .form-control:focus, .form-select:focus{
    box-shadow:none; border-color:#3f51b5;
  }
  .form-label{ font-size:.9rem; color:#6b7280; }
  .help{ font-size:.8rem; color:#9CA3AF; }

  .btn-theme{ background:#9F3B3B; border:none; color:#fff; }
  .btn-theme:hover{ background:#873131; color:#fff; }
  .btn-muted{ background:#e5e7eb; color:#111827; border:none; }

  .insumo-row{
    border-bottom:1px dashed #e5e7eb;
    padding-bottom:10px; margin-bottom:10px;
  }
  .insumo-subtotal{
    min-width: 110px; text-align:right; font-weight:600;
  }
  .badge-precio{
    font-size:.8rem; background:#eef2ff; color:#3730a3; border-radius:999px; padding:2px 8px;
  }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">
    <h2 class="md-title">Editar Cotización #{{ $cotizacione->id }}</h2>

    {{-- Errores --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('cotizaciones.update', $cotizacione->id) }}" method="POST" novalidate>
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <input type="text" name="descripcion"
               class="form-control @error('descripcion') is-invalid @enderror"
               value="{{ old('descripcion', $cotizacione->descripcion) }}"
               required placeholder="Breve detalle de la cotización">
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Servicio</label>
          <select name="type_service_id"
                  class="form-select @error('type_service_id') is-invalid @enderror" required>
            <option value="">— Seleccione… —</option>
            @foreach($servicios as $servicio)
              <option value="{{ $servicio->id }}"
                @selected(old('type_service_id', $cotizacione->type_service_id) == $servicio->id)>
                {{ $servicio->descripcion }}
              </option>
            @endforeach
          </select>
          <div class="help">Tipo/paquete principal</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Costo mano de obra (Q)</label>
          <input type="number" step="0.01" min="0"
                 name="costo_mo" id="costo_mo"
                 class="form-control @error('costo_mo') is-invalid @enderror"
                 value="{{ old('costo_mo', $cotizacione->costo_mo) }}" placeholder="0.00">
        </div>
      </div>

      {{-- Insumos dinámicos --}}
      <div class="mt-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <label class="form-label m-0 fw-bold">Insumos</label>
          <button type="button" id="add-insumo" class="btn btn-sm btn-outline-secondary">
            + Agregar Insumo
          </button>
        </div>
        <div id="insumos-container"></div>
      </div>

      {{-- Total dinámico --}}
      <div class="card p-3 mt-3" style="background:#f8f9fa; border-radius:12px; border:none;">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="fw-bold m-0">Total estimado</h5>
          <span id="total-cotizacion" style="color:#C24242; font-weight:800;">Q 0.00</span>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-theme px-4">Guardar cambios</button>
        <a href="{{ route('cotizaciones.index') }}" class="btn btn-muted px-4">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Catálogo de insumos desde backend
  const INSUMOS = @json($insumos); // [{id, nombre, precio}, ...]

  // Pre-selección (insumos ya asociados a la cotización con su cantidad en el pivot)
  const PRESELECTED = @json(
    $cotizacione->insumos->map(fn($i)=>[
      'id' => $i->id,
      'cantidad' => $i->pivot->cantidad ?? 1
    ])
  );

  const container = document.getElementById('insumos-container');
  const addBtn = document.getElementById('add-insumo');
  const costoMoEl = document.getElementById('costo_mo');
  const totalEl = document.getElementById('total-cotizacion');
  let idx = 0;

  function fmtQ(n){
    const val = isNaN(n) ? 0 : Number(n);
    return 'Q ' + val.toFixed(2);
  }

  function insumoOptionsHtml(selectedId = ''){
    let html = '<option value="">Seleccione insumo…</option>';
    for (const i of INSUMOS){
      html += `<option value="${i.id}" data-precio="${i.precio}" ${String(selectedId)===String(i.id)?'selected':''}>
                 ${i.nombre} — Q${Number(i.precio).toFixed(2)}
               </option>`;
    }
    return html;
  }

  function renderRow(index, selectedId = '', cantidad = 1){
    const row = document.createElement('div');
    row.className = 'row align-items-end insumo-row';
    row.dataset.index = index;

    row.innerHTML = `
      <div class="col-md-6">
        <label class="form-label">Insumo</label>
        <select name="insumos[${index}][id]" class="form-select insumo-select" required>
          ${insumoOptionsHtml(selectedId)}
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Cantidad</label>
        <input type="number" name="insumos[${index}][cantidad]" class="form-control cantidad-input" min="1" value="${cantidad}" required>
      </div>
      <div class="col-md-3 d-flex justify-content-end gap-2">
        <div class="insumo-subtotal align-self-center text-nowrap">Q 0.00</div>
        <button type="button" class="btn btn-sm btn-outline-danger remove-insumo" title="Quitar">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
    `;
    container.appendChild(row);
    updateRowSubtotal(row);
    calcTotal();
  }

  function updateRowSubtotal(row){
    const select = row.querySelector('.insumo-select');
    const qty = parseFloat(row.querySelector('.cantidad-input').value) || 0;
    const opt = select.options[select.selectedIndex];
    const precio = opt ? parseFloat(opt.getAttribute('data-precio') || '0') : 0;
    const sub = precio * qty;
    row.querySelector('.insumo-subtotal').textContent = fmtQ(sub);
  }

  function calcTotal(){
    let total = parseFloat(costoMoEl.value) || 0;
    container.querySelectorAll('.insumo-row').forEach(row=>{
      const text = row.querySelector('.insumo-subtotal').textContent.replace('Q','').trim();
      total += parseFloat(text) || 0;
    });
    totalEl.textContent = fmtQ(total);
  }

  // Eventos
  addBtn.addEventListener('click', ()=> renderRow(idx++));
  container.addEventListener('click', (e)=>{
    if(e.target.closest('.remove-insumo')){
      e.target.closest('.insumo-row').remove();
      calcTotal();
    }
  });
  container.addEventListener('input', (e)=>{
    if(e.target.classList.contains('cantidad-input')){
      updateRowSubtotal(e.target.closest('.insumo-row'));
      calcTotal();
    }
  });
  container.addEventListener('change', (e)=>{
    if(e.target.classList.contains('insumo-select')){
      updateRowSubtotal(e.target.closest('.insumo-row'));
      calcTotal();
    }
  });
  costoMoEl.addEventListener('input', calcTotal);

  // Inicializar filas:
  if (PRESELECTED && PRESELECTED.length){
    PRESELECTED.forEach(p => renderRow(idx++, p.id, p.cantidad ?? 1));
  } else {
    // Si no trae insumos, deja una fila por defecto
    renderRow(idx++);
  }

  // Calcular total inicial con datos existentes
  calcTotal();
</script>
@endpush
