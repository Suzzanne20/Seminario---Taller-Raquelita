<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra2;
use App\Models\Proveedor;
use App\Models\Insumo;
use App\Models\OrdenCompraDetalle;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrdenCompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $ordenes = OrdenCompra2::with('proveedor')
            ->when($q, fn($query) =>
                $query->where('nombre', 'like', "%{$q}%")
            )
            ->paginate(10)
            ->withQueryString();
        return view('ordenes_compras.index', compact('ordenes', 'q'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proveedores = Proveedor::all();
        $insumos = Insumo::all();
        return view('ordenes_compras.create', compact('proveedores','insumos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_orden' => ['required', 'date'],
            'fecha_entrega_esperada' => ['nullable', 'date', 'after_or_equal:fecha_orden'],
            'proveedor_id' => ['required', 'exists:proveedor,id'],
            'estado' => ['required', 'in:pendiente,aprobada,recibida,cancelada'],
            'observaciones' => ['nullable', 'string', 'max:1000'],

            // Detalles
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.insumo_id' => ['required', 'exists:insumo,id'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0.01'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            // No validamos 'subtotal' del cliente; lo recalculamos en el servidor
        ]);

        DB::transaction(function () use ($validated) {
            // Recalcular subtotales en el servidor
            $detalles = collect($validated['detalles'])->map(function ($d) {
                $cantidad = (float) $d['cantidad'];
                $precio = (float) $d['precio_unitario'];

                return [
                    'insumo_id' => (int) $d['insumo_id'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => round($cantidad * $precio, 2),
                ];
            });

            $total = $detalles->sum('subtotal');

            // Crear la orden
            $orden = OrdenCompra2::create([
                'fecha_orden' => $validated['fecha_orden'],
                'fecha_entrega_esperada' => $validated['fecha_entrega_esperada'] ?? null,
                'proveedor_id' => $validated['proveedor_id'],
                'estado' => $validated['estado'],
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => $total,
            ]);

            // Crear los detalles
            $orden->detalles()->createMany($detalles->toArray());
        });

        return redirect()
            ->route('ordenes_compras.index')
            ->with('success', 'Orden de compra registrada correctamente con sus detalles.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $orden = OrdenCompra2::with(['proveedor', 'detalles.insumo'])->findOrFail($id);

        return view('ordenes_compras.show', compact('orden'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $proveedores = Proveedor::all();
        $insumos = Insumo::all();
        $ordenes = OrdenCompra2::with('proveedor')->findOrFail($id);
        return view('ordenes_compras.edit', compact('ordenes', 'proveedores','insumos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $orden = OrdenCompra2::findOrFail($id);

        $request->validate([
            'fecha_orden' => 'required|date',
            'proveedor_id' => 'required|exists:proveedor,id',
            'fecha_entrega_esperada' => 'nullable|date',
            'estado' => 'required|in:pendiente,aprobada,recibida,cancelada',
            'observaciones' => 'nullable|string',
            'total' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
            'detalles.*.insumo_id' => 'required|exists:insumo,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.subtotal' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $orden) {
            // Actualizar datos principales
            $orden->update([
                'fecha_orden' => $request->fecha_orden,
                'fecha_entrega_esperada' => $request->fecha_entrega_esperada,
                'proveedor_id' => $request->proveedor_id,
                'estado' => $request->estado,
                'observaciones' => $request->observaciones,
                'total' => $request->total,
            ]);

            // Eliminar detalles anteriores
            $orden->detalles()->delete();

            // Insertar nuevos detalles
            foreach ($request->detalles as $detalle) {
                $orden->detalles()->create([
                    'insumo_id' => $detalle['insumo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);
            }
        });

        return redirect()->route('ordenes_compras.index')
            ->with('success', 'Orden de compra actualizada correctamente.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ordenes = OrdenCompra2::findOrFail($id);
        $ordenes->delete();
        return redirect()->route('ordenes_compras.index')
            ->with('success', 'Orden de compra eliminada correctamente.');
    }

    public function updateEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,aprobada,recibida,cancelada',
        ]);

        $orden = OrdenCompra2::findOrFail($id);
        $orden->estado = $request->estado;
        $orden->save();

        return response()->json([
            'success' => true,
            'estado' => ucfirst($orden->estado),
        ]);
    }


}
