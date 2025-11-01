<div class="modal fade" id="otQuickModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:14px;">
      <div class="modal-header">
        <h5 class="modal-title">Editar OT #{{ $orden->id }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      {{-- NOTA: ponemos data-* con JSON para que el JS lo lea sin depender de <script type=json> --}}
      <form id="formQuickOT"
            action="{{ route('ordenes.update', $orden->id) }}"
            method="POST"
            novalidate
            data-items='@json($orden->items->map(fn($x)=>["id"=>(int)$x->insumo_id,"cantidad"=>(float)$x->cantidad])->values())'
            data-insumos='@json($insumos->map(fn($i)=>["id"=>(int)$i->id,"nombre"=>$i->nombre,"precio"=>(float)$i->precio])->values())'>
        @csrf
        @method('PUT')

        <div class="modal-body">
          <style>
            .opt-pill{display:inline-flex;align-items:center;gap:.4rem;margin-right:.6rem}
            .opt-pill .dot{width:10px;height:10px;border-radius:50%}
            .dot.nueva{background:#222}
            .dot.asignada{background:#0d6efd}
            .dot.pendiente{background:#ffc107}
            .dot.enproceso{background:#20c997}
            .dot.finalizada{background:#198754}
          </style>

          <input type="hidden" name="type_service_id" value="{{ $orden->type_service_id }}">

          <div class="row g-3">
            {{-- IZQUIERDA --}}
            <div class="col-lg-7">
              {{-- Cliente --}}
              @php $owner = optional($orden->vehiculo->clientes)->first(); @endphp
              <div class="p-3 border rounded-3">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-bold mb-1">
                      <span id="qe_cliente_nombre">{{ $owner?->nombre ?? '—' }}</span>
                      @if($owner && $owner->telefono)
                        <span class="badge text-bg-dark ms-2">{{ $owner->telefono }}</span>
                      @endif
                    </div>
                    <div class="text-muted small">Datos del Cliente</div>
                  </div>
                  <div>
                    <button type="button" id="btnClientePanel" class="btn btn-danger">
                      {{ $owner ? 'Cambiar datos de cliente' : 'Añadir datos de cliente' }}
                    </button>
                  </div>
                </div>

                {{-- Panel para seleccionar cliente --}}
                <div id="clientePanel" class="mt-3" style="display:none;">
                  <div class="row g-2 align-items-end">
                    <div class="col-md-9">
                      <label class="form-label">Seleccionar cliente</label>
                      <input type="text" class="form-control" id="cliSearch" placeholder="Escribe para filtrar…">
                      <select class="form-select mt-1" id="cliSelect">
                        @foreach($clientes as $c)
                          <option value="{{ $c->id }}">
                            {{ str_pad($c->id,2,'0',STR_PAD_LEFT) }} - {{ $c->nombre }}{{ $c->telefono ? ' ('.$c->telefono.')':'' }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <button type="button" class="btn btn-dark w-100" id="btnVincularCliente">Usar</button>
                      <a href="{{ route('clientes.create') }}" class="btn btn-outline-primary w-100 mt-2" target="_blank">+ Nuevo</a>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Descripción --}}
              <div class="mt-3">
                <label class="form-label">Descripción / Falla</label>
                <textarea name="descripcion" rows="3" class="form-control">{{ $orden->descripcion }}</textarea>
              </div>

              {{-- Checklist --}}
              @php
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
              @endphp
              <div class="mt-3 p-3 border rounded-3">
                <div class="fw-semibold mb-2">Checklist</div>
                <div class="row g-2">
                  @foreach($checks as $name=>$label)
                    <div class="col-6 col-md-4">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               name="checks[{{ $name }}]" value="1"
                               {{ !empty($chk[$name]) ? 'checked':'' }}>
                        <label class="form-check-label">{{ $label }}</label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

              {{-- Insumos --}}
              <div class="mt-3">
                <div class="d-flex justify-content-between align-items-center">
                  <label class="form-label fw-semibold mb-0">Productos / Servicios</label>
                  <button type="button" class="btn btn-outline-primary btn-sm" id="qe_add">+ Agregar</button>
                </div>
                <div id="qe_items" class="mt-1"></div>
              </div>
            </div>

            {{-- DERECHA --}}
            <div class="col-lg-5">
              <div class="p-3 border rounded-3">
                <label class="form-label">Vehículo (placa)</label>
                <input name="vehiculo_placa" id="qe_placa" list="qe_placas" class="form-control"
                       value="{{ $orden->vehiculo_placa }}" maxlength="7" style="text-transform:uppercase">
                <datalist id="qe_placas">
                  @foreach($vehiculos as $v)
                    <option value="{{ $v->placa }}">{{ $v->placa }} — {{ $v->linea }} {{ $v->modelo }}</option>
                  @endforeach
                </datalist>

                <div class="row g-2 mt-2">
                  <div class="col-6">
                    <label class="form-label">Kilometraje</label>
                    <input type="number" name="kilometraje" class="form-control" value="{{ $orden->kilometraje }}">
                  </div>
                  <div class="col-6">
                    <label class="form-label">Próx. servicio (km)</label>
                    <input type="number" name="proximo_servicio" class="form-control" value="{{ $orden->proximo_servicio }}">
                  </div>
                  <div class="col-6">
                    <label class="form-label">Técnico</label>
                    <select name="tecnico_id" class="form-select">
                      <option value="">Seleccione…</option>
                      @foreach($tecnicos as $t)
                        <option value="{{ $t->id }}"
                          @selected(optional($orden->tecnico)->id == $t->id)>{{ $t->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-6">
                    <label class="form-label">Mano de obra (Q)</label>
                    <input type="number" step="0.01" min="0" name="costo_mo" id="qe_mo"
                           class="form-control" value="{{ $orden->costo_mo ?? 0 }}">
                  </div>
                </div>
              </div>

              <div class="p-3 border rounded-3 mt-3">
                <label class="form-label">Estado</label>
                <select name="estado_id" id="qe_estado" class="form-select">
                  @foreach($estadosFlow as $e)
                    @php $slug = \Illuminate\Support\Str::of($e->nombre)->lower()->replace(' ','')->value(); @endphp
                    <option value="{{ $e->id }}" data-dot="{{ $slug }}" @selected($orden->estado_id==$e->id)>{{ $e->nombre }}</option>
                  @endforeach
                </select>
                <div class="mt-2 small text-muted">
                  <span class="opt-pill"><span class="dot nueva"></span>Nueva</span>
                  <span class="opt-pill"><span class="dot asignada"></span>Asignada</span>
                  <span class="opt-pill"><span class="dot pendiente"></span>Pendiente</span>
                  <span class="opt-pill"><span class="dot enproceso"></span>En Proceso</span>
                  <span class="opt-pill"><span class="dot finalizada"></span>Finalizada</span>
                </div>
              </div>

              @php
                $insTotal = $orden->items->sum(fn($it)=> (float)$it->insumo->precio * (float)$it->cantidad);
                $mo = (float)($orden->costo_mo ?? 0);
              @endphp
              <div class="p-3 border rounded-3 mt-3">
                <div class="d-flex justify-content-between"><span>Insumos</span><strong id="qe_ins">Q {{ number_format($insTotal,2) }}</strong></div>
                <div class="d-flex justify-content-between"><span>Mano de obra</span><strong id="qe_mo_view">Q {{ number_format($mo,2) }}</strong></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between"><span>Total</span><strong id="qe_total">Q {{ number_format($insTotal + $mo,2) }}</strong></div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-dark" id="qe_save" type="submit">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.initQuickEditOT = function(root){
  const $  = (sel,scope=root)=> (scope||root).querySelector(sel);
  const $$ = (sel,scope=root)=> Array.from((scope||root).querySelectorAll(sel));
  const q  = n => 'Q ' + (Number(n||0)).toFixed(2);

  const form    = $('#formQuickOT');
  const itemsCt = $('#qe_items');
  const addBtn  = $('#qe_add');

  // ============= Cliente: toggle panel + filtro + set hidden =============
  const btnPanel = $('#btnClientePanel');
  const panel    = $('#clientePanel');
  btnPanel?.addEventListener('click', ()=>{
    if (!panel) return;
    panel.style.display = (panel.style.display==='block') ? 'none':'block';
  });
  const cliSearch = $('#cliSearch');
  const cliSelect = $('#cliSelect');
  cliSearch?.addEventListener('input', ()=>{
    const term = (cliSearch.value||'').toLowerCase();
    [...(cliSelect?.options||[])].forEach(opt => opt.hidden = !opt.text.toLowerCase().includes(term));
  });
  $('#btnVincularCliente')?.addEventListener('click', ()=>{
    const id = cliSelect?.value;
    if (!id) return;
    // crea/actualiza el hidden cliente_id para el update
    let h = form.querySelector('input[name="cliente_id"]');
    if (!h){ h=document.createElement('input'); h.type='hidden'; h.name='cliente_id'; form.appendChild(h); }
    h.value = id;
    $('#qe_cliente_nombre').textContent = cliSelect.selectedOptions[0].text.replace(/^\d+\s*-\s*/,'');
    panel.style.display='none';
  });

  // ============= Insumos dinámicos =============
  const INS = JSON.parse(form.dataset.insumos || '[]'); // catálogo
  const PRE = JSON.parse(form.dataset.items   || '[]'); // items actuales

  let idx = 0;

  function optHtml(sel=''){
    let h = '<option value="">Seleccione…</option>';
    INS.forEach(i=>{
      const p = Number(i.precio||0).toFixed(2);
      const s = String(sel)==String(i.id)?'selected':'';
      h+=`<option value="${i.id}" ${s} data-precio="${p}">${i.nombre} (Q${p})</option>`;
    });
    return h;
  }

  function rowHtml(i, preset){
    return `
      <div class="row g-2 align-items-center border-bottom py-1" data-i="${i}">
        <div class="col-7">
          <select class="form-select ins" name="insumos[${i}][id]" required>
            ${optHtml(preset?.id||'')}
          </select>
        </div>
        <div class="col-2">
          <input type="number" min="1" value="${preset?.cantidad||1}" class="form-control qty" name="insumos[${i}][cantidad]" required>
        </div>
        <div class="col-2 text-end"><span class="uprice">Q 0.00</span></div>
        <div class="col-1 text-end"><button class="btn btn-sm btn-outline-danger del" type="button">X</button></div>
      </div>`;
  }

  function recalc(){
    let sub=0;
    $$('#qe_items [data-i]').forEach(r=>{
      const sel=r.querySelector('.ins');
      const qty=Number(r.querySelector('.qty').value)||0;
      const up = Number(sel?.selectedOptions[0]?.getAttribute('data-precio')||0);
      r.querySelector('.uprice').textContent = q(up);
      if(qty>0) sub += up*qty;
    });
    $('#qe_ins').textContent = q(sub);
    const mo = Number($('#qe_mo')?.value||0);
    $('#qe_mo_view').textContent = q(mo);
    $('#qe_total').textContent   = q(sub+mo);
  }

  // Render inicial
  (PRE.length?PRE:[{}]).forEach(it=>{ itemsCt.insertAdjacentHTML('beforeend', rowHtml(idx,it)); idx++; });
  recalc();

  // Delegados
  addBtn?.addEventListener('click', ()=>{ itemsCt.insertAdjacentHTML('beforeend', rowHtml(idx)); idx++; recalc(); });
  itemsCt.addEventListener('change', e=>{ if(e.target.classList.contains('ins')) recalc(); });
  itemsCt.addEventListener('input',  e=>{ if(e.target.classList.contains('qty') || e.target.id==='qe_mo') recalc(); });
  itemsCt.addEventListener('click',  e=>{ if(e.target.classList.contains('del')){ e.target.closest('[data-i]').remove(); recalc(); } });
  $('#qe_mo')?.addEventListener('input', recalc);

  // Placa en mayúsculas
  $('#qe_placa')?.addEventListener('input', e=> e.target.value = e.target.value.toUpperCase());

  // ============= Submit AJAX PUT =============
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const url = form.getAttribute('action');
    const fd  = new FormData(form);
    // Laravel entenderá @method('PUT'), pero reforzamos por header
    const resp= await fetch(url, { method:'POST',
      headers:{ 'X-HTTP-Method-Override':'PUT', 'Accept':'application/json' }, body:fd });
    if (resp.ok){ location.reload(); }
    else {
      let msg='Error al guardar. Revisa los campos.';
      try{ const j=await resp.json(); if (j.message) msg=j.message; }catch(_){}
      alert(msg);
    }
  });
};
</script>
