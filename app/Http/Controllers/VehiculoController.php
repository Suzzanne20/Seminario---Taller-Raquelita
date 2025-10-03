<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Marca;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index()
    {
        // Se cargan los vehiculo con las marcas seleccionadas
        $vehiculos = Vehiculo::with('marca')->get();
        return view('vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        // Paso las marcas existentes para que aparezcan en el select
        $marcas = Marca::orderBy('nombre')->get(['id', 'nombre']);
        return view('vehiculos.create', compact('marcas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'placa'      => 'required|string|max:7|unique:vehiculo,placa',
            'marca_id'   => 'required|integer|exists:marca,id',
            'modelo'     => 'required|integer',
            'linea'      => 'required|string|max:45',
            'motor'      => 'required|string|max:45',
            'cilindraje' => 'required|numeric',
        ]);


        Vehiculo::create($validated);

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo registrado correctamente.');
    }

    public function edit(string $placa)
    {
        $vehiculo = Vehiculo::with('marca')->findOrFail($placa);
        $marcas   = Marca::orderBy('nombre')->get(['id', 'nombre']);
        return view('vehiculos.edit', compact('vehiculo', 'marcas'));
    }
    
    public function update(Request $request, string $placa)
    {
        $vehiculo = Vehiculo::findOrFail($placa);

        $validated = $request->validate([
            'marca_id'   => 'required|integer|exists:marca,id',
            'modelo'     => 'required|integer',
            'linea'      => 'required|string|max:45',
            'motor'      => 'required|string|max:45',
            'cilindraje' => 'required|numeric',
        ]);

        $vehiculo->update($validated);

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(string $placa)
    {
        // ya no eliminamos marcas; pueden ser compartidas por otros vehículos
        Vehiculo::findOrFail($placa)->delete();

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo eliminado correctamente.');
    }
}
