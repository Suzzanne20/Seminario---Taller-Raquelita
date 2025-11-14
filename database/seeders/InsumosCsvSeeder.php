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
    // Fuerza a UTF-8 y elimina bytes inválidos
    $s = @mb_convert_encoding($s, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
    return preg_replace('/[^\P{C}\n\r\t]+/u', '', $s); // quita no imprimibles
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
                // Campos aceptados (encabezados alternos)
                $nombre      = $this->first($r, ['nombre','producto','item','articulo']);
                if (!$nombre) continue; // si no hay nombre, saltamos

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
                    'nombre'         => Str::limit($this->utf(trim($nombre)), 50, ''),     // VARCHAR(50)
                    'costo'          => $costo,                                           // DECIMAL(10,2) | null
                    'stock'          => $stock,                                           // INT | null
                    'stock_minimo'   => $stockMin ?? 0,                                   // INT (no nullable en tu migración)
                    'descripcion'    => $descripcion ? Str::limit($this->utf(trim($descripcion)), 200, '') : null,
                    'type_insumo_id' => $typeId,
                    'precio'         => $precio,                                          // DECIMAL(10,2) | null
                    'created_at'     => $now,
                    'updated_at'     => $now,




                ];

                // Inserciones por lotes
                if (count($batch) >= 500) {
                    DB::table('insumo')->upsert(
                        $batch,
                        ['nombre'], // evita duplicados por nombre
                        ['costo','stock','stock_minimo','descripcion','type_insumo_id','precio','updated_at']
                    );
                    $ok   += count($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('insumo')->upsert(
                    $batch,
                    ['nombre'],
                    ['costo','stock','stock_minimo','descripcion','type_insumo_id','precio','updated_at']
                );
                $ok += count($batch);
            }

            DB::commit();
            $this->command->info("Importación completa. Filas procesadas: {$ok}");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('Error al importar CSV: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Lee CSV devolviendo: [ filas_asociativas, delimitador_usado ]
     * - Auto-detecta delimitador (',', ';', o tab)
     * - Limpia BOM UTF-8
     * - Normaliza encabezados a snake_case
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

        // Ahora sí parseamos
        $rows = [];
        $headers = null;

        $h = fopen('php://memory', 'r+');
        fwrite($h, $raw);
        rewind($h);

        while (($data = fgetcsv($h, 0, $delim)) !== false) {
            if ($headers === null) {
                // Normaliza encabezados -> snake_case
                $headers = array_map(function ($x) {
                    $x = trim((string)$x);
                    $x = preg_replace('/\s+/', ' ', $x);
                    return Str::slug(mb_strtolower($x, 'UTF-8'), '_');
                }, $data);
                continue;
            }

            // Arma fila asociativa
            $row = [];
            foreach ($headers as $i => $key) {
                $row[$key] = isset($data[$i]) ? trim((string)$data[$i]) : '';
            }

            // descarta completamente vacías
            if (collect($row)->filter(fn($v)=>$v!=='')->isNotEmpty()) {
                $rows[] = $row;
            }
        }
        fclose($h);

        return [$rows, $delim];
    }

    /** Primer valor disponible por claves alternativas */
    private function first(array $row, array $keys, $default = null)
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $row) && trim((string)$row[$k]) !== '') {
                return $row[$k];
            }
        }
        return $default;
    }

    /** Conversión a float (admite 1.234,56 o 1,234.56 o 1234.56) */
    private function f($v): ?float
    {
        if ($v === null) return null;
        $s = trim((string)$v);
        if ($s === '') return null;

        // Si trae coma como decimal (ej. 1234,56) lo convertimos a punto
        if (preg_match('/^\d{1,3}(\.\d{3})*,\d+$/', $s)) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } else {
            // quita separadores de miles con coma (1,234.56 -> 1234.56)
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
