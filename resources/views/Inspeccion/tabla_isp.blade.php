@extends('layouts.app')

@section('title','Inspecciones')

@push('styles')
<style>
  html, body { height:100%; background:#f5f6f8 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f5f6f8 !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  :root{
    --brand:#9F3B3B;
    --brand-2:#C24242;
    --ink:#1e1e1e;
  }

  .btn-theme{ background:var(--brand); border-color:var(--brand); color:#fff; border-radius:12px; padding:.55rem 1rem; }
  .btn-theme:hover{ background:var(--brand-2); border-color:var(--brand-2); color:#fff; }
  .control{ height:40px; border-radius:12px; }

  .page-header{ text-align:center; margin:48px 0 16px; }
  .page-header h1{ font-weight:800; color:var(--brand-2); letter-spacing:.2px; }

  .toolbar{
    max-width:1150px; margin:0 auto 14px;
    display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap;
  }

  .card-shell{ max-width:1150px; margin:0 auto; border-radius:16px; overflow:hidden; }

  /* Alertas bonitas */
  .alert-wrap{ max-width:1150px; margin:0 auto 12px; }
  .alert-soft{
    border-radius:14px; padding:12px 14px; display:flex; align-items:flex-start; gap:10px;
    border:1px solid; background:#fff;
    box-shadow:0 8px 24px rgba(16,24,40,.05), 0 1px 2px rgba(16,24,40,.08);
  }
  .alert-soft.success{ border-color:#c6f6d5; background:#f0fff4; color:#153e1f; }
  .alert-soft.error{ border-color:#fecaca; background:#fff1f2; color:#7f1d1d; }
  .alert-soft .close{ appearance:none; border:none; background:transparent; font-size:18px; line-height:1; opacity:.6; cursor:pointer; }
  .alert-soft .close:hover{ opacity:1; }

  /* Tabla */
  .table thead th{ background:#101828; color:#fff; border-color:#101828; font-weight:700; }
  .table tbody tr{ transition:.15s ease; }
  .table tbody tr:hover{ background:#fafafa; }
  .table td,.table th{ vertical-align:middle; }

  /* BOTONES REDONDOS DE ACCIONES (ver / editar / eliminar) */
  .btn-round{
    width:38px;
    height:38px;
    border-radius:999px;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:0;               /* quitamos padding que descentraba el icono */
  }
  .btn-round i{
    font-size:1.05rem;
    line-height:1;           /* centra visualmente el ícono */
    display:block;
  }

  .chip{
    display:inline-block; padding:.25rem .55rem; border-radius:999px; font-weight:700;
    border:1px solid #e5e7eb; background:#eef2f7; color:#111;
  }
  .chip.type{ background:#fff7ed; border-color:#ffedd5; color:#9a3412; font-weight:800; }

  /* Paginación */
  .pagination .page-link{ color:#1d1d1d; border-color:#e9ecef; }
  .pagination .page-link:hover{ color:#1d1d1d; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d; color:#fff; }
  .pagination .page-item.disabled .page-link{ color:#adb5bd; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-link:focus{ box-shadow:0 0 0 .15rem rgba(159,59,59,.15); }

  /* Tooltips mínimos */
  [data-title] { position:relative; }
  [data-title]:hover::after{
    content:attr(data-title);
    position:absolute; bottom:calc(100% + 6px); right:0;
    background:#111; color:#fff; font-size:12px; padding:4px 6px; border-radius:6px; white-space:nowrap;
  }

  /* Modal de confirmación bonito */
  .confirm-backdrop{ position:fixed; inset:0; background:rgba(17,17,17,.45); display:none; align-items:center; justify-content:center; z-index:10000; }
  .confirm-backdrop.is-open{ display:flex; animation:fadeIn .18s ease-out; }
  @keyframes fadeIn{ from{opacity:0} to{opacity:1} }

  .confirm-card{
    width:min(520px,92vw); background:#fff; border-radius:16px; text-align:center;
    padding:26px 20px 18px; box-shadow:0 22px 60px rgba(0,0,0,.22);
    transform:translateY(8px); animation:popIn .2s ease-out forwards;
  }
  @keyframes popIn{ to{ transform:translateY(0) } }

  .confirm-title{ font-weight:800; font-size:1.35rem; margin-top:14px; color:#111827 }
  .confirm-text{ color:#6b7280; margin:6px 16px 16px }
  .confirm-actions{ display:flex; gap:10px; justify-content:center; margin-top:6px; flex-wrap:wrap; }
  .cbtn{ appearance:none; border:0; border-radius:12px; padding:.6rem 1.1rem; font-weight:700; cursor:pointer }
  .cbtn.cancel{ background:#e5e7eb; color:#111827 }
  .cbtn.cancel:hover{ filter:brightness(.97) }
  .cbtn.danger{ background:#ef4444; color:#fff }
  .cbtn.danger:hover{ filter:brightness(.95) }

  /* anim icon */
  .cicon{ width:90px; height:90px; display:inline-block }
  .ccircle{ stroke-dasharray:300; stroke-dashoffset:300; animation:dash .75s ease forwards }
  .cx{ stroke-dasharray:80; stroke-dashoffset:80; animation:dash .55s .25s ease forwards }
  @keyframes dash{ to{ stroke-dashoffset:0 } }
  .ccircle-stroke{ stroke:#fecaca } .cx-stroke{ stroke:#ef4444 }
</style>
@endpush

@section('content')

  {{-- Alertas bonitas --}}
  <div class="alert-wrap">
    @if(session('ok'))
      <div class="alert-soft success" id="msg-ok">
        <div>✅</div>
        <div class="flex-grow-1">
          <strong>¡Listo!</strong> {{ session('ok') }}
        </div>
        <button class="close" onclick="this.closest('.alert-soft').remove()">×</button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert-soft error" id="msg-err">
        <div>❌</div>
        <div class="flex-grow-1">
          <strong>Ocurrió un problema.</strong> {{ session('error') }}
        </div>
        <button class="close" onclick="this.closest('.alert-soft').remove()">×</button>
      </div>
    @endif
  </div>

  {{-- Título --}}
  <div class="page-header">
    <h1>Inspecciones</h1>
  </div>

  {{-- Toolbar: botón + buscador --}}
  <div class="toolbar">
    <a href="{{ route('inspecciones.create') }}" class="btn btn-theme">
      <i class="bi bi-plus-lg me-1"></i> Nueva inspección
    </a>

    <form action="{{ route('inspecciones.index') }}" method="GET" class="d-flex align-items-center gap-2">
      <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control control"
               placeholder="Buscar por placa (ej. P123ABC)">
      </div>
      <button class="btn btn-dark control" type="submit" style="border-radius:12px;">Buscar</button>
      @if(request()->filled('q'))
        <a href="{{ route('inspecciones.index') }}" class="btn btn-outline-secondary control" style="border-radius:12px;">
          Limpiar
        </a>
      @endif
    </form>
  </div>

  {{-- Tabla --}}
  <div class="card card-shell shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width:90px">ID</th>
            <th style="width:200px">Fecha</th>
            <th style="width:150px">Placa</th>
            <th style="width:140px">Tipo</th>
            <th>Observaciones</th>
            <th class="text-center" style="width:200px">Acciones</th>
          </tr>
        </thead>
        <tbody>
        @forelse($items as $r)
          <tr>
            <td class="fw-semibold">{{ $r->id }}</td>
            <td>
              @php $fc = $r->fecha_creacion ?? ($r->created_at ?? null); @endphp
              {{ $fc ? \Illuminate\Support\Carbon::parse($fc)->format('d/m/Y H:i') : '—' }}
            </td>
            <td><span class="chip">{{ $r->vehiculo_placa ?? '—' }}</span></td>
            <td>
              <span class="chip type">
                {{ optional(DB::table('type_vehiculo')->find($r->type_vehiculo_id))->descripcion ?? $r->type_vehiculo_id ?? '—' }}
              </span>
            </td>
            <td class="text-start">
              {{ \Illuminate\Support\Str::limit($r->observaciones ?? '—', 120) }}
            </td>
            <td class="text-center">
              <div class="d-inline-flex justify-content-center gap-2">
                @if(Route::has('inspecciones.show'))
                  <a href="{{ route('inspecciones.show', $r) }}"
                     class="btn btn-outline-secondary btn-round" data-title="Ver">
                    <i class="bi bi-eye"></i>
                  </a>
                @endif

                <a href="{{ route('inspecciones.edit', $r) }}"
                   class="btn btn-outline-primary btn-round" data-title="Editar">
                  <i class="bi bi-pencil-square"></i>
                </a>

                @role('admin')
                <form action="{{ route('inspecciones.destroy', $r) }}" method="POST"
                      class="d-inline js-delete-form" data-item-id="{{ $r->id }}">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger btn-round" data-title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                @endrole
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <div class="mb-2" style="font-size:42px;">🗂️</div>
              <div class="fw-bold">No hay inspecciones registradas</div>
              <div class="small">Crea tu primera inspección para verla aquí</div>
              <div class="mt-3">
                <a href="{{ route('inspecciones.create') }}" class="btn btn-theme">
                  <i class="bi bi-plus-lg me-1"></i> Nueva inspección
                </a>
              </div>
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    {{-- Paginación --}}
    @if ($items instanceof \Illuminate\Contracts\Pagination\Paginator)
      <div class="card-footer bg-white">
        {{ $items->appends(['q'=>request('q')])->links() }}
      </div>
    @endif
  </div>

  {{-- Íconos Bootstrap --}}
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  {{-- Modal de confirmación de eliminación --}}
  <div class="confirm-backdrop" id="confirmDelete" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="confirm-card">
      <span class="cicon" aria-hidden="true">
        <svg viewBox="0 0 120 120">
          <circle cx="60" cy="60" r="44" fill="none" stroke-width="10" class="ccircle ccircle-stroke"></circle>
          <path d="M42 42 L78 78" fill="none" stroke-width="10" stroke-linecap="round" class="cx cx-stroke"></path>
          <path d="M78 42 L42 78" fill="none" stroke-width="10" stroke-linecap="round" class="cx cx-stroke"></path>
        </svg>
      </span>

      <h3 id="confirmTitle" class="confirm-title">¿Eliminar inspección?</h3>
      <p class="confirm-text" id="confirmText">Esta acción no se puede deshacer. Se eliminarán también sus fotos asociadas.</p>

      <div class="confirm-actions">
        <button type="button" class="cbtn cancel" id="btnCancelDel">Cancelar</button>
        <button type="button" class="cbtn danger" id="btnConfirmDel">Eliminar</button>
      </div>
    </div>
  </div>

  {{-- Scripts: auto-hide alertas + modal confirmación --}}
  <script>
    // Auto-hide alertas
    setTimeout(() => document.getElementById('msg-ok')?.remove(), 5000);
    setTimeout(() => document.getElementById('msg-err')?.remove(), 8000);

    // Modal confirmación
    (function(){
      const backdrop = document.getElementById('confirmDelete');
      const btnOk    = document.getElementById('btnConfirmDel');
      const btnNo    = document.getElementById('btnCancelDel');
      const titleEl  = document.getElementById('confirmTitle');
      const textEl   = document.getElementById('confirmText');
      let currentForm = null;

      document.querySelectorAll('form.js-delete-form').forEach(f => {
        f.addEventListener('submit', function(e){
          e.preventDefault();
          currentForm = this;

          const id = this.dataset.itemId || '';
          titleEl.textContent = id ? `¿Eliminar inspección #${id}?` : '¿Eliminar inspección?';
          textEl.textContent  = 'Esta acción no se puede deshacer. Se eliminarán también sus fotos asociadas.';

          openModal();
        });
      });

      const openModal  = () => { backdrop.classList.add('is-open'); trapFocus(); };
      const closeModal = () => { backdrop.classList.remove('is-open'); releaseFocus(); };

      btnOk.addEventListener('click', () => { if(currentForm){ closeModal(); currentForm.submit(); } });
      btnNo.addEventListener('click', closeModal);
      backdrop.addEventListener('click', (e)=> { if(e.target === backdrop) closeModal(); });

      document.addEventListener('keydown', (e)=>{
        if(!backdrop.classList.contains('is-open')) return;
        if(e.key === 'Escape') closeModal();
        if(e.key === 'Enter')  { e.preventDefault(); btnOk.click(); }
      });

      let lastFocus = null;
      function trapFocus(){ lastFocus = document.activeElement; btnNo.focus(); }
      function releaseFocus(){ if(lastFocus) lastFocus.focus(); }
    })();
  </script>
@endsection
