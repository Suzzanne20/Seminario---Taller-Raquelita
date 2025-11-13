@extends('layouts.app')

@section('title', 'Inventario - Costos y Ganancias')

@push('styles')
    <style>
        body {
            background-color: rgba(122, 211, 126, 0.05);
            color: #1e1e1e;
            font-family: sans-serif;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1.5rem 3rem;
        }

        .title {
            text-align: center;
            margin-bottom: 1.5rem;
            margin-top: 1rem;
        }

        .title h1 {
            font-size: 2.3rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #c24242;
        }

        .title p {
            font-size: 0.95rem;
            color: #1d4ed8;
        }

        .filter-card {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 1rem;
            padding: 1.25rem 1.75rem;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            border: 1px solid #e5e7eb;
            max-width: 480px;
            margin: 0 auto 2rem auto;
        }

        .filter-card label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.35rem;
        }

        .filter-card .month-input {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
            outline: none;
            min-width: 220px;

            position: relative;
            color: #111827;
        }

        .filter-card .month-input:focus {
            border-color: #c24242;
            box-shadow: 0 0 0 2px rgba(194, 66, 66, 0.25);
        }

        .filter-card .month-input:invalid {
            color: transparent;
        }

        .filter-card .month-input::before {
            content: 'Selecciona mes';
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            pointer-events: none;
        }

        .filter-card .month-input:valid::before {
            display: none;
        }

        .filter-card button {
            border-radius: 999px;
            padding: 0.55rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            background: linear-gradient(135deg, #c24242, #9f3b3b);
            color: #fff;
            border: none;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.1s ease, opacity 0.1s ease;
        }

        .filter-card button:hover {
            opacity: 0.95;
            box-shadow: 0 10px 18px rgba(156, 34, 34, 0.35);
            transform: translateY(-1px);
        }

        .filter-card button:active {
            transform: translateY(0);
            box-shadow: none;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 1rem;
        }

        .kpi {
            background: white;
            padding: 1.1rem 1.2rem;
            border-radius: 0.9rem;
            text-align: left;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.04);
            border-top: 4px solid #c24242;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .kpi .label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
        }

        .kpi .value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
        }

        .table-wrapper {
            background: white;
            padding: 1.5rem 1.5rem 1.75rem;
            border-radius: 0.9rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            margin-top: 2.2rem;
        }

        .table-wrapper h2 {
            color: #1e40af;
            font-weight: 700;
            margin-bottom: 0.75rem;
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
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .charts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .chart-box {
            background: white;
            padding: 1.5rem 1.5rem 1.75rem;
            border-radius: 0.9rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.06);
            min-height: 300px;
            display: flex;
            flex-direction: column;
        }

        .chart-box h2 {
            color: #1d4ed8;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .calendar-header-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .calendar-header-grid > div {
            text-align: center;
            font-weight: 700;
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
        }

        .calendar-days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.25rem;
        }

        .calendar-day {
            padding: 0.3rem;
            background-color: #f3f4f6;
            border-radius: 0.5rem;
            color: #374151;
            min-height: 65px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .calendar-day-empty {
            background-color: transparent;
            box-shadow: none;
        }

        .calendar-day .day-number {
            font-weight: 600;
            font-size: 0.75rem;
            color: #111827;
        }

        .calendar-day .day-count {
            font-weight: 700;
            font-size: 1.25rem;
            line-height: 1;
            text-align: center;
            margin-top: 0.25rem;
            align-self: center;
        }

        .calendar-day .day-count-zero {
            color: #9ca3af;
        }

        .calendar-day .day-count-gt-zero {
            color: #1d4ed8;
        }

        /* --- 1. AQUÍ ESTÁ EL CSS PARA EL SCROLL --- */
        .table-scroll-container {
            max-height: 450px; /* Puedes cambiar 450px al alto que quieras (ej. 300px) */
            overflow-y: auto;
        }

        footer {
            text-align: center;
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 2.5rem;
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

            <div class="filter-card">

                <div class="flex flex-col w-full sm:w-auto">
                    <label for="mes">Visualizar ganancias del mes de: </label>
                    <input
                        type="month"
                        id="mes"
                        name="mes"
                        value="{{ old('mes', $mes) }}"
                        class="month-input"
                        required
                    >
                </div>

                <div class="w-full sm:w-auto flex sm:justify-end mt-3 sm:mt-6">
                    <button type="submit">
                        Actualizar
                    </button>
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

            <div class="table-scroll-container">
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
            </div> </div>

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
                <h2 class="text-lg font-bold mb-4">Actividad mensual (OT cerradas / día)</h2>

                @if(isset($calendario) && isset($diasDeSemana) && !empty($calendario))

                    <div class="calendar-header-grid">
                        @foreach($diasDeSemana as $dia)
                            <div>{{ $dia }}</div>
                        @endforeach
                    </div>

                    <div class="calendar-days-grid">
                        @foreach($calendario as $dia)
                            @if($dia['dia'] !== null)
                                <div class="calendar-day">
                                    <span class="day-number">{{ $dia['dia'] }}</span>
                                    <span class="day-count {{ $dia['total'] > 0 ? 'day-count-gt-zero' : 'day-count-zero' }}">
                                        {{ $dia['total'] }}
                                    </span>
                                </div>
                            @else
                                <div class="calendar-day-empty"></div>
                            @endif
                        @endforeach
                    </div>

                @else
                    <div class="text-center text-gray-500 py-8 flex-grow flex items-center justify-center">
                        No hay datos de actividad para este mes.
                    </div>
                @endif
            </div>
        </div>

        <footer>
            Datos calculados automáticamente desde la base de datos.
        </footer>
    </div>
@endsection

@push('scripts')
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
