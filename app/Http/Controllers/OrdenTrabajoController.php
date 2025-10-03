<?php

namespace App\Http\Controllers;

use App\Models\{OrdenTrabajo, Vehiculo, TypeService, Estado, Cotizacion, User, Insumo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdenTrabajoController extends Controller
{
    public function index()
{
    $q = request('q'); // búsqueda por placa

    $ordenes = OrdenTrabajo::with(['vehiculo','servicio','estado'])
        ->when($q, function ($query, $q) {
            $query->whereHas('vehiculo', fn($qq) =>
                $qq->where('placa', 'like', "%{$q}%")
            );
        })
        ->orderByDesc('id')
        ->paginate(10)
        ->withQueryString(); 

    $view = view()->exists('ordenes.ot_lista') ? 'ordenes.ot_lista' : 'ordenes.index';
    return view($view, compact('ordenes','q'));
}
    public function create()
    {
        $vehiculos    = Vehiculo::orderBy('placa')->get(['placa','linea','modelo']);
        $servicios    = TypeService::orderBy('descripcion')->get(['id','descripcion']);
        $insumos      = Insumo::orderBy('nombre')->get(['id','nombre','precio']);
        $cotizaciones = Cotizacion::with('insumos')
                           ->where('estado_id', 6) // aprobadas
                           ->latest()
                           ->get(['id','descripcion','type_service_id','costo_mo','total']);
        $tecnicos     = User::whereHas('roles', fn($q)=>$q->whereRaw('LOWER(name)=?',['mecanico']))
                           ->orderBy('name')->get(['id','name']);

        return view('ordenes.ot_registro', compact(
            'vehiculos','servicios','insumos','cotizaciones','tecnicos'
        ));
    }

    public function store(Request $request)
    {
        if ($request->filled('vehiculo_placa')) {
            $request->merge(['vehiculo_placa' => strtoupper(trim($request->vehiculo_placa))]);
        }

        $data = $request->validate([
            'vehiculo_placa'   => 'required|string|exists:vehiculo,placa',
            'type_service_id'  => 'required|integer|exists:type_service,id',
            'descripcion'      => 'nullable|string|max:100',
            'kilometraje'      => 'nullable|integer|min:0',
            'proximo_servicio' => 'nullable|integer|min:0',
            'costo_mo'         => 'nullable|numeric|min:0',
            'cotizacion_id'    => 'nullable|integer|exists:cotizaciones,id',
            'tecnico_id'       => [
                'nullable','integer','exists:users,id',
                function ($attr, $value, $fail) {
                    if ($value) {
                        $ok = User::where('id',$value)
                                 ->whereHas('roles', fn($q)=>$q->whereRaw('LOWER(name)=?',['mecanico']))
                                 ->exists();
                        if (!$ok) $fail('El usuario seleccionado no tiene el rol de Mecánico.');
                    }
                },
            ],
            'insumos'            => 'nullable|array',
            'insumos.*.id'       => 'required_with:insumos|integer|exists:insumo,id',
            'insumos.*.cantidad' => 'required_with:insumos|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($data) {

            // === ID del creador (entero seguro) ===
            $userId = (int) (Auth::id() ?? 0);
            if ($userId <= 0) {
                $userId = (int) User::where('email', optional(Auth::user())->email)->value('id');
            }
            if ($userId <= 0) {
                abort(401, 'No autenticado');
            }

            // === Cargar cotización si viene ===
            $cotizacion = null;
            if (!empty($data['cotizacion_id'])) {
                $cotizacion = Cotizacion::with('insumos')->find($data['cotizacion_id']);
            }

            if ($cotizacion) {
                $type_service_id = $cotizacion->type_service_id;
                $descripcion     = $cotizacion->descripcion;
                $costo_mo        = (float) ($cotizacion->costo_mo ?? 0);
                $insumosOT       = $cotizacion->insumos->map(fn($i)=>[
                    'id'       => $i->id,
                    'cantidad' => (float) $i->pivot->cantidad,
                ])->values()->all();
            } else {
                $type_service_id = $data['type_service_id'];
                $descripcion     = $data['descripcion'] ?? null;
                $costo_mo        = (float) ($data['costo_mo'] ?? 0);
                $insumosOT       = $data['insumos'] ?? [];
            }

            // === Calcular total de insumos ===
            $totalInsumos = 0.0;
            if (!empty($insumosOT)) {
                $ids     = collect($insumosOT)->pluck('id')->all();
                $precios = Insumo::whereIn('id', $ids)->pluck('precio', 'id');
                foreach ($insumosOT as $row) {
                    $precio   = (float) ($precios[$row['id']] ?? 0);
                    $cantidad = (float) $row['cantidad'];
                    $totalInsumos += $precio * $cantidad;
                }
            }

            // === Crear OT con estado por defecto “Nueva” (id 1) ===
            $orden = OrdenTrabajo::create([
                'fecha_creacion'   => now(),
                'descripcion'      => $descripcion,
                'kilometraje'      => $data['kilometraje'] ?? null,
                'proximo_servicio' => $data['proximo_servicio'] ?? null,
                'costo_mo'         => $costo_mo,
                'total'            => $costo_mo + $totalInsumos,
                'id_creador'       => $userId,
                'vehiculo_placa'   => $data['vehiculo_placa'],
                'type_service_id'  => $type_service_id,
                'estado_id'        => 1, // NUEVA
            ]);

            // === Guardar insumos de la OT ===
            if (!empty($insumosOT)) {
                foreach ($insumosOT as $row) {
                    DB::table('insumo_ot')->insert([
                        'orden_trabajo_id' => $orden->id,
                        'insumo_id'        => $row['id'],
                        'cantidad'         => $row['cantidad'],
                    ]);
                }
            }

            // === Asignación del técnico (si viene) ===
            if (!empty($data['tecnico_id'])) {
                DB::table('asignacion_orden')->insert([
                    'orden_trabajo_id' => $orden->id,
                    'usuario_id'       => $data['tecnico_id'],
                ]);
            }
        });

        return redirect()
            ->route('ordenes.index')
            ->with('success', '¡Orden de trabajo creada correctamente!');
    }

    public function show(OrdenTrabajo $orden)
    {
        $orden->load(['vehiculo','servicio','estado']);
        $view = view()->exists('ordenes.ot_show') ? 'ordenes.ot_show'
               : (view()->exists('ordenes.show') ? 'ordenes.show' : 'ordenes.ot_editar');
        return view($view, compact('orden'));
    }

    public function edit(OrdenTrabajo $orden)
    {
        $orden->load(['vehiculo','servicio','estado','insumos']); // <= incluye insumos
        $vehiculos = Vehiculo::orderBy('placa')->get(['placa','linea','modelo']);
        $servicios = TypeService::orderBy('descripcion')->get(['id','descripcion']);
        $tecnicos  = User::role('Mecanico')->orderBy('name')->get(['id','name']);
        $insumos   = Insumo::orderBy('nombre')->get(['id','nombre','precio']);
        return view('ordenes.ot_editar', compact('orden','vehiculos','servicios','tecnicos','insumos'));
    }


    public function update(Request $request, OrdenTrabajo $orden)
    {
        if ($request->filled('vehiculo_placa')) {
            $request->merge(['vehiculo_placa' => strtoupper(trim($request->vehiculo_placa))]);
        }

        $data = $request->validate([
            'descripcion'      => 'nullable|string|max:100',
            'costo_mo'         => 'nullable|numeric|min:0',
            'total'            => 'nullable|numeric|min:0',
            'type_service_id'  => 'required|integer|exists:type_service,id',
            'kilometraje'      => 'nullable|integer|min:0',
            'proximo_servicio' => 'nullable|integer|min:0',
            'vehiculo_placa'   => 'nullable|string|exists:vehiculo,placa',
            'estado_id'        => 'nullable|integer|exists:estado,id',
            'tecnico_id'       => [
                'nullable','integer','exists:users,id',
                function ($attr, $value, $fail) {
                    if ($value) {
                        $ok = User::where('id',$value)
                                  ->whereHas('roles', fn($q)=>$q->whereRaw('LOWER(name)=?',['mecanico']))
                                  ->exists();
                        if (!$ok) $fail('El usuario seleccionado no tiene el rol de Mecánico.');
                    }
                },
            ],
        ]);

        DB::transaction(function () use ($orden, $data) {
            $orden->update($data);

            if (array_key_exists('tecnico_id', $data)) {
                DB::table('asignacion_orden')->where('orden_trabajo_id', $orden->id)->delete();
                if (!empty($data['tecnico_id'])) {
                    DB::table('asignacion_orden')->insert([
                        'orden_trabajo_id' => $orden->id,
                        'usuario_id'       => $data['tecnico_id'],
                    ]);
                }
            }
        });

        return redirect()->route('ordenes.index')->with('success', 'Orden de trabajo actualizada correctamente.');
    }

    public function destroy(OrdenTrabajo $orden)
    {
        DB::transaction(function () use ($orden) {
            DB::table('asignacion_orden')->where('orden_trabajo_id', $orden->id)->delete();
            $orden->delete();
        });

        return back()->with('success', 'Orden de trabajo eliminada correctamente.');
    }
}

