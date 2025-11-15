<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InsumosCsvSeeder extends Seeder
{
    private string $csvPath = 'database/seeders/data/insumos.csv';

    // Tipo por defecto si el CSV no trae tipo/categoría
    private int $defaultTypeId = 10;

    private function utf($s) {
        if ($s === null) return null;
        $s = @mb_convert_encoding($s, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        return preg_replace('/[^\P{C}\n\r\t]+/u', '', $s);
    }

    private function codigoStr($v, int $len = 4): ?string {
    if ($v === null) return null;
    $s = trim((string)$v);
    if ($s === '') return null;
    // deja solo dígitos
    $s = preg_replace('/\D+/', '', $s);
    if ($s === '') return null;
    // rellena a la izquierda con ceros hasta la longitud deseada
    return str_pad($s, $len, '0', STR_PAD_LEFT);
}

    public function run(): void
    {
        $path = base_path($this->csvPath);
        if (!is_file($path)) {
            $this->command->error("No se encontró el CSV en: {$path}");
            return;
        }

        [$rows, $delim] = $this->readCsv($path);
        $this->command->line("Delimitador CSV detectado: " . ($delim === "\t" ? "\\t" : $delim));
        $this->command->line("Filas leídas del CSV (sin encabezado): " . count($rows));

        if (empty($rows)) {
            $this->command->warn('El CSV está vacío o no tiene filas válidas.');
            return;
        }

        $now   = Carbon::now();
        $batch = [];
        $ok    = 0;

        DB::beginTransaction();
        try {
            foreach ($rows as $r) {

                $codigo = $this->codigoStr($this->first($r, ['codigo','code','sku','clave']), 4);
                if ($codigo === null) continue; // sin código, saltamos

                // Campos aceptados (encabezados alternos)
                $nombre      = $this->first($r, ['nombre','producto','item','articulo']);
                if (!$nombre) continue;

                $costo       = $this->f($this->first($r, ['costo','costo_unitario','coste']));
                $stock       = $this->i($this->first($r, ['stock','existencias','cantidad']));
                $stockMin    = $this->i($this->first($r, ['stock_minimo','minimo','min']), 0);
                $descripcion = $this->first($r, ['descripcion','detalle','observaciones']);
                $precio      = $this->f($this->first($r, ['precio','pvp','venta','precio_venta']));

                // type_insumo_id directo o por nombre (tipo/categoria)
                $typeId = $this->i($this->first($r, ['type_insumo_id']));
                if (!$typeId) {
                    $tipoNombre = $this->first($r, ['tipo','categoria','rubro']);
                    if ($tipoNombre) {
                        $val = trim($tipoNombre);
                        $found = DB::table('type_insumo')
                            ->whereRaw('LOWER(nombre)=?', [Str::lower($val)])
                            ->first();
                        if ($found) {
                            $typeId = (int) $found->id;
                        } else {
                            $typeId = DB::table('type_insumo')->insertGetId([
                                'nombre'     => $val,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);
                        }
                    } else {
                        $typeId = $this->defaultTypeId;
                    }
                }

                $batch[] = [
                    'codigo'         => $codigo,                                    // NUEVO
                    'nombre'         => Str::limit($this->utf(trim($nombre)), 50, ''),
                    'costo'          => $costo,
                    'stock'          => $stock,
                    'stock_minimo'   => $stockMin ?? 0,
                    'descripcion'    => $descripcion ? Str::limit($this->utf(trim($descripcion)), 200, '') : null,
                    'type_insumo_id' => $typeId,
                    'precio'         => $precio,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];

                // Inserciones por lotes
                if (count($batch) >= 500) {
                    $this->flushBatch($batch, $ok);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $this->flushBatch($batch, $ok);
            }

            DB::commit();
            $this->command->info("Importación completa. Filas procesadas: {$ok}");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('Error al importar CSV: '.$e->getMessage());
            throw $e;
        }
    }

    private function flushBatch(array $batch, int &$ok): void
    {
        // Preferimos upsert por 'codigo' (nuevo campo clave); si no existiera índice único,
        // actuará como insert (no falla). De respaldo, podrías cambiar a ['nombre'] si lo necesitas.
        DB::table('insumo')->upsert(
            $batch,
            ['codigo'], // clave de conflicto preferida
            ['nombre','costo','stock','stock_minimo','descripcion','type_insumo_id','precio','updated_at']
        );
        $ok += count($batch);
    }

    /**
     * Lee CSV devolviendo: [ filas_asociativas, delimitador_usado ]
     */
    private function readCsv(string $path): array
    {
        $raw = file_get_contents($path);
        if ($raw === false) return [[], ','];

        // Limpia BOM UTF-8
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

        // Detecta delimitador en la primera línea
        $firstLine = strtok($raw, "\r\n");
        $cComa = substr_count($firstLine, ',');
        $cPtoyComa = substr_count($firstLine, ';');
        $cTab = substr_count($firstLine, "\t");

        $delim = ',';
        if ($cPtoyComa > $cComa && $cPtoyComa >= $cTab) $delim = ';';
        if ($cTab > $cComa && $cTab > $cPtoyComa)       $delim = "\t";

        $rows = [];
        $headers = null;

        $h = fopen('php://memory', 'r+');
        fwrite($h, $raw);
        rewind($h);

        while (($data = fgetcsv($h, 0, $delim)) !== false) {
            if ($headers === null) {
                $headers = array_map(function ($x) {
                    $x = trim((string)$x);
                    $x = preg_replace('/\s+/', ' ', $x);
                    return Str::slug(mb_strtolower($x, 'UTF-8'), '_');
                }, $data);
                continue;
            }

            $row = [];
            foreach ($headers as $i => $key) {
                $row[$key] = isset($data[$i]) ? trim((string)$data[$i]) : '';
            }

            if (collect($row)->filter(fn($v)=>$v!=='')->isNotEmpty()) {
                $rows[] = $row;
            }
        }
        fclose($h);

        return [$rows, $delim];
    }

    private function first(array $row, array $keys, $default = null)
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $row) && trim((string)$row[$k]) !== '') {
                return $row[$k];
            }
        }
        return $default;
    }

    private function f($v): ?float
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '') return null;

        if (preg_match('/^\d{1,3}(\.\d{3})*,\d+$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            $s = str_replace(',', '', $s);
        }
        return is_numeric($s) ? (float)$s : null;
    }

    private function i($v, $default = null): ?int
    {
        if ($v === null) return $default;
        $s = trim((string)$v);
        if ($s === '') return $default;
        return (int) preg_replace('/[^\d\-]/', '', $s);
    }
}
