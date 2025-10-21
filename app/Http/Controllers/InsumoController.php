<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{

    public function index(Request $request)
{
    $q = trim($request->get('q', ''));

    $insumos = \App\Models\Insumo::with('tipoInsumo')
        ->when($q, fn($query) =>
            $query->where('nombre', 'like', "%{$q}%")
        )
        ->paginate(10)
        ->withQueryString();

    return view('insumos.index', compact('insumos', 'q'));
}

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
            'costo'         => 'nullable|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'stock_minimo'  => 'required|integer|min:0',
            'descripcion'   => 'required|string|max:200',
            'type_insumo_id'=> 'required|exists:type_insumo,id',
        ]);

        $costo = $request->costo ?? 0;
        $porcentajeGanancia = 20; // ⚡ Porcentaje fijo (puedes hacerlo dinámico más adelante)
        $precio = $costo + ($costo * $porcentajeGanancia / 100) + ($costo * 0.12);

        Insumo::create([
            'nombre'         => $request->nombre,
            'costo'          => $costo,
            'stock'          => $request->stock,
            'stock_minimo'   => $request->stock_minimo,
            'descripcion'    => $request->descripcion,
            'type_insumo_id' => $request->type_insumo_id,
            'precio'         => $precio,
        ]);

        return redirect()->route('insumos.index')
            ->with('success', 'Insumo creado correctamente.');
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
            'costo' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'descripcion' => 'required|string|max:200',
            'type_insumo_id' => 'required|integer|exists:type_insumo,id',
        ]);

        $costo = $request->costo ?? 0;
        $porcentajeGanancia = 20;
        $precio = $costo + ($costo * $porcentajeGanancia / 100) + ($costo * 0.12);

        $insumo->update([
            'nombre'         => $request->nombre,
            'costo'          => $costo,
            'stock'          => $request->stock,
            'stock_minimo'   => $request->stock_minimo,
            'descripcion'    => $request->descripcion,
            'type_insumo_id' => $request->type_insumo_id,
            'precio'         => $precio,
        ]);

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

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids'); // array de IDs seleccionados

         if ($ids && count($ids) > 0) {
        Insumo::whereIn('id', $ids)->delete();
        }

        return redirect()->route('insumos.index')
            ->with('success', 'Insumos eliminados correctamente.');
    }


}
