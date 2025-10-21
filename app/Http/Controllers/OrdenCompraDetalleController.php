<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrdenCompraDetalleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $detalles = OrdenCompraDetalle::with(['ordenCompra', 'insumo'])->get();
        return view('orden_detalle.index', compact('detalles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // En un caso real, necesitarías seleccionar la Orden de Compra e Insumo
        return view('orden_detalle.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Reglas de validación para un solo detalle
        $request->validate([
            'orden_compra_id' => 'required|exists:orden_compra,id',
            'insumo_id' => 'required|exists:insumo,id',
            'cantidad' => 'required|numeric|min:0.01',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
        ]);

        OrdenCompraDetalle::create($request->all());

        return redirect()->route('ordenes_detalle.index')
            ->with('success', 'Detalle de orden de compra creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OrdenCompraDetalle $ordenCompraDetalle)
    {
        return view('orden_detalle.show', compact('ordenCompraDetalle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrdenCompraDetalle $ordenCompraDetalle)
    {
        return view('orden_detalle.edit', compact('ordenCompraDetalle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrdenCompraDetalle $ordenCompraDetalle)
    {
        $request->validate([
            'orden_compra_id' => 'required|exists:orden_compra,id',
            'insumo_id' => 'required|exists:insumo,id',
            'cantidad' => 'required|numeric|min:0.01',
            'precio_unitario' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $ordenCompraDetalle->update($request->all());

        return redirect()->route('ordenes_detalle.index')
            ->with('success', 'Detalle de orden de compra actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ordenCompraDetalle->delete();

        return back()->with('success', 'Detalle de orden de compra eliminado exitosamente.');
    }
}
