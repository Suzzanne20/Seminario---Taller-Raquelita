<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $proveedores = Proveedor::query()
            ->latest()
            ->paginate(15);

        return view('proveedores.index', compact('proveedores'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('proveedores.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:150',
            'nit'        => 'nullable|string|max:50',
            'telefono'   => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:150',
            'direccion'  => 'nullable|string|max:255',
        ]);

        $data = $request->all();

        Proveedor::create($data);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
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
        $proveedor = Proveedor::findOrFail($id);

        return view('proveedores.edit', compact('proveedor'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $data = $request->validate([
            'nombre'     => 'required|string|max:150',
            'nit'        => 'nullable|string|max:50',
            'telefono'   => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:150',
            'direccion'  => 'nullable|string|max:255',
        ]);

        $proveedor->update($data);

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();

        return redirect()
            ->route('proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }
}
