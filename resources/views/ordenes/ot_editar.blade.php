@extends('layouts.app')

@php
  // dueño del vehículo (primer cliente asociado)
  $owner   = optional($orden->vehiculo->clientes)->first();
  $admin   = optional($orden->creador)->name ?? '—';

  // mapeo rápido por descripcion para enlazar chips
  $svcByDesc = collect($servicios)->keyBy(fn($s) => \Illuminate\Support\Str::lower($s->descripcion));
  $idCorrectivo  = optional($svcByDesc->get('correctivo'))->id;
  $idPreventivo  = optional($svcByDesc->get('preventivo'))->id;
  $idOtro        = optional($svcByDesc->get('otro'))->id;

  $estadoActual = (int) $orden->estado_id;
@endphp

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  .md-card{
    max-width: 1180px; margin: 32px auto 64px; background:#fff;
    border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.08); padding:24px;
  }
  .md-title{ font-weight:800; color:#C24242; text-align:center; }

  .form-control, .form-select{
    border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent; padding-left:0;
  }
  .form-control:focus, .form-select:focus{ box-shadow:none; border-color:#3f51b5; }
  .form-label{ font-size:.9rem; color:#6b7280; }

  .btn-theme{ background:#9F3B3B; border:none; color:#fff; border-radius:12px; }
  .btn-theme:hover{ background:#873131; color:#fff; }
  .btn-muted{ background:#e5e7eb; color:#111827; border:none; border-radius:12px; }

  .chip{
    padding:.45rem .85rem; border-radius:999px; font-weight:600;
    border:1px solid #e5e7eb; color:#374151; background:#fff; cursor:pointer; user-select:none;
  }
  .chip.active{ background:#111827; color:#fff; border-color:#111827; }

  .i-row{ border-bottom:1px dashed #e5e7eb; padding:.35rem 0; }
  .i-remove{ border:none; background:#fee2e2; color:#991b1b; border-radius:8px; padding:.25rem .55rem; }
  .i-remove:hover{ background:#fecaca; }

  .resume{ background:#f8f9fa; border-radius:12px; padding:16px; box-shadow:0 4px 14px rgba(0,0,0,.04) inset; }
  .resume .big{ font-size:1.25rem; font-weight:800; color:#C24242; }

  .pane{ background:#fafafa; border:1px solid #eee; border-radius:12px; padding:16px; }

  .edit-mini{
    border:1px solid #e5e7eb; border-radius:10px; padding:6px 10px; color:#374151; background:#fff;
    text-decoration:none; font-size:.9rem;
  }
  .edit-mini:hover{ background:#f3f4f6; }

  .btn-flow .btn{ border-radius:0; }
  .btn-flow .btn:first-child{ border-top-left-radius:10px; border-bottom-left-radius:10px; }
  .btn-flow .btn:last-child{ border-top-right-radius:10px; border-bottom-right-radius:10px; }

  .muted-kpi{ font-size:.9rem; color:#6b7280; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
      <h2 class="md-title mb-0">Orden de Trabajo #{{ $orden->id }}</h2>
      <div class="d-flex gap-2">
        <a href="#" class="edit-mini"><i class="bi bi-printer me-1"></i> Imprimir</a>
        <a href="{{ route('ordenes.index') }}" class="btn btn-muted">Volver</a>
      </div>
    </div>

    {{-- Errores --}}
    @if ($errors->any())
      <div class="alert alert-danger"><strong>Corrige los errores:</strong>
        <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
      </div>
    @endif

    <form action="{{ route('ordenes.update', $orden->id) }}" method="POST" novalidate>
      @csrf @method('PUT')

      <div class="row g-4">
        {{-- IZQUIERDA: cliente + chips + falla + insumos --}}
        <div class="col-lg-7">
          <div class="pane mb-3">
            <div class="row g-2 align-items-center">
              <div class="col-auto">
                <div class="display-6"><i class="bi bi-person"></i></div>
              </div>
              <div class="col">
                <div class="fw-bold">{{ $owner->nombre ?? '—' }}</div>
                <div class="text-muted">{{ $owner->telefono ?? '—' }}</div>
                <div class="muted-kpi mt-1">
                  Creada: {{ optional($orden->fecha_creacion)->format('d/m/Y H:i') }}
                  · por {{ $admin }}
                </div>
              </div>
              <div class="col-auto">
                @if($owner)
                  <a class="edit-mini" href="{{ route('clientes.edit', $owner->id) }}"><i class="bi bi-pencil-square"></i> Editar</a>
                @endif
              </div>
            </div>
          </div>

          {{-- Chips de tipo servicio (sin duplicar el select; se sincroniza hidden) --}}
          @php
            $tsActual = (int) $orden->type_service_id;
          @endphp
          <input type="hidden" id="type_service_id" name="type_service_id" value="{{ old('type_service_id', $tsActual) }}">
          <div class="d-flex gap-2 mb-3">
            <span class="chip svc-chip {{ $tsActual===$idCorrectivo ? 'active':'' }}" data-id="{{ $idCorrectivo }}">Reparación</span>
            <span class="chip svc-chip {{ $tsActual===$idPreventivo ? 'active':'' }}" data-id="{{ $idPreventivo }}">Mantenimiento</span>
            <span class="chip svc-chip {{ $tsActual===$idOtro ? 'active':'' }}" data-id="{{ $idOtro }}">Otro</span>
          </div>
          @error('type_service_id') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

          <div class="mb-3">
            <label class="form-label fw-semibold">Descripción / Falla</label>
            <textarea class="form-control @error('descripcion') is-invalid @enderror" name="descripcion" rows="3"
              placeholder="Describe la falla o el requerimiento…">{{ old('descripcion', $orden->descripcion) }}</textarea>
            @error('descripcion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>

          {{-- Insumos --}}
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 fw-bold">Productos / Servicios</h5>
            <button type="button" id="add-insumo" class="btn btn-outline-primary btn-sm">+ Agregar insumo</button>
          </div>
          <div id="insumos-container" class="mb-2"></div>

          <div class="resume mt-3">
            <div class="row g-3">
              <div class="col-md-4"><div class="text-muted">SUBTOTAL</div><div id="sub_insumos" class="fw-bold">Q 0.00</div></div>
              <div class="col-md-4"><div class="text-muted">Mano de Obra</div><div id="mo_view" class="fw-bold">Q 0.00</div></div>
              <div class="col-md-4 text-md-end"><div class="text-muted">TOTAL</div><div id="total_view" class="big">Q 0.00</div></div>
            </div>
          </div>
        </div>

        {{-- DERECHA: vehículo + kpis + estado + vínculos --}}
        <div class="col-lg-5">
          <div class="pane mb-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-bold">{{ $orden->vehiculo->marca->nombre ?? 'Vehículo' }} · {{ $orden->vehiculo->linea ?? '' }} {{ $orden->vehiculo->modelo ?? '' }}</div>
                <div class="muted-kpi">Placa</div>
                {{-- Placa editable con datalist --}}
                <input name="vehiculo_placa" id="vehiculo_placa" list="lista_placas"
                       class="form-control @error('vehiculo_placa') is-invalid @enderror"
                       value="{{ old('vehiculo_placa', $orden->vehiculo_placa) }}" maxlength="7" style="text-transform:uppercase">
                <datalist id="lista_placas">
                  @foreach($vehiculos as $v)
                    <option value="{{ $v->placa }}">{{ $v->placa }} — {{ $v->linea }} {{ $v->modelo }}</option>
                  @endforeach
                </datalist>
                @error('vehiculo_placa') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
              </div>
              <div class="text-end">
                <a href="{{ route('vehiculos.edit', $orden->vehiculo_placa) }}" class="edit-mini"><i class="bi bi-pencil-square"></i> Editar</a>
              </div>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-sm-6">
                <div class="muted-kpi">Kilometraje</div>
                <input type="number" min="0" name="kilometraje"
                       class="form-control @error('kilometraje') is-invalid @enderror"
                       value="{{ old('kilometraje', $orden->kilometraje) }}">
                @error('kilometraje')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-sm-6">
                <div class="muted-kpi">Próximo servicio (km)</div>
                <input type="number" min="0" name="proximo_servicio"
                       class="form-control @error('proximo_servicio') is-invalid @enderror"
                       value="{{ old('proximo_servicio', $orden->proximo_servicio) }}">
                @error('proximo_servicio')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-sm-6">
                <div class="muted-kpi">Técnico asignado</div>
                <select name="tecnico_id" class="form-select @error('tecnico_id') is-invalid @enderror">
                  <option value="">Seleccione…</option>
                  @foreach($tecnicos as $t)
                    <option value="{{ $t->id }}" @selected(old('tecnico_id', $orden->tecnico_id ?? null)==$t->id)>{{ $t->name }}</option>
                  @endforeach
                </select>
                @error('tecnico_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-sm-6">
                <div class="muted-kpi">Costo mano de obra (Q)</div>
                <input type="number" step="0.01" min="0" name="costo_mo" id="costo_mo"
                       class="form-control @error('costo_mo') is-invalid @enderror"
                       value="{{ old('costo_mo', $orden->costo_mo ?? 0) }}">
                @error('costo_mo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          {{-- Estado (btn-check estilo flujo) --}}
          <div class="pane mb-3">
            <div class="text-secondary small mb-2">Estado de la Orden de Trabajo</div>
            <div class="btn-group btn-flow w-100" role="group" aria-label="flujo-estado">
              @foreach($estadosFlow as $e)
                @php $checked = old('estado_id', $estadoActual) == $e->id; @endphp
                <input type="radio" class="btn-check" name="estado_id" id="estado_{{ $e->id }}" value="{{ $e->id }}" autocomplete="off" {{ $checked ? 'checked':'' }}>
                <label class="btn btn-outline-dark" for="estado_{{ $e->id }}">{{ $e->nombre }}</label>
              @endforeach
            </div>
            @error('estado_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>

          {{-- Vínculos: Inspección / Cotización --}}
          <div class="d-flex gap-2 mb-3">
            @if($inspeccion)
              <a class="btn btn-dark flex-fill" href="{{ route('inspecciones.show', $inspeccion->id) }}">
                <i class="bi bi-car-front-fill me-1"></i> Inspección 360°
              </a>
            @else
              <button class="btn btn-dark flex-fill" type="button" disabled>
                <i class="bi bi-car-front-fill me-1"></i> Inspección 360°
              </button>
            @endif

            @if($cotizacion)
              <a class="btn btn-secondary flex-fill" href="{{ route('cotizaciones.show', $cotizacion->id) }}">
                <i class="bi bi-clipboard-check me-1"></i> Cotización
              </a>
            @else
              <button class="btn btn-secondary flex-fill" type="button" disabled>
                <i class="bi bi-clipboard-check me-1"></i> Cotización
              </button>
            @endif
          </div>

          <div class="d-flex justify-content-end">
            <button class="btn btn-theme px-4"><i class="bi bi-save me-1"></i> Guardar cambios</button>
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
  // ======= Sincronizar chips -> hidden type_service_id =======
  const chips = document.querySelectorAll('.svc-chip');
  const svcHidden = document.getElementById('type_service_id');
  chips.forEach(ch => {
    ch.addEventListener('click', () => {
      chips.forEach(c => c.classList.remove('active'));
      ch.classList.add('active');
      const id = ch.getAttribute('data-id') || '';
      svcHidden.value = id;
    });
  });

  // ======= Placa uppercase =======
  const placa = document.getElementById('vehiculo_placa');
  placa?.addEventListener('input', () => placa.value = placa.value.toUpperCase());

  // ======= Insumos dinámicos =======
  const INSUMOS   = @json($insumos);
  const OT_ITEMS  = @json($orden->items->map(fn($x)=>['id'=>$x->insumo_id,'cantidad'=>$x->cantidad])->values());
  const q         = n => 'Q ' + (parseFloat(n||0)).toFixed(2);

  let idx = 0;
  const cont  = document.getElementById('insumos-container');
  const addBtn= document.getElementById('add-insumo');

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
          <input type="number" min="1" value="${cant}" name="insumos[${i}][cantidad]" class="form-control cantidad-input" required>
        </div>
        <div class="col-md-2 text-end"><span class="precio-unit">Q 0.00</span></div>
        <div class="col-md-2 text-end"><button type="button" class="i-remove">X</button></div>
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
    document.getElementById('mo_view').textContent   = q(mo);
    document.getElementById('total_view').textContent= q(sub+mo);
  }

  if (cont){
    if (Array.isArray(OT_ITEMS) && OT_ITEMS.length){
      OT_ITEMS.forEach(it => { cont.insertAdjacentHTML('beforeend', renderRow(idx, it)); idx++; });
    } else {
      cont.insertAdjacentHTML('beforeend', renderRow(idx)); idx++;
    }
    recalc();
  }

  addBtn?.addEventListener('click', ()=>{ cont.insertAdjacentHTML('beforeend', renderRow(idx)); idx++; recalc(); });
  document.addEventListener('change', e=>{ if(e.target.classList.contains('insumo-select')) recalc(); });
  document.addEventListener('input',  e=>{ if(e.target.classList.contains('cantidad-input') || e.target.id==='costo_mo') recalc(); });
  document.addEventListener('click',  e=>{ if(e.target.classList.contains('i-remove')){ e.target.closest('.i-row').remove(); recalc(); }});
})();
</script>
@endpush


