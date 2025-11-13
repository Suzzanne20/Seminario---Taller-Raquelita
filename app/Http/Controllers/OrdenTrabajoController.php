<?php

namespace App\Http\Controllers;

use App\Models\{OrdenTrabajo, Vehiculo, TypeService, Estado, Cotizacion, User, Insumo, Cliente};
use Illuminate\Http\Request;
use App\Services\UltraMsg;
use App\Services\OrderWaTemplates;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class OrdenTrabajoController extends Controller
{
    public function index()
    {
        $q = request('q'); // búsqueda por placa

        $ordenes = OrdenTrabajo::with([
            'vehiculo','servicio','estado', 'vehiculo.marca', 'vehiculo.clientes',
            'insumos' => fn($qq) => $qq->select('insumo.id','nombre','precio')
        ])
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
        $clientes     = Cliente::orderBy('nombre')->get(['id','nombre']);

        return view('ordenes.ot_registro', compact(
            'vehiculos','servicios','insumos','cotizaciones','tecnicos', 'clientes'
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
            'cliente_id'       => 'nullable|integer|exists:cliente,id',
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
            'checks'             => 'nullable|array',
            'checks.*'           => 'boolean',
        ]);

        // === IMPORTANTE: guardar el $orden que retorna la transacción
        $orden = DB::transaction(function () use ($data) {

            $userId = (int) (Auth::id() ?? 0);
            if ($userId <= 0) {
                $userId = (int) User::where('email', optional(Auth::user())->email)->value('id');
            }
            if ($userId <= 0) abort(401, 'No autenticado');

            // cotización (si viene)
            $cotizacion = !empty($data['cotizacion_id'])
                ? Cotizacion::with('insumos')->find($data['cotizacion_id'])
                : null;

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

            // pivot cliente_vehiculo
            if (!empty($data['cliente_id'] ?? null)) {
                DB::table('cliente_vehiculo')->updateOrInsert([
                    'cliente_id'     => (int)$data['cliente_id'],
                    'vehiculo_placa' => $data['vehiculo_placa'],
                ], []);
            }

            // total insumos
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

            // crear OT
            $orden = OrdenTrabajo::create([
                'fecha_creacion'     => now(),
                'descripcion'        => $descripcion,
                'kilometraje'        => $data['kilometraje'] ?? null,
                'proximo_servicio'   => $data['proximo_servicio'] ?? null,
                'costo_mo'           => $costo_mo,
                'total'              => $costo_mo + $totalInsumos,
                'id_creador'         => $userId,
                'vehiculo_placa'     => $data['vehiculo_placa'],
                'type_service_id'    => $type_service_id,
                'estado_id'          => 1,
                'mantenimiento_json' => self::normalizeChecks($data['checks'] ?? []),
            ]);

            // items
            if (!empty($insumosOT)) {
                foreach ($insumosOT as $row) {
                    DB::table('insumo_ot')->insert([
                        'orden_trabajo_id' => $orden->id,
                        'insumo_id'        => $row['id'],
                        'cantidad'         => $row['cantidad'],
                    ]);
                }
            }

            // técnico
            if (!empty($data['tecnico_id'])) {
                DB::table('asignacion_orden')->insert([
                    'orden_trabajo_id' => $orden->id,
                    'usuario_id'       => $data['tecnico_id'],
                ]);
            }

            return $orden; // <- CLAVE
        });

        // === WhatsApp: usar $orden (existe fuera de la transacción)
        try {
            $cliente = null;
            if (!empty($data['cliente_id'])) {
                $cliente = \App\Models\Cliente::find($data['cliente_id']);
            } else {
                $cliente = optional($orden->vehiculo)->clientes?->first();
            }

            $telefono = $cliente?->telefono ?? null;
            $to = UltraMsg::normalizePhone($telefono);

            if ($to) {
                $wa = new UltraMsg;
                $wa->sendText($to, OrderWaTemplates::created($orden));
            }
        } catch (\Throwable $e) {
            \Log::warning('WA create OT failed: '.$e->getMessage());
        }

        return redirect()->route('ordenes.index')
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
    $orden->load([
        'vehiculo.clientes',   
        'servicio',
        'estado',
        'items.insumo',   
        'creador',        
    ]);

    $vehiculos = Vehiculo::orderBy('placa')->get(['placa','linea','modelo']);
    $servicios = TypeService::orderBy('descripcion')->get(['id','descripcion']);
    $tecnicos  = User::role('Mecanico')->orderBy('name')->get(['id','name']);
    $insumos   = Insumo::orderBy('nombre')->get(['id','nombre','precio']);

    $estadosFlow = Estado::whereIn('nombre', ['Nueva','Asignada','Pendiente','En proceso','Finalizada'])
        ->orderByRaw("FIELD(nombre,'Nueva','Asignada','Pendiente','En proceso','Finalizada')")
        ->get(['id','nombre']);

    // En caso de haber cotizaciones
    $cotizacion = null;
    if (Schema::hasTable('cotizaciones')) {
        $q = Cotizacion::where('estado_id', 6); // aprobada
        if (Schema::hasColumn('cotizaciones', 'vehiculo_placa')) {
            $q->where('vehiculo_placa', $orden->vehiculo_placa ?? '');
        } else {
            $q->where('type_service_id', $orden->type_service_id);
        }
        $cotizacion = $q->latest()->first();
    }

    // Para las inspecciones se basa en la placa y en tiempo estimado 
    $inspeccion = null;
    if (Schema::hasTable('inspecciones') && Schema::hasColumn('inspecciones','vehiculo_placa')) {
        $desde = optional($orden->fecha_creacion)->copy()->subDays(3);
        $hasta = optional($orden->fecha_creacion)->copy()->addDays(3);
        $inspeccion = DB::table('inspecciones')
            ->where('vehiculo_placa', $orden->vehiculo_placa ?? '')
            ->when($desde && $hasta, fn($qq)=>$qq->whereBetween('created_at', [$desde, $hasta]))
            ->latest()->first();
    }

    return view('ordenes.ot_editar', compact(
        'orden','vehiculos','servicios','tecnicos','insumos','estadosFlow','cotizacion','inspeccion'
    ));
}


    public function update(Request $request, OrdenTrabajo $orden)
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
            'estado_id'        => 'required|integer|exists:estado,id',
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
            'insumos'             => 'nullable|array',
            'insumos.*.id'        => 'required_with:insumos|integer|exists:insumo,id',
            'insumos.*.cantidad'  => 'required_with:insumos|numeric|min:0.01',
            'checks'              => 'nullable|array',
            'checks.*'            => 'boolean',
        ]);

        $oldEstadoId   = (int) $orden->estado_id;
        $oldEstadoName = optional($orden->estado)->nombre ?? '—';

        DB::transaction(function () use ($orden, $data) {
            $totalInsumos = 0.0;
            $items = $data['insumos'] ?? [];
            if (!empty($items)) {
                $ids     = collect($items)->pluck('id')->all();
                $precios = Insumo::whereIn('id', $ids)->pluck('precio', 'id');
                foreach ($items as $row) {
                    $totalInsumos += (float) ($precios[$row['id']] ?? 0) * (float) $row['cantidad'];
                }
            }

            $orden->update([
                'vehiculo_placa'     => $data['vehiculo_placa'],
                'type_service_id'    => $data['type_service_id'],
                'descripcion'        => $data['descripcion'] ?? null,
                'kilometraje'        => $data['kilometraje'] ?? null,
                'proximo_servicio'   => $data['proximo_servicio'] ?? null,
                'costo_mo'           => (float)($data['costo_mo'] ?? 0),
                'total'              => (float)($data['costo_mo'] ?? 0) + $totalInsumos,
                'estado_id'          => (int) $data['estado_id'],
                'mantenimiento_json' => self::normalizeChecks($data['checks'] ?? []),
            ]);

            DB::table('asignacion_orden')->where('orden_trabajo_id', $orden->id)->delete();
            if (!empty($data['tecnico_id'])) {
                DB::table('asignacion_orden')->insert([
                    'orden_trabajo_id' => $orden->id,
                    'usuario_id'       => $data['tecnico_id'],
                ]);
            }

            DB::table('insumo_ot')->where('orden_trabajo_id', $orden->id)->delete();
            if (!empty($items)) {
                foreach ($items as $row) {
                    DB::table('insumo_ot')->insert([
                        'orden_trabajo_id' => $orden->id,
                        'insumo_id'        => $row['id'],
                        'cantidad'         => $row['cantidad'],
                    ]);
                }
            }
        });

        $changed = $oldEstadoId !== (int) $data['estado_id'];
        if ($changed) {
            try {
                $cliente  = optional($orden->vehiculo)->clientes?->first();
                $telefono = $cliente?->telefono ?? null;
                $to = UltraMsg::normalizePhone($telefono);

                if ($to) {
                    $orden->load('estado');
                    $newEstadoName = $orden->estado?->nombre ?? '—';

                    $wa = new UltraMsg;
                    $wa->sendText($to, OrderWaTemplates::statusChanged($orden, $oldEstadoName, $newEstadoName));
                }
            } catch (\Throwable $e) {
                \Log::warning('WA status change failed: '.$e->getMessage());
            }
        }

        return back()->with('success', 'Cambios guardados correctamente.');
    }


    public function destroy(OrdenTrabajo $orden)
    {
        $orden->delete(); // las pivote se borran solitas
        return back()->with('success', 'Orden de trabajo eliminada correctamente.');
    }

    private static function normalizeChecks(array $in): array
    {
        $keys = [
            'filtro_aceite','filtro_aire','filtro_a_acondicionado','filtro_caja',
            'aceite_diferencial','filtro_combustible','aceite_hidraulico',
            'transfer','engrase'
        ];
        $out = [];
        foreach ($keys as $k) { $out[$k] = !empty($in[$k]); }

        if (!empty($in['aceite_caja']) && empty($out['filtro_a_acondicionado'])) {
        $out['filtro_a_acondicionado'] = true;
        }
        return $out;
    }



}

