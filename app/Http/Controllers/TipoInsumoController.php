<?php

namespace App\Http\Controllers;

use App\Models\TipoInsumo;
use Illuminate\Http\Request;

class TipoInsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiposInsumo = TipoInsumo::all();
        return view('insumos.tipo_insumos.index', compact('tiposInsumo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('insumos.tipo-insumos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        TipoInsumo::create($request->all());

        return redirect()->route('tipo-insumos.index')->with('success', 'Tipo de insumo creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tiposInsumo = TipoInsumo::findOrFail($id);
        return view('insumos.tipo_insumos.edit', compact('tiposInsumo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $tiposInsumo = \App\Models\TipoInsumo::findOrFail($id);
        $tiposInsumo->update($request->all());

        return redirect()->route('tipo-insumos.index')->with('success', 'Tipo de insumo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tiposInsumo = TipoInsumo::findOrFail($id);
        $tiposInsumo->delete();

        return redirect()->route('tipo-insumos.index')->with('success', 'Tipo de insumo eliminado correctamente.');
    }
}
