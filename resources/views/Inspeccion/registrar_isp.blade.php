@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background: #f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background: #f0f0f0 !important; color: #212529; }
  @media (max-width: 576px) {
    .page-body { min-height: calc(100vh - 64px); }
  }
</style>
@endpush
@section('title','Registrar Inspección 360')

@section('content')
<style>
  *{box-sizing:border-box}
  :root{--brand:#8f2f2f;--brand2:#b43b3b;--ink:#1b1b1b;--muted:#6b6b6b;--bg:#f7f7f8;--panel:#fff;--line:#e6e6e6}
  body{background:var(--bg)}

  /* Barra de secciones */
  .isp-nav{background:var(--brand);color:#fff;padding:18px 16px 12px;border-radius:12px 12px 0 0;margin-bottom:10px}
  .isp-nav .wrap{max-width:1200px;margin:0 auto;display:flex;align-items:center;gap:22px}
  .isp-nav .sections{display:flex;flex-wrap:wrap;gap:12px;padding-top:8px;margin-left:28px}
  .isp-nav .tab{appearance:none;border:none;cursor:pointer;font-weight:700;padding:10px 16px;color:#fff;background:rgba(255,255,255,.12);border-radius:999px;transition:all .2s}
  .isp-nav .tab.is-active{background:#fff;color:var(--brand);box-shadow:0 3px 10px rgba(0,0,0,.12)}
  @media (max-width:900px){.isp-nav .wrap{flex-direction:column;align-items:flex-start;gap:10px}.isp-nav .sections{margin-left:0;padding-top:6px}}

  /* Layout */
  .container{max-width:1200px;margin:0 auto 16px;padding:0 16px;display:grid;grid-template-columns:1.25fr .95fr;gap:18px}
  @media (max-width:1000px){.container{grid-template-columns:1fr}}

  /* Lienzo */
  .canvas{background:var(--panel);border:1px solid var(--line);border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);padding:12px}
  .hint{color:var(--muted);font-size:12px;margin:4px 6px 10px}
  .vehicle-area{position:relative;background:#fff;border:1px dashed var(--line);border-radius:12px;min-height:420px;padding:14px;display:flex;align-items:center;justify-content:center;overflow:hidden}
  .vehicle-area img{max-width:100%;height:auto;display:block}
  .marker{position:absolute;width:18px;height:18px;border-radius:50%;background:#e95d5d;border:2px solid #fff;box-shadow:0 1px 2px rgba(0,0,0,.25);transform:translate(-50%,-50%)}

  /* Panel derecho */
  .panel{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);display:flex;flex-direction:column;gap:12px}
  .meta{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  @media (max-width:600px){.meta{grid-template-columns:1fr}}
  .meta-item{display:flex;gap:8px;align-items:center}
  .meta-item .icon{font-size:20px}
  .meta-item label{display:block;font-size:12px;color:var(--muted)}
  .meta-item input,.meta-item select,.meta-item textarea{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:10px}

  .issues{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:10px}
  .issue{display:flex;align-items:center;gap:10px;background:#f6f6f7;border:1px solid var(--line);border-radius:10px;padding:8px 10px}
  .issue .num{font-weight:700}
  .issue input[type='text']{flex:1;padding:8px 10px;border:1px solid var(--line);border-radius:8px}
  .iconbtn{background:#fff;border:1px solid var(--line);border-radius:10px;padding:6px 8px;display:inline-flex;align-items:center;gap:6px;cursor:pointer}
  .iconbtn img{width:18px;height:18px}
  .rm{border-radius:50%;width:28px;height:28px;display:grid;place-items:center}
  .ok{color:#1a7f37;font-weight:700}

  .btn{padding:10px 12px;border-radius:10px;border:1px solid var(--line);background:#fff;cursor:pointer;font-weight:600}
  .btn.primary{background:var(--brand);border-color:var(--brand);color:#fff}
  .btn.ghost{background:#fff}
  .actions{display:flex;gap:10px}
  .outputWrap{display:none!important}

  /* Forzar texto oscuro en el formulario (para layouts oscuros) */
  .isp-form, .isp-form *{ color:#1b1b1b !important; }
  .isp-form input,.isp-form select,.isp-form textarea{
    color:#1b1b1b !important;background:#fff !important;border-color:var(--line);
  }
  .isp-form select option{ color:#1b1b1b;background:#fff; }
  .isp-form input::placeholder,.isp-form textarea::placeholder{ color:#9aa0a6 !important; }
  .isp-nav{ color:#fff !important; }
  .isp-nav .tab{ color:#fff !important; }
  .isp-nav .tab.is-active{ color:var(--brand) !important; }
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

  {{-- NAV --}}
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
    {{-- Lienzo para marcar --}}
    <section class="canvas">
      <div class="hint">Click en la carrocería para añadir un punto. Escribe la descripción y, si quieres, adjunta foto.</div>
      <div class="vehicle-area" id="vehicleArea">
        <img id="sectionImage" src="{{ Vite::asset('resources/otros/assets/sections/front.jpg') }}" alt="Sección actual">
      </div>
    </section>

    {{-- Panel derecho --}}
    <aside class="panel">
      <div class="meta">
        <div class="meta-item">
          <span class="icon">👨‍🔧</span>
          <div>
            <label for="tecnico">Técnico</label>
            <input type="text" id="tecnico" name="tecnico" placeholder="Nombre del técnico" value="{{ old('tecnico') }}">
          </div>
        </div>

        <div class="meta-item">
          <span class="icon">📅</span>
          <div>
            <label for="fecha">Fecha</label>
            <input type="date" id="fecha" name="fecha" value="{{ old('fecha') }}">
          </div>
        </div>

        <div class="meta-item">
          <span class="icon">🚘</span>
          <div>
            <label for="vehiculo_placa">Placa</label>
            <input type="text" id="vehiculo_placa" name="vehiculo_placa" maxlength="7" placeholder="P123ABC" value="{{ old('vehiculo_placa') }}">
          </div>
        </div>

        <div class="meta-item">
          <span class="icon">🚗</span>
          <div>
            <label for="type_vehiculo_id">Tipo de vehículo</label>
            <select id="type_vehiculo_id" name="type_vehiculo_id" required>
              <option value="" hidden>Selecciona…</option>
              @foreach(($tipos ?? []) as $t)
                <option value="{{ $t->id }}" @selected(old('type_vehiculo_id')==$t->id)>{{ $t->descripcion }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="meta-item" style="grid-column:1 / -1">
          <span class="icon">📝</span>
          <div style="width:100%">
            <label for="observaciones">Observaciones (opcional)</label>
            <textarea id="observaciones" name="observaciones" rows="3" placeholder="Notas generales…">{{ old('observaciones') }}</textarea>
          </div>
        </div>
      </div>

      <h3>Detalles marcados</h3>
      <ol class="issues" id="issuesList"></ol>

      {{-- JSON con puntos (coordenadas y textos) --}}
      <input type="hidden" name="detalles_json" id="detalles_json">

      <div class="actions">
        <button class="btn ghost" type="button" id="clearBtn">Limpiar sección</button>
        <button class="btn primary" type="submit" id="saveBtn">Guardar</button>
      </div>

      <div class="outputWrap">
        <pre id="output" class="output" aria-live="polite"></pre>
      </div>
    </aside>
  </main>

  {{-- visor de imagen --}}
  <dialog id="viewer" style="border:none;border-radius:12px;padding:12px">
    <img id="viewerImg" alt="Detalle" style="max-width:80vw;max-height:70vh;display:block;margin-bottom:10px">
    <button id="viewerClose" class="btn" type="button">Cerrar</button>
  </dialog>
</form>

<script>
  // Rutas de imágenes por sección (ajusta si tus archivos están en otra carpeta)
  const sectionImages = {
    front: @json(Vite::asset('resources/otros/assets/sections/front.jpg')),
    top:   @json(Vite::asset('resources/otros/assets/sections/top.jpg')),
    right: @json(Vite::asset('resources/otros/assets/sections/right.jpg')),
    left:  @json(Vite::asset('resources/otros/assets/sections/left.jpg')),
    back:  @json(Vite::asset('resources/otros/assets/sections/back.jpg')),
  };

  // Iconos
  const ICONS = {
    img: @json(Vite::asset('resources/otros/assets/icon-img.png')),
    eye: @json(Vite::asset('resources/otros/assets/icon-eye.png')),
  };

  // Estado por sección: cada item = { x, y, text, image(base64), _fileInput }
  let current = 'front';
  const state = { front:[], top:[], right:[], left:[], back:[] };

  // Elementos
  const tabs         = document.querySelectorAll('.isp-nav .tab');
  const vehicleArea  = document.getElementById('vehicleArea');
  const sectionImg   = document.getElementById('sectionImage');
  const issuesList   = document.getElementById('issuesList');
  const clearBtn     = document.getElementById('clearBtn');
  const output       = document.getElementById('output');
  const viewer       = document.getElementById('viewer');
  const viewerImg    = document.getElementById('viewerImg');
  const viewerClose  = document.getElementById('viewerClose');
  const detallesJson = document.getElementById('detalles_json');
  const form         = document.querySelector('form.isp-form');

  // Tabs
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

  // Click en el lienzo (añadir punto)
  vehicleArea.addEventListener('click', (e) => {
    // evitar que un click en un botón existente dispare nuevos puntos
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

  // Pintar marcadores y lista
  function render(){
    // Marcadores
    [...vehicleArea.querySelectorAll('.marker')].forEach(m => m.remove());
    state[current].forEach((it, i) => {
      const m = document.createElement('div');
      m.className = 'marker';
      m.style.left = it.x + '%';
      m.style.top  = it.y + '%';
      m.title = (i+1)+'. '+(it.text || 'Detalle');
      vehicleArea.appendChild(m);
    });

    // Lista de issues
    issuesList.innerHTML='';
    state[current].forEach((it, i) => {
      const li = document.createElement('li'); li.className='issue';

      const num = document.createElement('span'); num.className='num'; num.textContent = (i+1)+'.';

      const input = document.createElement('input');
      input.type='text'; input.placeholder='Escribe el detalle'; input.value = it.text || '';
      input.addEventListener('input', () => { it.text = input.value; });

      const btnImg = document.createElement('button');
      btnImg.className='iconbtn'; btnImg.type='button'; btnImg.title='Agregar/Cambiar imagen';
      btnImg.innerHTML = `<img src="${ICONS.img}" alt="img">`;
      btnImg.addEventListener('click', () => pickImageFor(it));

      const ok = document.createElement('span'); ok.className='ok'; ok.textContent = it.image ? '✓' : '';

      const btnView = document.createElement('button');
      btnView.className='iconbtn'; btnView.type='button'; btnView.title='Ver imagen';
      btnView.innerHTML = `<img src="${ICONS.eye}" alt="ver">`;
      btnView.disabled = !it.image;
      btnView.addEventListener('click', () => { if(!it.image) return; viewerImg.src = it.image; viewer.showModal(); });

      const btnDel = document.createElement('button');
      btnDel.className='iconbtn rm'; btnDel.type='button'; btnDel.title='Eliminar';
      btnDel.textContent = '—';
      btnDel.addEventListener('click', () => { state[current].splice(i,1); render(); });

      // Crear input file oculto si no existe
      if(!it._fileInput){
        const file = document.createElement('input');
        file.type = 'file';
        file.accept = 'image/*';
        file.capture = 'environment';
        file.name = `fotos[${current}][]`;
        file.style.display = 'none';
        file.addEventListener('change', (e)=>{
          const f = e.target.files?.[0]; if(!f) return;
          const reader = new FileReader();
          reader.onload = ()=>{ it.image = reader.result; render(); };
          reader.readAsDataURL(f);
        });
        it._fileInput = file;
        form.appendChild(file);
      }

      if (it.image) ok.textContent = '✓';

      li.append(num, input, btnImg, ok, btnView, btnDel);
      issuesList.appendChild(li);
    });

    if (output) output.textContent = JSON.stringify(state, null, 2);
  }

  // Abrir selector de imagen
  function pickImageFor(item){ if(item._fileInput){ item._fileInput.click(); } }

  // Cerrar visor
  viewerClose.addEventListener('click', ()=> viewer.close());

  // Enviar: serializar puntos a hidden
  form.addEventListener('submit', ()=>{
    // limpiamos la propiedad _fileInput porque no es serializable
    const clean = JSON.parse(JSON.stringify(state, (k,v)=> k === '_fileInput' ? undefined : v));
    detallesJson.value = JSON.stringify(clean);
  });

  // Limpiar sección
  clearBtn.addEventListener('click', () => {
    state[current] = []; render(); if (output) output.textContent='';
  });

  // Inicial
  setSection('front');
</script>
@endsection
