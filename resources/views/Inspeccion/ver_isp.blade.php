@extends('layouts.app')
@section('title','Detalle de Inspección')

@php
  // === Normalizar puntos/JSON ===
  $marks = is_array($rec->detalles_json)
      ? $rec->detalles_json
      : (json_decode($rec->detalles_json ?? '[]', true) ?: []);

  $sections = ['front','top','right','left','back'];

  // Contadores
  $secCounts = [];
  foreach ($sections as $s) $secCounts[$s] = count($marks[$s] ?? []);
  $totalPuntos = array_sum($secCounts);
@endphp

@push('styles')
<style>
  :root{ --brand:#8f2f2f; --line:#e5e7eb; --muted:#6b7280 }
  body{ background:#f7f7fb }
  .shadow-soft{ box-shadow:0 .5rem 1.5rem rgba(15,23,42,.06) }
  .badge-chip{
    border:1px solid var(--line); background:#fff; border-radius:999px;
    padding:.35rem .75rem; font-weight:700; display:inline-flex; gap:.5rem; align-items:center;
  }
  .stage{
    position:relative; background:#fff; border:1px dashed var(--line);
    border-radius:1rem; min-height:520px; display:flex; align-items:center;
    justify-content:center; overflow:hidden;
  }
  .marker{
    position:absolute; width:18px; height:18px; border-radius:50%;
    background:radial-gradient(circle at 40% 40%, #ff9a9a 0%, #e95d5d 60%);
    border:2px solid #fff; box-shadow:0 3px 10px rgba(233,93,93,.35);
    transform:translate(-50%,-50%); cursor:help;
  }
  .tab-pill .nav-link{ border-radius:999px; font-weight:700 }
  .tab-pill .nav-link.active{ background:var(--brand); color:#fff }
</style>
@endpush

@section('content')
<div class="container py-4">

  {{-- === Encabezado === --}}
  <div class="row justify-content-center">
    <div class="col-12 col-xl-10">
      <div class="card border-0 shadow-soft mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
              <h2 class="mb-2 fw-bold">Inspección #{{ $rec->id }}</h2>
              <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge-chip">
                  <span class="text-secondary">Placa:</span> <span class="fw-bold">{{ $rec->vehiculo_placa }}</span>
                </span>
                <span class="badge-chip">
                  <span class="text-secondary">Tipo:</span> <span class="fw-bold">{{ $rec->type_vehiculo_id }}</span>
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
                <span class="badge rounded-pill text-bg-light border">Puntos: <strong class="ms-1">{{ $totalPuntos }}</strong></span>
              </div>
            </div>
            <div class="text-lg-end">
              <a href="{{ route('inspecciones.index') }}" class="btn btn-outline-secondary me-2">← Volver</a>
              <a href="{{ route('inspecciones.edit',$rec) }}" class="btn btn-danger" style="background:var(--brand); border-color:var(--brand)">✏️ Modificar</a>
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
            <li class="nav-item"><button class="nav-link active" data-sec="front" type="button">
              Delantera <span class="badge text-bg-light border ms-1">{{ $secCounts['front'] }}</span>
            </button></li>
            <li class="nav-item"><button class="nav-link" data-sec="top" type="button">
              Superior <span class="badge text-bg-light border ms-1">{{ $secCounts['top'] }}</span>
            </button></li>
            <li class="nav-item"><button class="nav-link" data-sec="right" type="button">
              Derecha <span class="badge text-bg-light border ms-1">{{ $secCounts['right'] }}</span>
            </button></li>
            <li class="nav-item"><button class="nav-link" data-sec="left" type="button">
              Izquierda <span class="badge text-bg-light border ms-1">{{ $secCounts['left'] }}</span>
            </button></li>
            <li class="nav-item"><button class="nav-link" data-sec="back" type="button">
              Trasera <span class="badge text-bg-light border ms-1">{{ $secCounts['back'] }}</span>
            </button></li>
          </ul>

          {{-- Lienzo del carro --}}
          <div class="stage mb-4 mx-auto" id="stage" style="max-width:1000px">
            <img id="sectionImg" alt="Sección actual" class="img-fluid">
            {{-- markers vía JS --}}
          </div>

          {{-- Tabla de puntos (centrada y compacta) --}}
          <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr class="text-center">
                      <th style="width:70px">#</th>
                      <th style="width:120px">X %</th>
                      <th style="width:120px">Y %</th>
                      <th>Descripción</th>
                      <th style="width:130px">Imagen</th>
                    </tr>
                  </thead>
                  <tbody id="pointsTbody">
                    <tr><td colspan="5" class="text-center text-muted">Sin puntos en esta sección…</td></tr>
                  </tbody>
                </table>
              </div>
              <p class="text-center text-muted small mb-0">Los puntos representan hallazgos marcados sobre la vista seleccionada.</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>

{{-- === Modal de foto === --}}
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-soft">
      <div class="modal-header">
        <h6 class="modal-title">Evidencia</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body bg-black">
        <img id="modalImg" class="img-fluid d-block mx-auto" alt="Foto" style="max-height:78vh;object-fit:contain">
        <div id="modalCap" class="text-light small mt-2"></div>
      </div>
    </div>
  </div>
</div>

{{-- === Scripts === --}}
<script>
  // Imágenes por sección
  const SECTION_IMG = {
    front: @json(Vite::asset('resources/otros/assets/sections/front.jpg')),
    top:   @json(Vite::asset('resources/otros/assets/sections/top.jpg')),
    right: @json(Vite::asset('resources/otros/assets/sections/right.jpg')),
    left:  @json(Vite::asset('resources/otros/assets/sections/left.jpg')),
    back:  @json(Vite::asset('resources/otros/assets/sections/back.jpg')),
  };

  // Puntos
  const MARKS = @json($marks);

  // Estado UI
  let current = 'front';
  const stage = document.getElementById('stage');
  const sectionImg = document.getElementById('sectionImg');
  const pointsTbody = document.getElementById('pointsTbody');

  // Tabs
  document.querySelectorAll('#secTabs .nav-link').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.querySelectorAll('#secTabs .nav-link').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      current = btn.dataset.sec;
      render();
    });
  });

  // Render canvas + tabla
  function render(){
    sectionImg.src = SECTION_IMG[current];

    // Borrar markers previos
    [...stage.querySelectorAll('.marker')].forEach(m=>m.remove());

    const list = MARKS[current] || [];

    // Dibujar markers
    list.forEach((p,i)=>{
      const m = document.createElement('div');
      m.className = 'marker';
      m.style.left = (p.x || 0) + '%';
      m.style.top  = (p.y || 0) + '%';
      m.title = `${i+1}. ${p.text || 'Detalle'}`;
      stage.appendChild(m);
    });

    // Tabla
    if(!list.length){
      pointsTbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Sin puntos en esta sección…</td></tr>`;
    }else{
      pointsTbody.innerHTML = list.map((p,i)=>{
        const hasImg = !!p.stream_url;
        return `
          <tr class="text-center">
            <td><span class="badge text-bg-secondary">${i+1}</span></td>
            <td>${(p.x ?? 0).toFixed(2)}%</td>
            <td>${(p.y ?? 0).toFixed(2)}%</td>
            <td class="text-start">${escapeHtml(p.text || '—')}</td>
            <td>
              ${hasImg
                ? `<button class="btn btn-outline-dark btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#photoModal"
                            data-src="${p.stream_url}"
                            data-caption="${escapeHtml(p.text||'')}">Ver</button>`
                : '<span class="text-muted">—</span>'
              }
            </td>
          </tr>
        `;
      }).join('');
    }
  }

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, s=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[s]));
  }

  // Modal de foto
  const photoModal = document.getElementById('photoModal');
  const modalImg   = document.getElementById('modalImg');
  const modalCap   = document.getElementById('modalCap');

  photoModal.addEventListener('show.bs.modal', event => {
    const btn = event.relatedTarget;
    const src = btn?.getAttribute('data-src') || '';
    const cap = btn?.getAttribute('data-caption') || '';
    modalImg.src = src;
    modalCap.textContent = cap;
  });

  render();
</script>
@endsection
