<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class OrdenTrabajoImportSeeder extends Seeder
{
    public function run()
    {
        $csvFile = database_path('seeders/data/ordenes_trabajo.csv');
        
        if (!File::exists($csvFile)) {
            $this->command->error("Archivo no encontrado: $csvFile");
            return;
        }

        // OBTENER IDs REALES DE TU BASE DE DATOS
        $defaults = [
            'id_creador' => 1, // Ajusta según tu BD
            'type_service_id' => 1, // Ajusta según tu BD
            'estado_id' => 5, // Ajusta según tu BD
        ];

        // Leer todo el contenido del archivo
        $content = File::get($csvFile);
        $lines = explode("\n", trim($content));
        
        // Obtener headers (primera línea)
        $headers = str_getcsv(trim($lines[0]), ';');
        
        $csvData = [];
        
        // Procesar las demás líneas
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;
            
            $row = str_getcsv($line, ';');
            $csvData[] = $row;
        }

        $imported = 0;
        $errors = 0;
        $placasNoEncontradas = [];

        foreach ($csvData as $index => $row) {
            $currentLine = $index + 2;
            
            if (count($row) < 1) {
                $errors++;
                continue;
            }

            try {
                // Buscar la placa
                $placa = $this->findPlaca($row, $headers);
                
                if (!$placa) {
                    $errors++;
                    continue;
                }

                // Combinar headers con datos
                $data = [];
                foreach ($headers as $i => $header) {
                    if (isset($row[$i])) {
                        $data[$header] = $row[$i];
                    } else {
                        $data[$header] = '';
                    }
                }

                // Verificar si la placa existe en la tabla vehiculo
                $vehiculoExiste = DB::table('vehiculo')->where('placa', $placa)->exists();
                
                if (!$vehiculoExiste) {
                    $placasNoEncontradas[] = $placa;
                    $errors++;
                    continue;
                }

                // Construir el mantenimiento_json en el MISMO FORMATO que el controlador
                $mantenimientoJson = $this->buildMantenimientoJsonCompatible($data);
                
                // Preparar datos para la orden de trabajo
                $ordenData = [
                    'fecha_creacion' => $this->parseFecha($data['fecha'] ?? now()->format('d/m/Y')),
                    'descripcion' => $this->buildDescripcion($data),
                    'mantenimiento_json' => $mantenimientoJson,
                    'kilometraje' => $this->cleanKilometraje($data['este_cambio'] ?? ''),
                    'proximo_servicio' => $this->cleanKilometraje($data['proximo_cambio'] ?? ''),
                    'costo_mo' => 0.00,
                    'total' => 0.00,
                    'id_creador' => $defaults['id_creador'],
                    'vehiculo_placa' => $placa,
                    'type_service_id' => $defaults['type_service_id'],
                    'estado_id' => $defaults['estado_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                DB::table('orden_trabajo')->insert($ordenData);
                $imported++;

                if ($imported % 100 === 0) {
                    $this->command->info("Procesados: $imported órdenes...");
                }

            } catch (\Exception $e) {
                $errors++;
                if ($errors <= 10) {
                    $this->command->warn("Error en fila {$currentLine}: " . $e->getMessage());
                }
            }
        }

        $this->command->info("   ✅ Importación completada:");
        $this->command->info("   ✅ Órdenes importadas: $imported");
        $this->command->info("   ❌ Errores: $errors");
        
        if (!empty($placasNoEncontradas)) {
            $this->command->warn("   🚫 Placas no encontradas: " . count(array_unique($placasNoEncontradas)) . " placas únicas");
        }
    }

    /**
     * Buscar la placa en diferentes posiciones posibles
     */
    private function findPlaca($row, $headers)
    {
        foreach ($headers as $i => $header) {
            if (strtolower(trim($header)) === 'placa' && isset($row[$i])) {
                return trim($row[$i]);
            }
        }
        
        if (isset($row[0]) && !empty(trim($row[0]))) {
            return trim($row[0]);
        }
        
        return null;
    }

    /**
     * Convertir fecha de formato dd/mm/yyyy a Y-m-d H:i:s
     */
    private function parseFecha($fecha)
    {
        try {
            return Carbon::createFromFormat('d/m/Y', trim($fecha))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return now()->format('Y-m-d H:i:s');
        }
    }

    /**
     * Construir JSON de mantenimiento en el MISMO FORMATO que el controlador
     */
    private function buildMantenimientoJsonCompatible($data)
    {
        // Mismo formato que la función normalizeChecks() del controlador
        $mantenimiento = [
            'filtro_aceite' => isset($data['filtro_aceite']) && trim($data['filtro_aceite']) === 'SI',
            'filtro_aire' => isset($data['filtro_aire']) && trim($data['filtro_aire']) === 'SI',
            'filtro_a_acondicionado' => false, // No viene en el CSV histórico
            'filtro_caja' => false, // No viene en el CSV histórico
            'aceite_diferencial' => false, // No viene en el CSV histórico
            'filtro_combustible' => isset($data['filtro_diesel']) && trim($data['filtro_diesel']) === 'SI',
            'aceite_hidraulico' => false, // No viene en el CSV histórico
            'transfer' => false, // No viene en el CSV histórico
            'engrase' => isset($data['engrase']) && trim($data['engrase']) === 'SI',
        ];

        return json_encode($mantenimiento, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Construir descripción automática
     */
    private function buildDescripcion($data)
    {
        $servicios = [];
        
        // Usar los mismos nombres que en el controlador
        if (isset($data['filtro_aceite']) && trim($data['filtro_aceite']) === 'SI') {
            $servicios[] = 'filtro aceite';
        }
        if (isset($data['filtro_aire']) && trim($data['filtro_aire']) === 'SI') {
            $servicios[] = 'filtro aire';
        }
        if (isset($data['filtro_diesel']) && trim($data['filtro_diesel']) === 'SI') {
            $servicios[] = 'filtro combustible';
        }
        if (isset($data['engrase']) && trim($data['engrase']) === 'SI') {
            $servicios[] = 'engrase';
        }
        
        $descripcion = 'Mantenimiento preventivo';
        if (!empty($servicios)) {
            $descripcion .= ' - ' . implode(', ', $servicios);
        }
        
        if (!empty($data['observaciones'])) {
            $observaciones = substr(trim($data['observaciones']), 0, 50);
            $descripcion .= ' - ' . $observaciones;
        }
        
        return substr($descripcion, 0, 100);
    }

    /**
     * Limpiar y convertir kilometraje
     */
    private function cleanKilometraje($value)
    {
        $value = trim($value);
        
        if (empty($value) || $value === 'NO' || $value === 'SI' || $value === 'X') {
            return null;
        }
        
        $value = str_replace([',', '.'], '', $value);
        
        return is_numeric($value) ? (int)$value : null;
    }
}