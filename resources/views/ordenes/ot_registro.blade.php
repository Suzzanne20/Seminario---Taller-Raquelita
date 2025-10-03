@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  /* Card principal */
  .md-card{
    max-width: 1080px;
    margin: 32px auto 64px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    padding:24px;
  }

  .md-title{ font-weight:800; color:#C24242; text-align:center; }

  /* Inputs estilo material-lite */
  .form-control, .form-select{
    border:none; border-bottom:2px solid #e6e6e6; border-radius:0; background:transparent; padding-left:0;
  }
  .form-control:focus, .form-select:focus{
    box-shadow:none; border-color:#3f51b5;
  }
  .form-label{ font-size:.9rem; color:#6b7280; }

  /* Botones */
  .btn-theme{ background:#9F3B3B; border:none; color:#fff; }
  .btn-theme:hover{ background:#873131; color:#fff; }
  .btn-muted{ background:#e5e7eb; color:#111827; border:none; }
  .chip{
    padding:.45rem .85rem; border-radius:999px; font-weight:600;
    border:1px solid #e5e7eb; color:#374151; background:#fff;
  }
  .chip.active{ background:#111827; color:#fff; border-color:#111827; }

  /* Tabla-insumos */
  .i-row{ border-bottom:1px dashed #e5e7eb; padding:.35rem 0; }
  .i-remove{ border:none; background:#fee2e2; color:#991b1b; border-radius:8px; padding:.25rem .55rem; }
  .i-remove:hover{ background:#fecaca; }

  /* Totales */
  .resume{
    background:#f8f9fa; border-radius:12px; padding:16px;
    box-shadow:0 4px 14px rgba(0,0,0,.04) inset;
  }
  .resume .big{ font-size:1.25rem; font-weight:800; color:#C24242; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="md-card">

    <h2 class="md-title mb-3">Nueva Orden de Trabajo</h2>

    {{-- Errores globales --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <strong>Por favor corrige los errores:</strong>
        <ul class="mb-0">
          @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('ordenes.store') }}" method="POST" novalidate>
      @csrf

      {{-- Cabecera / vínculos rápidos --}}
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="chip active">Nueva</span>
          <span class="chip">Asignada</span>
          <span class="chip">Pendiente</span>
          <span class="chip">En proceso</span>
          <span class="chip">Finalizada</span>
        </div>
      </div>

      {{-- Sección: datos de vehículo y servicio --}}
      <div class="row g-4">
        <div class="col-12">
          <label class="form-label fw-semibold">Crear desde cotización aprobada (opcional)</label>
          <select name="cotizacion_id" id="cotizacion_id"
                  class="form-select @error('cotizacion_id') is-invalid @enderror">
            <option value="">— Sin cotización —</option>
            @foreach($cotizaciones as $c)
              <option value="{{ $c->id }}" @selected(old('cotizacion_id')==$c->id)>
                #{{ $c->id }} — {{ $c->servicio->descripcion ?? 'Servicio' }} — Total Q{{ number_format($c->total,2) }}
              </option>
            @endforeach
          </select>
          @error('cotizacion_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          <small class="text-muted">Si eliges una cotización, la placa deja de ser obligatoria.</small>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Vehículo (placa)</label>
          <select name="vehiculo_placa" id="vehiculo_placa"
                  class="form-select @error('vehiculo_placa') is-invalid @enderror">
            <option value="">Seleccione…</option>
            @foreach($vehiculos as $v)
              <option value="{{ $v->placa }}" @selected(old('vehiculo_placa')==$v->placa)>
                {{ $v->placa }} — {{ $v->linea }} {{ $v->modelo }}
              </option>
            @endforeach
          </select>
          @error('vehiculo_placa') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Tipo de servicio</label>
          <select name="type_service_id"
                  class="form-select @error('type_service_id') is-invalid @enderror" required>
            <option value="">Seleccione…</option>
            @foreach($servicios as $s)
              <option value="{{ $s->id }}" @selected(old('type_service_id')==$s->id)>
                {{ $s->descripcion }}
              </option>
            @endforeach
          </select>
          @error('type_service_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Kilometraje</label>
          <input type="number" name="kilometraje" min="0"
                 class="form-control @error('kilometraje') is-invalid @enderror"
                 value="{{ old('kilometraje') }}">
          @error('kilometraje') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Próximo servicio (km)</label>
          <input type="number" name="proximo_servicio" min="0"
                 class="form-control @error('proximo_servicio') is-invalid @enderror"
                 value="{{ old('proximo_servicio') }}">
          @error('proximo_servicio') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Costo mano de obra (Q)</label>
          <input type="number" step="0.01" min="0" name="costo_mo"
                 class="form-control @error('costo_mo') is-invalid @enderror"
                 value="{{ old('costo_mo', 0) }}" id="costo_mo">
          @error('costo_mo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Técnico asignado</label>
          <select name="tecnico_id" id="tecnico_id"
                  class="form-select @error('tecnico_id') is-invalid @enderror">
            <option value="">Seleccione…</option>
            @foreach($tecnicos as $t)
              <option value="{{ $t->id }}" @selected(old('tecnico_id')==$t->id)>{{ $t->name }}</option>
            @endforeach
          </select>
          @error('tecnico_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Descripción / Falla</label>
          <textarea name="descripcion" rows="3"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    placeholder="Describe la falla o el requerimiento…">{{ old('descripcion') }}</textarea>
          @error('descripcion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Insumos dinámicos --}}
      <hr class="my-4">
      <h5 class="fw-bold mb-2">Insumos</h5>

      <div id="insumos-container" class="mb-2"></div>

      <button type="button" id="add-insumo" class="btn btn-outline-primary btn-sm">
        + Agregar insumo
      </button>

      {{-- Resumen --}}
      <div class="resume mt-4">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted">Subtotal insumos</div>
            <div id="sub_insumos" class="fw-bold">Q 0.00</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted">Mano de obra</div>
            <div id="mo_view" class="fw-bold">Q 0.00</div>
          </div>
          <div class="col-md-4 text-md-end">
            <div class="text-muted">TOTAL</div>
            <div id="total_view" class="big">Q 0.00</div>
          </div>
        </div>
      </div>

      {{-- Botones --}}
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-theme px-4">
          <i class="bi bi-floppy me-1"></i> Guardar
        </button>
        <a href="{{ route('ordenes.index') }}" class="btn btn-muted px-4">Cancelar</a>
      </div>

    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // ========= Datos de insumos desde el backend =========
  const INSUMOS = @json($insumos);

  // ========= Helpers de formato =========
  const q = n => 'Q ' + (parseFloat(n||0)).toFixed(2);

  // ========= Render de una fila de insumo =========
  let idx = 0;
  function insumoOptions(){
    let html = '<option value="">Seleccione insumo…</option>';
    INSUMOS.forEach(i => {
      html += `<option value="${i.id}" data-precio="${i.precio ?? 0}">${i.nombre} (Q${(i.precio ?? 0).toFixed(2)})</option>`;
    });
    return html;
  }
  function renderRow(i){
    return `
      <div class="row align-items-center i-row" data-i="${i}">
        <div class="col-md-6 mb-2 mb-md-0">
          <select name="insumos[${i}][id]" class="form-select insumo-select" required>
            ${insumoOptions()}
          </select>
        </div>
        <div class="col-md-2">
          <input type="number" min="1" value="1" name="insumos[${i}][cantidad]" class="form-control cantidad-input" required>
        </div>
        <div class="col-md-2 text-end">
          <span class="precio-unit">Q 0.00</span>
        </div>
        <div class="col-md-2 text-end">
          <button type="button" class="i-remove">X</button>
        </div>
      </div>
    `;
  }

  function recalc(){
    let sub = 0;
    document.querySelectorAll('#insumos-container .i-row').forEach(row=>{
      const sel = row.querySelector('.insumo-select');
      const qty = parseFloat(row.querySelector('.cantidad-input').value) || 0;
      const price = parseFloat(sel?.selectedOptions[0]?.getAttribute('data-precio') || 0);
      row.querySelector('.precio-unit').textContent = q(price);
      if(qty>0){ sub += price * qty; }
    });
    const mo = parseFloat(document.getElementById('costo_mo').value || 0);
    document.getElementById('sub_insumos').textContent = q(sub);
    document.getElementById('mo_view').textContent = q(mo);
    document.getElementById('total_view').textContent = q(sub + mo);
  }

  // Agregar fila
  document.getElementById('add-insumo').addEventListener('click', ()=>{
    document.getElementById('insumos-container').insertAdjacentHTML('beforeend', renderRow(idx));
    idx++; recalc();
  });

  // Delegados
  document.addEventListener('change', e=>{
    if(e.target.classList.contains('insumo-select')) recalc();
  });
  document.addEventListener('input', e=>{
    if(e.target.classList.contains('cantidad-input') || e.target.id==='costo_mo') recalc();
  });
  document.addEventListener('click', e=>{
    if(e.target.classList.contains('i-remove')){
      e.target.closest('.i-row').remove(); recalc();
    }
  });

  // Placa requerida solo si NO hay cotización
  (function(){
    const cot = document.getElementById('cotizacion_id');
    const placa = document.getElementById('vehiculo_placa');
    function toggleReq(){ placa && (placa.required = !cot.value); }
    cot?.addEventListener('change', toggleReq);
    toggleReq();
  })();
</script>
@endpush
