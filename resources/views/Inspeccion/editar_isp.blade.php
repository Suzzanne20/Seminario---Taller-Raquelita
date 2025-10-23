@extends('layouts.app') 
@section('title','Editar Inspección 360')

@php
  // Normaliza puntos guardados (detalles_json) para JS
  $empty = ['front'=>[], 'top'=>[], 'right'=>[], 'left'=>[], 'back'=>[]];
  $raw = $rec->detalles_json;
  if (is_string($raw)) {
      $decoded = json_decode($raw, true);
      $marks = is_array($decoded) ? array_merge($empty, $decoded) : $empty;
  } elseif (is_array($raw)) {
      $marks = array_merge($empty, $raw);
  } else {
      $marks = $empty;
  }
@endphp

@push('styles')
<style>
  :root{--brand:#8f2f2f;--line:#e6e6e6;--panel:#fff;--bg:#f7f7f8}
  body{background:var(--bg)}
  .isp-nav{background:var(--brand);color:#fff;padding:18px 16px 12px;border-radius:12px 12px 0 0;margin-bottom:10px}
  .sections{display:flex;gap:12px;flex-wrap:wrap}
  .tab{appearance:none;border:1px solid transparent;cursor:pointer;font-weight:700;padding:10px 16px;color:#fff;background:rgba(255,255,255,.12);border-radius:999px;transition:.2s}
  .tab.is-active{background:#fff;color:var(--brand)!important;border-color:#fff;box-shadow:0 3px 10px rgba(0,0,0,.12)}

  .container{max-width:1200px;margin:0 auto 16px;padding:0 16px;display:grid;grid-template-columns:1.25fr .95fr;gap:18px}
  @media (max-width:1000px){.container{grid-template-columns:1fr}}
  .canvas{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:12px}
  .vehicle-area{position:relative;background:#fff;border:1px dashed var(--line);border-radius:12px;min-height:428px;padding:14px;display:flex;align-items:center;justify-content:center;overflow:hidden}
  .vehicle-area img{max-width:100%;height:auto;display:block}
  .marker{position:absolute;width:18px;height:18px;border-radius:50%;background:#e95d5d;border:2px solid #fff;box-shadow:0 1px 2px rgba(0,0,0,.25);transform:translate(-50%,-50%)}

  .panel{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:12px}
  .meta{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  @media (max-width:600px){.meta{grid-template-columns:1fr}}
  .meta label{font-size:12px;opacity:.8;margin-bottom:4px;display:block}
  .meta input,.meta select,.meta textarea{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:#fff}

  .issues{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:10px}
  .issue{display:flex;align-items:center;gap:10px;background:#f6f6f7;border:1px solid var(--line);border-radius:10px;padding:8px 10px}
  .issue .num{font-weight:700}
  .issue input[type='text']{flex:1;padding:8px 10px;border:1px solid var(--line);border-radius:8px}
  .iconbtn{background:#fff;border:1px solid var(--line);border-radius:10px;padding:6px 8px;display:inline-flex;align-items:center;gap:6px;cursor:pointer}

  .btn{padding:10px 12px;border-radius:10px;border:1px solid var(--line);background:#fff;cursor:pointer;font-weight:600}
  .btn.primary{background:var(--brand);border-color:var(--brand);color:#fff!important}
  .btn.danger{border-color:#e74c3c;color:#e74c3c!important}

  /* Etiquetas de estado */
  .tag{font-size:11px;font-weight:800;border-radius:999px;padding:2px 8px;line-height:1}
  .tag-n{background:#e8f5e9;color:#1b5e20;border:1px solid #c8e6c9}   /* N (Nuevo) */
  .tag-m{background:#fff3e0;color:#e65100;border:1px solid #ffe0b2}   /* M (Modificado) */
  .tag-i{background:#eef2ff;color:#1e3a8a;border:1px solid #dbe4ff}   /* I (Inicial/Primer registro) */
  .legend{display:flex;gap:8px;flex-wrap:wrap;font-size:12px;opacity:.85}
</style>
@endpush

@section('content')
<form class="isp-form" action="{{ route('inspecciones.update', $rec) }}" method="POST" enctype="multipart/form-data">
  @csrf @method('PUT')

  @if (session('error'))
    <div style="background:#fdecea;border:1px solid #e74c3c;color:#922b21;padding:10px;border-radius:8px;margin:10px 0;">
      {{ session('error') }}
    </div>
  @endif
  @if ($errors->any())
    <div style="background:#fff3cd;border:1px solid #ffec99;color:#8a6d3b;padding:10px;border-radius:8px;margin:10px 0;">
      <ul style="margin:0;padding-left:18px;">@foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
    </div>
  @endif

  <header class="isp-nav">
    <nav class="sections" role="tablist">
      <button class="tab is-active" type="button" data-panel="front">Parte delantera</button>
      <button class="tab" type="button" data-panel="top">Superior</button>
      <button class="tab" type="button" data-panel="right">Lado derecho</button>
      <button class="tab" type="button" data-panel="left">Lado izquierdo</button>
      <button class="tab" type="button" data-panel="back">Parte trasera</button>
    </nav>
  </header>

  <main class="container">
    {{-- Lienzo --}}
    <section class="canvas">
      <div class="vehicle-area" id="vehicleArea">
        <img id="sectionImage" src="{{ Vite::asset('resources/otros/assets/sections/front.jpg') }}" alt="Sección actual">
      </div>
      <small style="opacity:.75;display:block;margin-top:6px">
        Click en la carrocería para añadir un punto. 
        <span class="legend ms-1">
          <span class="tag tag-i">I</span> Inicial (primer registro)
          <span class="tag tag-m">M</span> Modificado
          <span class="tag tag-n">N</span> Nuevo
        </span>
      </small>
    </section>

    {{-- Panel derecho --}}
    <aside class="panel">
      <div class="meta">
        <div>
          <label>Técnico</label>
          <input type="text" value="{{ optional($rec->tecnicoRel)->name ?? '' }}" disabled>
        </div>
        <div>
          <label>Fecha</label>
          <input type="text" value="{{ optional($rec->fecha_creacion)->format('Y-m-d H:i') }}" disabled>
        </div>
        <div>
          <label for="vehiculo_placa">Placa</label>
          <input type="text" id="vehiculo_placa" name="vehiculo_placa" maxlength="7" value="{{ $rec->vehiculo_placa }}">
        </div>
        <div>
          <label for="type_vehiculo_id">Tipo de vehículo</label>
          <select id="type_vehiculo_id" name="type_vehiculo_id" required>
            <option value="" hidden>Selecciona…</option>
            @foreach(($tipos ?? []) as $t)
              <option value="{{ $t->id }}" @selected($rec->type_vehiculo_id==$t->id)>{{ $t->descripcion }}</option>
            @endforeach
          </select>
        </div>
        <div style="grid-column:1 / -1">
          <label for="observaciones">Observaciones</label>
          <textarea id="observaciones" name="observaciones" rows="3">{{ $rec->observaciones }}</textarea>
        </div>
      </div>

      <h3 style="margin:6px 0 0">Detalles marcados</h3>
      <ol class="issues" id="issuesList"></ol>

      <input type="hidden" name="detalles_json" id="detalles_json">

      <div style="display:flex;gap:10px;margin-top:6px;flex-wrap:wrap">
        <button class="btn" type="button" id="clearBtn">Limpiar sección</button>
        <button class="btn primary" type="submit">Guardar cambios</button>
        <a class="btn" href="{{ route('inspecciones.show',$rec) }}">Cancelar</a>
      </div>
    </aside>
  </main>

  {{-- Visor de imagen --}}
  <dialog id="viewer" style="border:none;border-radius:16px;padding:0;max-width:90vw">
    <div style="padding:12px 12px 0">
      <img id="viewerImg" alt="Detalle" style="max-width:90vw;max-height:75vh;display:block;border-radius:10px">
      <div style="display:flex;justify-content:flex-end;padding:12px">
        <button id="viewerClose" class="btn" type="button">Cerrar</button>
      </div>
    </div>
  </dialog>
</form>

{{-- ELIMINAR --}}
<div style="max-width:1200px;margin:0 auto 16px;padding:0 16px">
  <form action="{{ route('inspecciones.destroy', $rec) }}" method="POST"
        onsubmit="return confirm('¿Seguro que deseas eliminar esta inspección?');">
    @csrf @method('DELETE')
    <button type="submit" class="btn danger">Eliminar inspección</button>
  </form>
</div>

{{-- ===== JS ===== --}}
<script>
  // Secciones
  const sectionImages = {
    front: @json(Vite::asset('resources/otros/assets/sections/front.jpg')),
    top:   @json(Vite::asset('resources/otros/assets/sections/top.jpg')),
    right: @json(Vite::asset('resources/otros/assets/sections/right.jpg')),
    left:  @json(Vite::asset('resources/otros/assets/sections/left.jpg')),
    back:  @json(Vite::asset('resources/otros/assets/sections/back.jpg')),
  };

  // Estado original y de trabajo
  const originalState = @json($marks) || {front:[],top:[],right:[],left:[],back:[]};
  const state = JSON.parse(JSON.stringify(originalState)); // copia editable

  let current = 'front';
  const tabs        = document.querySelectorAll('.isp-nav .tab');
  const vehicleArea = document.getElementById('vehicleArea');
  const sectionImg  = document.getElementById('sectionImage');
  const issuesList  = document.getElementById('issuesList');
  const clearBtn    = document.getElementById('clearBtn');
  const viewer      = document.getElementById('viewer');
  const viewerImg   = document.getElementById('viewerImg');
  const viewerClose = document.getElementById('viewerClose');
  const detallesJson= document.getElementById('detalles_json');
  const form        = document.querySelector('form.isp-form');

  tabs.forEach(tab => tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('is-active'));
    tab.classList.add('is-active');
    setSection(tab.dataset.panel);
  }));

  function setSection(key){
    current = key;
    sectionImg.src = sectionImages[current];
    render();
  }

  // Añadir punto haciendo click (siempre "N")
  vehicleArea.addEventListener('click', (e) => {
    const rect = vehicleArea.getBoundingClientRect();
    const isIn = (e.target === vehicleArea || e.target === sectionImg);
    if(!isIn) return;
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    const item = { x, y, text:'', image:null, status:'N' };
    state[current].push(item);
    render();
    setTimeout(() => issuesList.querySelector('li:last-child input')?.focus(), 0);
  });

  function isEqualPoint(a,b){
    if(!a || !b) return false;
    const fx=v=>Number.parseFloat(v ?? 0).toFixed(2);
    return fx(a.x)===fx(b.x) && fx(a.y)===fx(b.y) && String(a.text||'')===String(b.text||'');
  }

  // I = inicial (igual al original), M = modificado, N = nuevo
  function computeStatus(sec, idx){
    const cur = state[sec][idx];
    const org = (originalState[sec]||[])[idx];

    if(!org){ cur.status = 'N'; return 'N'; }              // no existía antes
    if(isEqualPoint(cur, org)){ cur.status = 'I'; return 'I'; } // igual al primer registro
    cur.status = 'M'; return 'M';                           // cambió respecto al original
  }

  function render(){
    // Limpia markers
    [...vehicleArea.querySelectorAll('.marker')].forEach(m => m.remove());

    // Pinta markers y lista
    issuesList.innerHTML='';
    (state[current]||[]).forEach((it, i) => {
      // Marker
      const m = document.createElement('div');
      m.className = 'marker';
      m.style.left = (it.x||0) + '%';
      m.style.top  = (it.y||0) + '%';
      m.title = `${i+1}. ${it.text || 'Detalle'}`;
      vehicleArea.appendChild(m);

      // Item de lista
      const li   = document.createElement('li'); li.className='issue';
      const num  = document.createElement('span'); num.className='num'; num.textContent = (i+1)+'.';

      // Recalcula estado (I/M/N)
      const st = computeStatus(current, i);
      const badge = document.createElement('span');
      if(st){
        badge.className = 'tag ' + (st==='N'?'tag-n':st==='M'?'tag-m':'tag-i');
        badge.textContent = st;
      }

      const input = document.createElement('input');
      input.type='text'; input.placeholder='Escribe el detalle'; input.value = it.text || '';
      input.addEventListener('input', () => { it.text = input.value; render(); });

      const btnImg  = document.createElement('button');
      btnImg.className='iconbtn'; btnImg.type='button'; btnImg.title='Agregar/Cambiar imagen'; btnImg.textContent='🖼';
      btnImg.addEventListener('click', () => pickImageFor(it));

      const btnView = document.createElement('button');
      btnView.className='iconbtn'; btnView.type='button'; btnView.title='Ver imagen'; btnView.textContent='👁';
      btnView.disabled = !it.image && !it.stream_url;
      btnView.addEventListener('click', () => {
        const src = it.image || it.stream_url;
        if(!src) return; viewerImg.src = src; viewer.showModal();
      });

      const btnDel  = document.createElement('button');
      btnDel.className='iconbtn'; btnDel.type='button'; btnDel.title='Eliminar punto'; btnDel.textContent='—';
      btnDel.addEventListener('click', () => { state[current].splice(i,1); render(); });

      // input file oculto
      if(!it._fileInput){
        const file = document.createElement('input');
        file.type='file'; file.accept='image/*'; file.capture='environment';
        file.name = `fotos[${current}][]`;
        file.style.display='none';
        file.addEventListener('change', (ev)=>{
          const f = ev.target.files?.[0]; if(!f) return;
          const reader = new FileReader();
          reader.onload = ()=>{ it.image = reader.result; render(); };
          reader.readAsDataURL(f);
        });
        it._fileInput = file;
        form.appendChild(file);
      }

      if(st) li.append(num, badge, input, btnImg, btnView, btnDel);
      else   li.append(num, input, btnImg, btnView, btnDel);
      issuesList.appendChild(li);
    });
  }

  function pickImageFor(item){ item._fileInput?.click(); }
  viewerClose.addEventListener('click', ()=> viewer.close());
  clearBtn.addEventListener('click', () => { state[current] = []; render(); });

  // Antes de enviar: limpiar llaves privadas y mantener status (I/M/N)
  form.addEventListener('submit', ()=>{
    const clean = JSON.parse(JSON.stringify(state, (k,v)=> (k==='_fileInput'?undefined:v)));
    detallesJson.value = JSON.stringify(clean);
  });

  // Init
  document.querySelector('.isp-nav .tab.is-active')?.click();
</script>
@endsection
