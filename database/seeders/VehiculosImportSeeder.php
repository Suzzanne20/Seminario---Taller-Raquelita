<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class VehiculosImportSeeder extends Seeder
{
    public function run()
    {
        $csvFile = database_path('seeders/data/vehiculos.csv');
        
        if (!File::exists($csvFile)) {
            $this->command->error("Archivo no encontrado: $csvFile");
            return;
        }

        // Leer el archivo línea por línea y separar por punto y coma
        $csvData = [];
        $file = fopen($csvFile, 'r');
        
        while (($line = fgets($file)) !== false) {
            $row = str_getcsv(trim($line), ';'); // ← CAMBIADO: usar ';' como delimitador
            $csvData[] = $row;
        }
        fclose($file);

        // Definir manualmente TODAS las columnas según el DDL
        $headers = [
            'placa', 'modelo', 'linea', 'motor', 'cilindraje', 'marca_id',
            'cantidad_aceite_motor', 'marca_aceite', 'tipo_aceite', 'filtro_aceite',
            'filtro_aire', 'cantidad_aceite_cc', 'marca_cc', 'tipo_aceite_cc',
            'filtro_aceite_cc', 'filtro_de_enfriador', 'tipo_caja',
            'cantidad_aceite_diferencial', 'marca_aceite_d', 'tipo_aceite_d',
            'cantidad_aceite_transfer', 'marca_aceite_t', 'tipo_aceite_t',
            'filtro_cabina', 'filtro_diesel', 'contra_filtro_diesel',
            'candelas', 'pastillas_delanteras', 'pastillas_traseras',
            'fajas', 'aceite_hidraulico'
        ];

        $imported = 0;
        $errors = 0;

        foreach ($csvData as $index => $row) {
            // Verificar que la fila tenga exactamente 32 columnas
            if (count($row) != 31) {
                $this->command->warn("Fila {$index} tiene " . count($row) . " columnas, se esperaban 31. Datos: " . implode(',', $row));
                $errors++;
                continue;
            }

            try {
                $data = array_combine($headers, $row);
                
                // Limpiar y formatear datos según el tipo de columna
                $vehiculoData = [
                    'placa' => strtoupper(trim($data['placa'])),
                    'modelo' => $this->cleanModelo($data['modelo']),
                    'linea' => $this->cleanString($data['linea']),
                    'motor' => $this->cleanString($data['motor']),
                    'cilindraje' => $this->cleanCilindraje($data['cilindraje']),
                    'marca_id' => (int)$data['marca_id'],
                    'cantidad_aceite_motor' => $this->cleanNullable($data['cantidad_aceite_motor']),
                    'marca_aceite' => $this->cleanNullable($data['marca_aceite']),
                    'tipo_aceite' => $this->cleanNullable($data['tipo_aceite']),
                    'filtro_aceite' => $this->cleanNullable($data['filtro_aceite']),
                    'filtro_aire' => $this->cleanNullable($data['filtro_aire']),
                    'cantidad_aceite_cc' => $this->cleanNullable($data['cantidad_aceite_cc']),
                    'marca_cc' => $this->cleanNullable($data['marca_cc']),
                    'tipo_aceite_cc' => $this->cleanNullable($data['tipo_aceite_cc']),
                    'filtro_aceite_cc' => $this->cleanNullable($data['filtro_aceite_cc']),
                    'filtro_de_enfriador' => $this->cleanNullable($data['filtro_de_enfriador']),
                    'tipo_caja' => $this->cleanNullable($data['tipo_caja']),
                    'cantidad_aceite_diferencial' => $this->cleanNullable($data['cantidad_aceite_diferencial']),
                    'marca_aceite_d' => $this->cleanNullable($data['marca_aceite_d']),
                    'tipo_aceite_d' => $this->cleanNullable($data['tipo_aceite_d']),
                    'cantidad_aceite_transfer' => $this->cleanNullable($data['cantidad_aceite_transfer']),
                    'marca_aceite_t' => $this->cleanNullable($data['marca_aceite_t']),
                    'tipo_aceite_t' => $this->cleanNullable($data['tipo_aceite_t']),
                    'filtro_cabina' => $this->cleanNullable($data['filtro_cabina']),
                    'filtro_diesel' => $this->cleanNullable($data['filtro_diesel']),
                    'contra_filtro_diesel' => $this->cleanNullable($data['contra_filtro_diesel']),
                    'candelas' => $this->cleanNullable($data['candelas']),
                    'pastillas_delanteras' => $this->cleanNullable($data['pastillas_delanteras']),
                    'pastillas_traseras' => $this->cleanNullable($data['pastillas_traseras']),
                    'fajas' => $this->cleanNullable($data['fajas']),
                    'aceite_hidraulico' => $this->cleanNullable($data['aceite_hidraulico']),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                DB::table('vehiculo')->insert($vehiculoData);
                $imported++;

                // Mostrar progreso cada 100 registros
                if ($imported % 100 === 0) {
                    $this->command->info("Procesados: $imported vehículos...");
                }

            } catch (\Exception $e) {
                $errors++;
                $placa = $data['placa'] ?? 'DESCONOCIDA';
                $this->command->warn("Error con placa {$placa} (fila {$index}): " . $e->getMessage());
            }
        }

        $this->command->info("✅ Importación completada:");
        $this->command->info("   ✅ Vehículos importados: $imported");
        $this->command->info("   ❌ Errores: $errors");
        $this->command->info("   📊 Total procesado: " . ($imported + $errors));
    }

    /**
     * Limpiar campo modelo (smallint)
     */
    private function cleanModelo($value)
    {
        if (empty($value) || $value === 'X' || $value === 'XX') {
            return 0;
        }
        return is_numeric($value) ? (int)$value : 0;
    }

    /**
     * Limpiar campo cilindraje (decimal)
     */
    private function cleanCilindraje($value)
    {
        if (empty($value) || $value === 'X' || $value === 'XX') {
            return null;
        }
        return is_numeric($value) ? (float)$value : null;
    }

    /**
     * Limpiar campos string (NOT NULL)
     */
    private function cleanString($value)
    {
        if (empty($value) || $value === 'X' || $value === 'XX') {
            return '';
        }
        return trim($value);
    }

    /**
     * Limpiar campos NULLables
     */
    private function cleanNullable($value)
    {
        if (empty($value) || $value === 'X' || $value === 'XX') {
            return null;
        }
        return trim($value);
    }
}