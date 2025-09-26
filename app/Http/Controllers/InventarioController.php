<?php

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index()
    {
        // Ingresos: sumatoria de órdenes de trabajo
        $ingresos = OrdenTrabajo::sum('total');

        // Como no tienes tabla orden_compra, dejamos egresos = 0
        $egresos = 0;

        // Ganancia
        $ganancia = $ingresos - $egresos;

        // Total de órdenes de trabajo (cerradas o todas según definas)
        $totalOT = OrdenTrabajo::count();

        // Registros para la tabla
        $registros = OrdenTrabajo::select('descripcion', 'total')
            ->get()
            ->map(function ($ot) {
                return [
                    'descripcion' => $ot->descripcion,
                    'ingresos'    => $ot->total,
                    'egresos'     => 0,
                    'ganancia'    => $ot->total,
                ];
            });

        return view('inventario.index', compact('ingresos', 'egresos', 'ganancia', 'registros', 'totalOT'));
    }
}
