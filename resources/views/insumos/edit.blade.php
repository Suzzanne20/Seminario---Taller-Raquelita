@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  .md-card{
    max-width: 1180px;
    margin: 32px auto 64px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    padding:24px;
  }
  .md-title{ font-weight:800; color:#C24242; text-align:center; }

  .form-control, .form-select{
    border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent; padding-left:0;
  }
  .form-control:focus, .form-select:focus{ box-shadow:none; border-color:#3f51b5; }
  .form-label{ font-size:.9rem; color:#6b7280; }

  .btn-theme{ background:#9F3B3B; border:none; color:#fff; }
  .btn-theme:hover{ background:#873131; color:#fff; }
  .btn-muted{ background:#e5e7eb; color:#111827; border:none; }

  .chip{
    padding:.45rem .85rem; border-radius:999px; font-weight:600;
    border:1px solid #e5e7eb; color:#374151; background:#fff;
  }
  .chip.active{ background:#111827; color:#fff; border-color:#111827; }

  .i-row{ border-bottom:1px dashed #e5e7eb; padding:.35rem 0; }
  .i-remove{ border:none; background:#fee2e2; color:#991b1b; border-radius:8px; padding:.25rem .55rem; }
  .i-remove:hover{ background:#fecaca; }

  .resume{
    background:#f8f9fa; border-radius:12px; padding:16px;
    box-shadow:0 4px 14px rgba(0,0,0,.04) inset;
  }
  .resume .big{ font-size:1.25rem; font-weight:800; color:#C24242; }

  .veh-card{
    background:#fafafa; border:1px solid #eee; border-radius:12px; padding:16px;
  }
  .veh-title{ font-weight:700; font-size:1.05rem; }
  .veh-meta{ font-size:.9rem; color:#6b7280; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
      <h2 class="md-title mb-0">Orden de Trabajo #{{ $orden->id }}</h2>
      <div class="d-flex gap-2">
        <a href="#" class="btn btn-outline-dark" style="border-radius:12px;">
          <i class="bi bi-printer me-1"></i> Imprimir
        </a>
        <a href="{{ route('ordenes.index') }}" class="btn btn-muted" style="border-radius:12px;">Volver</a>
      </div>
    </div>

    {{-- Estado como chips (selección) --}}
    <div class="d-flex align-items-center gap-2 mb-3">
      @php
        $estados = [
          1 => 'Nueva',
          2 => 'Asignada',
          4 => 'Pendiente',
          5 => 'En proceso',
          6 => 'Finalizada',
        ];
      @endphp
      @foreach($estados as $id => $label)
        <span class="chip {{ $orden->estado_id == $id ? 'active' : '' }}">{{ $label }}</span>
      @endforeach
    </div>

    {{-- Errores --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>Corrige los errores:</strong>
        <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
      </div>
    @endif

    <form action="{{ route('ordenes.update', $orden->id) }}" method="POST" novalidate>
      @csrf
      @method('PUT')

      <div class="row g-4">
        {{-- Columna izquierda: falla + insumos --}}
        <div class="col-lg-7">
          {{-- Datos del cliente/vehículo (compacto arriba en mock) --}}
          <div class="veh-card mb-3">
            <div class="d-flex justify-content-between">
              <div>
                <div class="veh-title">{{ $orden->vehiculo->placa ?? '—' }}</div>
                <div class="veh-meta">
                  {{ $orden->vehiculo->linea ?? '—' }}
                  {{ $orden->vehiculo->modelo ?? '' }}
                </div>
              </div>
              <div class="text-end">
                <div class="veh-meta">Creada: {{ optional($orden->fecha_creacion)->format('d/m/Y H:i') }}</div>
                <div class="veh-meta">Servicio: {{ $orden->servicio->descripcion ?? '—' }}</div>
              </div>
            </div>
          </div>

          {{-- Tipo requerimiento (simples toggles visuales) --}}
          <div class="d-flex gap-2 mb-2">
            <span class="chip {{ ($orden->servicio->descripcion ?? '') === 'Correctivo' ? 'active' : '' }}">Reparación</span>
            <span class="chip {{ ($orden->servicio->descripcion ?? '') === 'Preventivo' ? 'active' : '' }}">Mantenimiento</span>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Descripción / Falla</label>
            <textarea class="form-control @error('descripcion') is-invalid @enderror" name="descripcion" rows="3"
              placeholder="Describe la falla o el requerimiento…">{{ old('descripcion', $orden->descripcion) }}</textarea>
            @error('descripcion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>

          {{-- Tabla de insumos dinámica --}}
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 fw-bold">Productos / Servicios</h5>
            <button type="button" id="add-insumo" class="btn btn-outline-primary btn-sm">+ Agregar insumo</button>
          </div>

          <div id="insumos-container" class="mb-2"></div>

          {{-- Totales (abajo, estilo mock) --}}
          <div class="row g-3 mt-3">
            <div class="col-md-4">
              <div class="text-muted">SUBTOTAL</div>
              <div id="sub_insumos" class="fw-bold">Q 0.00</div>
            </div>
            <div class="col-md-4">
              <div class="text-muted">MO</div>
              <div id="mo_view" class="fw-bold">Q 0.00</div>
            </div>
            <div class="col-md-4">
              <div class="text-muted">TOTAL</div>
              <div id="total_view" class="fw-bold">Q 0.00</div>
            </div>
          </div>
        </div>

        {{-- Columna derecha: ficha del vehículo --}}
        <div class="col-lg-5">
          <div class="veh-card">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="veh-title">{{ $orden->vehiculo->marca->nombre ?? 'Vehículo' }}</div>
                <div class="veh-meta">{{ $orden->vehiculo->linea ?? '' }} · {{ $orden->vehiculo->modelo ?? '' }}</div>
              </div>
              <a href="{{ route('vehiculos.edit', $orden->vehiculo_placa) }}" class="small text-decoration-none">
                <i class="bi bi-pencil-square"></i> Editar
              </a>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-12">
                <label class="form-label fw-semibold">Tipo de servicio</label>
                <select name="type_service_id" class="form-select @error('type_service_id') is-invalid @enderror" required>
                  <option value="">Seleccione…</option>
                  @foreach($servicios as $s)
                    <option value="{{ $s->id }}" @selected(old('type_service_id',$orden->type_service_id)==$s->id)>
                      {{ $s->descripcion }}
                    </option>
                  @endforeach
                </select>
                @error('type_service_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-sm-6">
                <label class="form-label fw-semibold">Kilometraje</label>
                <input type="number" min="0" name="kilometraje"
                       class="form-control @error('kilometraje') is-invalid @enderror"
                       value="{{ old('kilometraje', $orden->kilometraje) }}">
                @error('kilometraje')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-sm-6">
                <label class="form-label fw-semibold">Próximo servicio (km)</label>
                <input type="number" min="0" name="proximo_servicio"
                       class="form-control @error('proximo_servicio') is-invalid @enderror"
                       value="{{ old('proximo_servicio', $orden->proximo_servicio) }}">
                @error('proximo_servicio')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-sm-6">
                <label class="form-label fw-semibold">Técnico asignado</label>
                <select name="tecnico_id" class="form-select @error('tecnico_id') is-invalid @enderror">
                  <option value="">Seleccione…</option>
                  @foreach($tecnicos as $t)
                    <option value="{{ $t->id }}" @selected(old('tecnico_id',$orden->tecnico_id ?? null)==$t->id)>
                      {{ $t->name }}
                    </option>
                  @endforeach
                </select>
                @error('tecnico_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-sm-6">
                <label class="form-label fw-semibold">Costo mano de obra (Q)</label>
                <input type="number" step="0.01" min="0" name="costo_mo" id="costo_mo"
                       class="form-control @error('costo_mo') is-invalid @enderror"
                       value="{{ old('costo_mo', $orden->costo_mo ?? 0) }}">
                @error('costo_mo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold">Estado</label>
                <select name="estado_id" class="form-select">
                  @foreach($estados as $id => $label)
                    <option value="{{ $id }}" @selected(old('estado_id',$orden->estado_id)==$id)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <button class="btn btn-theme"><i class="bi bi-save me-1"></i> Guardar cambios</button>
              </div>
            </div>
          </div>
        </div>
      </div> {{-- row --}}
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  // ======= Datos desde backend =======
  const INSUMOS = @json($insumos);
  // Insumos ya guardados en la OT (pivot insumo_ot: cantidad)
  const OT_ITEMS = @json(
    $orden->insumos->map(fn($i)=>['id'=>$i->id,'cantidad'=>$i->pivot->cantidad])->values()
  );

  const q = n => 'Q ' + (parseFloat(n||0)).toFixed(2);

  let idx = 0;
  const cont = document.getElementById('insumos-container');
  const addBtn = document.getElementById('add-insumo');

  function insumoOptions(selId){
    let html = '<option value="">Seleccione insumo…</option>';
    INSUMOS.forEach(i=>{
      const pr = Number(i.precio||0).toFixed(2);
      const selected = selId && Number(selId)===Number(i.id) ? 'selected' : '';
      html += `<option value="${i.id}" ${selected} data-precio="${pr}">${i.nombre} (Q${pr})</option>`;
    });
    return html;
  }
  function renderRow(i, preset){
    const idSel = preset?.id ?? '';
    const cant  = preset?.cantidad ?? 1;
    return `
      <div class="row align-items-center i-row" data-i="${i}">
        <div class="col-md-6 mb-2 mb-md-0">
          <select name="insumos[${i}][id]" class="form-select insumo-select" required>
            ${insumoOptions(idSel)}
          </select>
        </div>
        <div class="col-md-2">
          <input type="number" min="1" value="${cant}" name="insumos[${i}][cantidad]"
                 class="form-control cantidad-input" required>
        </div>
        <div class="col-md-2 text-end">
          <span class="precio-unit">Q 0.00</span>
        </div>
        <div class="col-md-2 text-end">
          <button type="button" class="i-remove">X</button>
        </div>
      </div>`;
  }

  function recalc(){
    let sub = 0;
    document.querySelectorAll('#insumos-container .i-row').forEach(row=>{
      const sel = row.querySelector('.insumo-select');
      const qty = parseFloat(row.querySelector('.cantidad-input').value)||0;
      const price = parseFloat(sel?.selectedOptions[0]?.getAttribute('data-precio')||0);
      row.querySelector('.precio-unit').textContent = q(price);
      if(qty>0) sub += price * qty;
    });
    const mo = parseFloat(document.getElementById('costo_mo').value||0);
    document.getElementById('sub_insumos').textContent = q(sub);
    document.getElementById('mo_view').textContent = q(mo);
    document.getElementById('total_view').textContent = q(sub+mo);
  }

  // Render inicial con los insumos existentes
  if (cont){
    if (Array.isArray(OT_ITEMS) && OT_ITEMS.length){
      OT_ITEMS.forEach(it=>{
        cont.insertAdjacentHTML('beforeend', renderRow(idx, it));
        idx++;
      });
    } else {
      cont.insertAdjacentHTML('beforeend', renderRow(idx));
      idx++;
    }
    recalc();
  }

  addBtn?.addEventListener('click', ()=>{
    cont.insertAdjacentHTML('beforeend', renderRow(idx));
    idx++; recalc();
  });

  document.addEventListener('change', e=>{
    if (e.target.classList.contains('insumo-select')) recalc();
  });
  document.addEventListener('input', e=>{
    if (e.target.classList.contains('cantidad-input') || e.target.id==='costo_mo') recalc();
  });
  document.addEventListener('click', e=>{
    if (e.target.classList.contains('i-remove')){
      e.target.closest('.i-row').remove(); recalc();
    }
  });
})();
</script>
@endpush

