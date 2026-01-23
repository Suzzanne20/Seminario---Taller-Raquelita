<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsumoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $tipo_insumo = $request->get('tipo_insumo', '');
        $stock = $request->get('stock', '');

        $insumos = Insumo::with('tipoInsumo')
            ->when($q, fn($query) =>
                $query->where('nombre', 'like', "%{$q}%")
                      ->orWhere('codigo', 'like', "%{$q}%")
            )
            ->when($tipo_insumo, fn($query) =>
                $query->where('type_insumo_id', $tipo_insumo)
            )
            ->when($stock == 'bajo', fn($query) =>
                $query->whereRaw('stock <= stock_minimo AND stock > 0')
            )
            ->when($stock == 'sin_stock', fn($query) =>
                $query->where('stock', 0)
            )
            ->when($stock == 'normal', fn($query) =>
                $query->whereRaw('stock > stock_minimo')
            )
            ->paginate(10)
            ->withQueryString();

        $tiposInsumo = TipoInsumo::all();

        return view('insumos.index', compact('insumos', 'q', 'tiposInsumo', 'tipo_insumo', 'stock'));
    }

    public function create()
    {
        $tiposInsumo = TipoInsumo::all();
        return view('insumos.create', compact('tiposInsumo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // 1–4 dígitos; si envían "12" lo rellenamos luego a "0012"
            'codigo'         => ['required','regex:/^\d{1,4}$/','unique:insumo,codigo'],
            'nombre'         => ['required','string','max:50'],
            'costo'          => ['nullable','numeric','min:0'],
            'precio'         => ['required','numeric','min:0'],
            'stock'          => ['required','integer','min:0'],
            'stock_minimo'   => ['required','integer','min:0'],
            'descripcion'    => ['nullable','string','max:200'],
            'type_insumo_id' => ['required','exists:type_insumo,id'],
        ]);

        // Normaliza código: solo dígitos y pad a 4
        $codigo = str_pad(preg_replace('/\D+/', '', $request->codigo), 4, '0', STR_PAD_LEFT);

        Insumo::create([
            'codigo'         => $codigo,          
            'nombre'         => $request->nombre,
            'costo'          => (float)$request->costo,
            'precio'         => (float)$request->precio, 
            'stock'          => (int) $request->stock,
            'stock_minimo'   => (int) $request->stock_minimo,
            'descripcion'    => $request->descripcion,
            'type_insumo_id' => (int) $request->type_insumo_id,
        ]);

        return redirect()->route('insumos.index')->with('success', 'Insumo creado correctamente.');
    }

    public function edit($id)
    {
        $tiposInsumo = TipoInsumo::all();
        $insumo = Insumo::findOrFail($id);
        return view('insumos.edit', compact('insumo', 'tiposInsumo'));
    }

    public function update(Request $request, Insumo $insumo)
    {
        $rules = [
            'nombre'         => ['required','string','max:50'],
            'costo'          => ['nullable','numeric','min:0'],
            'precio'         => ['required','numeric','min:0'],
            'stock'          => ['required','integer','min:0'],
            'stock_minimo'   => ['required','integer','min:0'],
            'descripcion'    => ['nullable','string','max:200'],
            'type_insumo_id' => ['required','integer','exists:type_insumo,id'],
        ];

        // Si quieres permitir editar el código, descomenta esto:
        /*
        $rules['codigo'] = [
            'required','regex:/^\d{1,4}$/',
            Rule::unique('insumo','codigo')->ignore($insumo->id,'id'),
        ];
        */

        $request->validate($rules);

        $payload = [
            'nombre'         => $request->nombre,
            'costo'          => (float)$request->costo,
            'precio'         => (float)$request->precio, 
            'stock'          => (int) $request->stock,
            'stock_minimo'   => (int) $request->stock_minimo,
            'descripcion'    => $request->descripcion,
            'type_insumo_id' => (int) $request->type_insumo_id,
        ];

        // Si permites editar código:
        /*
        if ($request->filled('codigo')) {
            $payload['codigo'] = str_pad(preg_replace('/\D+/', '', $request->codigo), 4, '0', STR_PAD_LEFT);
        }
        */

        $insumo->update($payload);

        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado exitosamente.');
    }

    public function destroy($id)
    {
        Insumo::findOrFail($id)->delete();
        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado correctamente');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = (array) $request->input('ids');
        if ($ids) { Insumo::whereIn('id', $ids)->delete(); }
        return redirect()->route('insumos.index')->with('success', 'Insumos eliminados correctamente.');
    }
}
