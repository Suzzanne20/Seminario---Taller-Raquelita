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
        // Limpiar la tabla primero (opcional)
        // Marca::truncate();

        $marcas = [
            [
                'nombre' => 'AUDI',
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
                'nombre' => 'HONDA',
                'activo' => 1,
                'mostrar_en_registro' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        // Usar DB facade para mejor performance
        DB::table('marca')->insert($marcas);

        $this->command->info('✅ 3 marcas insertadas: AUDI, CHEVROLET, HONDA');
    }
}