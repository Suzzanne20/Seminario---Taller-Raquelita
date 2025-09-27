<?php

namespace App\Http\Controllers;

use App\Models\Recepcion;
use App\Models\Vehiculo;
use App\Models\Foto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema; // para verificar columnas opcionales
use Throwable;

class RecepcionController extends Controller
{
    /** Inicio (opcional) */
    public function start()
    {
        return view('Inspeccion.inicio');
    }

    /** Listado (opcional) */
    public function index()
    {
        $items = Recepcion::orderByDesc('id')->paginate(12);
        return view('Inspeccion.tabla_isp', compact('items'));
    }

    /** Formulario: carga tipos desde BD y si está vacío usa fallback */
    public function create()
    {
        $tipos = DB::table('type_vehiculo')
            ->select('id','descripcion')
            ->orderBy('id')
            ->get();

        if ($tipos->isEmpty()) {
            // Fallback temporal por si la tabla está vacía
            $tipos = collect([
                (object)['id'=>1,'descripcion'=>'Carro estándar'],
                (object)['id'=>2,'descripcion'=>'Pick-up'],
                (object)['id'=>3,'descripcion'=>'Camioneta'],
            ]);
        }

        return view('Inspeccion.registrar_isp', compact('tipos'));
    }

    /** GUARDAR con verificación + mensajes */
    public function store(Request $request)
    {
        // Normaliza la placa: mayúsculas, solo A-Z0-9
        $placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->input('vehiculo_placa')));
        $request->merge(['vehiculo_placa' => $placa]);

        // Validación estricta
        $data = $request->validate(
            [
                'vehiculo_placa'   => ['required','string','size:7','regex:/^[A-Z0-9]{7}$/','exists:vehiculo,placa'],
                'type_vehiculo_id' => ['required','integer','exists:type_vehiculo,id'],
                'observaciones'    => ['nullable','string','max:255'],
                'tecnico'          => ['nullable','string','max:120'],
                'fecha'            => ['nullable','date'],
                'fotos.*.*'        => ['nullable','image','max:4096'],
                'detalles_json'    => ['nullable','string'],
            ],
            [
                'vehiculo_placa.exists'   => 'La placa no está registrada en la base de datos.',
                'vehiculo_placa.size'     => 'La placa debe tener exactamente 7 caracteres.',
                'vehiculo_placa.regex'    => 'La placa solo puede contener letras y números (sin guiones).',
                'type_vehiculo_id.exists' => 'El tipo de vehículo no es válido.',
            ]
        );

        // Observaciones (técnico / fecha + texto libre)
        $metaObs = '';
        if ($request->filled('tecnico')) $metaObs .= 'Tec: '.$request->tecnico.'. ';
        if ($request->filled('fecha'))   $metaObs .= 'Fecha: '.$request->fecha.'. ';
        if (!empty($data['observaciones'])) $metaObs .= $data['observaciones'];

        // Mapear textos para fotos (desde el JSON de la vista)
        $mapText = [];
        if ($request->filled('detalles_json')) {
            $json = json_decode($request->input('detalles_json'), true);
            foreach (['front','top','right','left','back'] as $sec) {
                if (isset($json[$sec]) && is_array($json[$sec])) {
                    $mapText[$sec] = array_map(fn($it) => $it['text'] ?? null, $json[$sec]);
                }
            }
        }

        try {
            DB::beginTransaction();

            // Revalida existencia del vehículo (coherente con FK)
            if (!Vehiculo::where('placa', $placa)->exists()) {
                throw new \RuntimeException('Vehículo no existe');
            }

            $fechaCreacion = $request->filled('fecha')
                ? $request->date('fecha')->startOfDay()
                : now();

            // Crear recepción
            $rec = Recepcion::create([
                'fecha_creacion'   => $fechaCreacion,
                'vehiculo_placa'   => $placa,
                'type_vehiculo_id' => (int) $data['type_vehiculo_id'],
                'observaciones'    => $metaObs,
            ]);

            // Verificación explícita en BD
            $ok = $rec && Recepcion::whereKey($rec->id)->exists();
            if (!$ok) throw new \RuntimeException('No se pudo verificar la inserción.');

            /* ==================== GUARDAR FOTOS (ROBUSTO + VARCHAR(45)) ==================== */
            // Requiere: php artisan storage:link
            $filesBySection = $request->file('fotos', []);   // <- si no hay, queda []

            if (!empty($filesBySection)) {
                $disk = 'public';
                $dir  = "inspecciones/{$placa}/{$rec->id}"; // carpeta real en storage

                foreach ($filesBySection as $seccion => $files) {
                    if (!is_array($files)) continue;

                    foreach ($files as $idx => $file) {
                        if (!$file || !$file->isValid()) continue;

                        // Generar nombre corto y único (cabe en VARCHAR(45))
                        $ext      = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                        $short    = substr(bin2hex(random_bytes(12)), 0, 20); // 20 chars
                        $filename = $short . '.' . $ext;

                        // Guardar archivo: storage/app/public/inspecciones/PLACA/ID/NOMBRE.ext
                        $file->storeAs($dir, $filename, $disk);

                        // Descripción alineada al índice del punto marcado
                        $desc = $mapText[$seccion][$idx] ?? '';

                        // Insertar fila en BD: SOLO el nombre (no la ruta completa)
                        Foto::create([
                            'path_foto'    => $filename,  // <= 45 chars
                            'descripcion'  => $desc,
                            'recepcion_id' => $rec->id,
                        ]);
                    }
                }
            }
            /* ============================================================================ */

            DB::commit();
            return back()->with('ok', 'Se guardó correctamente.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'No se pudo guardar.');
        }
    }

    /** ======================= NUEVO: VER (solo lectura) ======================= */
    public function show(Recepcion $rec)
    {
        return view('Inspeccion.ver_isp', compact('rec'));
    }

    /** ======================= NUEVO: EDITAR (carga tipos) ===================== */
    public function edit(Recepcion $rec)
    {
        $tipos = DB::table('type_vehiculo')
            ->select('id','descripcion')
            ->orderBy('id')
            ->get();

        return view('Inspeccion.editar_isp', compact('rec','tipos'));
    }

    /** ======================= NUEVO: ACTUALIZAR =============================== */
    public function update(Request $request, Recepcion $rec)
    {
        // Normaliza placa
        $placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->input('vehiculo_placa')));
        $request->merge(['vehiculo_placa' => $placa]);

        // Validación
        $data = $request->validate(
            [
                'vehiculo_placa'   => ['required','string','size:7','regex:/^[A-Z0-9]{7}$/','exists:vehiculo,placa'],
                'type_vehiculo_id' => ['required','integer','exists:type_vehiculo,id'],
                'observaciones'    => ['nullable','string','max:255'],
                'tecnico'          => ['nullable','string','max:120'],
                'fecha'            => ['nullable','date'],
                'fotos.*.*'        => ['nullable','image','max:4096'],
                'detalles_json'    => ['nullable','string'],
            ]
        );

        // Observaciones
        $metaObs = '';
        if ($request->filled('tecnico')) $metaObs .= 'Tec: '.$request->tecnico.'. ';
        if ($request->filled('fecha'))   $metaObs .= 'Fecha: '.$request->fecha.'. ';
        if (!empty($data['observaciones'])) $metaObs .= $data['observaciones'];

        // Mapear textos para fotos nuevas
        $mapText = [];
        if ($request->filled('detalles_json')) {
            $json = json_decode($request->input('detalles_json'), true);
            foreach (['front','top','right','left','back'] as $sec) {
                if (isset($json[$sec]) && is_array($json[$sec])) {
                    $mapText[$sec] = array_map(fn($it) => $it['text'] ?? null, $json[$sec]);
                }
            }
        }

        try {
            DB::beginTransaction();

            // Actualizar cabecera
            $rec->vehiculo_placa   = $placa;
            $rec->type_vehiculo_id = (int) $data['type_vehiculo_id'];
            $rec->observaciones    = $metaObs;

            // Si existe la columna detalles_json, la actualizamos
            if ($request->filled('detalles_json') && Schema::hasColumn('recepcion','detalles_json')) {
                $rec->detalles_json = $request->input('detalles_json');
            }

            $rec->save();

            // Guardar NUEVAS fotos (no borra las existentes)
            $filesBySection = $request->file('fotos', []);
            if (!empty($filesBySection)) {
                $disk = 'public';
                $dir  = "inspecciones/{$placa}/{$rec->id}";

                foreach ($filesBySection as $seccion => $files) {
                    if (!is_array($files)) continue;

                    foreach ($files as $idx => $file) {
                        if (!$file || !$file->isValid()) continue;

                        $ext      = strtolower($file->getClientOriginalExtension() ?: 'jpg');
                        $short    = substr(bin2hex(random_bytes(12)), 0, 20);
                        $filename = $short . '.' . $ext;

                        $file->storeAs($dir, $filename, $disk);

                        $desc = $mapText[$seccion][$idx] ?? '';

                        Foto::create([
                            'path_foto'    => $filename,
                            'descripcion'  => $desc,
                            'recepcion_id' => $rec->id,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('inspecciones.show', $rec)->with('ok','Cambios guardados.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error','No se pudo actualizar.');
        }
    }

    /** ======================= NUEVO: ELIMINAR ================================ */
    public function destroy(Recepcion $rec)
    {
        $baseDir = "inspecciones/{$rec->vehiculo_placa}/{$rec->id}";

        DB::beginTransaction();
        try {
            // Eliminar archivos físicos ligados a la recepción
            foreach ($rec->fotos as $foto) {
                if ($foto->path_foto) {
                    Storage::disk('public')->delete($baseDir . '/' . $foto->path_foto);
                }
            }
            // Borrar carpeta por si quedaron residuos
            Storage::disk('public')->deleteDirectory($baseDir);

            // Borrar registros dependientes y la recepción
            Foto::where('recepcion_id', $rec->id)->delete();
            $rec->delete();

            DB::commit();
            return redirect()->route('inspecciones.create')->with('ok','Inspección eliminada correctamente.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error','No se pudo eliminar la inspección.');
        }
    }
}
