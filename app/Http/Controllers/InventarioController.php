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
            // Accedemos a la descripción usando la relación 'servicio'
            return $ot->servicio->descripcion ?? 'Sin Tipo';
        })
            ->map->count()
            ->sortDesc();


        $conteoPorDia = $ordenes->groupBy(function($ot) {

            return Carbon::parse($ot->fecha)->format('N');
        })->map->count();


        $actividadSemanal = [
            'Lun' => $conteoPorDia->get(1, 0),
            'Mar' => $conteoPorDia->get(2, 0),
            'Mié' => $conteoPorDia->get(3, 0),
            'Jue' => $conteoPorDia->get(4, 0),
            'Vie' => $conteoPorDia->get(5, 0),
            'Sáb' => $conteoPorDia->get(6, 0),
            'Dom' => $conteoPorDia->get(7, 0)
        ];

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
            'actividadSemanal' // <-- NUEVO: Pasamos la nueva variable a la vista
        ));
    }
}
