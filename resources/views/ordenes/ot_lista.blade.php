@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:rgba(255, 255, 255, 0.144) !important; color:#212529; }
  @media (max-width:576px){ .page-body { min-height:calc(100vh - 64px); } }

  .btn-theme{ background:#9F3B3B; border-color:#9F3B3B; color:#fff; }
  .btn-theme:hover{ background:#873131; border-color:#873131; color:#fff; }

  .pagination .page-link{ color:#1d1d1d; border-color:#e9ecef; }
  .pagination .page-link:hover{ color:#1d1d1d; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-item.active .page-link{ background:#535353; border-color:#1d1d1d; color:#fff; }
  .pagination .page-item.disabled .page-link{ color:#adb5bd; background:#f8f9fa; border-color:#e9ecef; }
  .pagination .page-link:focus{ box-shadow:0 0 0 .15rem rgba(159,59,59,.15); }
</style>
@endpush

@section('content')
<div class="container py-4">

  <div class="container">
    <h1 class="text-center mb-4" style="color:#C24242;">Órdenes de Trabajo</h1>
  </div>

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <a href="{{ route('ordenes.create') }}"
     class="btn"
     style="background:#9F3B3B; border-color:#9F3B3B; color:#fff; border-radius:12px; padding:.55rem 1rem;">
    <i class="bi bi-plus-lg me-1"></i> Nueva Orden
  </a>

  <form action="{{ route('ordenes.index') }}" method="GET" class="d-flex align-items-center gap-2">
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
      <input type="text" name="q" class="form-control" placeholder="Buscar por placa…"
             value="{{ request('q') }}">
    </div>

    {{-- Filtro por estado --}}
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-flag"></i></span>
      <select name="estado" class="form-select" style="min-width:220px">
        <option value="">— Todos los estados —</option>
        @foreach($estados as $e)
          <option value="{{ $e->id }}" @selected((string)$e->id === request('estado'))>
            {{ $e->nombre }}
          </option>
        @endforeach
      </select>
    </div>

    <button class="btn btn-dark" type="submit" style="border-radius:12px;">Buscar</button>

    @if(request()->hasAny(['q','estado']) && (request('q') || request('estado')))
      <a href="{{ route('ordenes.index') }}" class="btn btn-outline-secondary" style="border-radius:12px;">
        Limpiar
      </a>
    @endif
  </form>
</div>

  @php

    if (!empty($chk['aceite_caja']) && empty($chk['filtro_a_acondicionado'])) {
      $chk['filtro_a_acondicionado'] = true;
  }
  // Columnas del checklist: [abreviatura, título largo]
  $CL = [
    'filtro_aceite'      => ['F Aceite',  'Filtro de aceite'],
    'filtro_aire'        => ['F Aire',   'Filtro de aire'],
    'filtro_a_acondicionado'  => ['F A/C',  'Filtro de aire acondicionado'],
    'filtro_caja'        => ['F Caja',  'Filtro de caja'],
    'aceite_diferencial' => ['A Difer', 'Aceite de diferencial'],
    'filtro_combustible' => ['F Comb', 'Filtro de combustible'],
    'aceite_hidraulico'  => ['A Hidr', 'Aceite hidráulico'],
    'transfer'           => ['Transf',  'Transfer'],
    'engrase'            => ['Grasa',  'Engrase'],
  ];

  // Heurísticas para vincular un check con un insumo (para el tooltip)
  // Puedes ajustar/afinar palabras clave cuando quieras.
  $MATCH = [
    'filtro_aceite'      => '/filtro.*aceite|aceite.*filtro/i',
    'filtro_aire'        => '/filtro.*aire|aire.*filtro/i',
    'filtro_a_acondicionado' => '/filtro.*acondicionado|acondicionado.*filtro/i',
    'filtro_caja'        => '/filtro.*caja/i',
    'aceite_diferencial' => '/aceite.*difer/i',
    'filtro_combustible' => '/filtro.*combust/i',
    'aceite_hidraulico'  => '/aceite.*hidraul/i',
    'transfer'           => '/transfer/i',
    'engrase'            => '/grasa|engrase|lubric/i',
  ];
@endphp


  <div class="table-responsive shadow-sm rounded-3">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Fecha</th>
          <th>Placa</th>
          <th>Tipo de Serv.</th>

          {{-- columnas del checklist --}}
          @foreach($CL as [$abbr,$title])
            <th class="text-center" title="{{ $title }}">{{ $abbr }}</th>
          @endforeach

          <th>Kms.</th>
          <th>Próx. Serv.</th>
          <th>Total</th>
          <th>Estado</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($ordenes as $ot)
        @php
          // Marca + línea
          $marca  = $ot->vehiculo->marca->nombre ?? '';
          $linea  = $ot->vehiculo->linea ?? '';
          $label1 = trim($marca.' '.$linea) ?: '—';

          // Teléfono del primer cliente asociado
          $telRaw = optional($ot->vehiculo->clientes)->first()->telefono ?? '';

          // Formatear 8 dígitos como 0000-0000
          $telDigits = preg_replace('/\D+/', '', (string)$telRaw);
          if (strlen($telDigits) === 8) {
              $telFmt = substr($telDigits,0,4).'-'.substr($telDigits,4);
          } else {
              $telFmt = $telRaw ?: '—';
          }

          // Tooltip con 2 líneas (HTML permitido)
          $tooltipHtml = "<div>{$label1}</div><div>Contacto: {$telFmt}</div>";
        @endphp
          @php
            // Decodifica el JSON del checklist
            $raw = $ot->mantenimiento_json ?? [];
            $chk = is_array($raw) ? $raw : (json_decode($raw ?? '[]', true) ?: []);

            // Totales
            $insumosTotal = $ot->insumos?->sum(fn($i) => (float)$i->precio * (float)($i->pivot->cantidad ?? 0)) ?? 0;
            $mo = (float)($ot->costo_mo ?? 0);
            $total = $insumosTotal + $mo;

            // Función para tooltip del check -> intenta encontrar un insumo "relacionado"
            $checkTip = function(string $key) use ($ot,$MATCH){
              $re = $MATCH[$key] ?? null;
              if (!$re || !$ot->insumos) return null;

              $prod = $ot->insumos->first(function($i) use ($re){
                return preg_match($re, \Illuminate\Support\Str::lower($i->nombre ?? ''));
              });

              if (!$prod) return null;

              $qty  = (float)($prod->pivot->cantidad ?? 0);
              $unit = (float)($prod->precio ?? 0);
              $line = $qty * $unit;
              return "{$prod->nombre} — {$qty} × Q" . number_format($unit,2) . " = Q" . number_format($line,2);
            };
          @endphp

        <tr>
          <td>{{ $ot->id }}</td>
          <td>
            @php
              $fc = $ot->fecha_creacion ? \Illuminate\Support\Carbon::parse($ot->fecha_creacion)->format('d/m/Y H:i') : '—';
            @endphp
            {{ $fc }}
          </td>
          <td>
            <span
              class="text-decoration-underline"
              data-bs-toggle="tooltip"
              data-bs-html="true"
              data-bs-custom-class="tt-placa"
              title="{!! $tooltipHtml !!}">
              {{ $ot->vehiculo->placa ?? '—' }}
            </span>
          </td>
          <td>{{ $ot->servicio->descripcion ?? '—' }}</td>

          {{-- columnas del checklist: ✅ si está marcado, – si no --}}
          @foreach($CL as $key => [$abbr,$title])
            @php $tip = $checkTip($key); @endphp
            <td class="text-center">
              @if(!empty($chk[$key]))
                <span class="text-success" data-bs-toggle="tooltip" title="{{ $tip ?? $title }}">
                  <i class="bi bi-check-circle-fill"></i>
                </span>
              @else
                <span class="text-muted" title="{{ $title }}"><i class="bi bi-dash-lg"></i></span>
              @endif
            </td>
          @endforeach

          <td>{{ $ot->kilometraje ?? '—' }}</td>
          <td>{{ $ot->proximo_servicio ?? '—' }}</td>

          {{-- Total con desglose en tooltip --}}
          <td>
            <span class="badge rounded-pill bg-dark"
                  data-bs-toggle="tooltip"
                  title="Insumos: Q{{ number_format($insumosTotal,2) }} • Mano de obra: Q{{ number_format($mo,2) }}">
              Q{{ number_format($total,2) }}
            </span>
          </td>

          <td>
            <span class="badge bg-{{ $ot->estado->badge_class ?? 'dark' }}">
              {{ $ot->estado->nombre ?? '—' }}
            </span>
          </td>

          <td class="text-center">
            <div class="d-inline-flex gap-2">

            <a href="{{ route('ordenes.edit', $ot->id) }}" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-pencil-square"></i> </a>

            @role('admin')
            <form action="{{ route('ordenes.destroy',$ot) }}"
                  method="POST"
                  class="d-inline js-del"
                  data-title="Eliminar orden"
                  data-text="Se eliminará la Orden de Trabajo #{{ $ot->id }} del vehiculo {{ $ot->vehiculo->placa ?? '—' }}.  Esta acción no se puede deshacer.">
              @csrf @method('DELETE')
              <button class="btn btn-danger btn-sm rounded-pill">
                <i class="bi bi-trash3"></i>
              </button>
            </form>
                @endrole
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-4">No hay órdenes registradas.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- paginación --}}
  <div class="mt-3">
    {{ $ordenes->links() }}
  </div>
</div>

@push('scripts')
  {{-- SweetAlert2 (CDN) --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener('click', (e) => {
      const form = e.target.closest('form.js-del');
      if (!form) return;

      e.preventDefault();

      const title = form.dataset.title || '¿Eliminar?';
      const text  = form.dataset.text  || 'Esta acción no se puede deshacer.';

      Swal.fire({
        title: title,
        html: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
          confirmButton: 'btn btn-danger',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  </script>
@endpush


@push('scripts')
<script>
  // tooltips
  window.addEventListener('DOMContentLoaded', () => {
    const tt = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tt.forEach(el => new bootstrap.Tooltip(el));
  });
</script>
@endpush

{{-- Contenedor para inyectar el modal --}}
<div id="qe_container" data-edit-url-template="{{ route('ordenes.edit', '__ID__') }}"></div>


@push('scripts')
<script>
(function() {
  // Tooltips
  window.addEventListener('DOMContentLoaded', () => {
    [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      .forEach(el => new bootstrap.Tooltip(el));
  });

  // Abrir modal al hacer clic en el lápiz
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.js-edit-ot'); // ES UN BUTTON
    if (!btn) return;

    const cont = document.getElementById('qe_container');
    const tpl  = cont.dataset.editUrlTemplate;      // /ordenes/__ID__/edit
    const url  = tpl.replace('__ID__', btn.dataset.id);

    const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
    if (!resp.ok) { alert('No se pudo cargar el editor.'); return; }

    cont.innerHTML = await resp.text();             // inyecta el parcial (con el modal incluido)
    const modalEl  = document.getElementById('otQuickModal');
    const modal    = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Inicializa la lógica del modal (viene definida en el parcial)
    if (window.initQuickEditOT) window.initQuickEditOT(modalEl);

    // Limpia al cerrar para evitar IDs duplicados
    modalEl.addEventListener('hidden.bs.modal', () => { cont.innerHTML = ''; }, { once:true });
    modal.show();
  });
})();
</script>
@endpush



@endsection

