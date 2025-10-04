<?php

namespace App\Http\Controllers;

use App\Models\Recepcion;
use App\Models\Vehiculo;
use App\Models\Foto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class RecepcionController extends Controller
{
    /** Inicio (opcional) */
    public function start()
    {
        return view('Inspeccion.inicio');
    }

    /** Listado + filtro por placa (?q=) */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $items = Recepcion::query()
            ->when($q, fn($query) =>
                $query->where('vehiculo_placa', 'LIKE', "%{$q}%")
            )
            ->orderByDesc('id')
            ->paginate(12);

        // Mantén el término de búsqueda en la paginación (si lo usas en la vista)
        $items->appends(['q' => $q]);

        return view('Inspeccion.tabla_isp', compact('items'));
    }

    /** Formulario crear */
    public function create()
    {
        $tipos = DB::table('type_vehiculo')
            ->select('id','descripcion')
            ->orderBy('id')
            ->get();

        if ($tipos->isEmpty()) {
            $tipos = collect([
                (object)['id'=>1,'descripcion'=>'Carro estándar'],
                (object)['id'=>2,'descripcion'=>'Pick-up'],
                (object)['id'=>3,'descripcion'=>'Camioneta'],
            ]);
        }

        return view('Inspeccion.registrar_isp', compact('tipos'));
    }

    /** Guardar recepción + FOTOS (BLOB) + DETALLES (puntos) */
    public function store(Request $request)
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

                // fotos puede venir agrupado por secciones (fotos[front][]) o simple
                'fotos'            => ['nullable'],
                'fotos.*'          => ['nullable'],
                'fotos.*.*'        => ['nullable','image','max:4096'],

                // JSON con puntos {front:[{x,y,text}], top:[], right:[], left:[], back:[]}
                'detalles_json'    => ['nullable','string'],
            ],
            [
                'vehiculo_placa.exists'   => 'La placa no está registrada en la base de datos.',
                'vehiculo_placa.size'     => 'La placa debe tener exactamente 7 caracteres.',
                'vehiculo_placa.regex'    => 'La placa solo puede contener letras y números (sin guiones).',
                'type_vehiculo_id.exists' => 'El tipo de vehículo no es válido.',
            ]
        );

        // Observaciones visibles
        $metaObs = '';
        if ($request->filled('tecnico')) $metaObs .= 'Tec: '.$request->tecnico.'. ';
        if ($request->filled('fecha'))   $metaObs .= 'Fecha: '.$request->fecha.'. ';
        if (!empty($data['observaciones'])) $metaObs .= $data['observaciones'];

        // Puntos por sección
        $sections = ['front','top','right','left','back'];
        $detalles = [];
        if ($request->filled('detalles_json')) {
            $tmp = json_decode($request->input('detalles_json'), true) ?: [];
            foreach ($sections as $sec) {
                $arr = $tmp[$sec] ?? [];
                $detalles[$sec] = array_values(array_filter($arr, fn($it) =>
                    is_array($it) && isset($it['x'],$it['y'])
                ));
            }
        } else {
            foreach ($sections as $sec) $detalles[$sec] = [];
        }

        try {
            DB::beginTransaction();

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
                'detalles_json'    => null, // se setea más abajo con enlaces de fotos
            ]);

            // Guardar fotos (BLOB) y enlazar a puntos por índice
            $fotos = $request->file('fotos', null);

            if ($fotos) {
                $isSeccionado = is_array($fotos) && (
                    isset($fotos['front']) || isset($fotos['top']) ||
                    isset($fotos['right']) || isset($fotos['left']) ||
                    isset($fotos['back'])
                );

                if ($isSeccionado) {
                    foreach ($fotos as $seccion => $files) {
                        if (!is_array($files)) $files = [$files];
                        foreach ($files as $idx => $file) {
                            if (!$file || !$file->isValid()) continue;

                            $binary = file_get_contents($file->getRealPath());
                            $foto = Foto::create([
                                'path_foto'    => $binary,   // MEDIUMBLOB
                                'descripcion'  => $detalles[$seccion][$idx]['text'] ?? '',
                                'recepcion_id' => $rec->id,
                            ]);

                            if (isset($detalles[$seccion][$idx])) {
                                $detalles[$seccion][$idx]['foto_id']    = $foto->id;
                                $detalles[$seccion][$idx]['stream_url'] = route('fotos.stream', $foto);
                            }
                        }
                    }
                } else {
                    // Caso simple: asigna en orden a los primeros puntos libres
                    $files = is_array($fotos) ? $fotos : [$fotos];
                    foreach ($files as $file) {
                        if (!$file || !$file->isValid()) continue;

                        $binary = file_get_contents($file->getRealPath());
                        $foto = Foto::create([
                            'path_foto'    => $binary,
                            'descripcion'  => '',
                            'recepcion_id' => $rec->id,
                        ]);

                        foreach ($sections as $sec) {
                            foreach ($detalles[$sec] as $i => $p) {
                                if (!isset($p['foto_id'])) {
                                    $detalles[$sec][$i]['foto_id']    = $foto->id;
                                    $detalles[$sec][$i]['stream_url'] = route('fotos.stream', $foto);
                                    continue 3;
                                }
                            }
                        }
                    }
                }
            }

            // Guardar puntos enriquecidos
            $rec->detalles_json = $detalles;
            $rec->save();

            DB::commit();
            return redirect()->route('inspecciones.show', $rec)->with('ok', 'Se guardó correctamente.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'No se pudo guardar: '.$e->getMessage());
        }
    }

    /** Mostrar recepción */
    public function show(Recepcion $rec)
    {
        return view('Inspeccion.ver_isp', compact('rec'));
    }

    /** Editar */
    public function edit(Recepcion $rec)
    {
        $tipos = DB::table('type_vehiculo')
            ->select('id','descripcion')
            ->orderBy('id')
            ->get();

        return view('Inspeccion.editar_isp', compact('rec','tipos'));
    }

    /** Actualizar + nuevas fotos (BLOB) + detalles_json */
    public function update(Request $request, Recepcion $rec)
    {
        $placa = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->input('vehiculo_placa')));
        $request->merge(['vehiculo_placa' => $placa]);

        $data = $request->validate(
            [
                'vehiculo_placa'   => ['required','string','size:7','regex:/^[A-Z0-9]{7}$/','exists:vehiculo,placa'],
                'type_vehiculo_id' => ['required','integer','exists:type_vehiculo,id'],
                'observaciones'    => ['nullable','string','max:255'],
                'tecnico'          => ['nullable','string','max:120'],
                'fecha'            => ['nullable','date'],
                'fotos'            => ['nullable'],
                'fotos.*'          => ['nullable'],
                'fotos.*.*'        => ['nullable','image','max:4096'],
                'detalles_json'    => ['nullable','string'],
            ]
        );

        $metaObs = '';
        if ($request->filled('tecnico')) $metaObs .= 'Tec: '.$request->tecnico.'. ';
        if ($request->filled('fecha'))   $metaObs .= 'Fecha: '.$request->fecha.'. ';
        if (!empty($data['observaciones'])) $metaObs .= $data['observaciones'];

        $sections = ['front','top','right','left','back'];

        // Puntos entrantes
        $incoming = [];
        if ($request->filled('detalles_json')) {
            $tmp = json_decode($request->input('detalles_json'), true) ?: [];
            foreach ($sections as $sec) {
                $arr = $tmp[$sec] ?? [];
                $incoming[$sec] = array_values(array_filter($arr, fn($it) =>
                    is_array($it) && isset($it['x'],$it['y'])
                ));
            }
        } else {
            foreach ($sections as $sec) $incoming[$sec] = [];
        }

        // Puntos actuales (para preservar foto_id si no cambia)
        $current = (array) ($rec->detalles_json ?? []);
        foreach ($sections as $sec) {
            $curr = $current[$sec] ?? [];
            foreach ($incoming[$sec] as $i => $p) {
                if (isset($curr[$i]['foto_id']) && !isset($incoming[$sec][$i]['foto_id'])) {
                    $incoming[$sec][$i]['foto_id']    = $curr[$i]['foto_id'];
                    $incoming[$sec][$i]['stream_url'] = $curr[$i]['stream_url'] ?? null;
                }
            }
        }

        try {
            DB::beginTransaction();

            // Cabecera
            $rec->vehiculo_placa   = $placa;
            $rec->type_vehiculo_id = (int) $data['type_vehiculo_id'];
            $rec->observaciones    = $metaObs;
            $rec->save();

            // Nuevas fotos
            $fotos = $request->file('fotos', null);

            if ($fotos) {
                $isSeccionado = is_array($fotos) && (
                    isset($fotos['front']) || isset($fotos['top']) ||
                    isset($fotos['right']) || isset($fotos['left']) ||
                    isset($fotos['back'])
                );

                if ($isSeccionado) {
                    foreach ($fotos as $seccion => $files) {
                        if (!is_array($files)) $files = [$files];
                        foreach ($files as $idx => $file) {
                            if (!$file || !$file->isValid()) continue;

                            $binary = file_get_contents($file->getRealPath());
                            $foto = Foto::create([
                                'path_foto'    => $binary,
                                'descripcion'  => $incoming[$seccion][$idx]['text'] ?? '',
                                'recepcion_id' => $rec->id,
                            ]);

                            if (isset($incoming[$seccion][$idx])) {
                                $incoming[$seccion][$idx]['foto_id']    = $foto->id;
                                $incoming[$seccion][$idx]['stream_url'] = route('fotos.stream', $foto);
                            }
                        }
                    }
                } else {
                    $files = is_array($fotos) ? $fotos : [$fotos];
                    foreach ($files as $file) {
                        if (!$file || !$file->isValid()) continue;

                        $binary = file_get_contents($file->getRealPath());
                        $foto = Foto::create([
                            'path_foto'    => $binary,
                            'descripcion'  => '',
                            'recepcion_id' => $rec->id,
                        ]);

                        foreach ($sections as $sec) {
                            foreach ($incoming[$sec] as $i => $p) {
                                if (!isset($p['foto_id'])) {
                                    $incoming[$sec][$i]['foto_id']    = $foto->id;
                                    $incoming[$sec][$i]['stream_url'] = route('fotos.stream', $foto);
                                    continue 3;
                                }
                            }
                        }
                    }
                }
            }

            $rec->detalles_json = $incoming; // array -> cast/JSON
            $rec->save();

            DB::commit();
            return redirect()->route('inspecciones.show', $rec)->with('ok','Cambios guardados.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error','No se pudo actualizar: '.$e->getMessage());
        }
    }

    /** Eliminar (BD y fotos BLOB relacionadas) */
    public function destroy(Recepcion $rec)
    {
        DB::beginTransaction();
        try {
            Foto::where('recepcion_id', $rec->id)->delete();
            $rec->delete();

            DB::commit();
            return redirect()->route('inspecciones.create')->with('ok','Inspección eliminada correctamente.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('error','No se pudo eliminar la inspección: '.$e->getMessage());
        }
    }

    /** Servir la imagen desde BLOB */
    public function streamFoto(Foto $foto)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->buffer($foto->path_foto) ?: 'image/jpeg';

        return response($foto->path_foto, 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=31536000');
    }
}
