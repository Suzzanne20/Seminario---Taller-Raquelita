<?php

namespace App\Http\Controllers;

use App\Models\{OrdenTrabajo, Vehiculo, TypeService, Estado, Cotizacion, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OrdenTrabajoController extends Controller
{
    public function index()
    {
        $ordenes = OrdenTrabajo::with([
                'vehiculo',
                'servicio',   // 👈 estandarizamos el nombre
                'estado',     // 👈 relación directa
            ])
            ->orderByDesc('id')
            ->paginate(10);

        $view = view()->exists('ordenes.ot_lista') ? 'ordenes.ot_lista' : 'ordenes.index';
        return view($view, compact('ordenes'));
    }

    public function create()
    {
        $vehiculos    = Vehiculo::orderBy('placa')->get(['placa','linea','modelo']);
        $servicios    = TypeService::orderBy('descripcion')->get(['id','descripcion']);
        $estados      = Estado::orderBy('nombre')->get(['id','nombre']);
        // Si quieres listar cotizaciones aprobadas para “vincular” una OT:
        $cotizaciones = Cotizacion::where('estado_id', 2)->orderByDesc('id')->get(); // 2 = aprobada

        $view = view()->exists('ordenes.ot_registro') ? 'ordenes.ot_registro' : 'ordenes.create';
        return view($view, compact('vehiculos','servicios','estados','cotizaciones'));
    }

public function store(Request $request)
{
    if ($request->filled('vehiculo_placa')) {
        $request->merge([
            'vehiculo_placa' => strtoupper(trim($request->vehiculo_placa))
        ]);
    }

    $rules = [
        'descripcion'      => 'nullable|string|max:255',
        'costo_mo'         => 'nullable|numeric|min:0',
        'total'            => 'nullable|numeric|min:0',
        'type_service_id'  => 'required|integer|exists:type_service,id',
        'kilometraje'      => 'nullable|integer|min:0',
        'proximo_servicio' => 'nullable|integer|min:0',
        'empleado_id'      => 'nullable|integer',
        'cotizacion_id'    => 'nullable|integer|exists:cotizaciones,id',
        'estado_id'        => 'nullable|integer|exists:estado,id',
    ];

    // Si NO viene de cotización, exige placa
    $rules['vehiculo_placa'] = $request->filled('cotizacion_id')
        ? 'nullable|string|exists:vehiculo,placa'
        : 'required|string|exists:vehiculo,placa';

    $data = $request->validate($rules);

    DB::transaction(function () use ($data) {

        // ---- Resolver id_creador como ENTERO SEGURO ----
        $userId = \Illuminate\Support\Facades\Auth::id();
        if (!is_numeric($userId)) {
            // si devolvió el nombre de usuario, intenta resolver su ID en tabla usuario
            $userId = Usuario::where('nombre', $userId)->value('id');
        }
        if (!is_numeric($userId)) {
            // último fallback (asegúrate de que exista ese usuario)
            $userId = 1;
        }

        $payload = $data; // no mutamos $data original
        $payload['fecha_creacion'] = now();
        $payload['costo_mo']       = $payload['costo_mo'] ?? 0;
        $payload['total']          = $payload['total'] ?? 0;
        $payload['id_creador']     = (int) $userId;

        // Estado por defecto: 'Pendiente' o id=1
        if (empty($payload['estado_id'])) {
            $payload['estado_id'] = Estado::where('nombre', 'Pendiente')->value('id') ?? 1;
        }

        OrdenTrabajo::create($payload);
    });

    return redirect()
        ->route('ordenes.index')
        ->with('success', 'Orden de trabajo creada correctamente.');
}

    public function show(OrdenTrabajo $orden)
    {
        $orden->load(['vehiculo','servicio','estado']); // 👈 limpio

        $view = view()->exists('ordenes.ot_show') ? 'ordenes.ot_show'
               : (view()->exists('ordenes.show') ? 'ordenes.show' : 'ordenes.ot_editar');
        return view($view, compact('orden'));
    }

    public function edit(OrdenTrabajo $orden)
    {
        $orden->load(['vehiculo','servicio','estado']); // 👈 quita 'cotizaciones' si no existe relación

        $vehiculos    = Vehiculo::orderBy('placa')->get(['placa','linea','modelo']);
        $servicios    = TypeService::orderBy('descripcion')->get(['id','descripcion']);
        $cotizaciones = Cotizacion::where('estado_id', 2)->orderByDesc('id')->get();

        $view = view()->exists('ordenes.ot_editar') ? 'ordenes.ot_editar' : 'ordenes.edit';
        return view($view, compact('orden','vehiculos','servicios','cotizaciones'));
    }

    public function update(Request $request, OrdenTrabajo $orden)
    {
        if ($request->filled('vehiculo_placa')) {
            $request->merge(['vehiculo_placa' => strtoupper(trim($request->vehiculo_placa))]);
        }

        $data = $request->validate([
            'descripcion'      => 'nullable|string|max:255',
            'costo_mo'         => 'nullable|numeric|min:0',
            'total'            => 'nullable|numeric|min:0',
            'type_service_id'  => 'required|integer|exists:type_service,id',
            'kilometraje'      => 'nullable|integer|min:0',
            'proximo_servicio' => 'nullable|integer|min:0',
            'empleado_id'      => 'nullable|integer',
            'cotizacion_id'    => 'nullable|integer|exists:cotizaciones,id',
            'vehiculo_placa'   => 'nullable|string|exists:vehiculo,placa',
            'estado_id'        => 'nullable|integer|exists:estado,id',
        ]);

        $orden->update($data);

        return redirect()->route('ordenes.index')->with('success', 'Orden de trabajo actualizada correctamente.');
    }

    public function destroy(OrdenTrabajo $orden)
    {
        $orden->delete();
        return back()->with('success', 'Orden de trabajo eliminada correctamente.');
    }
}
