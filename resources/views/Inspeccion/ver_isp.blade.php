@extends('layouts.app')
@section('title','Detalle de Inspección')

@php
    // === Normalizar puntos/JSON igual que en edición ===
    $empty   = ['front'=>[], 'top'=>[], 'right'=>[], 'left'=>[], 'back'=>[]];
    $raw     = $rec->detalles_json;

    if (is_array($raw)) {
        $marks = array_merge($empty, $raw);
    } elseif (is_string($raw)) {
        $decoded = json_decode($raw, true);
        $marks   = is_array($decoded) ? array_merge($empty, $decoded) : $empty;
    } else {
        $marks = $empty;
    }

    $sections = ['front','top','right','left','back'];

    // Contadores por sección
    $secCounts = [];
    foreach ($sections as $s) {
        $secCounts[$s] = count($marks[$s] ?? []);
    }
    $totalPuntos = array_sum($secCounts);

    // ===== Mapeo numérico → nombre de tipo =====
    $tipoMap = [
        2 => 'Camioneta',
        3 => 'Pick up',
        1 => 'Sedán',
        // agrega más si tienes
    ];

    $tipoNombre = $tipoMap[$rec->type_vehiculo_id] ?? 'No definido';
@endphp

@push('styles')
<style>
  :root{
    --brand:#8f2f2f;
    --line:#e5e7eb;
    --muted:#6b7280;
  }
  body{ background:#f7f7fb }

  .shadow-soft{ box-shadow:0 .5rem 1.5rem rgba(15,23,42,.06) }

  .badge-chip{
    border:1px solid var(--line);
    background:#fff;
    border-radius:999px;
    padding:.35rem .75rem;
    font-weight:700;
    display:inline-flex;
    gap:.5rem;
    align-items:center;
  }

  /* === CONTENEDOR IGUAL AL DEL REGISTRO === */
  .canvas{
    background:#fff;
    border:1px solid var(--line);
    border-radius:14px;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
    padding:12px;
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
    background:radial-gradient(circle at 40% 40%, #ff9a9a 0%, #e95d5d 60%);
    border:2px solid #fff;
    box-shadow:0 3px 10px rgba(233,93,93,.35);
    transform:translate(-50%,-50%);
    cursor:help;
  }

  .tab-pill .nav-link{
    border-radius:999px;
    font-weight:700;
  }
  .tab-pill .nav-link.active{
    background:var(--brand);
    color:#fff;
  }

  /* ===== BOTONES DE ACCIÓN (Volver / Modificar) ===== */
  .btn-isp{
    background:var(--brand);
    border:1px solid var(--brand);
    color:#fff !important;
    border-radius:999px;
    padding:12px 32px;
    font-weight:600;
    font-size:16px;
    width:180px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    text-decoration:none;
    transition:
      transform .18s ease,
      box-shadow .18s ease,
      background .18s ease,
      border-color .18s ease;
    box-shadow:0 6px 16px rgba(143,47,47,.35);
  }
  .btn-isp__icon{
    display:inline-flex;
    transition:transform .18s ease;
  }
  .btn-isp:hover{
    transform:translateY(-2px) scale(1.02);
    box-shadow:0 12px 24px rgba(143,47,47,.45);
    background:#a63a3a;
    border-color:#a63a3a;
  }
  .btn-isp:hover .btn-isp__icon{
    transform:translateX(-3px);
  }
  .btn-isp:active{
    transform:translateY(0) scale(.97);
    box-shadow:0 4px 10px rgba(143,47,47,.35);
  }

  /* === ICONO "VER" EN LA TABLA === */
  .table-actions{
    display:flex;
    justify-content:center;
    align-items:center;
  }

  .table-actions .btn-circle{
    width:34px;
    height:34px;
    border-radius:50%;
    padding:0;
    border:none;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    background:#b43b3b;
    color:#fff;
    box-shadow:0 0 0 2px rgba(0,0,0,.18);
    transition:background .15s ease, transform .15s ease, box-shadow .15s ease;
  }

  .table-actions .btn-circle:hover{
    background:#8f2f2f;
    transform:translateY(-1px) scale(1.04);
    box-shadow:0 6px 14px rgba(148,27,27,.45);
  }

  .table-actions .btn-circle:focus{
    outline:none;
    box-shadow:0 0 0 2px #2563eb;
  }

  .table-actions .btn-circle i{
    font-size:14px;
    line-height:1;
  }

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
    max-height:67vh;
    object-fit:contain;
    border-radius:12px;
    align-self:center;
  }

  /* Tarjetita del texto */
  .img-overlay__meta{
    align-self:stretch;
    margin-top:.55rem;
    background:rgba(15,23,42,.9);
    border-radius:12px;
    padding:.6rem .8rem;
    border:1px solid rgba(148,163,184,.45);
  }
  .img-overlay__meta--hidden{
    display:none;
  }
  .img-overlay__label{
    font-size:.72rem;
    font-weight:600;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:#9ca3af;
    margin-bottom:.15rem;
  }
  .img-overlay__cap{
    margin:0;
    font-size:.9rem;
    color:#e5e7eb;
    line-height:1.45;
    white-space:pre-wrap;
  }

  .img-overlay__close{
    border:none;
    background:#dc2626;    /* rojo visible */
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
<div class="container py-4">

  {{-- === Encabezado === --}}
  <div class="row justify-content-center">
    <div class="col-12 col-xl-10">
      <div class="card border-0 shadow-soft mb-4">
        <div class="card-body p-4">

          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
            {{-- Columna de datos --}}
            <div>
              <h2 class="mb-2 fw-bold">Inspección #{{ $rec->id }}</h2>

              <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge-chip">
                  <span class="text-secondary">Placa:</span>
                  <span class="fw-bold">{{ $rec->vehiculo_placa }}</span>
                </span>

                <span class="badge-chip">
                  <span class="text-secondary">Tipo:</span>
                  <span class="fw-bold">{{ $tipoNombre }}</span>
                </span>

                <span class="badge-chip">
                  <span class="text-secondary">Fecha:</span>
                  <span class="fw-bold">{{ optional($rec->fecha_creacion)->format('Y-m-d H:i') }}</span>
                </span>

                <span class="badge-chip">
                  <span class="text-secondary">Técnico:</span>
                  <span class="fw-bold">{{ optional($rec->tecnicoRel)->name ?? '— No asignado —' }}</span>
                </span>
              </div>

              <div class="d-flex flex-wrap gap-2">
                <span class="badge rounded-pill text-bg-light border">
                  Puntos: <strong class="ms-1">{{ $totalPuntos }}</strong>
                </span>
              </div>
            </div>

            {{-- Columna de botones --}}
            <div class="d-flex flex-column align-items-end gap-2">
              <a href="{{ route('inspecciones.index') }}" class="btn-isp">
                <span class="btn-isp__icon">←</span>
                <span>Volver</span>
              </a>

              <a href="{{ route('inspecciones.edit',$rec) }}" class="btn-isp">
                <span class="btn-isp__icon">✏️</span>
                <span>Modificar</span>
              </a>
            </div>
          </div>

          @if($rec->observaciones)
            <div class="mt-3 p-3 border rounded-3 bg-light">
              <div class="fw-bold text-secondary mb-1">Observaciones</div>
              <div class="text-dark">{{ $rec->observaciones }}</div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- === Vista centrada: Canvas + Tabla debajo === --}}
  <div class="row justify-content-center">
    <div class="col-12 col-xl-10">
      <div class="card border-0 shadow-soft">
        <div class="card-body p-4">

          {{-- Tabs de secciones --}}
          <ul class="nav nav-pills justify-content-center gap-2 tab-pill mb-3" id="secTabs" role="tablist">
            <li class="nav-item">
              <button class="nav-link active" data-sec="front" type="button">
                Delantera <span class="badge text-bg-light border ms-1">{{ $secCounts['front'] }}</span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-sec="top" type="button">
                Superior <span class="badge text-bg-light border ms-1">{{ $secCounts['top'] }}</span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-sec="right" type="button">
                Derecha <span class="badge text-bg-light border ms-1">{{ $secCounts['right'] }}</span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-sec="left" type="button">
                Izquierda <span class="badge text-bg-light border ms-1">{{ $secCounts['left'] }}</span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-sec="back" type="button">
                Trasera <span class="badge text-bg-light border ms-1">{{ $secCounts['back'] }}</span>
              </button>
            </li>
          </ul>

          {{-- Lienzo del carro --}}
          <div class="canvas mb-4 mx-auto" style="max-width:1000px">
            <div class="vehicle-area" id="vehicleArea">
              <img id="sectionImg" alt="Sección actual" class="img-fluid">
              {{-- markers vía JS --}}
            </div>
          </div>

          {{-- Tabla de puntos --}}
          <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr class="text-center">
                      <th style="width:70px">#</th>
                      <th>Descripción</th>
                      <th style="width:130px">Imagen</th>
                    </tr>
                  </thead>
                  <tbody id="pointsTbody">
                    <tr>
                      <td colspan="3" class="text-center text-muted">
                        Sin puntos en esta sección…
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p class="text-center text-muted small mb-0">
                Los puntos representan hallazgos marcados sobre la vista seleccionada.
              </p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

{{-- === Overlay de foto === --}}
<div id="imgOverlay" class="img-overlay" aria-hidden="true">
  <div class="img-overlay__content">
    <button type="button"
            id="imgOverlayClose"
            class="img-overlay__close"
            aria-label="Cerrar imagen">×</button>
    <img id="imgOverlayImg" src="" alt="Evidencia" class="img-overlay__img">
    <div id="imgOverlayMeta" class="img-overlay__meta img-overlay__meta--hidden">
      <div class="img-overlay__label">Detalle del punto</div>
      <p id="imgOverlayCap" class="img-overlay__cap"></p>
    </div>
  </div>
</div>

{{-- === Scripts === --}}
<script>
  // Imágenes por sección
  const sectionImages = {
    front: "{{ asset('img/sections/front.jpg') }}",
    top:   "{{ asset('img/sections/top.jpg') }}",
    right: "{{ asset('img/sections/right.jpg') }}",
    left:  "{{ asset('img/sections/left.jpg') }}",
    back:  "{{ asset('img/sections/back.jpg') }}",
  };

  // Puntos enviados desde PHP
  const MARKS = @json($marks);

  // Estado UI
  let current = 'front';
  const vehicleArea = document.getElementById('vehicleArea');
  const sectionImg  = document.getElementById('sectionImg');
  const pointsTbody = document.getElementById('pointsTbody');

  // Overlay imagen
  const imgOverlay      = document.getElementById('imgOverlay');
  const imgOverlayImg   = document.getElementById('imgOverlayImg');
  const imgOverlayMeta  = document.getElementById('imgOverlayMeta');
  const imgOverlayCap   = document.getElementById('imgOverlayCap');
  const imgOverlayClose = document.getElementById('imgOverlayClose');

  function openOverlay(src, cap){
    imgOverlayImg.src = src;

    const cleanCap = (cap || '').trim();
    if (cleanCap.length) {
      imgOverlayCap.textContent = cleanCap;
      imgOverlayMeta.classList.remove('img-overlay__meta--hidden');
    } else {
      imgOverlayCap.textContent = '';
      imgOverlayMeta.classList.add('img-overlay__meta--hidden');
    }

    imgOverlay.classList.add('img-overlay--show');
    imgOverlay.setAttribute('aria-hidden','false');
  }

  function closeOverlay(){
    imgOverlay.classList.remove('img-overlay--show');
    imgOverlay.setAttribute('aria-hidden','true');
    imgOverlayImg.src = '';
    imgOverlayCap.textContent = '';
    imgOverlayMeta.classList.add('img-overlay__meta--hidden');
  }

  imgOverlayClose.addEventListener('click', closeOverlay);

  imgOverlay.addEventListener('click', (e) => {
    if (e.target === imgOverlay) closeOverlay();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeOverlay();
  });

  // Tabs
  document.querySelectorAll('#secTabs .nav-link').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('#secTabs .nav-link').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      current = btn.dataset.sec;
      render();
    });
  });

  // Render canvas + tabla
  function render(){
    // Imagen de la sección actual
    sectionImg.src = sectionImages[current];

    // Borrar markers previos
    [...vehicleArea.querySelectorAll('.marker')].forEach(m => m.remove());

    const list = MARKS[current] || [];

    // Dibujar markers
    list.forEach((p, i) => {
      const m = document.createElement('div');
      m.className = 'marker';
      m.style.left = (p.x || 0) + '%';
      m.style.top  = (p.y || 0) + '%';
      m.title = `${i+1}. ${p.text || 'Detalle'}`;
      vehicleArea.appendChild(m);
    });

    // Tabla
    if (!list.length) {
      pointsTbody.innerHTML =
        `<tr><td colspan="3" class="text-center text-muted">Sin puntos en esta sección…</td></tr>`;
    } else {
      pointsTbody.innerHTML = list.map((p, i) => {
        const hasImg = !!p.stream_url;
        const captionSafe = escapeHtml(p.text || '');
        return `
          <tr class="text-center">
            <td><span class="badge text-bg-secondary">${i+1}</span></td>
            <td class="text-start">${escapeHtml(p.text || '—')}</td>
            <td>
              ${
                hasImg
                  ? `<div class="table-actions">
                       <button
                         type="button"
                         class="btn-circle btn-view-img"
                         title="Ver imagen"
                         data-src="${p.stream_url}"
                         data-caption="${captionSafe}">
                         <i class="bi bi-eye"></i>
                       </button>
                     </div>`
                  : '<span class="text-muted">—</span>'
              }
            </td>
          </tr>
        `;
      }).join('');
    }

    // Conectar eventos de los botones "Ver"
    wireViewButtons();
  }

  function wireViewButtons(){
    document.querySelectorAll('.btn-view-img').forEach(btn => {
      btn.addEventListener('click', () => {
        const src = btn.getAttribute('data-src') || '';
        const cap = btn.getAttribute('data-caption') || '';
        if (!src) return;
        openOverlay(src, cap);
      });
    });
  }

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, s => ({
      '&':'&amp;',
      '<':'&lt;',
      '>':'&gt;',
      '"':'&quot;',
      "'":'&#39;'
    }[s]));
  }

  // Inicializar
  render();
</script>
@endsection
