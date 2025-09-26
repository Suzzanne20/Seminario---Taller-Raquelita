<?php

namespace App\Http\Controllers;

use App\Models\{OrdenTrabajo, Vehiculo, TypeService, Estado, EstadoOrden};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenTrabajoController extends Controller
{
    public function index()
    {
        $ordenes = OrdenTrabajo::with([
            'vehiculo',
            'tipoServicio',
            'estadoActual.estado',
        ])
        ->orderByDesc('id')
        ->get();

        return view('ordenes.ot_lista', compact('ordenes'));
    }

    public function create()
    {
        $vehiculos   = Vehiculo::orderBy('placa')->get(['placa','linea','modelo']);
        $servicios   = TypeService::orderBy('descripcion')->get(['id','descripcion']);
        $estados     = Estado::orderBy('nombre')->get(['id','nombre']); // por si deseas elegirlo

        return view('ordenes.ot_registro', compact('vehiculos','servicios','estados'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'vehiculo_placa' => strtoupper(trim($request->vehiculo_placa)),
        ]);
        
        $request->validate([
            'vehiculo_placa'   => 'required|string|exists:vehiculo,placa',
            'type_service_id'  => 'required|integer|exists:type_service,id',
            'kilometraje'      => 'required|integer|min:0',
            'proximo_servicio' => 'nullable|integer|min:0',
            'descripcion'      => 'nullable|string|max:100',
            'costo_mo'         => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $ot = OrdenTrabajo::create([
                'fecha_creacion'   => now(),
                'vehiculo_placa'   => $request->vehiculo_placa,
                'type_service_id'  => $request->type_service_id,
                'kilometraje'      => $request->kilometraje,
                'proximo_servicio' => $request->proximo_servicio,
                'descripcion'      => $request->descripcion,
                'costo_mo'         => $request->costo_mo ?? 0,
                'total'            => 0,           // Suma segun se ingrese insumos y mano de obra
                'id_creador'       => auth()->id() ?? 1,
            ]);

            // Estado inicial = 'Pendiente' (ajusta si usas otro nombre/id)
            $estadoPend = Estado::where('nombre', 'Pendiente')->value('id') ?? 1;
            EstadoOrden::create([
                'estado_id'        => $estadoPend,
                'orden_trabajo_id' => $ot->id,
            ]);
        });

        return redirect()->route('ordenes.index')->with('success', 'Orden registrada correctamente.');
    }

    public function edit(OrdenTrabajo $orden)
    {
        $orden->load(['vehiculo','tipoServicio','estadoActual.estado']);

        $vehiculos = Vehiculo::orderBy('placa')->get(['placa','linea','modelo']);
        $servicios = TypeService::orderBy('descripcion')->get(['id','descripcion']);

        return view('ordenes.ot_editar', compact('orden','vehiculos','servicios'));
    }

    public function update(Request $request, OrdenTrabajo $orden)
    {
        $request->validate([
            'vehiculo_placa'   => 'required|string|exists:vehiculo,placa',
            'type_service_id'  => 'required|integer|exists:type_service,id',
            'kilometraje'      => 'required|integer|min:0',
            'proximo_servicio' => 'nullable|integer|min:0',
            'descripcion'      => 'nullable|string|max:100',
            'costo_mo'         => 'nullable|numeric|min:0',
        ]);

        $orden->update($request->only([
            'vehiculo_placa','type_service_id','kilometraje','proximo_servicio','descripcion','costo_mo'
        ]));

        return redirect()->route('ordenes.index')->with('success', 'Orden actualizada.');
    }

    public function destroy(OrdenTrabajo $orden)
    {
        $orden->delete();
        return back()->with('success', 'Orden eliminada.');
    }
}

