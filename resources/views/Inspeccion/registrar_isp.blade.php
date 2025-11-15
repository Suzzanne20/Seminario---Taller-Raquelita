@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background: #f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background: #f0f0f0 !important; color: #212529; }
  @media (max-width: 576px) { .page-body { min-height: calc(100vh - 64px); } }
</style>
@endpush

@section('title','Registrar Inspección 360')

@section('content')
<style>
  *{box-sizing:border-box}
  :root{
    --brand:#8f2f2f;
    --brand2:#b43b3b;
    --ink:#1b1b1b;
    --muted:#6b6b6b;
    --bg:#f7f7f8;
    --panel:#fff;
    --line:#e6e6e6;
  }
  body{background:var(--bg)}

  .isp-nav{
    background:var(--brand);
    color:#fff;
    padding:18px 16px 12px;
    border-radius:12px 12px 0 0;
    margin-bottom:10px;
  }
  .isp-nav .wrap{
    max-width:1200px;
    margin:0 auto;
    display:flex;
    align-items:center;
    gap:22px;
  }
  .isp-nav .sections{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    padding-top:8px;
    margin-left:28px;
  }
  .isp-nav .tab{
    appearance:none;
    border:none;
    cursor:pointer;
    font-weight:700;
    padding:10px 16px;
    color:#fff;
    background:rgba(255,255,255,.12);
    border-radius:999px;
    transition:all .2s;
  }
  .isp-nav .tab.is-active{
    background:#fff;
    color:var(--brand);
    box-shadow:0 3px 10px rgba(0,0,0,.12);
  }
  @media (max-width:900px){
    .isp-nav .wrap{flex-direction:column;align-items:flex-start;gap:10px;}
    .isp-nav .sections{margin-left:0;padding-top:6px;}
  }

  .container{
    max-width:1200px;
    margin:0 auto 16px;
    padding:0 16px;
    display:grid;
    grid-template-columns:1.25fr .95fr;
    gap:18px;
  }
  @media (max-width:1000px){
    .container{grid-template-columns:1fr;}
  }

  .canvas{
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:14px;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
    padding:12px;
  }
  .hint{
    color:var(--muted);
    font-size:12px;
    margin:4px 6px 10px;
  }
  .vehicle-area{
    position:relative;
    background:#fff;
    border:1px dashed var(--line);
    border-radius:12px;
    min-height:420px;
    padding:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
  }
  .vehicle-area img{
    max-width:100%;
    height:auto;
    display:block;
  }
  .marker{
    position:absolute;
    width:18px;
    height:18px;
    border-radius:50%;
    background:#e95d5d;
    border:2px solid #fff;
    box-shadow:0 1px 2px rgba(0,0,0,.25);
    transform:translate(-50%,-50%);
  }

  .panel{
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:14px;
    padding:16px;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
    display:flex;
    flex-direction:column;
    gap:12px;
  }

  /* ===== META (tecnico, placa, tipo, observaciones) ===== */
  .meta{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
  }
  @media (max-width:900px){
    .meta{grid-template-columns:1fr;}
  }
  .meta-item{
    display:flex;
    gap:8px;
    align-items:flex-start;           /* alineamos arriba todo */
  }
  .meta-item .icon{
    font-size:20px;
    line-height:1;
    padding-top:4px;                  /* baja un poquito el emoji */
  }
  .meta-item .field{
    flex:1;
    display:flex;
    flex-direction:column;
    gap:4px;
  }
  .meta-item label{
    font-size:12px;
    color:var(--muted);
  }
  .meta-item input,
  .meta-item select,
  .meta-item textarea{
    width:100%;
    padding:10px 12px;
    border:1px solid var(--line);
    border-radius:10px;
  }

  /* ==== BUSCADOR DE PLACAS ==== */
  .plate-wrapper{ width:100%; }
  .plate-combo{ position:relative; }
  .plate-input{
    width:100%;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid var(--line);
    background:#fff;
  }
  .plate-list{
    position:absolute;
    left:0;
    right:0;
    margin-top:4px;
    max-height:220px;
    overflow-y:auto;
    background:#fff;
    border-radius:10px;
    border:1px solid var(--line);
    box-shadow:0 12px 24px rgba(15,23,42,.14);
    padding:4px 0;
    display:none;
    z-index:40;
  }
  .plate-list.show{ display:block; }
  .plate-item{
    width:100%;
    padding:6px 10px;
    text-align:left;
    border:none;
    background:transparent;
    font-size:14px;
    color:var(--ink);
    cursor:pointer;
  }
  .plate-item:hover{
    background:#f3f4f6;
  }

  .issues{
    margin:0;
    padding-left:20px;
    display:flex;
    flex-direction:column;
    gap:10px;
  }
  .issue{
    display:flex;
    align-items:center;
    gap:10px;
    background:#f6f6f7;
    border:1px solid var(--line);
    border-radius:10px;
    padding:8px 10px;
  }
  .issue .num{font-weight:700;}
  .issue input[type='text']{
    flex:1;
    padding:8px 10px;
    border:1px solid var(--line);
    border-radius:8px;
  }
  .iconbtn{
    background:#fff;
    border:1px solid var(--line);
    border-radius:10px;
    padding:6px 8px;
    display:inline-flex;
    align-items:center;
    gap:6px;
    cursor:pointer;
  }
  .iconbtn img{width:18px;height:18px;}
  .rm{
    border-radius:50%;
    width:28px;
    height:28px;
    display:grid;
    place-items:center;
  }
  .ok{color:#1a7f37;font-weight:700;}

  .btn{
    padding:10px 12px;
    border-radius:10px;
    border:1px solid var(--line);
    background:#fff;
    cursor:pointer;
    font-weight:600;
    text-decoration:none;
  }
  .btn.primary{
    background:var(--brand);
    border-color:var(--brand);
    color:#fff !important;
  }
  .btn.primary:hover{
    background:#a63a3a;
    border-color:#a63a3a;
    color:#fff !important;
  }
  .btn.ghost{background:#fff;}
  .actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
  }
  @media (max-width:600px){
    .actions{
      flex-direction:column;
    }
    .actions .btn{
      width:100%;
      text-align:center;
    }
  }

  .outputWrap{display:none!important;}

  .isp-form, .isp-form *{ color:#1b1b1b !important; }
  .isp-form input,
  .isp-form select,
  .isp-form textarea{
    color:#1b1b1b !important;
    background:#fff !important;
    border-color:var(--line);
  }
  .isp-form select option{
    color:#1b1b1b;
    background:#fff;
  }
  .isp-form input::placeholder,
  .isp-form textarea::placeholder{
    color:#9aa0a6 !important;
  }
  .isp-nav{ color:#fff !important; }
  .isp-nav .tab{ color:#fff !important; }
  .isp-nav .tab.is-active{ color:var(--brand) !important; }

  /* ===== OVERLAY DE IMAGEN CENTRADO ===== */
  .img-overlay{
    position:fixed;
    inset:0;
    display:none;
    align-items:center;
    justify-content:center;
    z-index:1080;
    background:rgba(15,23,42,.85);
    backdrop-filter:blur(2px);
  }
  .img-overlay--show{
    display:flex;
  }
  .img-overlay__content{
    background:#020617;
    border-radius:18px;
    max-width:min(900px, 90vw);
    max-height:80vh;
    padding:.75rem;
    box-shadow:0 20px 40px rgba(15,23,42,.7);
    display:flex;
    flex-direction:column;
    align-items:flex-end;
  }
  .img-overlay__img{
    max-width:100%;
    max-height:72vh;
    object-fit:contain;
    border-radius:12px;
    align-self:center;
  }

  /* BOTÓN ROJO DE CIERRE */
  .img-overlay__close{
    border:none;
    background:#dc2626;
    color:#fff;
    font-size:1.3rem;
    line-height:1;
    border-radius:999px;
    padding:.25rem .6rem;
    cursor:pointer;
    box-shadow:0 0 0 2px rgba(0,0,0,.35);
    transition:background .15s ease, transform .15s ease, box-shadow .15s ease;
    margin-bottom:.25rem;
  }
  .img-overlay__close:hover{
    background:#b91c1c;
    transform:scale(1.05);
    box-shadow:0 0 0 2px rgba(248,250,252,.7);
  }
</style>

<form class="isp-form" action="{{ route('inspecciones.store') }}" method="POST" enctype="multipart/form-data">
  @csrf

  {{-- mensajes --}}
  @if (session('ok'))
    <div style="background:#e9f7ef;border:1px solid #2ecc71;color:#1e8449;padding:10px 12px;border-radius:8px;margin:10px 0;">
      {{ session('ok') }}
    </div>
  @endif
  @if (session('error'))
    <div style="background:#fdecea;border:1px solid #e74c3c;color:#922b21;padding:10px 12px;border-radius:8px;margin:10px 0;">
      {{ session('error') }}
    </div>
  @endif
  @if ($errors->any())
    <div style="background:#fff3cd;border:1px solid #ffec99;color:#8a6d3b;padding:10px 12px;border-radius:8px;margin:10px 0;">
      <ul style="margin:0;padding-left:18px;">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <header class="isp-nav">
    <div class="wrap">
      <nav class="sections" role="tablist">
        <button class="tab is-active" type="button" data-panel="front">Parte delantera</button>
        <button class="tab" type="button" data-panel="top">Superior</button>
        <button class="tab" type="button" data-panel="right">Lado derecho</button>
        <button class="tab" type="button" data-panel="left">Lado izquierdo</button>
        <button class="tab" type="button" data-panel="back">Parte trasera</button>
      </nav>
    </div>
  </header>

  <main class="container">
    {{-- Lienzo --}}
    <section class="canvas">
      <div class="hint">
        Click en la carrocería para añadir un punto. Escribe la descripción y, si quieres, adjunta foto.
      </div>
      <div class="vehicle-area" id="vehicleArea">
        <img id="sectionImage" src="{{ asset('img/sections/front.jpg') }}" alt="Sección actual" class="img-fluid">
      </div>
    </section>

    {{-- Panel derecho --}}
    <aside class="panel">
      <div class="meta">
        {{-- Técnico --}}
        <div class="meta-item">
          <span class="icon">👨‍🔧</span>
          <div class="field">
            <label for="tecnico_id">Técnico</label>
            <select id="tecnico_id" name="tecnico_id">
              <option value="" hidden>Selecciona (opcional)…</option>
              @foreach(($tecnicos ?? []) as $t)
                <option value="{{ $t->id }}" @selected(old('tecnico_id')==$t->id)>{{ $t->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Placa (buscador con filtro) --}}
        <div class="meta-item">
          <span class="icon">🚘</span>
          <div class="field plate-wrapper">
            <label for="vehiculo_placa_search">Placa</label>
            <div class="plate-combo">
              <input
                type="text"
                id="vehiculo_placa_search"
                name="vehiculo_placa"
                class="plate-input"
                placeholder="Escribe o selecciona placa…"
                autocomplete="off"
                required
                value="{{ old('vehiculo_placa') }}"
              >
              <div class="plate-list" id="plateList">
                @foreach(($placas ?? []) as $p)
                  <button type="button" class="plate-item" data-value="{{ $p }}">{{ $p }}</button>
                @endforeach
              </div>
            </div>
            <small style="font-size:11px;color:var(--muted);display:block;margin-top:2px;">
              Escribe parte de la placa y se filtrará la lista; luego haz clic en la correcta.
            </small>
          </div>
        </div>

        {{-- Tipo vehículo --}}
        <div class="meta-item">
          <span class="icon">🚗</span>
          <div class="field">
            <label for="type_vehiculo_id">Tipo de vehículo</label>
            <select id="type_vehiculo_id" name="type_vehiculo_id" required>
              <option value="" hidden>Selecciona…</option>
              @foreach(($tipos ?? []) as $t)
                <option value="{{ $t->id }}" @selected(old('type_vehiculo_id')==$t->id)>{{ $t->descripcion }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Observaciones --}}
        <div class="meta-item" style="grid-column:1 / -1">
          <span class="icon">📝</span>
          <div class="field">
            <label for="observaciones">Observaciones (opcional)</label>
            <textarea id="observaciones" name="observaciones" rows="3" placeholder="Notas generales…">{{ old('observaciones') }}</textarea>
          </div>
        </div>
      </div>

      <h5>Detalles marcados</h5>
      <ol class="issues" id="issuesList"></ol>

      <input type="hidden" name="detalles_json" id="detalles_json">

      <div class="actions">
        <button class="btn primary" type="button" id="clearBtn">Limpiar sección</button>
        <button class="btn primary" type="submit" id="saveBtn">Guardar</button>
        <a href="{{ route('inspecciones.index') }}" class="btn primary">Cancelar</a>
      </div>

      <div class="outputWrap">
        <pre id="output" class="output" aria-live="polite"></pre>
      </div>
    </aside>
  </main>

  {{-- Overlay de imagen centrado --}}
  <div id="imgOverlay" class="img-overlay" aria-hidden="true">
    <div class="img-overlay__content">
      <button type="button"
              id="imgOverlayClose"
              class="img-overlay__close"
              aria-label="Cerrar imagen">×</button>
      <img id="imgOverlayImg" src="" alt="Evidencia" class="img-overlay__img">
    </div>
  </div>
</form>

<script>
  // Rutas desde public/img/sections
  const sectionImages = {
    front: "{{ asset('img/sections/front.jpg') }}",
    top:   "{{ asset('img/sections/top.jpg') }}",
    right: "{{ asset('img/sections/right.jpg') }}",
    left:  "{{ asset('img/sections/left.jpg') }}",
    back:  "{{ asset('img/sections/back.jpg') }}",
  };

  const ICONS = {
    img: "{{ asset('img/sections/icon-img.png') }}",
    eye: "{{ asset('img/sections/icon-eye.png') }}",
  };

  let current = 'front';
  const state = { front:[], top:[], right:[], left:[], back:[] };

  const tabs         = document.querySelectorAll('.isp-nav .tab');
  const vehicleArea  = document.getElementById('vehicleArea');
  const sectionImg   = document.getElementById('sectionImage');
  const issuesList   = document.getElementById('issuesList');
  const clearBtn     = document.getElementById('clearBtn');
  const output       = document.getElementById('output');
  const detallesJson = document.getElementById('detalles_json');
  const form         = document.querySelector('form.isp-form');

  // ===== BUSCADOR DE PLACAS =====
  const plateInput = document.getElementById('vehiculo_placa_search');
  const plateList  = document.getElementById('plateList');
  const plateItems = plateList ? Array.from(plateList.querySelectorAll('.plate-item')) : [];

  function togglePlateList(show){
    if(!plateList) return;
    if(show) plateList.classList.add('show');
    else     plateList.classList.remove('show');
  }

  function filterPlateList(){
    if(!plateList) return;
    const term = (plateInput.value || '').trim().toUpperCase();
    plateItems.forEach(btn => {
      const val   = btn.dataset.value.toUpperCase();
      const match = !term || val.includes(term);
      btn.style.display = match ? 'block' : 'none';
    });
  }

  if (plateInput){
    plateInput.addEventListener('focus', () => {
      togglePlateList(true);
      filterPlateList();
    });
    plateInput.addEventListener('input', filterPlateList);
  }

  plateItems.forEach(btn => {
    btn.addEventListener('click', () => {
      plateInput.value = btn.dataset.value;
      togglePlateList(false);
    });
  });

  document.addEventListener('click', (e) => {
    if (!plateList) return;
    if (e.target !== plateInput && !plateList.contains(e.target)) {
      togglePlateList(false);
    }
  });

  // ===== Overlay de imagen =====
  const imgOverlay      = document.getElementById('imgOverlay');
  const imgOverlayImg   = document.getElementById('imgOverlayImg');
  const imgOverlayClose = document.getElementById('imgOverlayClose');

  function openOverlay(src){
    imgOverlayImg.src = src;
    imgOverlay.classList.add('img-overlay--show');
    imgOverlay.setAttribute('aria-hidden','false');
  }

  function closeOverlay(){
    imgOverlay.classList.remove('img-overlay--show');
    imgOverlay.setAttribute('aria-hidden','true');
    imgOverlayImg.src = '';
  }

  imgOverlayClose.addEventListener('click', closeOverlay);
  imgOverlay.addEventListener('click', (e) => {
    if (e.target === imgOverlay) closeOverlay();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeOverlay();
  });

  // Tabs de secciones
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('is-active'));
      tab.classList.add('is-active');
      setSection(tab.dataset.panel);
    });
  });

  function setSection(key){
    current = key;
    sectionImg.src = sectionImages[current];
    render();
  }

  // Click en la carrocería para agregar punto
  vehicleArea.addEventListener('click', (e) => {
    if (e.target.closest('.marker') || e.target.closest('button')) return;

    const rect = vehicleArea.getBoundingClientRect();
    const isIn = (e.target === vehicleArea || e.target === sectionImg);
    if(!isIn) return;

    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;

    const item = { x, y, text:'', image:null };
    state[current].push(item);
    render();

    setTimeout(() => {
      const last = issuesList.querySelector('li:last-child input[type="text"]');
      if(last) last.focus();
    }, 0);
  });

  function render(){
    // Borrar marcadores
    [...vehicleArea.querySelectorAll('.marker')].forEach(m => m.remove());

    // Dibujar marcadores
    state[current].forEach((it, i) => {
      const m = document.createElement('div');
      m.className = 'marker';
      m.style.left = it.x + '%';
      m.style.top  = it.y + '%';
      m.title = (i+1)+'. '+(it.text || 'Detalle');
      vehicleArea.appendChild(m);
    });

    // Lista de detalles
    issuesList.innerHTML='';
    state[current].forEach((it, i) => {
      const li = document.createElement('li');
      li.className='issue';

      const num = document.createElement('span');
      num.className='num';
      num.textContent = (i+1)+'.';

      const input = document.createElement('input');
      input.type='text';
      input.placeholder='Escribe el detalle';
      input.value = it.text || '';
      input.addEventListener('input', () => { it.text = input.value; });

      const btnImg = document.createElement('button');
      btnImg.className='iconbtn';
      btnImg.type='button';
      btnImg.title='Agregar/Cambiar imagen';
      btnImg.innerHTML = `<img src="${ICONS.img}" alt="img">`;
      btnImg.addEventListener('click', () => pickImageFor(it));

      const ok = document.createElement('span');
      ok.className='ok';
      ok.textContent = it.image ? '✓' : '';

      const btnView = document.createElement('button');
      btnView.className='iconbtn';
      btnView.type='button';
      btnView.title='Ver imagen';
      btnView.innerHTML = `<img src="${ICONS.eye}" alt="ver">`;
      btnView.disabled = !it.image;
      btnView.addEventListener('click', () => {
        if(!it.image) return;
        openOverlay(it.image);
      });

      const btnDel = document.createElement('button');
      btnDel.className='iconbtn rm';
      btnDel.type='button';
      btnDel.title='Eliminar';
      btnDel.textContent = '—';
      btnDel.addEventListener('click', () => {
        state[current].splice(i,1);
        render();
      });

      // Input file oculto (solo una vez)
      if(!it._fileInput){
        const file = document.createElement('input');
        file.type = 'file';
        file.accept = 'image/*';
        file.capture = 'environment';
        file.name = `fotos[${current}][]`;
        file.style.display = 'none';
        file.addEventListener('change', (e)=>{
          const f = e.target.files?.[0];
          if(!f) return;
          const reader = new FileReader();
          reader.onload = ()=>{
            it.image = reader.result;
            render();
          };
          reader.readAsDataURL(f);
        });
        it._fileInput = file;
        form.appendChild(file);
      }

      if (it.image) ok.textContent = '✓';

      li.append(num, input, btnImg, ok, btnView, btnDel);
      issuesList.appendChild(li);
    });

    if (output) {
      output.textContent = JSON.stringify(state, null, 2);
    }
  }

  function pickImageFor(item){
    if(item._fileInput){
      item._fileInput.click();
    }
  }

  form.addEventListener('submit', ()=>{
    const clean = JSON.parse(JSON.stringify(state, (k,v)=> k === '_fileInput' ? undefined : v));
    detallesJson.value = JSON.stringify(clean);
  });

  clearBtn.addEventListener('click', () => {
    state[current] = [];
    render();
    if (output) output.textContent='';
  });

  // Inicial
  setSection('front');
</script>
@endsection
