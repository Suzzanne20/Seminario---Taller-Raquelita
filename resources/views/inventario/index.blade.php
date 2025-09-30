@extends('layouts.app')

@push('styles')
<style>
  html, body { height: 100%; background: #f0f0f0 !important; }
  .page-body { min-height: calc(100vh - 72px); background: #f0f0f0 !important; color: #212529; }
  @media (max-width: 576px) {
    .page-body { min-height: calc(100vh - 64px); }
  }
</style>
@endpush

@section('title', 'Inventario - Costos y Ganancias')

@section('content')
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <!-- Encabezado y filtros -->
        <header class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Panel de Costos y Ganancias</h1>
                <p class="text-sm text-gray-500">Centro de mantenimiento de vehículos</p>
            </div>
            <form class="grid grid-cols-2 md:grid-cols-6 gap-3 bg-white p-4 rounded-xl shadow">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600">Rango de fechas</label>
                    <input type="date" class="mt-1 w-full border rounded-lg px-3 py-2" />
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600">a</label>
                    <input type="date" class="mt-1 w-full border rounded-lg px-3 py-2" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Técnico</label>
                    <select class="mt-1 w-full border rounded-lg px-3 py-2">
                        <option>Todos</option>
                        <option>Juan Pérez</option>
                        <option>Ana López</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600">Estado OT</label>
                    <select class="mt-1 w-full border rounded-lg px-3 py-2">
                        <option>Todos</option>
                        <option>Abierta</option>
                        <option>En proceso</option>
                        <option>Cerrada</option>
                    </select>
                </div>
            </form>
        </header>

        <!-- KPIs -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs text-gray-500">Ingresos</p>
                <p class="text-2xl font-bold">Q {{ number_format($ingresos, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs text-gray-500">Costos</p>
                <p class="text-2xl font-bold">Q {{ number_format($egresos, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs text-gray-500">Ganancia</p>
                <p class="text-2xl font-bold {{ $ganancia >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    Q {{ number_format($ganancia, 2) }}
                </p>
            </div>
            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-xs text-gray-500">OT Cerradas</p>
                <p class="text-2xl font-bold">{{ $totalOT }}</p>
            </div>
        </section>

        <!-- Tabla de Entradas y Salidas -->
        <section class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Entradas y Salidas</h2>
            <div class="overflow-auto">
                <table class="min-w-full text-sm border rounded-lg">
                    <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="px-3 py-2 rounded-l-lg">Descripción</th>
                        <th class="px-3 py-2">Ingresos (OT)</th>
                        <th class="px-3 py-2">Egresos (OC)</th>
                        <th class="px-3 py-2 rounded-r-lg">Ganancia</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse ($registros as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $r['descripcion'] }}</td>
                            <td class="px-3 py-2">Q {{ number_format($r['ingresos'], 2) }}</td>
                            <td class="px-3 py-2">Q {{ number_format($r['egresos'], 2) }}</td>
                            <td class="px-3 py-2 font-semibold text-green-700">
                                Q {{ number_format($r['ganancia'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                No hay registros de órdenes de trabajo.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Visual Margen por Servicio / Técnico -->
        <section class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Margen por Servicio / Técnico</h2>
            <canvas id="barMargen"></canvas>
        </section>

        <!-- Visual Actividad semanal -->
        <section class="bg-white rounded-xl shadow p-6">
            <h2 class="font-semibold mb-4">Actividad semanal (OT cerradas / día)</h2>
            <div class="grid grid-cols-7 gap-2 text-center text-sm">
                <div class="p-3 rounded-lg bg-green-50">Lun<br><span class="font-semibold">8</span></div>
                <div class="p-3 rounded-lg bg-green-100">Mar<br><span class="font-semibold">10</span></div>
                <div class="p-3 rounded-lg bg-green-200">Mié<br><span class="font-semibold">12</span></div>
                <div class="p-3 rounded-lg bg-green-100">Jue<br><span class="font-semibold">9</span></div>
                <div class="p-3 rounded-lg bg-green-300">Vie<br><span class="font-semibold">15</span></div>
                <div class="p-3 rounded-lg bg-green-50">Sáb<br><span class="font-semibold">6</span></div>
                <div class="p-3 rounded-lg bg-gray-100">Dom<br><span class="font-semibold">—</span></div>
            </div>
        </section>

        <footer class="py-4 text-xs text-gray-500 text-center">
            Datos calculados automáticamente desde la base de datos.
        </footer>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfica de ejemplo: Margen por servicio/técnico
        new Chart(document.getElementById('barMargen'), {
            type: 'bar',
            data: {
                labels: ['Afinación','Frenos','Suspensión','Diagnóstico','Aceite'],
                datasets: [{ label: 'Margen %', data: [42,35,38,60,48], backgroundColor: '#60a5fa' }]
            },
            options: {
                scales: { y: { max: 100, ticks: { callback: v => v+'%' } } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
@endpush
