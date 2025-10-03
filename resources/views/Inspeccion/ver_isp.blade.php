@extends('layouts.app')
@section('title','Detalle de Inspección')

@section('content')
@php
  // 1) Normaliza puntos guardados
  $marks = is_array($rec->detalles_json)
      ? $rec->detalles_json
      : (json_decode($rec->detalles_json ?? '[]', true) ?: []);
  $sections = ['front','top','right','left','back'];

  // 2) Fotos desde los puntos (enlazadas) + IDs enlazados
  $photoList = [];
  $linkedIds = [];
  foreach ($sections as $sec) {
    foreach (($marks[$sec] ?? []) as $p) {
      if (!empty($p['stream_url'])) {
        $photoList[] = [
          'id'   => $p['foto_id'] ?? null,
          'url'  => $p['stream_url'],
          'text' => $p['text'] ?? '',
          'sec'  => strtoupper($sec),
        ];
        if (!empty($p['foto_id'])) $linkedIds[] = (int)$p['foto_id'];
      }
    }
  }

  // 3) Agrega TODAS las fotos BLOB no enlazadas
  foreach ($rec->fotos as $f) {
    if (!in_array($f->id, $linkedIds, true)) {
      $photoList[] = [
        'id'   => $f->id,
        'url'  => route('fotos.stream', $f),
        'text' => $f->descripcion ?? '',
        'sec'  => '—',
      ];
    }
  }

  // Contadores por sección para las tabs
  $secCounts = [];
  foreach ($sections as $s) $secCounts[$s] = count($marks[$s] ?? []);
@endphp

<style>
  :root{
    --brand:#8f2f2f; --brand2:#b43b3b;
    --ink:#101114; --muted:#6b7280;
    --line:#e6e6e6; --bg:#f5f6f8; --panel:#ffffff;
    --ring:0 0 0 3px rgba(180,59,59,.18);
  }
  body{ background:var(--bg) }

  /* ===== Shell ===== */
  .wrap{max-width:1240px;margin:22px auto;padding:0 18px;color:var(--ink)}
  .card{background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:16px;box-shadow:0 8px 26px rgba(0,0,0,.06)}

  /* ===== Header fancy ===== */
  .head{
    position:relative; overflow:hidden; border-radius:16px; padding:18px;
    background:
      radial-gradient(900px 300px at -10% 0%, rgba(255,180,180,.25) 0%, transparent 55%),
      radial-gradient(900px 300px at 110% -10%, rgba(255,210,210,.25) 0%, transparent 55%),
      linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
    border:1px solid #eee;
  }
  .hrow{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between}
  .hstack{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
  .title{margin:0;font-weight:900;letter-spacing:.2px;font-size:clamp(20px,2.4vw,26px)}
  .chip{display:inline-flex;gap:8px;align-items:center;border:1px solid var(--line);background:#fff;padding:6px 10px;border-radius:999px;font-weight:700}
  .chip b{font-weight:900}
  .head .actions .btn{border-radius:10px;padding:8px 12px;font-weight:700}

  .btn{border:1px solid var(--line);background:#fff;border-radius:10px;padding:8px 10px;font-weight:600;cursor:pointer;transition:transform .12s ease, box-shadow .12s ease, border-color .12s ease}
  .btn:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(0,0,0,.08);border-color:#ddd}
  .btn.primary{background:var(--brand);border-color:var(--brand);color:#fff}

  /* ===== Layout 2 columnas con aside sticky ===== */
  .viewer{display:grid;grid-template-columns:1.35fr .95fr;gap:20px;margin-top:16px}
  @media (max-width:1060px){ .viewer{grid-template-columns:1fr} }

  /* ===== Tabs & Canvas ===== */
  .canvas{background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:14px}
  .tabs{display:flex;gap:10px;flex-wrap:wrap;margin:4px 0 12px}
  .tab{
    position:relative; border:1px solid var(--line); background:#fff; color:var(--ink);
    border-radius:999px; padding:9px 14px; font-weight:800; cursor:pointer;
  }
  .tab .count{margin-left:8px;background:#f2f2f3;border:1px solid #ededee;border-radius:999px;padding:2px 8px;font-size:12px;font-weight:800}
  .tab.active{background:var(--brand);color:#fff;border-color:var(--brand)}
  .tab.active .count{background:rgba(255,255,255,.18);border-color:transparent;color:#fff}

  .stage{
    position:relative;background:#fff;border:1px dashed var(--line);border-radius:14px;
    min-height:460px;padding:14px;display:flex;align-items:center;justify-content:center;overflow:hidden
  }
  .stage img{max-width:100%;height:auto;display:block;user-select:none}
  .marker{
    position:absolute;width:18px;height:18px;border-radius:50%;
    background:radial-gradient(circle at 40% 40%, #ff9a9a 0%, #e95d5d 60%);
    border:2px solid #fff; box-shadow:0 3px 10px rgba(233,93,93,.4);
    transform:translate(-50%,-50%); cursor:help;
  }
  .marker::after{
    content:""; position:absolute; inset:-10px; border-radius:50%;
    box-shadow:0 0 0 10px rgba(233,93,93,.08); opacity:0; transition:opacity .15s;
  }
  .marker:hover::after{ opacity:1; }
  .legend{color:var(--muted); font-size:13px; margin-top:8px}

  /* ===== Aside sticky + Masonry-like gallery ===== */
  .aside{background:var(--panel);border:1px solid var(--line);border-radius:16px;padding:14px;position:sticky;top:18px;align-self:start}
  .aside h3{margin:0 0 10px;font-weight:900;letter-spacing:.2px}
  .grid{
    display:grid; grid-template-columns:repeat(auto-fill,minmax(210px,1fr));
    gap:14px; align-items:start;
  }
  .photo{
    background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;
    display:flex;flex-direction:column; transition:transform .12s ease, box-shadow .12s ease;
    box-shadow:0 6px 18px rgba(0,0,0,.06);
  }
  .photo:hover{ transform:translateY(-3px); box-shadow:0 14px 32px rgba(0,0,0,.1); }
  .thumb{width:100%;aspect-ratio:4/3;object-fit:cover;display:block;transition:transform .25s ease}
  .photo:hover .thumb{ transform:scale(1.03) }
  .meta{padding:10px 12px;font-size:13px;color:#1f2937;min-height:56px}
  .meta .sec{display:inline-block;margin-bottom:6px;font-size:12px;background:#f8fafc;border:1px solid #eef2f7;border-radius:999px;padding:3px 8px;font-weight:800}
  .btn.view{margin:10px 12px 12px auto; display:inline-flex; align-items:center; gap:6px}
  .muted{color:#6b6b6b}

  /* ===== Modal mejorado ===== */
  dialog#viewer{border:none;border-radius:16px;padding:0;max-width:92vw;background:transparent}
  .viewer-card{background:#000;border-radius:16px;overflow:hidden}
  .viewer-top{display:flex;justify-content:flex-end;padding:8px}
  .viewer-body{padding:0 10px 10px}
  .viewer-img{max-width:90vw;max-height:78vh;display:block;margin:auto;border-radius:10px;cursor:zoom-in}
  .viewer-cap{color:#e5e7eb;margin:8px 8px 0}
  .btn-close{background:#fff;color:#111;border:1px solid #e5e7eb;border-radius:10px;padding:6px 10px}
</style>

<div class="wrap">
  {{-- ===== Head ===== --}}
  <section class="head card">
    <div class="hrow">
      <div class="hstack">
        <h2 class="title">Inspección #{{ $rec->id }}</h2>
        <span class="chip">Placa: <b>{{ $rec->vehiculo_placa }}</b></span>
        <span class="chip">Tipo: <b>{{ $rec->type_vehiculo_id }}</b></span>
        <span class="chip">Fecha: <b>{{ optional($rec->fecha_creacion)->format('Y-m-d H:i') }}</b></span>
      </div>
      <div class="actions">
        <a class="btn" href="{{ route('inspecciones.index') }}">← Volver</a>
        <a class="btn primary" href="{{ route('inspecciones.edit',$rec) }}">✏️ Modificar</a>
      </div>
    </div>
    @if($rec->observaciones)
      <div style="margin-top:10px;color:#111"><b>Observaciones:</b> {{ $rec->observaciones }}</div>
    @endif
  </section>

  {{-- ===== Viewer ===== --}}
  <div class="viewer">
    {{-- Izquierda: Canvas con puntos --}}
    <section class="canvas">
      <div class="tabs" id="tabs">
        <button class="tab active" data-sec="front">Delantera <span class="count">{{ $secCounts['front'] }}</span></button>
        <button class="tab" data-sec="top">Superior <span class="count">{{ $secCounts['top'] }}</span></button>
        <button class="tab" data-sec="right">Derecha <span class="count">{{ $secCounts['right'] }}</span></button>
        <button class="tab" data-sec="left">Izquierda <span class="count">{{ $secCounts['left'] }}</span></button>
        <button class="tab" data-sec="back">Trasera <span class="count">{{ $secCounts['back'] }}</span></button>
      </div>

      <div class="stage" id="stage">
        <img id="sectionImg" alt="Sección actual">
        {{-- markers via JS --}}
      </div>
      <p class="legend">Los puntos representan hallazgos marcados sobre la vista seleccionada.</p>
    </section>

    {{-- Derecha: Galería sticky (todas las fotos) --}}
    <aside class="aside">
      <h3>Evidencias ({{ count($photoList) }})</h3>

      @if (count($photoList))
        <div class="grid">
          @foreach ($photoList as $ph)
            <article class="photo">
              <img class="thumb" src="{{ $ph['url'] }}" alt="{{ $ph['text'] ?: 'Evidencia' }}">
              <div class="meta">
                <span class="sec">Sección: {{ $ph['sec'] }}</span>
                <div>{{ $ph['text'] ?: 'Sin descripción' }}</div>
              </div>
              <button type="button" class="btn view js-view-photo"
                      data-src="{{ $ph['url'] }}" data-caption="{{ $ph['text'] }}">
                👁 Ver grande
              </button>
            </article>
          @endforeach
        </div>
      @else
        <p class="muted">No hay fotos registradas para esta inspección.</p>
      @endif
    </aside>
  </div>
</div>

{{-- ===== Modal ===== --}}
<dialog id="viewer">
  <div class="viewer-card">
    <div class="viewer-top">
      <button id="viewerClose" class="btn-close">Cerrar ✕</button>
    </div>
    <div class="viewer-body">
      <img id="viewerImg" class="viewer-img" alt="Evidencia">
      <p id="viewerCap" class="viewer-cap"></p>
    </div>
  </div>
</dialog>

<script>
  // Imágenes base por sección
  const SECTION_IMG = {
    front: @json(Vite::asset('resources/otros/assets/sections/front.jpg')),
    top:   @json(Vite::asset('resources/otros/assets/sections/top.jpg')),
    right: @json(Vite::asset('resources/otros/assets/sections/right.jpg')),
    left:  @json(Vite::asset('resources/otros/assets/sections/left.jpg')),
    back:  @json(Vite::asset('resources/otros/assets/sections/back.jpg')),
  };

  const MARKS = @json($marks);

  let current = 'front';
  const tabs = document.querySelectorAll('#tabs .tab');
  const stage = document.getElementById('stage');
  const sectionImg = document.getElementById('sectionImg');

  function render() {
    sectionImg.src = SECTION_IMG[current];
    // limpiar markers
    [...stage.querySelectorAll('.marker')].forEach(m => m.remove());
    // dibujar markers
    (MARKS[current] || []).forEach((p, i) => {
      const m = document.createElement('div');
      m.className = 'marker';
      m.style.left = (p.x || 0) + '%';
      m.style.top  = (p.y || 0) + '%';
      m.title = `${i+1}. ${p.text || 'Detalle'}`;
      stage.appendChild(m);
    });
  }

  tabs.forEach(t => t.addEventListener('click', () => {
    tabs.forEach(x => x.classList.remove('active'));
    t.classList.add('active');
    current = t.dataset.sec;
    render();
  }));

  // ===== Modal viewer =====
  const viewer = document.getElementById('viewer');
  const viewerImg = document.getElementById('viewerImg');
  const viewerCap = document.getElementById('viewerCap');
  const viewerClose = document.getElementById('viewerClose');

  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.js-view-photo');
    if(!btn) return;
    viewerImg.src = btn.dataset.src;
    viewerCap.textContent = btn.dataset.caption || '';
    viewer.showModal();
  });

  // Zoom on click
  viewerImg.addEventListener('click', ()=>{
    if (viewerImg.style.transform) {
      viewerImg.style.transform = '';
      viewerImg.style.cursor = 'zoom-in';
    } else {
      viewerImg.style.transform = 'scale(1.1)';
      viewerImg.style.cursor = 'zoom-out';
    }
  });

  // Cerrar
  function closeViewer(){ viewer.close(); viewerImg.style.transform=''; viewerImg.style.cursor='zoom-in'; }
  viewerClose.addEventListener('click', closeViewer);
  document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape' && viewer.open) closeViewer(); });

  render();
</script>
@endsection
