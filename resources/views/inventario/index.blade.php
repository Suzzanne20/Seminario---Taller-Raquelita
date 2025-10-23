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

            min-height: 300px;
            display: flex;
            flex-direction: column;
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


        <form method="GET" action="{{ route('inventario.index') }}">
            <div class="bg-gray-50 p-6 rounded-lg shadow-md mb-6 flex flex-wrap gap-4 items-end">
                <div class="flex flex-col">
                    <label for="mes" class="text-gray-700 font-medium mb-1">Mes</label>
                    <input type="month" id="mes" name="mes" value="{{ old('mes', $mes) }}"
                           class="border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <button type="submit" class="bg-green-600 text-blue-100 px-4 py-2 rounded-md hover:bg-green-700 transition-colors duration-200 ease-in-out">
                        Actualizar
                    </button>
                </div>

                <div class="flex flex-col">
                    <label class="text-gray-700 font-medium mb-1">Técnico</label>
                    <select class="border border-gray-300 rounded-md p-2 bg-gray-100 cursor-not-allowed" disabled>
                        <option>Todos</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-gray-700 font-medium mb-1">Estado OT</label>
                    <select class="border border-gray-300 rounded-md p-2 bg-gray-100 cursor-not-allowed" disabled>
                        <option>Todos</option>
                    </select>
                </div>

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
                <h2 class="text-lg font-bold mb-4">Conteo por Tipo de Servicio</h2>

                @if($conteoServicios->isNotEmpty())
                    <canvas id="barMargen"></canvas>
                @else
                    <div class="text-center text-gray-500 py-8 flex-grow flex items-center justify-center">
                        No hay datos de servicios para mostrar.
                    </div>
                @endif

            </div>

            <div class="chart-box">
                <h2 class="text-lg font-bold mb-4">Actividad semanal (OT cerradas / día)</h2>


                <div class="grid grid-cols-7 text-center text-sm gap-2">
                    @foreach ($actividadSemanal as $dia => $val)
                        <div class="p-3 bg-gray-100 rounded-lg" style="color: #1e1e1e">
                            {{ $dia }}<br>
                            <span class="font-semibold" style="color: {{ $val > 0 ? '#1d4ed8' : '#1e1e1e' }}">
                            {{ $val }}
                        </span>
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
    {{-- Corregido el CDN de Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @if($conteoServicios->isNotEmpty())
        <script>
            new Chart(document.getElementById('barMargen'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($conteoServicios->keys()) !!},
                    datasets: [{
                        label: 'Cantidad de OTs',
                        data: {!! json_encode($conteoServicios->values()) !!},
                        backgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
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
    @endif


@endpush
