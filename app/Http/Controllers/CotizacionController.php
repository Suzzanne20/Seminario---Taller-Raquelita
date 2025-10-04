<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Insumo;
use App\Models\TypeService;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    public function index(Request $request)
{
    $q = trim($request->get('q', ''));

    $cotizaciones = Cotizacion::with(['servicio', 'estado'])
        ->when($q, function ($query) use ($q) {
            $query->where('descripcion', 'like', "%{$q}%")
                  ->orWhere('id', $q); // permite buscar por # exacto
        })
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString();

    return view('cotizaciones.index', compact('cotizaciones', 'q'));
}

    // Formulario para crear cotización
    public function create()
    {
        $servicios = TypeService::orderBy('descripcion')->get();
        $insumos   = Insumo::orderBy('nombre')->get();

        return view('cotizaciones.create', compact('servicios', 'insumos'));
    }

    // Guardar nueva cotización
    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
            'costo_mo'    => 'nullable|numeric',
            'type_service_id' => 'required|exists:type_service,id',
            'insumos'     => 'array',
            'insumos.*.id' => 'exists:insumo,id',
            'insumos.*.cantidad' => 'nullable|integer|min:0',
            'insumos.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);

        $cotizacione = Cotizacion::create([
            'fecha_creacion'  => now(),
            'descripcion'     => $data['descripcion'],
            'costo_mo'        => $data['costo_mo'] ?? 0,
            'total'           => 0,
            'type_service_id' => $data['type_service_id'],
            'estado_id'       => 4 // pendiente según tu tabla
        ]);

        if (!empty($data['insumos'])) {
            $syncData = [];
            foreach ($data['insumos'] as $item) {
                if (!empty($item['id']) && isset($item['cantidad']) && $item['cantidad'] > 0) {
                    $syncData[$item['id']] = [
                        'cantidad'        => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'] ?? 0
                    ];
                }
            }
            $cotizacione->insumos()->sync($syncData);
        }

        $cotizacione->load('insumos');
        $cotizacione->recalcularTotal();
        $cotizacione->save();

        return redirect()
            ->route('cotizaciones.index')
            ->with('ok', 'Cotización creada correctamente.');
    }

    // Ver detalle de una cotización
    public function show(Cotizacion $cotizacione)
    {
        $cotizacione->load(['servicio', 'insumos', 'estado']);
        return view('cotizaciones.show', compact('cotizacione'));
    }

    // Editar cotización
    public function edit(Cotizacion $cotizacione)
    {
        $servicios = TypeService::orderBy('descripcion')->get();
        $insumos   = Insumo::orderBy('nombre')->get();

        return view('cotizaciones.edit', compact('cotizacione', 'servicios', 'insumos'));
    }

    // Actualizar cotización
    public function update(Request $request, Cotizacion $cotizacione)
    {
        $data = $request->validate([
            'descripcion' => 'required|string|max:255',
            'costo_mo'    => 'nullable|numeric',
            'type_service_id' => 'required|exists:type_service,id',
            'insumos'     => 'array',
            'insumos.*.id' => 'exists:insumo,id',
            'insumos.*.cantidad' => 'nullable|integer|min:0',
            'insumos.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);

        $cotizacione->update([
            'descripcion'     => $data['descripcion'],
            'costo_mo'        => $data['costo_mo'] ?? 0,
            'type_service_id' => $data['type_service_id'],
        ]);

        if (!empty($data['insumos'])) {
            $syncData = [];
            foreach ($data['insumos'] as $item) {
                if (!empty($item['id'])) {
                    $cantidad = $item['cantidad'] ?? 0;
                    $precio   = $item['precio_unitario'] ?? 0;
                    $syncData[$item['id']] = [
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $precio
                    ];
                }
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

    // Aprobar cotización
    public function aprobar(Cotizacion $cotizacione)
    {
        // Actualizamos el estado a "aprobada" (id=6 según tu tabla)
        $cotizacione->update([
            'estado_id' => 6
        ]);

        // Descontar insumos del stock
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

    // Rechazar cotización
    public function rechazar(Cotizacion $cotizacione)
    {
        $cotizacione->update([
            'estado_id' => 7 // rechazada
        ]);

        return redirect()
            ->route('cotizaciones.index')
            ->with('ok', 'Cotización rechazada correctamente.');
    }
}
