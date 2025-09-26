<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Insumo;
use App\Models\TypeService;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    // Listar todas las cotizaciones
    public function index()
    {
        $cotizaciones = Cotizacion::with(['servicio', 'estado'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('cotizaciones.index', compact('cotizaciones'));
    }

    // Formulario para crear cotización
    public function create()
    {
        $servicios = TypeService::orderBy('descripcion')->get();
        $insumos = Insumo::orderBy('nombre')->get();

        return view('cotizaciones.create', compact('servicios', 'insumos'));
    }

    // Guardar nueva cotización
    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
            'costo_mo' => 'nullable|numeric',
            'type_service_id' => 'required|exists:type_service,id',
            'insumos' => 'array',
            'insumos.*.id' => 'exists:insumo,id',
            'insumos.*.cantidad' => 'nullable|integer|min:0',
        ]);

        // 1. Crear la cotización con estado_id = 1 (pendiente)
        $cot = Cotizacion::create([
            'fecha_creacion'  => now(),
            'descripcion'     => $data['descripcion'],
            'costo_mo'        => $data['costo_mo'] ?? 0,
            'total'           => 0,
            'type_service_id' => $data['type_service_id'],
            'estado_id'       => 1 // 1 = pendiente
        ]);

        // 2. Preparar insumos válidos
        if (!empty($data['insumos'])) {
            $syncData = [];
            foreach ($data['insumos'] as $item) {
                if (!empty($item['id']) && isset($item['cantidad']) && $item['cantidad'] > 0) {
                    $syncData[$item['id']] = ['cantidad' => $item['cantidad']];
                }
            }
            $cot->insumos()->sync($syncData);
        }

        // 3. Recalcular total
        $cot->load('insumos');
        $cot->recalcularTotal();
        $cot->save();

        return redirect()
            ->route('cotizaciones.index')
            ->with('ok', 'Cotización creada correctamente.');
    }

    // Ver detalle de una cotización
    public function show(Cotizacion $cotizacione)
    {
        $cotizacione->load(['servicio', 'insumos', 'estado']);
        return view('cotizaciones.show', ['cotizacion' => $cotizacione]);
    }

    // Editar cotización
    public function edit(Cotizacion $cotizacione)
    {
        $servicios = TypeService::orderBy('descripcion')->get();
        $insumos = Insumo::orderBy('nombre')->get();

        return view('cotizaciones.edit', compact('cotizacione', 'servicios', 'insumos'));
    }

    // Actualizar cotización
    public function update(Request $request, Cotizacion $cotizacione)
    {
        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
            'costo_mo' => 'nullable|numeric',
            'type_service_id' => 'required|exists:type_service,id',
            'insumos' => 'array',
            'insumos.*.id' => 'exists:insumo,id',
            'insumos.*.cantidad' => 'integer|min:0',
        ]);

        $cotizacione->update([
            'descripcion'     => $data['descripcion'],
            'costo_mo'        => $data['costo_mo'] ?? 0,
            'type_service_id' => $data['type_service_id'],
        ]);

        if (!empty($data['insumos'])) {
            $syncData = [];
            foreach ($data['insumos'] as $item) {
                $syncData[$item['id']] = ['cantidad' => $item['cantidad']];
            }
            $cotizacione->insumos()->sync($syncData);
        }

        $cotizacione->recalcularTotal();
        $cotizacione->save();

        return redirect()
            ->route('cotizaciones.index')
            ->with('ok', 'Cotización actualizada correctamente.');
    }

    // Eliminar cotización
    public function destroy(Cotizacion $cotizacione)
    {
        $cotizacione->delete();
        return redirect()
            ->route('cotizaciones.index')
            ->with('ok', 'Cotización eliminada correctamente.');
    }

    // Aprobar cotización y generar orden de trabajo
    public function aprobar(Cotizacion $cotizacione)
    {
        $cotizacione->estado_id = 2; // 2 = aprobada
        $cotizacione->save();

        // 🔽 Descontar stock de cada insumo
        foreach ($cotizacione->insumos as $insumo) {
            $cantidad = $insumo->pivot->cantidad;

            if ($insumo->stock >= $cantidad) {
                $insumo->decrement('stock', $cantidad);
            } else {
                return redirect()
                    ->route('cotizaciones.index')
                    ->with('error', "No hay suficiente stock para {$insumo->nombre}");
            }
        }

        return redirect()
            ->route('cotizaciones.index')
            ->with('ok', 'Cotización aprobada, insumos descontados y orden de trabajo generada.');
    }
}
