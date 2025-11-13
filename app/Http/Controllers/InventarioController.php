<?php

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use App\Models\OrdenCompra2;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InventarioController extends Controller
{
    public function index(Request $request)
    {
        $mes = $request->input('mes');
        $fechaInicio = null;
        $fechaFin = null;

        if ($mes) {
            try {
                $fechaInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth()->startOfDay();
                $fechaFin = Carbon::createFromFormat('Y-m', $mes)->endOfMonth()->endOfDay();
            } catch (\Exception $e) {
                $fechaInicio = null;
                $fechaFin = null;
            }
        }

        $query = OrdenTrabajo::with([
            'insumosOT.insumo',
            'servicio'
        ]);

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin]);
        }

        $ordenes = $query->get();

        $conteoServicios = $ordenes->groupBy(function ($ot) {
            return $ot->servicio->descripcion ?? 'Sin Tipo';
        })
            ->map->count()
            ->sortDesc();


        $calendario = [];
        $diasDeSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

        if ($fechaInicio) {

            $conteoPorDiaDelMes = $ordenes->groupBy(function($ot) {
                return Carbon::parse($ot->fecha_creacion)->format('j');
            })->map->count();

            $diaInicioSemana = $fechaInicio->copy()->isoWeekday();
            $diasEnMes = $fechaInicio->daysInMonth;

            for ($i = 1; $i < $diaInicioSemana; $i++) {
                $calendario[] = ['dia' => null, 'total' => 0];
            }

            for ($dia = 1; $dia <= $diasEnMes; $dia++) {
                $total = $conteoPorDiaDelMes->get($dia, 0);
                $calendario[] = [
                    'dia' => $dia,
                    'total' => $total
                ];
            }
        }

        $ingresos = $ordenes->sum('total');

        $egresos = $ordenes->reduce(function ($carry, $ot) {
            $costo_ot = $ot->insumosOT->sum(function ($uso) {
                return $uso->cantidad * ($uso->insumo->costo ?? 0);
            });
            return $carry + $costo_ot;
        }, 0);

        $ganancia = $ingresos - $egresos;

        $totalOT = $ordenes->count();

        $registros = $ordenes->map(function ($ot) {
            $egresos = $ot->insumosOT->sum(function ($uso) {
                return $uso->cantidad * ($uso->insumo->costo ?? 0);
            });

            return [
                'descripcion' => $ot->descripcion ?? ($ot->servicio->descripcion ?? 'OT sin descripción'),
                'ingresos' => $ot->total,
                'egresos' => $egresos,
                'ganancia' => $ot->total - $egresos,
            ];
        });

        return view('inventario.index', compact(
            'ingresos',
            'egresos',
            'ganancia',
            'totalOT',
            'registros',
            'mes',
            'conteoServicios',
            'calendario',
            'diasDeSemana'
        ));
    }
}
