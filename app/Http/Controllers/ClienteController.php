<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all(); 
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:45',
            'nit' => 'nullable|string|max:20',
            'telefono' => 'required|numeric',
            'direccion' => 'nullable|string|max:60',
        ]);

        \App\Models\Cliente::create($request->only('nombre','nit','telefono','direccion'));

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente registrado correctamente');
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:45',
            'nit' => 'nullable|string|max:20',
            'telefono' => 'required|numeric',
            'direccion' => 'nullable|string|max:60',
        ]);

        $cliente = \App\Models\Cliente::findOrFail($id);
        $cliente->update($request->only('nombre','nit','telefono','direccion'));

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente');
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente');
    }
}
