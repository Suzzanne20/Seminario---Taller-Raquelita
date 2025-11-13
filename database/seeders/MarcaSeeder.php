<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Marca;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la tabla primero (opcional - descomenta si quieres resetear)
        // Marca::truncate();

        $marcas = [
            [
                'nombre' => 'ACURA',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'AMAROK',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'AUDI',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'BMW',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'CHANGAN',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'CHERY',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'CHEVROLET',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'DODGE',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'FIAT',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'FORD',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'FOTON',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'GEO',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'GMC',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'GWM',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'HATCHBACK',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'HINO',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'HOMMER',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'HONDA',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'HYUNDAI',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'INFINITI',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'ISUZU',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'JAC',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'JEEP',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'JMT',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'KIA',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'LAND ROVER',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'LEXUS',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'MAHINDRA',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'MAZDA',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'MERCEDES',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'MINI COOPER',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'MITSUBISHI',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'NEON',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'NISSAN',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'PONTIAC',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'PORSCHE',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'RENAULT',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'SATURN VUE',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'SCION',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'SSANG YONG',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'SUBARU',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'SUZUKI',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'TERRACAN',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'TOYOTA',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'VOLKSWAGEN',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nombre' => 'VOLVO',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Usar DB facade para mejor performance
        DB::table('marca')->insert($marcas);

        $this->command->info('✅ ' . count($marcas) . ' marcas insertadas correctamente en orden alfabético.');
    }
}