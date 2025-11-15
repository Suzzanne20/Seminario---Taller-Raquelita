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
      <td>
        <span
          data-bs-toggle="tooltip"
          data-bs-placement="top"
          title="{{ $ot->descripcion ?: 'Sin descripción registrada' }}">
          {{ $ot->servicio->descripcion ?? '—' }}
        </span>
      </td>

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

        @php
          $filters = request()->only('q','estado','page');
          $qs = http_build_query($filters);
        @endphp

        <a href="{{ route('ordenes.edit', $ot->id) }}@if($qs)?{{ $qs }}@endif"
          class="btn btn-sm btn-outline-primary">
          <i class="bi bi-pencil-square"></i>
        </a>

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
      <td colspan="8" class="text-center py-4">
        @if(request('servicio') || request('estado') || request('q'))
          No se encontraron órdenes con los filtros aplicados.
          <div class="mt-2">
            <a href="{{ route('ordenes.index') }}" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-arrow-clockwise me-1"></i> Ver todas las órdenes
            </a>
          </div>
        @else
          No hay órdenes registradas.
        @endif
      </td>
    </tr>
  @endforelse
  </tbody>
</table>