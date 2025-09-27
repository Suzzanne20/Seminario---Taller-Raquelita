@extends('layouts.app')
@section('title','Editar Inspección 360')

@section('content')
<style>
  *{box-sizing:border-box}
  :root{--brand:#8f2f2f;--line:#e6e6e6;--panel:#fff;--bg:#f7f7f8}
  body{background:var(--bg)}
  .isp-nav{background:var(--brand);color:#fff;padding:18px 16px 12px;border-radius:12px 12px 0 0;margin-bottom:10px}
  .sections{display:flex;gap:12px;flex-wrap:wrap}
  .tab{appearance:none;border:none;cursor:pointer;font-weight:700;padding:10px 16px;color:#fff;background:rgba(255,255,255,.12);border-radius:999px}
  .tab.is-active{background:#fff;color:var(--brand);box-shadow:0 3px 10px rgba(0,0,0,.12)}

  .container{max-width:1200px;margin:0 auto 16px;padding:0 16px;display:grid;grid-template-columns:1.25fr .95fr;gap:18px}
  @media (max-width:1000px){.container{grid-template-columns:1fr}}

  .canvas{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:12px}
  .vehicle-area{position:relative;background:#fff;border:1px dashed var(--line);border-radius:12px;min-height:420px;padding:14px;display:flex;align-items:center;justify-content:center;overflow:hidden}
  .vehicle-area img{max-width:100%;height:auto;display:block}
  .marker{position:absolute;width:18px;height:18px;border-radius:50%;background:#e95d5d;border:2px solid #fff;box-shadow:0 1px 2px rgba(0,0,0,.25);transform:translate(-50%,-50%)}

  .panel{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:16px}
  .meta{display:grid;grid-template-columns:1fr 1fr;gap:10px}
  @media (max-width:600px){.meta{grid-template-columns:1fr}}
  .meta input,.meta select,.meta textarea{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:10px}

  .issues{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:10px}
  .issue{display:flex;align-items:center;gap:10px;background:#f6f6f7;border:1px solid var(--line);border-radius:10px;padding:8px 10px}
  .issue input[type='text']{flex:1;padding:8px 10px;border:1px solid var(--line);border-radius:8px}
  .iconbtn{background:#fff;border:1px solid var(--line);border-radius:10px;padding:6px 8px;display:inline-flex;align-items:center;gap:6px;cursor:pointer}

  .btn{padding:10px 12px;border-radius:10px;border:1px solid var(--line);background:#fff;cursor:pointer;font-weight:600}
  .btn.primary{background:var(--brand);border-color:var(--brand);color:#fff}
  .btn.danger{border-color:#e74c3c;color:#e74c3c}
  .thumb{max-width:180px;border:1px solid #eee;border-radius:8px;margin:6px}
</style>

{{-- ========== FORM ACTUALIZAR ========== --}}
<form class="isp-form" action="{{ route('inspecciones.update', $rec) }}" method="POST" enctype="multipart/form-data">
  @csrf
  @method('PUT')

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
    </section>

    {{-- Panel derecho --}}
    <aside class="panel">
      <div class="meta">
        <div>
          <label for="tecnico">Técnico</label>
          <input type="text" id="tecnico" name="tecnico" value="">
        </div>
        <div>
          <label for="fecha">Fecha</label>
          <input type="date" id="fecha" name="fecha" value="">
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

      <h3>Detalles marcados</h3>
      <ol class="issues" id="issuesList"></ol>

      {{-- JSON con puntos (coordenadas y textos) --}}
      <input type="hidden" name="detalles_json" id="detalles_json">

      {{-- FOTOS EXISTENTES (solo vista previa) --}}
      <div style="margin:8px 0">
        <h4>Fotos existentes</h4>
        @php $baseDir = "inspecciones/{$rec->vehiculo_placa}/{$rec->id}"; @endphp
        @forelse($rec->fotos as $f)
          <figure style="display:inline-block;text-align:center">
            <img class="thumb" src="{{ Storage::url($baseDir.'/'.$f->path_foto) }}" alt="{{ $f->descripcion }}">
            <figcaption style="max-width:180px">{{ $f->descripcion }}</figcaption>
          </figure>
        @empty
          <div style="color:#999">No hay fotos guardadas.</div>
        @endforelse
      </div>

      <div style="display:flex;gap:10px;margin-top:10px;flex-wrap:wrap">
        <button class="btn" type="button" id="clearBtn">Limpiar sección</button>
        <button class="btn primary" type="submit">Guardar cambios</button>
        <a class="btn" href="{{ route('inspecciones.show',$rec) }}">Cancelar</a>
      </div>

      <pre id="output" class="output" aria-live="polite" style="display:none"></pre>
    </aside>
  </main>

  {{-- visor de imagen --}}
  <dialog id="viewer">
    <img id="viewerImg" alt="Detalle">
    <button id="viewerClose" class="btn" type="button">Cerrar</button>
  </dialog>
</form>
{{-- ========== /FORM ACTUALIZAR ========== --}}

{{-- ========== FORM ELIMINAR (separado, sin anidar) ========== --}}
<div style="max-width:1200px;margin:0 auto 16px;padding:0 16px">
  <form action="{{ route('inspecciones.destroy', $rec) }}" method="POST"
        onsubmit="return confirm('¿Seguro que deseas eliminar esta inspección? Esta acción no se puede deshacer.');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn danger">Eliminar inspección</button>
  </form>
</div>
{{-- ========== /FORM ELIMINAR ========== --}}

@php
  // Normalizamos el estado inicial (evita ParseError de @json con array inline)
  $emptyState = ['front'=>[], 'top'=>[], 'right'=>[], 'left'=>[], 'back'=>[]];
  $raw = $rec->detalles_json;
  if (is_string($raw)) {
      $decoded = json_decode($raw, true);
      $initialState = is_array($decoded) ? $decoded : $emptyState;
  } elseif (is_array($raw)) {
      $initialState = $raw;
  } else {
      $initialState = $emptyState;
  }
  $initialState = array_merge($emptyState, $initialState);
@endphp

<script>
  // Imágenes de secciones
  const sectionImages = {
    front: @json(Vite::asset('resources/otros/assets/sections/front.jpg')),
    top:   @json(Vite::asset('resources/otros/assets/sections/top.jpg')),
    right: @json(Vite::asset('resources/otros/assets/sections/right.jpg')),
    left:  @json(Vite::asset('resources/otros/assets/sections/left.jpg')),
    back:  @json(Vite::asset('resources/otros/assets/sections/back.jpg')),
  };

  // Estado inicial (ya normalizado en PHP)
  const initialState = @json($initialState);
  let current = 'front';
  const state = Object.assign({front:[], top:[], right:[], left:[], back:[]}, initialState || {});

  const tabs = document.querySelectorAll('.isp-nav .tab');
  const vehicleArea= document.getElementById('vehicleArea');
  const sectionImg = document.getElementById('sectionImage');
  const issuesList = document.getElementById('issuesList');
  const clearBtn   = document.getElementById('clearBtn');
  const viewer     = document.getElementById('viewer');
  const viewerImg  = document.getElementById('viewerImg');
  const viewerClose= document.getElementById('viewerClose');
  const detallesJson = document.getElementById('detalles_json');

  tabs.forEach(tab => tab.addEventListener('click', () => {
    tabs.forEach(t => t.classList.remove('is-active'));
    tab.classList.add('is-active');
    setSection(tab.dataset.panel);
  }));

  function setSection(key){ current = key; sectionImg.src = sectionImages[current]; render(); }

  vehicleArea.addEventListener('click', (e) => {
    const rect = vehicleArea.getBoundingClientRect();
    const isIn = (e.target === vehicleArea || e.target === sectionImg); if(!isIn) return;
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
    [...vehicleArea.querySelectorAll('.marker')].forEach(m => m.remove());
    state[current].forEach((it, i) => {
      const m = document.createElement('div');
      m.className = 'marker';
      m.style.left = it.x + '%';
      m.style.top  = it.y + '%';
      m.title = (i+1)+'. '+(it.text || 'Detalle');
      vehicleArea.appendChild(m);
    });

    issuesList.innerHTML='';
    state[current].forEach((it, i) => {
      const li = document.createElement('li'); li.className='issue';
      const num = document.createElement('span'); num.textContent = (i+1)+'.';

      const input = document.createElement('input');
      input.type='text'; input.placeholder='Escribe el detalle'; input.value = it.text || '';
      input.addEventListener('input', () => { it.text = input.value; });

      const btnImg = document.createElement('button');
      btnImg.className='iconbtn'; btnImg.type='button'; btnImg.textContent = '🖼';
      btnImg.title='Agregar/Cambiar imagen';
      btnImg.addEventListener('click', () => pickImageFor(it));

      const btnView = document.createElement('button');
      btnView.className='iconbtn'; btnView.type='button'; btnView.textContent = '👁';
      btnView.title='Ver imagen';
      btnView.disabled = !it.image;
      btnView.addEventListener('click', () => { if(!it.image) return; viewerImg.src = it.image; viewer.showModal(); });

      const btnDel = document.createElement('button');
      btnDel.className='iconbtn'; btnDel.type='button'; btnDel.textContent = '—';
      btnDel.title='Eliminar punto';
      btnDel.addEventListener('click', () => { state[current].splice(i,1); render(); });

      if(!it._fileInput){
        const file = document.createElement('input');
        file.type = 'file'; file.accept = 'image/*'; file.capture = 'environment';
        file.name = `fotos[${current}][]`; file.style.display = 'none';
        file.addEventListener('change', (e)=>{
          const f = e.target.files?.[0]; if(!f) return;
          const reader = new FileReader();
          reader.onload = ()=>{ it.image = reader.result; render(); };
          reader.readAsDataURL(f);
        });
        it._fileInput = file;
        document.querySelector('form.isp-form').appendChild(file);
      }

      li.append(num, input, btnImg, btnView, btnDel);
      issuesList.appendChild(li);
    });
  }

  function pickImageFor(item){ if(item._fileInput){ item._fileInput.click(); } }
  viewerClose.addEventListener('click', ()=> viewer.close());

  document.querySelector('form.isp-form').addEventListener('submit', ()=>{
    const clean = JSON.parse(JSON.stringify(state, (k,v)=> k === '_fileInput' ? undefined : v));
    detallesJson.value = JSON.stringify(clean);
  });

  clearBtn.addEventListener('click', () => { state[current] = []; render(); });

  setSection('front');
</script>
@endsection
