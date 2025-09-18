<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $insumo = Insumo::all();
        $tiposInsumo = TipoInsumo::all();
        return view('insumos.index', compact('insumo', 'tiposInsumo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tiposInsumo = TipoInsumo::all();
        return view('insumos.create', compact('tiposInsumo'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'        => 'required|string|max:50',
            'costo'         => 'nullable|numeric',
            'stock'         => 'required|numeric',
            'stock_minimo'  => 'required|numeric',
            'descripcion'   => 'required|string|max:200',
            'type_insumo_id'=> 'required|exists:type_insumo,id',
            'precio'        => 'nullable|numeric',
        ]);

        Insumo::create($request->all());
        return redirect()->route('insumos.index')->with('success', 'Insumo creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Insumo $insumo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tiposInsumo = TipoInsumo::all();
        $insumo = Insumo::findOrFail($id);
        return view('insumos.edit', compact('insumo', 'tiposInsumo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Insumo $insumo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'costo' => 'nullable|numeric',
            'stock' => 'required|numeric',
            'stock_minimo' => 'required|numeric',
            'descripcion' => 'required|string',
            'type_insumo_id' => 'required|integer',
            'precio' => 'nullable|numeric',
        ]);

        $insumo->update($request->all());

        return redirect()->route('insumos.index')
            ->with('success', 'Insumo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $insumo->delete();

        return redirect()->route('insumos.index')
        ->with('success', 'Insumo eliminado correctamente');
    }
}
