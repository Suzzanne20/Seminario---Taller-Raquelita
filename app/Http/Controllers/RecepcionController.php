<?php

namespace App\Http\Controllers;

use App\Models\Recepcion;
use App\Models\Vehiculo;
use App\Models\Foto;
use App\Models\User;
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
            ->paginate(12)
            ->appends(['q' => $q]);

        return view('Inspeccion.tabla_isp', compact('items'));
    }

    /** Formulario crear */
    public function create()
    {
        // Tipos de vehículo
        $tipos = DB::table('type_vehiculo')->select('id','descripcion')->orderBy('id')->get();
        if ($tipos->isEmpty()) {
            $tipos = collect([
                (object)['id'=>1,'descripcion'=>'Carro estándar'],
                (object)['id'=>2,'descripcion'=>'Pick-up'],
                (object)['id'=>3,'descripcion'=>'Camioneta'],
            ]);
        }

        // Placas registradas
        $placas = Vehiculo::orderBy('placa')->pluck('placa')->all();

        // Técnicos con rol “mecánico”
        $tecnicos = User::whereHas('roles', fn($q)=> $q->whereRaw('LOWER(name)=?',['mecanico']))
                        ->orderBy('name')
                        ->get(['id','name']);

        return view('Inspeccion.registrar_isp', compact('tipos','placas','tecnicos'));
    }

    /** Guardar recepción + fotos (BLOB) + detalles (JSON) */
    public function store(Request $request)
    {
        // Normaliza placa
        if ($request->filled('vehiculo_placa')) {
            $request->merge([
                'vehiculo_placa' => strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->vehiculo_placa))
            ]);
        }

        $data = $request->validate(
            [
                'vehiculo_placa'   => ['required','string','size:7','regex:/^[A-Z0-9]{7}$/','exists:vehiculo,placa'],
                'type_vehiculo_id' => ['required','integer','exists:type_vehiculo,id'],
                'observaciones'    => ['nullable','string','max:255'],
                'tecnico_id'       => [
                    'nullable','integer','exists:users,id',
                    function ($attr, $value, $fail) {
                        if ($value && !$this->userEsMecanico((int)$value)) {
                            $fail('El usuario seleccionado no tiene el rol de Mecánico.');
                        }
                    },
                ],
                'fotos'            => ['nullable'],
                'fotos.*'          => ['nullable'],
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

        // Observaciones: SOLO el texto del usuario
        $obsTexto = $data['observaciones'] ?? null;

        // Puntos por sección
        $sections = ['front','top','right','left','back'];
        $detalles = $this->parseDetallesSecciones($request->input('detalles_json'));

        try {
            DB::beginTransaction();

            // Verificación extra FK de placa
            if (!Vehiculo::where('placa', $data['vehiculo_placa'])->exists()) {
                throw new \RuntimeException('Vehículo no existe');
            }

            // Crear recepción
            $rec = Recepcion::create([
                'fecha_creacion'   => now(),
                'vehiculo_placa'   => $data['vehiculo_placa'],
                'type_vehiculo_id' => (int) $data['type_vehiculo_id'],
                'observaciones'    => $obsTexto,                     // solo texto
                'detalles_json'    => null,                          // se actualiza al final
                'id_tecnico'       => $data['tecnico_id'] ?? null,   // columna dedicada
            ]);

            // Guardar fotos (BLOB) y enlazar por índice al punto
            $fotos = $request->file('fotos', null);
            if ($fotos) {
                $this->adjuntarFotosYEnlazarPuntos($rec, $fotos, $detalles, $sections);
            }

            // Guardar los puntos enriquecidos
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

    /** Mostrar recepción (precarga técnico y fotos) */
    public function show(Recepcion $rec)
    {
        $rec->load(['fotos', 'tecnicoRel']); // <-- asegúrate de tener la relación en el modelo
        return view('Inspeccion.ver_isp', compact('rec'));
    }

    /** Editar (si luego lo usas) */
    public function edit(Recepcion $rec)
    {
        $tipos = DB::table('type_vehiculo')->select('id','descripcion')->orderBy('id')->get();
        return view('Inspeccion.editar_isp', compact('rec','tipos'));
    }

    /** Actualizar (incluye id_tecnico si decides editarlo ahí) */
    public function update(Request $request, Recepcion $rec)
    {
        if ($request->filled('vehiculo_placa')) {
            $request->merge([
                'vehiculo_placa' => strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $request->vehiculo_placa))
            ]);
        }

        $data = $request->validate([
            'vehiculo_placa'   => ['required','string','size:7','regex:/^[A-Z0-9]{7}$/','exists:vehiculo,placa'],
            'type_vehiculo_id' => ['required','integer','exists:type_vehiculo,id'],
            'observaciones'    => ['nullable','string','max:255'],
            'tecnico_id'       => [
                'nullable','integer','exists:users,id',
                function ($attr, $value, $fail) {
                    if ($value && !$this->userEsMecanico((int)$value)) {
                        $fail('El usuario seleccionado no tiene el rol de Mecánico.');
                    }
                },
            ],
            'detalles_json'    => ['nullable','string'],
            'fotos'            => ['nullable'],
            'fotos.*'          => ['nullable'],
            'fotos.*.*'        => ['nullable','image','max:4096'],
        ]);

        // Observaciones: SOLO el texto
        $obsTexto = $data['observaciones'] ?? null;
        $sections = ['front','top','right','left','back'];

        // Puntos entrantes
        $incoming = $this->parseDetallesSecciones($request->input('detalles_json'));

        // Preservar foto_id/stream_url si ya existían
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
            $rec->vehiculo_placa   = $data['vehiculo_placa'];
            $rec->type_vehiculo_id = (int) $data['type_vehiculo_id'];
            $rec->observaciones    = $obsTexto;
            $rec->id_tecnico       = $data['tecnico_id'] ?? null;
            $rec->save();

            // Nuevas fotos (opcional)
            $fotos = $request->file('fotos', null);
            if ($fotos) {
                $this->adjuntarFotosYEnlazarPuntos($rec, $fotos, $incoming, $sections);
            }

            // Guardar puntos
            $rec->detalles_json = $incoming;
            $rec->save();

            DB::commit();
            return redirect()->route('inspecciones.show', $rec)->with('ok','Cambios guardados.');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error','No se pudo actualizar: '.$e->getMessage());
        }
    }

    /** Eliminar */
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

    /* ============================================================
     *               ENDPOINTS / UTILIDADES EXTRA
     * ============================================================ */

    /** GET JSON: técnicos (rol mecánico) con filtro q (para select2) */
    public function tecnicosLista(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $items = User::whereHas('roles', fn($r)=>$r->whereRaw('LOWER(name)=?',['mecanico']))
            ->when($q, fn($r)=>$r->where('name','like',"%{$q}%"))
            ->orderBy('name')
            ->limit(30)
            ->get(['id','name']);

        return response()->json($items);
    }

    /** GET JSON: placas con filtro q (para select2/autocomplete) */
    public function placasLista(Request $request)
    {
        $q = strtoupper(trim((string) $request->get('q')));
        $items = Vehiculo::when($q, fn($r)=>$r->where('placa','like',"%{$q}%"))
            ->orderBy('placa')
            ->limit(50)
            ->pluck('placa');

        return response()->json($items);
    }

    /** GET JSON: tipos de vehículo */
    public function tiposVehiculoLista()
    {
        $items = DB::table('type_vehiculo')
            ->orderBy('id')
            ->get(['id','descripcion']);
        return response()->json($items);
    }

    /** GET JSON: puntos/detalles de una recepción */
    public function puntos(Recepcion $rec)
    {
        return response()->json($rec->detalles_json ?: []);
    }

    /** Descargar ZIP de todas las fotos BLOB de la recepción */
    public function descargarZip(Recepcion $rec)
    {
        $rec->load('fotos');

        if ($rec->fotos->isEmpty()) {
            return back()->with('error','Esta inspección no tiene fotos para descargar.');
        }

        // Crear ZIP en memoria
        $zipFilename = "inspeccion_{$rec->id}_fotos.zip";
        $tmp = tempnam(sys_get_temp_dir(), 'zip_');

        $zip = new \ZipArchive();
        if ($zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error','No se pudo crear el ZIP.');
        }

        // Agregar cada foto al ZIP
        foreach ($rec->fotos as $i => $f) {
            // Detectar extensión básica por MIME
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($f->path_foto) ?: 'image/jpeg';
            $ext  = match ($mime) {
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                default      => 'jpg'
            };
            $nameInZip = sprintf('%03d_%s.%s', $i+1, $f->descripcion ? preg_replace('/[^a-z0-9_\-]+/i','_',$f->descripcion) : 'foto', $ext);
            $zip->addFromString($nameInZip, $f->path_foto);
        }

        $zip->close();

        return response()->download($tmp, $zipFilename)->deleteFileAfterSend(true);
    }

    /* ====================== Helpers privados ===================== */

    /** Verifica si un user id tiene rol "mecánico" (case-insensitive) */
    private function userEsMecanico(int $userId): bool
    {
        return User::where('id', $userId)
            ->whereHas('roles', fn($q)=>$q->whereRaw('LOWER(name)=?',['mecanico']))
            ->exists();
    }

    /**
     * Normaliza el JSON de detalles por secciones a estructura:
     * [front|top|right|left|back] => [ {x,y,text?,foto_id?,stream_url?}, ... ]
     */
    private function parseDetallesSecciones(?string $json): array
    {
        $sections = ['front','top','right','left','back'];
        $out = [];

        if (!$json) {
            foreach ($sections as $s) $out[$s] = [];
            return $out;
        }

        $tmp = json_decode($json, true) ?: [];
        foreach ($sections as $sec) {
            $arr = $tmp[$sec] ?? [];
            $out[$sec] = array_values(array_filter($arr, fn($it) =>
                is_array($it) && isset($it['x'],$it['y'])
            ));
        }

        return $out;
    }

    /**
     * Adjunta fotos (BLOB) a la recepción y enlaza a puntos por índice.
     * Soporta estructura seccionada: fotos[front][], fotos[top][], etc.
     */
    private function adjuntarFotosYEnlazarPuntos(Recepcion $rec, $fotos, array &$detalles, array $sections): void
    {
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
            // Caso simple: asigna en orden a los primeros puntos sin foto
            $files = is_array($fotos) ? $fotos : [$fotos];
            foreach ($files as $file) {
                if (!$file || !$file->isValid()) continue;

                $binary = file_get_contents($file->getRealPath());
                $foto = Foto::create([
                    'path_foto'    => $binary,
                    'descripcion'  => '',
                    'recepcion_id' => $rec->id,
                ]);

                // Busca el primer punto libre en cualquier sección
                $asignada = false;
                foreach ($sections as $sec) {
                    foreach ($detalles[$sec] as $i => $p) {
                        if (!isset($p['foto_id'])) {
                            $detalles[$sec][$i]['foto_id']    = $foto->id;
                            $detalles[$sec][$i]['stream_url'] = route('fotos.stream', $foto);
                            $asignada = true;
                            break 2;
                        }
                    }
                }

                // Si no hay punto libre, igual guardamos la foto “huérfana”
                if (!$asignada) {
                    // Nada más que hacer; seguirá apareciendo en la galería
                }
            }
        }
    }
}
