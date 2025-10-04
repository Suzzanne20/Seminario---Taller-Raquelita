<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\OrdenTrabajo;
use App\Models\Insumo;
use App\Models\Cotizacion;
use App\Models\Estado;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ── Totales generales
        $totalOrdenes      = OrdenTrabajo::count();
        $totalCotizaciones = Cotizacion::count();
        $ingresosMes = OrdenTrabajo::whereBetween('fecha_creacion', [
                            Carbon::now()->startOfMonth(),
                            Carbon::now()->endOfMonth()
                        ])->sum('total');

        // ── Órdenes por estado (para tarjetas y gráfico)
        $estados = Estado::orderBy('nombre')->get(['id','nombre']);
        $ordenesPorEstado = OrdenTrabajo::select('estado_id', DB::raw('COUNT(*) as total'))
                                ->groupBy('estado_id')->pluck('total','estado_id');

        $labelsEstados = [];
        $dataEstados   = [];
        foreach ($estados as $e) {
            $labelsEstados[] = $e->nombre;
            $dataEstados[]   = (int) ($ordenesPorEstado[$e->id] ?? 0);
        }

        // ── Top 5 insumos más usados (por cantidad)
        $topInsumos = DB::table('insumo_ot')
            ->join('insumo', 'insumo.id', '=', 'insumo_ot.insumo_id')
            ->select('insumo.nombre', DB::raw('SUM(insumo_ot.cantidad) as qty'))
            ->groupBy('insumo.id','insumo.nombre')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $labelsTopInsumos = $topInsumos->pluck('nombre');
        $dataTopInsumos   = $topInsumos->pluck('qty');

        // ── Órdenes últimos 7 días (línea)
        $rangos = collect(range(6,0))->map(fn($i)=>Carbon::today()->subDays($i));
        $ordenes7 = OrdenTrabajo::select(DB::raw('DATE(fecha_creacion) as d'), DB::raw('COUNT(*) as c'))
                   ->whereDate('fecha_creacion','>=', Carbon::today()->subDays(6))
                   ->groupBy('d')->pluck('c','d');

        $labels7 = $rangos->map->format('d/m');
        $data7   = $rangos->map(fn($d)=> (int) ($ordenes7[$d->toDateString()] ?? 0));

        // ── Listados rápidos
        $lowStock = Insumo::whereColumn('stock','<=','stock_minimo')
                    ->orderByRaw('(stock_minimo - stock) DESC')->limit(5)
                    ->get(['id','nombre','stock','stock_minimo']);

        $ultimasOT = OrdenTrabajo::with(['vehiculo','servicio','estado'])
                    ->latest('id')->limit(6)->get();

        // ── Cotizaciones por estado (badges)
        $cotiPorEstado = Cotizacion::select('estado_id', DB::raw('COUNT(*) as total'))
                          ->groupBy('estado_id')->pluck('total','estado_id');
        $cotiEstadosLabels = $estados->pluck('nombre');
        $cotiEstadosData   = $estados->map(fn($e)=> (int)($cotiPorEstado[$e->id] ?? 0));

        return view('dashboard', compact(
            'totalOrdenes','totalCotizaciones','ingresosMes',
            'labelsEstados','dataEstados',
            'labelsTopInsumos','dataTopInsumos',
            'labels7','data7',
            'lowStock','ultimasOT',
            'cotiEstadosLabels','cotiEstadosData'
        ));
    }
}
