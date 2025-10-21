<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TypeInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Tipos de insumos comunes en un taller mecánico
        DB::table('type_insumo')->insert([
            ['nombre' => 'Lubricantes y Fluidos', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Filtros', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Partes de Motor', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Herramientas y Consumibles', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Frenos', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
