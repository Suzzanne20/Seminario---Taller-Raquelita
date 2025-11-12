@extends('layouts.app')

@php
  // dueño del vehículo (primer cliente asociado)
  $owner   = optional($orden->vehiculo->clientes)->first();
  $admin   = optional($orden->creador)->name ?? '—';

  // mapeo por descripción para chips
  $svcByDesc = collect($servicios)->keyBy(fn($s) => \Illuminate\Support\Str::lower($s->descripcion));
  $idCorrectivo  = optional($svcByDesc->get('correctivo'))->id;
  $idPreventivo  = optional($svcByDesc->get('preventivo'))->id;
  $idOtro        = optional($svcByDesc->get('otro'))->id;

  $estadoActual = (int) $orden->estado_id;

  // checklist actual
  $raw = $orden->mantenimiento_json ?? [];
  $chk = is_array($raw) ? $raw : (json_decode($raw ?? '[]',true) ?: []);
  $checks = [
    'filtro_aceite'          => 'Filtro de aceite',
    'filtro_aire'            => 'Filtro de aire',
    'filtro_a_acondicionado' => 'Filtro de A/C',
    'filtro_caja'            => 'Filtro de caja',
    'aceite_diferencial'     => 'Aceite de diferencial',
    'filtro_combustible'     => 'Filtro de combustible',
    'aceite_hidraulico'      => 'Aceite hidráulico',
    'transfer'               => 'Transfer',
    'engrase'                => 'Engrase',
  ];

  // paleta por estado (usa nombres; ajusta si tus nombres difieren)
  $palette = [
    'nueva'       => ['#1f2937', '#111827', 'bi-asterisk'],       // gris oscuro
    'asignada'    => ['#0d6efd', '#0b5ed7', 'bi-person-check'],   // azul
    'pendiente'   => ['#ffc107', '#e0a800', 'bi-hourglass-split'],// ámbar
    'enproceso'   => ['#20c997', '#17b38a', 'bi-activity'],       // verde-menta
    'finalizada'  => ['#198754', '#157347', 'bi-check2-circle'],  // verde
  ];
@endphp

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:rgba(255, 255, 255, 0.144) !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  .md-card{
    max-width: 1180px; margin: 32px auto 64px; background:#fff;
    border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.08); padding:24px;
  }
  .md-title{ font-weight:800; color:#C24242; text-align:center; }

  .form-control, .form-select{
    border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent; padding-left:0;
  }
  .form-control:focus, .form-select:focus{ box-shadow:none; border-color:#9F3B3B; }
  .form-label{ font-size:.9rem; color:#6b7280; }

  .btn-theme{ background:#9F3B3B; border:none; color:#fff; border-radius:12px; }
  .btn-theme:hover{ background:#873131; color:#fff; }
  .btn-muted{ background:#e5e7eb; color:#111827; border:none; border-radius:12px; }

  .pane{ background:#fafafa; border:1px solid #eee; border-radius:12px; padding:16px; }

  /* -------- Cliente card -------- */
  .client-card{ position:relative; border:1px solid #eee; border-radius:12px; padding:16px; background:#fff; }
  .client-head{ display:flex; align-items:center; gap:.75rem; margin-bottom:.5rem; }
  .client-avatar{ width:42px; height:42px; border-radius:50%; display:grid; place-items:center; font-weight:800; color:#fff; background:#9F3B3B; }
  .client-title{ font-weight:700; }
  .client-sub{ font-size:.84rem; color:#6b7280; }
  .client-tag{ background:#111; color:#fff; border-radius:999px; padding:.15rem .5rem; font-size:.75rem; }

  /* -------- Chips servicio (lado derecho) -------- */
  .chip{
    padding:.45rem .85rem; border-radius:999px; font-weight:600;
    border:1px solid #e5e7eb; color:#374151; background:#fff; cursor:pointer; user-select:none;
    transition: all .15s ease-in-out;
  }
  .chip:hover{ transform: translateY(-1px); }
  .chip.active{ background:#111827; color:#fff; border-color:#111827; }

  /* -------- Selector de estado (cards) -------- */
  .status-wrap{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* se adapta y no se sale */
    gap:.5rem;
  }
  .status-card{
    position:relative;
    border-radius:12px;
    padding:.4rem .4rem;     /* más compacto */
    color:#fff;
    cursor:pointer;
    user-select:none;
    display:flex;
    flex-direction:column;
    gap:.15rem;
    min-height:45px;         /* menos alto */
    box-shadow:0 3px 10px rgba(0,0,0,.06);
    transform: translateY(0);
    transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease, outline-color .12s ease;
    opacity:.95;
  }
  .status-card .title{ font-weight:800; letter-spacing:.2px; font-size:.95rem; }
  .status-card .hint{ font-size:.76rem; line-height:1.05; opacity:.95; }
  .status-icon{ font-size:1rem; margin-right:.35rem; }
  .status-dot{ width:7px; height:7px; border-radius:50%; display:inline-block; margin-right:.35rem; background:#fff; opacity:.95 }

  .status-card:hover{
    transform: translateY(-1px);
    box-shadow:0 6px 16px rgba(0,0,0,.12);
  }

  /* ACTIVO: borde contrastado + “Activo” en la esquina  */
  .status-card.active{
    outline:3px solid rgba(255,255,255,.85);
    box-shadow:0 8px 22px rgba(0,0,0,.16);
    opacity:1;
  }
  .status-card.active::after{
    content:"Activo";
    position:absolute;
    top:6px; right:8px;
    font-size:.68rem;
    font-weight:700;
    padding:.05rem .4rem;
    border-radius:999px;
    background:rgba(255,255,255,.9);
    color:#111;
  }

  /* Breakpoint pequeño: aún más compacto */
  @media (max-width: 480px){
    .status-wrap{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    .status-card{ min-height:60px; padding:.5rem; }
    .status-card .title{ font-size:.9rem; }
    .status-card .hint{ font-size:.72rem; }
  }
  /* -------- Insumos -------- */
  .i-row{ border-bottom:1px dashed #e5e7eb; padding:.35rem 0; }
  .i-remove{ border:none; background:#fee2e2; color:#991b1b; border-radius:8px; padding:.25rem .55rem; }
  .i-remove:hover{ background:#fecaca; }

  .resume{ background:#f8f9fa; border-radius:12px; padding:16px; box-shadow:0 4px 14px rgba(0,0,0,.04) inset; }
  .resume .big{ font-size:1.25rem; font-weight:800; color:#C24242; }

  /* switches compactos */
  .form-check-input[type="checkbox"].form-switch{ width:2.6em; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
      <h2 class="md-title mb-0">Orden de Trabajo #{{ $orden->id }}</h2>
      <div class="d-flex gap-2">
        <a href="#" class="btn btn-muted"><i class="bi bi-printer me-1"></i> Imprimir</a>
        <a href="{{ route('ordenes.index') }}" class="btn btn-muted">Volver</a>
      </div>
    </div>

    @if ($errors->any())
      <div class="alert alert-danger"><strong>Corrige los errores:</strong>
        <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
      </div>
    @endif

    <form action="{{ route('ordenes.update', $orden) }}" method="POST" novalidate>
      @csrf @method('PUT')

      <div class="row g-4">

        {{-- IZQUIERDA --}}
        <div class="col-lg-7">

          {{-- Datos del Cliente --}}
          <div class="client-card mb-3">
            <div class="client-head">
              <div class="client-avatar">{{ strtoupper(substr($owner?->nombre ?? 'C',0,1)) }}</div>
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2">
                  <span class="client-title">{{ $owner?->nombre ?? 'Cliente sin asignar' }}</span>
                  @if($owner && $owner->telefono)<span class="client-tag">{{ $owner->telefono }}</span>@endif
                </div>
                <div class="client-sub">
                  Creada: {{ optional($orden->fecha_creacion)->format('d/m/Y H:i') }} · por {{ $admin }}
                </div>
              </div>
              <div>
                @if($owner)
                  <a class="btn btn-outline-dark btn-sm" href="{{ route('clientes.edit', $owner->id) }}">
                    <i class="bi bi-person-lines-fill me-1"></i> Editar contacto
                  </a>
                @endif
              </div>
            </div>
          </div>

          {{-- Descripción/Falla --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Descripción / Falla</label>
            <textarea class="form-control @error('descripcion') is-invalid @enderror" name="descripcion" rows="3"
              placeholder="Describe la falla o el requerimiento…">{{ old('descripcion', $orden->descripcion) }}</textarea>
            @error('descripcion')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>

          {{-- Checklist --}}
          <div class="pane mb-3">
            <div class="fw-semibold mb-2">Checklist</div>
            <div class="row g-2">
              @foreach($checks as $name=>$label)
                <div class="col-6 col-md-4">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           name="checks[{{ $name }}]" value="1" {{ !empty($chk[$name]) ? 'checked':'' }}>
                    <label class="form-check-label">{{ $label }}</label>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          {{-- Insumos --}}
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0 fw-bold">Productos / Servicios</h5>
            <button type="button" id="add-insumo" class="btn btn-outline-primary btn-sm">+ Agregar</button>
          </div>
          <div id="insumos-container" class="mb-2"></div>

          <div class="resume mt-3">
            <div class="row g-3">
              <div class="col-md-4"><div class="text-muted">SUBTOTAL</div><div id="sub_insumos" class="fw-bold">Q 0.00</div></div>
              <div class="col-md-4"><div class="text-muted">Mano de obra</div><div id="mo_view" class="fw-bold">Q 0.00</div></div>
              <div class="col-md-4 text-md-end"><div class="text-muted">TOTAL</div><div id="total_view" class="big">Q 0.00</div></div>
            </div>
          </div>
        </div>

        {{-- DERECHA --}}
        <div class="col-lg-5">

          {{-- Vehículo --}}
          <div class="pane mb-3">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-bold">{{ $orden->vehiculo->marca->nombre ?? 'Vehículo' }} · {{ $orden->vehiculo->linea ?? '' }} {{ $orden->vehiculo->modelo ?? '' }}</div>
                <div class="form-label mb-0">Placa</div>
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
                <a href="{{ route('vehiculos.edit', $orden->vehiculo_placa) }}" class="btn btn-outline-dark btn-sm">
                  Editar
                </a>
              </div>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-sm-6">
                <div class="form-label mb-0">Kilometraje</div>
                <input type="number" min="0" name="kilometraje"
                       class="form-control @error('kilometraje') is-invalid @enderror"
                       value="{{ old('kilometraje', $orden->kilometraje) }}">
                @error('kilometraje')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
              <div class="col-sm-6">
                <div class="form-label mb-0">Próx. servicio (km)</div>
                <input type="number" min="0" name="proximo_servicio"
                       class="form-control @error('proximo_servicio') is-invalid @enderror"
                       value="{{ old('proximo_servicio', $orden->proximo_servicio) }}">
                @error('proximo_servicio')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-sm-6">
                <div class="form-label mb-0">Técnico</div>
                <select name="tecnico_id" class="form-select @error('tecnico_id') is-invalid @enderror">
                  <option value="">Seleccione…</option>
                  @foreach($tecnicos as $t)
                    <option value="{{ $t->id }}" @selected(old('tecnico_id', optional($orden->tecnico)->id)==$t->id)>{{ $t->name }}</option>
                  @endforeach
                </select>
                @error('tecnico_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              <div class="col-sm-6">
                <div class="form-label mb-0">Mano de obra (Q)</div>
                <input type="number" step="0.01" min="0" name="costo_mo" id="costo_mo"
                       class="form-control @error('costo_mo') is-invalid @enderror"
                       value="{{ old('costo_mo', $orden->costo_mo ?? 0) }}">
                @error('costo_mo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>

          {{-- Tipo de servicio (chips movidos aquí) --}}
          @php $tsActual = (int) $orden->type_service_id; @endphp
          <input type="hidden" id="type_service_id" name="type_service_id" value="{{ old('type_service_id', $tsActual) }}">
          <div class="pane mb-3">
            <div class="form-label mb-2">Tipo de servicio</div>
            <div class="d-flex flex-wrap gap-2">
              <span class="chip svc-chip {{ $tsActual===$idCorrectivo ? 'active':'' }}" data-id="{{ $idCorrectivo }}">Reparación</span>
              <span class="chip svc-chip {{ $tsActual===$idPreventivo ? 'active':'' }}" data-id="{{ $idPreventivo }}">Mantenimiento</span>
              <span class="chip svc-chip {{ $tsActual===$idOtro ? 'active':'' }}" data-id="{{ $idOtro }}">Otro</span>
            </div>
            @error('type_service_id') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
          </div>

          {{-- Estado (nuevo selector bonito) --}}
          <div class="pane mb-3">
            <div class="form-label mb-2">Estado de la OT</div>
            <input type="hidden" name="estado_id" id="estado_id" value="{{ old('estado_id', $estadoActual) }}">
            <div class="status-wrap">
              @foreach($estadosFlow as $e)
                @php
                  $slug = \Illuminate\Support\Str::of($e->nombre)->lower()->replace(' ','')->value();
                  $pal  = $palette[$slug] ?? ['#6b7280','#4b5563','bi-circle'];
                  $isOn = (old('estado_id', $estadoActual) == $e->id);
                @endphp
                <div
                  class="status-card {{ $isOn ? 'active':'' }}"
                  data-id="{{ $e->id }}"
                  style="background: {{ $pal[0] }}; border: 1px solid {{ $pal[1] }};"
                  title="Cambiar a {{ $e->nombre }}"
                >
                  <div class="d-flex align-items-center">
                    <i class="bi {{ $pal[2] }} status-icon"></i>
                    <span class="title">{{ $e->nombre }}</span>
                  </div>

                </div>
              @endforeach
            </div>
            @error('estado_id')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
          </div>

          {{-- Vínculos --}}
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
    const estadoHidden = document.getElementById('estado_id');
    if (estadoHidden){
      document.querySelectorAll('.status-card').forEach(c=>{
        c.classList.toggle('active', c.dataset.id == String(estadoHidden.value||''));
      });
    }
  })();


(function(){
  // chips -> hidden type_service_id
  document.querySelectorAll('.svc-chip').forEach(ch=>{
    ch.addEventListener('click', ()=>{
      document.querySelectorAll('.svc-chip').forEach(c=>c.classList.remove('active'));
      ch.classList.add('active');
      document.getElementById('type_service_id').value = ch.dataset.id || '';
    });
  });

  // Uppercase placa
  const placa = document.getElementById('vehiculo_placa');
  placa?.addEventListener('input', () => placa.value = (placa.value || '').toUpperCase());

  // selector de estado (cards)
  const estadoHidden = document.getElementById('estado_id');
  document.querySelectorAll('.status-card').forEach(card=>{
    card.addEventListener('click', ()=>{
      document.querySelectorAll('.status-card').forEach(c=>c.classList.remove('active'));
      card.classList.add('active');
      estadoHidden.value = card.dataset.id;
    });
  });

  // Insumos dinámicos
  const INSUMOS   = @json($insumos->map(fn($i)=>['id'=>$i->id,'nombre'=>$i->nombre,'precio'=>(float)$i->precio]));
  const OT_ITEMS  = @json($orden->items->map(fn($x)=>['id'=>$x->insumo_id,'cantidad'=>(float)$x->cantidad])->values());
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
    } else { cont.insertAdjacentHTML('beforeend', renderRow(idx)); idx++; }
    recalc();
  }
  addBtn?.addEventListener('click', ()=>{ cont.insertAdjacentHTML('beforeend', renderRow(idx)); idx++; recalc(); });
  document.addEventListener('change', e=>{ if(e.target.classList.contains('insumo-select')) recalc(); });
  document.addEventListener('input',  e=>{ if(e.target.classList.contains('cantidad-input') || e.target.id==='costo_mo') recalc(); });
  document.addEventListener('click',  e=>{ if(e.target.classList.contains('i-remove')){ e.target.closest('.i-row').remove(); recalc(); }});
})();
</script>
@endpush
