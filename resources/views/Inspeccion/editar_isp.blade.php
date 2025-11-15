@extends('layouts.app')
@section('title','Editar Inspección 360')

@php
  // Normaliza puntos guardados (detalles_json) para JS
  $empty = ['front'=>[], 'top'=>[], 'right'=>[], 'left'=>[], 'back'=>[]];
  $raw   = $rec->detalles_json;

  if (is_string($raw)) {
      $decoded = json_decode($raw, true);
      $marks   = is_array($decoded) ? array_merge($empty, $decoded) : $empty;
  } elseif (is_array($raw)) {
      $marks = array_merge($empty, $raw);
  } else {
      $marks = $empty;
  }
@endphp

@push('styles')
<style>
  html, body { height:100%; background:#ffffff !important; }
  .page-body{ min-height:calc(100vh - 72px); background:#ffffff !important; }
  @media (max-width:576px){
    .page-body{ min-height:calc(100vh - 64px); }
  }

  :root{
    --brand:#8f2f2f;
    --line:#e6e6e6;
    --panel:#fff;
    --bg:#ffffff;
  }
  body{background:var(--bg)}

  .isp-nav{
    background:var(--brand);color:#fff;
    padding:18px 16px 12px;
    border-radius:12px 12px 0 0;
    margin:0 auto 10px;
    max-width:1200px;
  }
  .sections{display:flex;gap:12px;flex-wrap:wrap}
  .tab{
    appearance:none;border:1px solid transparent;cursor:pointer;
    font-weight:700;padding:10px 16px;color:#fff;
    background:rgba(255,255,255,.12);border-radius:999px;transition:.2s
  }
  .tab.is-active{
    background:#fff;color:var(--brand)!important;border-color:#fff;
    box-shadow:0 3px 10px rgba(0,0,0,.12)
  }

  .container{
    max-width:1200px;margin:0 auto 16px;padding:0 16px;
    display:grid;grid-template-columns:1.25fr .95fr;gap:18px
  }
  @media (max-width:1000px){.container{grid-template-columns:1fr}}

  .canvas{
    background:var(--panel);border:1px solid var(--line);
    border-radius:14px;padding:12px;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
  }
  .vehicle-area{
    position:relative;background:#fff;border:1px dashed var(--line);
    border-radius:12px;min-height:428px;padding:14px;
    display:flex;align-items:center;justify-content:center;overflow:hidden
  }
  .vehicle-area img{max-width:100%;height:auto;display:block}
  .marker{
    position:absolute;width:18px;height:18px;border-radius:50%;
    background:#e95d5d;border:2px solid #fff;
    box-shadow:0 1px 2px rgba(0,0,0,.25);transform:translate(-50%,-50%)
  }

  .panel{
    background:var(--panel);border:1px solid var(--line);
    border-radius:14px;padding:16px;
    display:flex;flex-direction:column;gap:12px;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
  }
  .meta{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  @media (max-width:600px){.meta{grid-template-columns:1fr}}
  .meta label{font-size:12px;opacity:.8;margin-bottom:4px;display:block}
  .meta input,.meta select,.meta textarea{
    width:100%;padding:10px 12px;border:1px solid var(--line);
    border-radius:10px;background:#fff
  }

  .issues{
    margin:0;padding-left:20px;
    display:flex;flex-direction:column;gap:10px
  }
  .issue{
    display:flex;align-items:center;gap:10px;
    background:#f6f6f7;border:1px solid var(--line);
    border-radius:10px;padding:8px 10px
  }
  .issue .num{font-weight:700}
  .issue input[type='text']{
    flex:1;padding:8px 10px;border:1px solid var(--line);border-radius:8px
  }

  .iconbtn{
    background:#fff;border:1px solid var(--line);
    border-radius:10px;padding:6px 8px;
    display:inline-flex;align-items:center;justify-content:center;
    cursor:pointer
  }
  .iconbtn img{
    width:18px;
    height:18px;
    display:block;
    object-fit:contain;
  }

  .btn{
    padding:10px 12px;border-radius:10px;border:1px solid var(--line);
    background:#fff;cursor:pointer;font-weight:600;
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
  .btn.danger{border-color:#e74c3c;color:#e74c3c!important}

  .tag{font-size:11px;font-weight:800;border-radius:999px;padding:2px 8px;line-height:1}
  .tag-n{background:#e8f5e9;color:#1b5e20;border:1px solid #c8e6c9}
  .tag-m{background:#fff3e0;color:#e65100;border:1px solid #ffe0b2}
  .tag-i{background:#eef2ff;color:#1e3a8a;border:1px solid #dbe4ff}
  .legend{display:flex;gap:8px;flex-wrap:wrap;font-size:12px;opacity:.85}

  /* ===== OVERLAY DE IMAGEN BONITO ===== */
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
  .img-overlay__cap{
    align-self:stretch;
    margin-top:.5rem;
    font-size:.85rem;
    color:#e5e7eb;
    background:rgba(15,23,42,.9);
    border-radius:.75rem;
    padding:.55rem .8rem;
    line-height:1.4;
  }
  .img-overlay__cap::before{
    content:'Comentario del daño';
    display:block;
    font-size:.78rem;
    text-transform:uppercase;
    letter-spacing:.06em;
    color:#9ca3af;
    margin-bottom:.2rem;
    font-weight:600;
  }
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
      <ul style="margin:0;padding-left:18px;">
        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- Tabs de secciones --}}
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
        <img id="sectionImage"
             src="{{ asset('img/sections/front.jpg') }}"
             alt="Sección actual"
             class="img-fluid">
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
          <input type="text" id="vehiculo_placa" name="vehiculo_placa"
                 maxlength="7" value="{{ $rec->vehiculo_placa }}">
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
        <button class="btn primary" type="button" id="clearBtn">Limpiar sección</button>
        <button class="btn primary" type="submit">Guardar cambios</button>
        <a class="btn primary" href="{{ route('inspecciones.show',$rec) }}">Cancelar</a>
      </div>
    </aside>
  </main>

  {{-- Overlay de imagen --}}
  <div id="imgOverlay" class="img-overlay" aria-hidden="true">
    <div class="img-overlay__content">
      <button type="button"
              id="imgOverlayClose"
              class="img-overlay__close"
              aria-label="Cerrar imagen">×</button>
      <img id="imgOverlayImg" src="" alt="Evidencia" class="img-overlay__img">
      <div id="imgOverlayCap" class="img-overlay__cap"></div>
    </div>
  </div>
</form>

@role('admin')
<div style="max-width:1200px;margin:0 auto 16px;padding:0 16px">
  <form action="{{ route('inspecciones.destroy', $rec) }}" method="POST"
        onsubmit="return confirm('¿Seguro que deseas eliminar esta inspección?');">
    @csrf @method('DELETE')
    <button type="submit" class="btn danger">Eliminar inspección</button>
  </form>
</div>
@endrole

{{-- ===== JS ===== --}}
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

  // Estado de trabajo (usamos lo que viene de PHP y aseguramos llaves)
  const initial = @json($marks) || {};
  const state   = Object.assign({front:[],top:[],right:[],left:[],back:[]}, initial);

  let current = 'front';
  const tabs        = document.querySelectorAll('.isp-nav .tab');
  const vehicleArea = document.getElementById('vehicleArea');
  const sectionImg  = document.getElementById('sectionImage');
  const issuesList  = document.getElementById('issuesList');
  const clearBtn    = document.getElementById('clearBtn');
  const detallesJson= document.getElementById('detalles_json');
  const form        = document.querySelector('form.isp-form');

  // Overlay imagen
  const imgOverlay      = document.getElementById('imgOverlay');
  const imgOverlayImg   = document.getElementById('imgOverlayImg');
  const imgOverlayCap   = document.getElementById('imgOverlayCap');
  const imgOverlayClose = document.getElementById('imgOverlayClose');

  function openOverlay(src, cap){
    imgOverlayImg.src = src;
    const txt = (cap && cap.trim()) ? cap : 'Sin comentario adicional.';
    imgOverlayCap.textContent = txt;
    imgOverlay.classList.add('img-overlay--show');
    imgOverlay.setAttribute('aria-hidden','false');
  }

  function closeOverlay(){
    imgOverlay.classList.remove('img-overlay--show');
    imgOverlay.setAttribute('aria-hidden','true');
    imgOverlayImg.src = '';
    imgOverlayCap.textContent = '';
  }

  imgOverlayClose.addEventListener('click', closeOverlay);
  imgOverlay.addEventListener('click', (e) => {
    if (e.target === imgOverlay) closeOverlay();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeOverlay();
  });

  // Marcar un punto como modificado (si no es nuevo)
  function touchStatus(item){
    if (item.status === 'N') return;  // nuevo se queda N
    item.status = 'M';
  }

  // Tabs
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

  // Añadir punto nuevo (siempre N)
  vehicleArea.addEventListener('click', (e) => {
    const rect = vehicleArea.getBoundingClientRect();
    const isIn = (e.target === vehicleArea || e.target === sectionImg);
    if(!isIn) return;

    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;

    const item = { x, y, text:'', image:null, status:'N' };
    state[current].push(item);
    render();

    setTimeout(() => {
      const lastInput = issuesList.querySelector('li:last-child input[type="text"]');
      lastInput?.focus();
    }, 0);
  });

  function render(){
    // Limpiar markers y lista
    [...vehicleArea.querySelectorAll('.marker')].forEach(m => m.remove());
    issuesList.innerHTML='';

    (state[current]||[]).forEach((it, i) => {
      // Marker
      const m = document.createElement('div');
      m.className = 'marker';
      m.style.left = (it.x||0) + '%';
      m.style.top  = (it.y||0) + '%';
      m.title = `${i+1}. ${it.text || 'Detalle'}`;
      vehicleArea.appendChild(m);

      // List item
      const li   = document.createElement('li'); li.className='issue';
      const num  = document.createElement('span'); num.className='num'; num.textContent = (i+1)+'.';

      // Status: usamos lo que venga del JSON o I si no hay
      const status = it.status || 'I';
      it.status = status; // normalizamos

      const badge = document.createElement('span');
      badge.className = 'tag ' + (status==='N' ? 'tag-n' : status==='M' ? 'tag-m' : 'tag-i');
      badge.textContent = status;

      const input = document.createElement('input');
      input.type='text';
      input.placeholder='Escribe el detalle';
      input.value = it.text || '';
      input.addEventListener('input', () => {
        it.text = input.value;
        touchStatus(it);
      });

      const btnImg  = document.createElement('button');
      btnImg.className='iconbtn'; btnImg.type='button'; btnImg.title='Agregar/Cambiar imagen';
      btnImg.innerHTML = `<img src="${ICONS.img}" alt="img">`;
      btnImg.addEventListener('click', () => pickImageFor(it));

      const btnView = document.createElement('button');
      btnView.className='iconbtn'; btnView.type='button'; btnView.title='Ver imagen';
      btnView.innerHTML = `<img src="${ICONS.eye}" alt="ver">`;
      btnView.disabled = !it.image && !it.stream_url;
      btnView.addEventListener('click', () => {
        const src = it.image || it.stream_url;
        if(!src) return;
        openOverlay(src, it.text || '');
      });

      const btnDel  = document.createElement('button');
      btnDel.className='iconbtn'; btnDel.type='button'; btnDel.title='Eliminar punto';
      btnDel.textContent='—';
      btnDel.addEventListener('click', () => {
        state[current].splice(i,1);
        render();
      });

      // input file oculto para este punto
      if(!it._fileInput){
        const file = document.createElement('input');
        file.type = 'file';
        file.accept = 'image/*';
        file.capture = 'environment';
        file.name = `fotos[${current}][]`;
        file.style.display = 'none';
        file.addEventListener('change', (ev)=>{
          const f = ev.target.files?.[0]; if(!f) return;
          const reader = new FileReader();
          reader.onload = ()=>{
            it.image = reader.result;
            touchStatus(it);
            render();
          };
          reader.readAsDataURL(f);
        });
        it._fileInput = file;
        form.appendChild(file);
      }

      li.append(num, badge, input, btnImg, btnView, btnDel);
      issuesList.appendChild(li);
    });
  }

  function pickImageFor(item){ item._fileInput?.click(); }

  clearBtn.addEventListener('click', () => {
    state[current] = [];
    render();
  });

  form.addEventListener('submit', ()=>{
    // Quitamos el _fileInput antes de enviar para no romper el JSON
    const clean = JSON.parse(JSON.stringify(state, (k,v)=> (k==='_fileInput'?undefined:v)));
    detallesJson.value = JSON.stringify(clean);
  });

  // Init
  setSection('front');
</script>
@endsection
