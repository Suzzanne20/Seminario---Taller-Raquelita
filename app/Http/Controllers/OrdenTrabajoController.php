<?php

namespace App\Http\Controllers;

use App\Models\{OrdenTrabajo, Vehiculo, TypeService, Estado, EstadoOrden, Cotizacion};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenTrabajoController extends Controller
{
    /** Lista de OTs (incluye relaciones de ambos flujos) */
    public function index()
    {
        $ordenes = OrdenTrabajo::with([
                'vehiculo',
                'tipoServicio',          
                'estadoActual.estado',     
                'cotizacion',             
                'servicio',                
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
        $estados      = Estado::orderBy('nombre')->get(['id','nombre']); // por si deseas elegirlo
        $cotizaciones = Cotizacion::where('estado', 'aprobada')->orderByDesc('id')->get();

        $view = view()->exists('ordenes.ot_registro') ? 'ordenes.ot_registro' : 'ordenes.create';
        return view($view, compact('vehiculos','servicios','estados','cotizaciones'));
    }


    public function store(Request $request)
    {

        if ($request->filled('vehiculo_placa')) {
            $request->merge(['vehiculo_placa' => strtoupper(trim($request->vehiculo_placa))]);
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
        ];

        if (!$request->filled('cotizacion_id')) {
            $rules['vehiculo_placa'] = 'required|string|exists:vehiculo,placa';
        } else {
            $rules['vehiculo_placa'] = 'nullable|string|exists:vehiculo,placa';
        }

        $data = $request->validate($rules);

        DB::transaction(function () use (&$data) {

            if (!empty($data['cotizacion_id'])) {
                $cot = Cotizacion::with(['vehiculo'])->find($data['cotizacion_id']);

                if ($cot && empty($data['vehiculo_placa']) && $cot->vehiculo && !empty($cot->vehiculo->placa)) {
                    $data['vehiculo_placa'] = strtoupper(trim($cot->vehiculo->placa));
                }

            }

            $data['fecha_creacion'] = now();
            $data['costo_mo']       = $data['costo_mo'] ?? 0;
            $data['total']          = $data['total'] ?? 0;
            $data['id_creador']     = auth()->id() ?? 1;

            $ot = OrdenTrabajo::create($data);

            $estadoPend = Estado::where('nombre', 'Pendiente')->value('id');
            if (!$estadoPend) {

                $estadoPend = 1;
            }

            EstadoOrden::create([
                'estado_id'        => $estadoPend,
                'orden_trabajo_id' => $ot->id,
            ]);
        });

        return redirect()->route('ordenes.index')->with('success', 'Orden de trabajo creada correctamente.');
    }

    public function show(OrdenTrabajo $orden)
    {
        $orden->load(['vehiculo','tipoServicio','estadoActual.estado','cotizacion','servicio']);

        $view = view()->exists('ordenes.ot_show') ? 'ordenes.ot_show'
               : (view()->exists('ordenes.show') ? 'ordenes.show' : 'ordenes.ot_editar'); // último fallback
        return view($view, compact('orden'));
    }

    public function edit(OrdenTrabajo $orden)
    {
        $orden->load(['vehiculo','tipoServicio','estadoActual.estado','cotizacion','servicio']);

        $vehiculos    = Vehiculo::orderBy('placa')->get(['placa','linea','modelo']);
        $servicios    = TypeService::orderBy('descripcion')->get(['id','descripcion']);
        $cotizaciones = Cotizacion::where('estado', 'aprobada')->orderByDesc('id')->get();

        $view = view()->exists('ordenes.ot_editar') ? 'ordenes.ot_editar' : 'ordenes.edit';
        return view($view, compact('orden','vehiculos','servicios','cotizaciones'));
    }

    public function update(Request $request, OrdenTrabajo $orden)
    {
        if ($request->filled('vehiculo_placa')) {
            $request->merge(['vehiculo_placa' => strtoupper(trim($request->vehiculo_placa))]);
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
            'vehiculo_placa'   => 'nullable|string|exists:vehiculo,placa',
        ];

        $data = $request->validate($rules);

        $orden->update($data);

        return redirect()->route('ordenes.index')->with('success', 'Orden de trabajo actualizada correctamente.');
    }

    public function destroy(OrdenTrabajo $orden)
    {
        $orden->delete();
        return back()->with('success', 'Orden de trabajo eliminada correctamente.');
    }
}

