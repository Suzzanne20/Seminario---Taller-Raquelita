@extends('layouts.app')
@section('title','Detalle de Inspección')

@section('content')
<style>
  :root{ --brand:#8f2f2f; --line:#e6e6e6; --muted:#6b6b6b; }
  body{background:#f6f6f7}
  .wrap{max-width:1100px;margin:18px auto;padding:0 16px}
  .card{background:#fff;border:1px solid var(--line);border-radius:14px;padding:16px;margin-bottom:14px}
  .hrow{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
  .badge{display:inline-block;padding:6px 10px;border:1px solid var(--line);border-radius:999px;background:#fafafa}
  .btn{border:1px solid var(--line);background:#fff;border-radius:10px;padding:8px 10px;font-weight:600;cursor:pointer}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}
  .photo{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#fff;display:flex;flex-direction:column}
  .thumb{width:100%;height:160px;object-fit:cover;display:block}
  .meta{padding:10px;font-size:13px;color:#222;min-height:54px}
  .muted{color:#6b6b6b}
  .warn{color:#b23b3b;font-size:12px;padding:8px}
</style>

<div class="wrap">
  <section class="card">
    <div class="hrow" style="justify-content:space-between">
      <div class="hrow">
        <h2 style="margin:0">Inspección #{{ $rec->id }}</h2>
        <span class="badge"><b>Placa:</b> {{ $rec->vehiculo_placa }}</span>
        <span class="badge"><b>Tipo:</b> {{ $rec->type_vehiculo_id }}</span>
        <span class="badge"><b>Fecha:</b> {{ optional($rec->fecha_creacion)->format('Y-m-d H:i') }}</span>
      </div>
      <div class="hrow">
        <a class="btn" href="{{ route('inspecciones.edit',$rec) }}">✏️ Modificar</a>
      </div>
    </div>
    @if($rec->observaciones)
      <div style="margin-top:8px"><b>Observaciones:</b> {{ $rec->observaciones }}</div>
    @endif
  </section>

  <section class="card">
    <h3 style="margin:0 0 10px">Fotos</h3>
    @php
      $baseDir = "inspecciones/{$rec->vehiculo_placa}/{$rec->id}";
    @endphp

    @if ($rec->fotos->count())
      <div class="grid">
        @foreach ($rec->fotos as $f)
          @php
            $stored  = ltrim($f->path_foto ?? '', '/');
            // Si $stored ya trae subcarpetas, úsalo tal cual; si no, móntalo bajo la carpeta de la recepción
            $relPath = (strpos($stored,'/') !== false) ? $stored : ($baseDir.'/'.$stored);
            $exists  = Storage::disk('public')->exists($relPath);
            $url     = Storage::url($relPath);
          @endphp
          <article class="photo">
            @if($exists)
              <img class="thumb" src="{{ $url }}" alt="{{ $f->descripcion }}">
            @else
              <div class="warn">⚠ No se encontró el archivo:<br><code>{{ $relPath }}</code></div>
            @endif
            <div class="meta">{{ $f->descripcion ?: 'Sin descripción' }}</div>
            @if($exists)
              <button type="button" class="btn js-view-photo"
                      data-src="{{ $url }}" data-caption="{{ $f->descripcion }}">
                Ver
              </button>
            @endif
          </article>
        @endforeach
      </div>
    @else
      <span class="muted">No hay fotos registradas.</span>
    @endif
  </section>
</div>

{{-- Visor simple --}}
<dialog id="viewer" style="border:none;border-radius:16px;padding:0;max-width:90vw">
  <div style="padding:12px 12px 0">
    <img id="viewerImg" alt="Foto" style="max-width:90vw;max-height:75vh;display:block;border-radius:10px">
    <p id="viewerCap" class="muted" style="margin:10px 4px 0"></p>
    <div style="display:flex;justify-content:flex-end;padding:12px">
      <button id="viewerClose" class="btn">Cerrar</button>
    </div>
  </div>
</dialog>

<script>
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

  viewerClose.addEventListener('click', ()=> viewer.close());
</script>
@endsection
