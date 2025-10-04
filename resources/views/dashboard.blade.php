@extends('layouts.app')

@push('styles')
<style>
  html, body { height:100%; background:#f0f0f0 !important; }
  .page-body { min-height:calc(100vh - 72px); background:#f0f0f0 !important; color:#212529; }
  @media (max-width:576px){ .page-body{ min-height:calc(100vh - 64px);} }

  .dash-title{ color:#C24242; font-weight:800; text-align:center; }
  .card-soft{
    background:#fff; border:0; border-radius:14px; box-shadow:0 8px 26px rgba(0,0,0,.08);
  }
  .metric{
    display:flex; align-items:center; gap:14px; padding:18px;
    border-radius:14px; background:linear-gradient(180deg,#fff, #fafafa);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.6), 0 1px 0 rgba(0,0,0,.03);
  }
  .metric .icon{
    width:44px; height:44px; display:grid; place-content:center;
    border-radius:12px; background:#9F3B3B; color:#fff; font-size:1.2rem;
  }
  .metric .label{ font-size:.9rem; color:#6b7280; margin-bottom:2px; }
  .metric .value{ font-weight:800; font-size:1.35rem; color:#111827; }

  .table > :not(caption) > * > *{ background-color:transparent !important; }
  .badge { font-weight:600; }
</style>
@endpush

@section('content')
<div class="container py-4">

  <div class="container"><br><br>
    <h1 class="dash-title mb-4">Panel general</h1>
  </div>

  {{-- Métricas principales --}}
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="metric">
        <div class="icon"><i class="bi bi-clipboard-check"></i></div>
        <div>
          <div class="label">Órdenes registradas</div>
          <div class="value">{{ number_format($totalOrdenes) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="metric">
        <div class="icon"><i class="bi bi-cash-stack"></i></div>
        <div>
          <div class="label">Ingresos del mes</div>
          <div class="value">Q {{ number_format($ingresosMes,2) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="metric">
        <div class="icon"><i class="bi bi-file-earmark-text"></i></div>
        <div>
          <div class="label">Cotizaciones</div>
          <div class="value">{{ number_format($totalCotizaciones) }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Gráficas --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-4">
      <div class="card-soft p-3 h-100">
        <h6 class="mb-2 fw-bold">Órdenes por estado</h6>
        <canvas id="chartEstados" height="210"></canvas>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card-soft p-3 h-100">
        <h6 class="mb-2 fw-bold">Top insumos usados</h6>
        <canvas id="chartTopInsumos" height="210"></canvas>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card-soft p-3 h-100">
        <h6 class="mb-2 fw-bold">Órdenes últimos 7 días</h6>
        <canvas id="chart7" height="210"></canvas>
      </div>
    </div>
  </div>

  {{-- Listas rápidas --}}
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card-soft p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-bold">Insumos con bajo stock</h6>
          <a href="{{ route('insumos.index') }}" class="small text-decoration-none">Ver inventario</a>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Insumo</th>
                <th class="text-center">Stock</th>
                <th class="text-center">Mínimo</th>
              </tr>
            </thead>
            <tbody>
              @forelse($lowStock as $i)
                <tr>
                  <td class="text-truncate">{{ $i->nombre }}</td>
                  <td class="text-center fw-semibold">{{ $i->stock }}</td>
                  <td class="text-center">{{ $i->stock_minimo }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-3">Todo en orden 👌</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card-soft p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-bold">Últimas órdenes</h6>
          <a href="{{ route('ordenes.index') }}" class="small text-decoration-none">Ver todas</a>
        </div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Placa</th>
                <th>Servicio</th>
                <th>Estado</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              @forelse($ultimasOT as $ot)
                <tr>
                  <td>{{ $ot->id }}</td>
                  <td>{{ $ot->vehiculo->placa ?? '—' }}</td>
                  <td>{{ $ot->servicio->descripcion ?? '—' }}</td>
                  <td>
                    <span class="badge bg-{{ $ot->estado->badge_class ?? 'secondary' }}">
                      {{ $ot->estado->nombre ?? '—' }}
                    </span>
                  </td>
                  <td class="text-end">Q {{ number_format($ot->total ?? 0, 2) }}</td>
                </tr>
              @empty
                <tr><td colspan="5" class="text-center text-muted py-3">Aún no hay registros</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Cotizaciones por estado (mini gráfico) --}}
  <div class="row g-3 mt-3">
    <div class="col-12">
      <div class="card-soft p-3">
        <h6 class="mb-2 fw-bold">Cotizaciones por estado</h6>
        <canvas id="chartCoti" height="110"></canvas>
      </div>
    </div>
  </div>
</div>

{{-- Íconos & ChartJS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
(function(){
  // Paleta coherente con tu tema
  const red    = '#9F3B3B';
  const red2   = '#C24242';
  const gray   = '#535353';
  const light  = '#e5e7eb';
  const info   = '#0dcaf0', warn = '#ffc107', ok = '#198754', pri = '#0d6efd', dang = '#dc3545';

  // Datos desde PHP
  const labelsEstados = @json($labelsEstados);
  const dataEstados   = @json($dataEstados);

  const labelsTop     = @json($labelsTopInsumos);
  const dataTop       = @json($dataTopInsumos);

  const labels7       = @json($labels7);
  const data7         = @json($data7);

  const cotiLabels    = @json($cotiEstadosLabels);
  const cotiData      = @json($cotiEstadosData);

  // Pie: Estados
  new Chart(document.getElementById('chartEstados'), {
    type:'doughnut',
    data:{
      labels: labelsEstados,
      datasets:[{
        data: dataEstados,
        backgroundColor: [gray, info, warn, pri, ok, dang].slice(0, labelsEstados.length),
        borderColor: '#fff', borderWidth: 2
      }]
    },
    options:{ plugins:{ legend:{ position:'bottom' }}, cutout:'60%' }
  });

  // Barras: Top insumos
  new Chart(document.getElementById('chartTopInsumos'), {
    type:'bar',
    data:{
      labels: labelsTop,
      datasets:[{
        label: 'Cantidad utilizada',
        data: dataTop,
        backgroundColor: red,
        borderRadius: 8
      }]
    },
    options:{
      scales:{ y:{ beginAtZero:true, grid:{ color: light } }, x:{ grid:{ display:false } } },
      plugins:{ legend:{ display:false } }
    }
  });

  // Línea: últimos 7 días
  new Chart(document.getElementById('chart7'), {
    type:'line',
    data:{
      labels: labels7,
      datasets:[{
        label:'Órdenes',
        data: data7,
        borderColor: red2,
        backgroundColor: red2,
        tension:.35, fill:false, pointRadius:4, pointBackgroundColor:red2
      }]
    },
    options:{
      scales:{ y:{ beginAtZero:true, grid:{ color: light } }, x:{ grid:{ display:false } } },
      plugins:{ legend:{ display:false } }
    }
  });

  // Barras apiladas pequeñas: cotizaciones por estado
  new Chart(document.getElementById('chartCoti'), {
    type:'bar',
    data:{
      labels: cotiLabels,
      datasets:[{ label:'Cotizaciones', data: cotiData, backgroundColor: pri, borderRadius:8 }]
    },
    options:{ plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
  });

})();
</script>
@endsection
