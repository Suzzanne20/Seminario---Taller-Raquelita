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

  /* Separadores visuales + consistencia */
  .section-title{ font-weight:800; color:#0f172a; letter-spacing:.2px; }
  .hr-soft{ border:0; border-top:1px solid #eef2f7; margin:1.25rem 0; }

  /* Etiquetas e inputs alineados */
  .form-label{ font-size:.9rem; color:#64748b; }
  .form-control, .form-select{ padding-left:0; }

  /* Pastillas de estados, más compactas */
  .chip{ padding:.4rem .75rem; border-radius:999px; border:1px solid #e5e7eb; }
  .chip.active{ background:#111827; color:#fff; border-color:#111827; }

  /* Bloque resumen fijo abajo del card en desktop */
  @media (min-width: 992px){
    .resume{ position: sticky; bottom: 0; }
  }

</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const INSUMOS = @json($insumos ?? []);
  const q = n => 'Q ' + (parseFloat(n||0)).toFixed(2);

  let idx = 0;
  function insumoOptions(){
    let html = '<option value="">Seleccione insumo…</option>';
    if (Array.isArray(INSUMOS) && INSUMOS.length) {
      INSUMOS.forEach(i => {
        const precio = Number(i.precio || 0).toFixed(2);
        html += `<option value="${i.id}" data-precio="${precio}">${i.nombre} (Q${precio})</option>`;
      });
    }
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
      </div>`;
  }

  function recalc(){
    let sub = 0;
    document.querySelectorAll('#insumos-container .i-row').forEach(row=>{
      const sel = row.querySelector('.insumo-select');
      const qty = parseFloat(row.querySelector('.cantidad-input').value) || 0;
      const price = parseFloat(sel?.selectedOptions[0]?.getAttribute('data-precio') || 0);
      row.querySelector('.precio-unit').textContent = q(price);
      if (qty>0) sub += price * qty;
    });
    const mo = parseFloat(document.getElementById('costo_mo').value || 0);
    document.getElementById('sub_insumos').textContent = q(sub);
    document.getElementById('mo_view').textContent  = q(mo);
    document.getElementById('total_view').textContent= q(sub + mo);
  }

  const addBtn = document.getElementById('add-insumo');
  const cont   = document.getElementById('insumos-container');

  if (addBtn && cont) {
    addBtn.addEventListener('click', ()=>{
      cont.insertAdjacentHTML('beforeend', renderRow(idx));
      idx++; recalc();
    });
  }

  document.addEventListener('change', e=>{
    if (e.target.classList.contains('insumo-select')) recalc();
  });
  document.addEventListener('input', e=>{
    if (e.target.classList.contains('cantidad-input') || e.target.id==='costo_mo') recalc();
  });
  document.addEventListener('click', e=>{
    if (e.target.classList.contains('i-remove')) {
      e.target.closest('.i-row').remove(); recalc();
    }
  });

  // crea una fila por defecto para que se vea algo
  if (cont && !cont.children.length) {
    cont.insertAdjacentHTML('beforeend', renderRow(idx));
    idx++; recalc();
  }

  // Placa siempre requerida
  const placa = document.getElementById('vehiculo_placa');
  if (placa) placa.required = true;
});
</script>
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

      {{-- Sección: datos de vehículo y servicio --}}
      <hr class="hr-soft">
      <div class="row g-4">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Cotización previa (opcional)</label>
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
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold d-flex align-items-center justify-content-between">
            <span>Cliente</span>
          </label>

          <select name="cliente_id" id="cliente_id"
                  class="form-select @error('cliente_id') is-invalid @enderror">
            <option value="">— Seleccione —</option>
            @foreach($clientes as $cli)
              <option value="{{ $cli->id }}">
                {{ str_pad($cli->id, 2, '0', STR_PAD_LEFT) }} - {{ $cli->nombre }}
              </option>
            @endforeach
          </select>
          @error('cliente_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

          <small class="text-muted">Si no existe, créalo con el botón “Nuevo”.</small>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill"
                    data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
              <i class="bi bi-plus-lg"></i> Nuevo
            </button>
        </div>

        
        <div class="col-md-4">
          <label class="form-label fw-semibold">Vehículo (placa)</label>
          <select name="vehiculo_placa" id="vehiculo_placa"
                  class="form-select @error('vehiculo_placa') is-invalid @enderror" required>
            <option value="">— Seleccione —</option>
            @foreach($vehiculos as $v)
              <option value="{{ $v->placa }}">
                {{ $v->placa }} — {{ $v->linea }} {{ $v->modelo }}
              </option>
            @endforeach
          </select>
          @error('vehiculo_placa') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          <small class="text-muted">Escribe para buscar por placa</small>
        </div>

        @push('scripts')
          <script>
          document.addEventListener('DOMContentLoaded', function(){
            const placa = document.getElementById('vehiculo_placa');
            if (!placa) return;
            placa.addEventListener('input', ()=> placa.value = placa.value.toUpperCase());
          });
          </script>
        @endpush

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

        <div class="col-md-4">
          <label class="form-label fw-semibold">Costo mano de obra (Q)</label>
          <input type="number" step="0.01" min="0" name="costo_mo"
                 class="form-control @error('costo_mo') is-invalid @enderror"
                 value="{{ old('costo_mo', 0) }}" id="costo_mo">
          @error('costo_mo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

      <hr class="hr-soft">
<h5 class="fw-bold mb-2">Checklist de mantenimiento</h5>

@php
  $checks = [
    'filtro_aceite'           => 'Filtro de aceite',
    'filtro_aire'             => 'Filtro de aire',
    'filtro_a_acondicionado'  => 'Filtro de aire acondicionado',
    'filtro_caja'             => 'Filtro de caja de transmisión',
    'aceite_diferencial'      => 'Aceite de diferencial',
    'filtro_combustible'      => 'Filtro de combustible',
    'aceite_hidraulico'       => 'Aceite hidráulico',
    'transfer'                => 'Transfer',
    'engrase'                 => 'Engrase',
  ];
@endphp

<div class="card border-0" style="background:#f9fafb;border-radius:12px;">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div class="fw-semibold text-muted">Marca los cambios a realizar</div>
      <div class="d-flex gap-2">
        <button type="button" id="mark-full" class="btn btn-light btn-sm rounded-pill">Marcar todo</button>
        <button type="button" id="unmark-all" class="btn btn-outline-secondary btn-sm rounded-pill">Quitar todo</button>
      </div>
    </div>

    <div class="row g-2">
      @foreach($checks as $name => $label)
        <div class="col-12 col-sm-6 col-lg-4">
          <label class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 border bg-white h-100"
                 style="border-color:#e5e7eb;">
            <input type="checkbox"
                   name="checks[{{ $name }}]"
                   value="1"                                              {{-- importante --}}
                   class="form-check-input m-0">
            <span class="small">{{ $label }}
            </span>
          </label>
        </div>
      @endforeach
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.getElementById('mark-full')?.addEventListener('click', ()=>{
    document.querySelectorAll('input[name^="checks["]').forEach(cb=> cb.checked = true);
  });
  document.getElementById('unmark-all')?.addEventListener('click', ()=>{
    document.querySelectorAll('input[name^="checks["]').forEach(cb=> cb.checked = false);
  });
</script>
@endpush

@push('scripts')
<script>
  document.getElementById('mark-full')?.addEventListener('click', ()=>{
    document.querySelectorAll('input[name^="checks["]').forEach(cb=> cb.checked = true);
  });
  document.getElementById('unmark-all')?.addEventListener('click', ()=>{
    document.querySelectorAll('input[name^="checks["]').forEach(cb=> cb.checked = false);
  });
</script>
@endpush        

        <div class="col-12">
        <hr class="hr-soft">
        <h5 class="fw-bold mb-2">Descripción / Falla</h5>
          <textarea name="descripcion" rows="3"
                    class="form-control @error('descripcion') is-invalid @enderror"
                    placeholder="Describe la falla o el requerimiento…">{{ old('descripcion') }}</textarea>
          @error('descripcion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Insumos dinámicos --}}

      <hr class="hr-soft">
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
{{-- Modal para registrar cliente desde OT --}}
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:14px;">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="formQuickCliente">
          @csrf
          <div class="mb-2">
            <label class="form-label">Nombre</label>
            <input name="nombre" type="text" class="form-control" required maxlength="45">
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">NIT</label>
              <input name="nit" type="text" class="form-control" maxlength="20" placeholder="CF o 1234567-8">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input name="telefono" type="text" class="form-control" required maxlength="20" placeholder="5555-5555">
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label">Dirección</label>
            <input name="direccion" type="text" class="form-control" maxlength="60">
          </div>
          <div id="quickCliErrors" class="text-danger small mt-2" style="display:none;"></div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-muted" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" id="btnSaveQuickCliente" class="btn btn-theme">Guardar</button>
      </div>
    </div>
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


document.addEventListener('DOMContentLoaded', () => {
  // Cliente: buscar por id y nombre
  new TomSelect('#cliente_id', {
    maxOptions: 1000,
    searchField: ['text'],         // busca en toda la etiqueta renderizada "01 - Nombre"
    allowEmptyOption: true,
    create: false,
    sortField: [{field:'text', direction:'asc'}],
    render: {
      option: (data) => {
        // data.text ya trae "01 - Nombre"
        return `<div>${data.text}</div>`;
      }
    }
  });

  // Placa: buscar por placa o descripción
  new TomSelect('#vehiculo_placa', {
    maxOptions: 2000,
    searchField: ['value','text'], // placa y texto mostrado
    allowEmptyOption: true,
    create: false,
    render: {
      option: (data) => `<div><strong>${data.value}</strong> <span class="text-muted">${data.text.replace(data.value+' — ','')}</span></div>`
    },
    onChange: (val)=>{
      // fuerza uppercase en el value seleccionado
      if(val){
        const sel = document.querySelector('#vehiculo_placa');
        if(sel){
          const opt = [...sel.options].find(o=>o.value.toUpperCase()===val.toUpperCase());
          if(opt) sel.value = opt.value.toUpperCase();
        }
      }
    }
  });

  // Cuando se cree un cliente por el modal, añadimos la opción "ID - Nombre" y lo seleccionamos
  window.__addClienteToSelect = function(id, nombre){
    const ddl = document.getElementById('cliente_id').tomselect;
    const label = String(id).padStart(2,'0') + ' - ' + nombre;
    ddl.addOption({value:id, text:label});
    ddl.addItem(String(id), true);
  };
});



  // ========= Modal Quick para registro de Cliente y enlace a vehiculo desde OT =========
(function(){
  const form  = document.getElementById('formQuickCliente');
  const save  = document.getElementById('btnSaveQuickCliente');
  const ddl   = document.getElementById('cliente_id');
  const errs  = document.getElementById('quickCliErrors');

  function getCsrf(){
    const el = document.querySelector('input[name="_token"]');
    return el ? el.value : '';
  }

  async function quickStore(){
    errs.style.display = 'none';
    errs.innerHTML = '';

    const fd = new FormData(form);

    const resp = await fetch("{{ route('clientes.quickStore') }}", {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept':'application/json' },
      body: fd
    });

    if (resp.ok){
      const data = await resp.json();
      // agrega opción y selecciona
      window.__addClienteToSelect(data.cliente.id, data.cliente.nombre);
      ddl.dispatchEvent(new Event('change'));

      // cierra modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
      modal.hide();
      form.reset();
      return;
    }

    // errores de validación
    const j = await resp.json().catch(()=>({}));
    const list = [];
    if (j.errors){
      Object.values(j.errors).forEach(arr => arr.forEach(msg => list.push(msg)));
    } else if (j.message){ list.push(j.message); }
    errs.innerHTML = list.map(m=>`• ${m}`).join('<br>');
    errs.style.display = 'block';
  }

  save?.addEventListener('click', quickStore);
})();
</script>






@endpush
