<?php

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use App\Models\Cotizacion;
use App\Models\TypeService;
use Illuminate\Http\Request;

class OrdenTrabajoController extends Controller
{
    // 📌 Listar todas las órdenes
    public function index()
    {
        $ordenes = OrdenTrabajo::with(['cotizacion', 'servicio'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('ordenes.ot_lista', compact('ordenes'));
    }

    // 📌 Crear una orden (manual, aunque normalmente se genera desde cotización)
    public function create()
    {
        $cotizaciones = Cotizacion::where('estado', 'aprobada')->get();
        $servicios = TypeService::orderBy('descripcion')->get();

        return view('ordenes.create', compact('cotizaciones', 'servicios'));
    }

    // 📌 Guardar en BD
    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion'     => 'required|string|max:255',
            'costo_mo'        => 'nullable|numeric',
            'total'           => 'nullable|numeric',
            'type_service_id' => 'required|exists:type_service,id',
            'empleado_id'     => 'nullable|integer',
            'cotizacion_id'   => 'required|exists:cotizaciones,id',
        ]);

        $data['fecha_creacion'] = now();

        OrdenTrabajo::create($data);

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden de trabajo creada correctamente.');
    }

    // 📌 Mostrar detalle
    public function show(OrdenTrabajo $orden)
    {
        $orden->load(['cotizacion', 'servicio']);
        return view('ordenes.show', compact('orden'));
    }

    // 📌 Editar
    public function edit(OrdenTrabajo $orden)
    {
        $cotizaciones = Cotizacion::where('estado', 'aprobada')->get();
        $servicios = TypeService::orderBy('descripcion')->get();

        return view('ordenes.edit', compact('orden', 'cotizaciones', 'servicios'));
    }

    // 📌 Actualizar
    public function update(Request $request, OrdenTrabajo $orden)
    {
        $data = $request->validate([
            'descripcion'     => 'required|string|max:255',
            'costo_mo'        => 'nullable|numeric',
            'total'           => 'nullable|numeric',
            'type_service_id' => 'required|exists:type_service,id',
            'empleado_id'     => 'nullable|integer',
            'cotizacion_id'   => 'required|exists:cotizaciones,id',
        ]);

        $orden->update($data);

    return redirect()->route('ordenes.index')
            ->with('success', 'Orden de trabajo eliminada correctamente.');
    }
}