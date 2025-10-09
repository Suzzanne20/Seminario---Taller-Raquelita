@extends('layouts.app')

@section('title', 'Inventario - Costos y Ganancias')

@push('styles')
    <style>
        body {
            background-color: #f8fafc;
            color: #1e1e1e;
            font-family: sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .title {
            text-align: center;
            margin-bottom: 2.5rem;
            margin-top: 2rem;
        }

        .title h1 {
            font-size: 2rem;
            font-weight: bold;
            color: #f30606;
        }

        .title p {
            font-size: 0.9rem;
            color: #1d4ed8;
        }

        .filters {
            background: #e60000;
            padding: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(4, 227, 194, 0.64);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }

        .kpi {
            background: white;
            padding: 1rem;
            border-radius: 0.75rem;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .kpi .label {
            font-size: 0.85rem;
            color: #1d4ed8;
        }

        .kpi .value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 0.5rem;
        }

        .table-wrapper {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }

        .table-wrapper h2 {
            color: #1e40af;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        table th, table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        table th {
            background-color: #f1f5f9;
            color: #1d4ed8;
        }

        .charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .chart-box {
            background: white;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .chart-box h2 {
            color: #1d4ed8;
        }

        .chart-box .grid > div {
            color: #1e1e1e !important;
        }

        footer {
            text-align: center;
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 3rem;
        }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="title">
            <h1>Panel de Costos y Ganancias</h1>
            <p>Centro de mantenimiento de vehículos</p>
        </div>

        <form class="filters">
            <div>
                <label>Desde</label>
                <input type="date" class="w-full border border-gray-300 rounded px-2 py-1">
            </div>
            <div>
                <label>Hasta</label>
                <input type="date" class="w-full border border-gray-300 rounded px-2 py-1">
            </div>
            <div>
                <label>Técnico</label>
                <select class="w-full border border-gray-300 rounded px-2 py-1">
                    <option>Todos</option>
                    <option>Juan Pérez</option>
                    <option>Ana López</option>
                </select>
            </div>
            <div>
                <label>Estado OT</label>
                <select class="w-full border border-gray-300 rounded px-2 py-1">
                    <option>Todos</option>
                    <option>Abierta</option>
                    <option>En proceso</option>
                    <option>Cerrada</option>
                </select>
            </div>
        </form>

        <div class="kpi-grid">
            <div class="kpi">
                <div class="label">Ingresos</div>
                <div class="value">Q {{ number_format($ingresos, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Costos</div>
                <div class="value">Q {{ number_format($egresos, 2) }}</div>
            </div>
            <div class="kpi">
                <div class="label">Ganancia</div>
                <div class="value" style="color: {{ $ganancia >= 0 ? '#16a34a' : '#dc2626' }};">
                    Q {{ number_format($ganancia, 2) }}
                </div>
            </div>
            <div class="kpi">
                <div class="label">OT Cerradas</div>
                <div class="value">{{ $totalOT }}</div>
            </div>
        </div>

        <div class="table-wrapper">
            <h2 class="text-xl font-semibold mb-4">Entradas y Salidas</h2>
            <table>
                <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Ingresos (OT)</th>
                    <th>Egresos (OC)</th>
                    <th>Ganancia</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($registros as $r)
                    <tr>
                        <td>{{ $r['descripcion'] }}</td>
                        <td>Q {{ number_format($r['ingresos'], 2) }}</td>
                        <td>Q {{ number_format($r['egresos'], 2) }}</td>
                        <td style="color: {{ $r['ganancia'] >= 0 ? '#16a34a' : '#dc2626' }};">
                            Q {{ number_format($r['ganancia'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-3">
                            No hay registros de órdenes de trabajo.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="charts">
            <div class="chart-box">
                <h2 class="text-lg font-bold mb-4">Margen por Servicio / Técnico</h2>
                <canvas id="barMargen"></canvas>
            </div>

            <div class="chart-box">
                <h2 class="text-lg font-bold mb-4">Actividad semanal (OT cerradas / día)</h2>
                <div class="grid grid-cols-7 text-center text-sm">
                    @foreach (['Lun'=>8,'Mar'=>10,'Mié'=>12,'Jue'=>9,'Vie'=>15,'Sáb'=>6,'Dom'=>'—'] as $dia => $val)
                        <div class="p-3 bg-gray-100 rounded-lg" style="color: #1e1e1e">
                            {{ $dia }}<br><span class="font-semibold">{{ $val }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <footer>
            Datos calculados automáticamente desde la base de datos.
        </footer>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('barMargen'), {
            type: 'bar',
            data: {
                labels: ['Afinación','Frenos','Suspensión','Diagnóstico','Aceite'],
                datasets: [{
                    label: 'Margen %',
                    data: [42, 35, 38, 60, 48],
                    backgroundColor: '#3b82f6'
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: v => v + '%'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
@endpush
