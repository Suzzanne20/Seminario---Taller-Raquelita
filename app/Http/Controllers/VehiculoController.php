<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Marca;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $vehiculos = Vehiculo::with(['marca'])
            ->whereHas('marca', function($query) {
                $query->where('activo', true);
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('placa', 'like', "%{$q}%")
                    ->orWhere('linea', 'like', "%{$q}%")
                    ->orWhereHas('marca', fn($m) => $m->where('nombre', 'like', "%{$q}%"));
                });
            })
            ->orderBy('placa')
            ->paginate(10)
            ->withQueryString();

        return view('vehiculos.index', compact('vehiculos', 'q'));
    }

    public function create()
    {
        // Mostrar marcas activas + marcas inactivas con mostrar_en_registro = true
        $marcas = Marca::where('activo', true)
            ->orWhere('mostrar_en_registro', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'activo', 'mostrar_en_registro']);
            
        return view('vehiculos.create', compact('marcas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'placa'      => [
                'required', 
                'string', 
                'max:7', 
                'unique:vehiculo,placa',
                'regex:/^[A-Z][0-9]{3}[A-Z]{3}$/'
            ],
            'marca_id'   => 'required|integer|exists:marca,id',
            'modelo'     => 'required|integer',
            'linea'      => 'required|string|max:45',
            'motor'      => 'required|string|max:45',
            'cilindraje' => 'required|numeric',
            
            // 🔽 NUEVOS CAMPOS (nullable)
            'cantidad_aceite_motor' => 'nullable|string|max:45',
            'marca_aceite' => 'nullable|string|max:45',
            'tipo_aceite' => 'nullable|string|max:45',
            'filtro_aceite' => 'nullable|string|max:45',
            'filtro_aire' => 'nullable|string|max:45',
            'cantidad_aceite_cc' => 'nullable|string|max:45',
            'marca_cc' => 'nullable|string|max:45',
            'tipo_aceite_cc' => 'nullable|string|max:45',
            'filtro_aceite_cc' => 'nullable|string|max:45',
            'filtro_de_enfriador' => 'nullable|string|max:45',
            'tipo_caja' => 'nullable|string|max:45',
            'cantidad_aceite_diferencial' => 'nullable|string|max:45',
            'marca_aceite_d' => 'nullable|string|max:45',
            'tipo_aceite_d' => 'nullable|string|max:45',
            'cantidad_aceite_transfer' => 'nullable|string|max:45',
            'marca_aceite_t' => 'nullable|string|max:45',
            'tipo_aceite_t' => 'nullable|string|max:45',
            'filtro_cabina' => 'nullable|string|max:45',
            'filtro_diesel' => 'nullable|string|max:45',
            'contra_filtro_diesel' => 'nullable|string|max:45',
            'candelas' => 'nullable|string|max:45',
            'pastillas_delanteras' => 'nullable|string|max:45',
            'pastillas_traseras' => 'nullable|string|max:45',
            'fajas' => 'nullable|string|max:45',
            'aceite_hidraulico' => 'nullable|string|max:45',
        ], [
            'placa.regex' => 'La placa debe tener el formato: 1 letra + 3 números + 3 letras (ejemplo: P123ABC)'
        ]);

        Vehiculo::create($validated);

        // Verificar si la marca está deshabilitada para mostrar advertencia
        $marca = Marca::find($validated['marca_id']);
        if (!$marca->activo) {
            return redirect()
                ->route('vehiculos.index')
                ->with('warning', 'Vehículo registrado correctamente. NOTA: La marca ' . $marca->nombre . ' está deshabilitada, por lo que este vehículo no se mostrará en el listado principal.');
        }

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo registrado correctamente.');
    }

    public function edit(string $placa)
    {
        $vehiculo = Vehiculo::with('marca')->findOrFail($placa);
        // En edición mostramos todas las marcas (activas e inactivas) para permitir cambios
        $marcas = Marca::orderBy('nombre')->get(['id', 'nombre', 'activo']);
        return view('vehiculos.edit', compact('vehiculo', 'marcas'));
    }
    
    public function update(Request $request, string $placa)
    {
        $vehiculo = Vehiculo::findOrFail($placa);

        $validated = $request->validate([
            'marca_id'   => 'required|integer|exists:marca,id',
            'modelo'     => 'required|integer',
            'linea'      => 'required|string|max:45',
            'motor'      => 'required|string|max:45',
            'cilindraje' => 'required|numeric',
            
            // 🔽 NUEVOS CAMPOS (nullable)
            'cantidad_aceite_motor' => 'nullable|string|max:45',
            'marca_aceite' => 'nullable|string|max:45',
            'tipo_aceite' => 'nullable|string|max:45',
            'filtro_aceite' => 'nullable|string|max:45',
            'filtro_aire' => 'nullable|string|max:45',
            'cantidad_aceite_cc' => 'nullable|string|max:45',
            'marca_cc' => 'nullable|string|max:45',
            'tipo_aceite_cc' => 'nullable|string|max:45',
            'filtro_aceite_cc' => 'nullable|string|max:45',
            'filtro_de_enfriador' => 'nullable|string|max:45',
            'tipo_caja' => 'nullable|string|max:45',
            'cantidad_aceite_diferencial' => 'nullable|string|max:45',
            'marca_aceite_d' => 'nullable|string|max:45',
            'tipo_aceite_d' => 'nullable|string|max:45',
            'cantidad_aceite_transfer' => 'nullable|string|max:45',
            'marca_aceite_t' => 'nullable|string|max:45',
            'tipo_aceite_t' => 'nullable|string|max:45',
            'filtro_cabina' => 'nullable|string|max:45',
            'filtro_diesel' => 'nullable|string|max:45',
            'contra_filtro_diesel' => 'nullable|string|max:45',
            'candelas' => 'nullable|string|max:45',
            'pastillas_delanteras' => 'nullable|string|max:45',
            'pastillas_traseras' => 'nullable|string|max:45',
            'fajas' => 'nullable|string|max:45',
            'aceite_hidraulico' => 'nullable|string|max:45',
        ]);

        $vehiculo->update($validated);

        // Verificar si la nueva marca está deshabilitada
        $marca = Marca::find($validated['marca_id']);
        if (!$marca->activo) {
            return redirect()
                ->route('vehiculos.index')
                ->with('warning', 'Vehículo actualizado correctamente. NOTA: La marca ' . $marca->nombre . ' está deshabilitada, por lo que este vehículo no se mostrará en el listado principal.');
        }

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    public function destroy(string $placa)
    {
        $vehiculo = Vehiculo::findOrFail($placa);

        try {
            // Verificar si el vehículo tiene órdenes de trabajo asociadas
            if ($vehiculo->ordenes()->count() > 0) {
                return redirect()
                    ->route('vehiculos.index')
                    ->with('error', 'No se puede eliminar el vehículo porque tiene órdenes de trabajo asociadas. Primero elimine las órdenes de trabajo relacionadas.');
            }

            $vehiculo->delete();

            return redirect()
                ->route('vehiculos.index')
                ->with('success', 'Vehículo eliminado correctamente.');

        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar error de integridad referencial
            if ($e->getCode() == 23000) {
                return redirect()
                    ->route('vehiculos.index')
                    ->with('error', 'No se puede eliminar el vehículo porque está siendo utilizado en órdenes de trabajo. Elimine primero las órdenes de trabajo asociadas.');
            }
            
            // Otros errores de base de datos
            return redirect()
                ->route('vehiculos.index')
                ->with('error', 'Error al eliminar el vehículo: ' . $e->getMessage());
        }
    }

    // Método para verificar dependencias antes de eliminar
    public function checkDependencies(string $placa)
    {
        $vehiculo = Vehiculo::findOrFail($placa);
        $ordenesCount = $vehiculo->ordenes()->count();
        
        return response()->json([
            'tiene_ordenes' => $ordenesCount > 0,
            'ordenes_count' => $ordenesCount
        ]);
    }

    // MÉTODO PARA AGREGAR MARCAS DESDE EL FORMULARIO
    public function storeMarca(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:45|unique:marca,nombre'
        ]);

        $marca = Marca::create([
            'nombre' => $request->nombre,
            'activo' => true // Nueva marca siempre activa por defecto
        ]);

        return response()->json([
            'success' => true,
            'marca' => $marca
        ]);
    }

    // MÉTODOS PARA GESTIÓN DE MARCAS
    public function indexMarcas(Request $request)
    {
        $q = trim($request->get('q', ''));

        $marcas = Marca::withCount('vehiculos')
            ->when($q, function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%");
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('vehiculos.marcas', compact('marcas', 'q'));
    }

    public function desactivarMarca($id)
    {
        $marca = Marca::findOrFail($id);
        $marca->update(['activo' => false]);

        return redirect()
            ->route('marcas.index')
            ->with('info', "Marca {$marca->nombre} deshabilitada correctamente."); // Cambiado a 'info'
    }

    // MÉTODO PARA ACTIVAR MARCA
    public function activarMarca($id)
    {
        $marca = Marca::findOrFail($id);
        $marca->update(['activo' => true]);

        return redirect()
            ->route('marcas.index')
            ->with('info', "Marca {$marca->nombre} activada correctamente."); // Cambiado a 'info'
    }

    public function destroyMarca($id)
    {
        $marca = Marca::findOrFail($id);

        // Verificar si la marca está siendo usada por algún vehículo
        if ($marca->vehiculos()->count() > 0) {
            return redirect()
                ->route('marcas.index')
                ->with('error', 'No se puede eliminar la marca porque está asociada a vehículos.');
        }

        $marcaNombre = $marca->nombre;
        $marca->delete();

        return redirect()
            ->route('marcas.index')
            ->with('success', "Marca {$marcaNombre} eliminada permanentemente.")
            ->with('marca_eliminada', true); // Bandera para identificar eliminación
    }

    public function toggleMostrarEnRegistro($id)
    {
        $marca = Marca::findOrFail($id);
        
        // Solo se puede cambiar en marcas inactivas
        if (!$marca->activo) {
            $marca->update([
                'mostrar_en_registro' => !$marca->mostrar_en_registro
            ]);
            
            $estado = $marca->mostrar_en_registro ? 'mostrar' : 'ocultar';
            return redirect()
                ->route('marcas.index')
                ->with('info', "La marca {$marca->nombre} se {$estado}á al registrar nuevos vehículos."); // Cambiado a 'info'
        }

        return redirect()
            ->route('marcas.index')
            ->with('info', 'Las marcas activas siempre se muestran en el registro.'); // Ya estaba como 'info'
    }

    public function detalles($placa)
    {
        $vehiculo = Vehiculo::with('marca')
            ->where('placa', $placa)
            ->firstOrFail();

        return response()->json([
            'placa' => $vehiculo->placa,
            'marca' => $vehiculo->marca->nombre ?? '—',
            'modelo' => $vehiculo->modelo,
            'linea' => $vehiculo->linea,
            'motor' => $vehiculo->motor,
            'cilindraje' => $vehiculo->cilindraje,
            'cantidad_aceite_motor' => $vehiculo->cantidad_aceite_motor,
            'marca_aceite' => $vehiculo->marca_aceite,
            'tipo_aceite' => $vehiculo->tipo_aceite,
            'filtro_aceite' => $vehiculo->filtro_aceite,
            'filtro_aire' => $vehiculo->filtro_aire,
            'cantidad_aceite_cc' => $vehiculo->cantidad_aceite_cc,
            'marca_cc' => $vehiculo->marca_cc,
            'tipo_aceite_cc' => $vehiculo->tipo_aceite_cc,
            'filtro_aceite_cc' => $vehiculo->filtro_aceite_cc,
            'filtro_de_enfriador' => $vehiculo->filtro_de_enfriador,
            'tipo_caja' => $vehiculo->tipo_caja,
            'cantidad_aceite_diferencial' => $vehiculo->cantidad_aceite_diferencial,
            'marca_aceite_d' => $vehiculo->marca_aceite_d,
            'tipo_aceite_d' => $vehiculo->tipo_aceite_d,
            'cantidad_aceite_transfer' => $vehiculo->cantidad_aceite_transfer,
            'marca_aceite_t' => $vehiculo->marca_aceite_t,
            'tipo_aceite_t' => $vehiculo->tipo_aceite_t,
            'filtro_cabina' => $vehiculo->filtro_cabina,
            'filtro_diesel' => $vehiculo->filtro_diesel,
            'contra_filtro_diesel' => $vehiculo->contra_filtro_diesel,
            'pastillas_delanteras' => $vehiculo->pastillas_delanteras,
            'pastillas_traseras' => $vehiculo->pastillas_traseras,
            'candelas' => $vehiculo->candelas,
            'fajas' => $vehiculo->fajas,
            'aceite_hidraulico' => $vehiculo->aceite_hidraulico,
        ]);
    }
}