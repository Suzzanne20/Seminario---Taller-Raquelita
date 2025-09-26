<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Marca;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index()
    {
        // Cargamos vehículos con sus marcas
        $vehiculos = Vehiculo::with('marca')->get(); // <-- corregido 'marcas' a 'marca'
        return view('vehiculos.index', compact('vehiculos'));
    }

    public function create()
    {
        // Paso las marcas existentes para que aparezcan en el select
        $marcas = Marca::select('nombre')->distinct()->get();
        return view('vehiculos.create', compact('marcas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'placa' => 'required|unique:vehiculo,placa|max:7',
            'modelo' => 'required|integer',
            'linea' => 'required|string|max:45',
            'motor' => 'required|string|max:45',
            'cilindraje' => 'required|numeric',
            'marca' => 'required|string|max:45',
        ]);

        // 1. Crear el vehículo
        $vehiculo = Vehiculo::create($request->only(['placa','modelo','linea','motor','cilindraje']));

        // 2. Crear o vincular la marca
        Marca::create([
            'nombre' => $request->marca,
            'vehiculo_placa' => $vehiculo->placa
        ]);

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo y marca registrados correctamente.');
    }

    public function edit($placa)
    {
        $vehiculo = Vehiculo::with('marca')->findOrFail($placa); // <-- corregido 'marcas' a 'marca'
        $marcas = Marca::select('nombre')->distinct()->get();     // <-- corregido variable a $marcas
        return view('vehiculos.edit', compact('vehiculo', 'marcas'));
    }

    public function update(Request $request, $placa)
    {
        $vehiculo = Vehiculo::findOrFail($placa);

        $request->validate([
            'modelo' => 'required|integer',
            'linea' => 'required|string|max:45',
            'motor' => 'required|string|max:45',
            'cilindraje' => 'required|numeric',
            'marca' => 'required|string|max:45',
        ]);

        // Actualizar datos del vehículo
        $vehiculo->update($request->only(['modelo','linea','motor','cilindraje']));

        // Actualizar o crear la marca vinculada
        Marca::updateOrCreate(
            ['vehiculo_placa' => $vehiculo->placa],
            ['nombre' => $request->marca]
        );

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo y marca actualizados correctamente.');
    }

    public function destroy($placa)
    {
        $vehiculo = Vehiculo::findOrFail($placa);

        // Eliminar primero las marcas asociadas
        Marca::where('vehiculo_placa', $vehiculo->placa)->delete();

        // Eliminar el vehículo
        $vehiculo->delete();

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo y marca eliminados correctamente.');
    }
}
