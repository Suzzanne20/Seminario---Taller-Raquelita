<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('insumo')->insert([
            // --- Lubricantes y Fluidos (ID 1) ---
            [
                'nombre' => 'Aceite de Motor Sintético 5W-30 (Galón)',
                'costo' => 8.50,
                'stock' => 150.00,
                'stock_minimo' => 50.00,
                'descripcion' => 'Aceite sintético de alto rendimiento para motores modernos.',
                'type_insumo_id' => 1,
                'precio' => 12.00,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Líquido de Frenos DOT 4 (Litro)',
                'costo' => 4.50,
                'stock' => 80.00,
                'stock_minimo' => 30.00,
                'descripcion' => 'Fluido hidráulico para sistemas de frenos DOT 4.',
                'type_insumo_id' => 1,
                'precio' => 7.50,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Anticongelante Concentrado (Galón)',
                'costo' => 15.00,
                'stock' => 75.00,
                'stock_minimo' => 25.00,
                'descripcion' => 'Líquido refrigerante concentrado color verde.',
                'type_insumo_id' => 1,
                'precio' => 20.00,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- Filtros (ID 2) ---
            [
                'nombre' => 'Filtro de Aceite Universal 1',
                'costo' => 3.20,
                'stock' => 300.00,
                'stock_minimo' => 100.00,
                'descripcion' => 'Filtro de aceite genérico para la mayoría de vehículos.',
                'type_insumo_id' => 2,
                'precio' => 5.50,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Filtro de Aire Deportivo',
                'costo' => 12.00,
                'stock' => 40.00,
                'stock_minimo' => 15.00,
                'descripcion' => 'Filtro de aire lavable de alto rendimiento.',
                'type_insumo_id' => 2,
                'precio' => 18.00,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- Partes de Motor (ID 3) ---
            [
                'nombre' => 'Bujía de Iridio (Unidad)',
                'costo' => 4.00,
                'stock' => 400.00,
                'stock_minimo' => 80.00,
                'descripcion' => 'Bujía de larga duración para alto rendimiento.',
                'type_insumo_id' => 3,
                'precio' => 7.50,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Correa de Distribución (Kit)',
                'costo' => 35.00,
                'stock' => 25.00,
                'stock_minimo' => 5.00,
                'descripcion' => 'Kit completo de correa y tensores.',
                'type_insumo_id' => 3,
                'precio' => 60.00,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- Herramientas y Consumibles (ID 4) ---
            [
                'nombre' => 'Guantes de Nitrilo (Caja)',
                'costo' => 7.00,
                'stock' => 80.00,
                'stock_minimo' => 20.00,
                'descripcion' => 'Guantes desechables de nitrilo, talla L.',
                'type_insumo_id' => 4,
                'precio' => 10.00,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Grasa de Litio Multiuso (Tubo)',
                'costo' => 2.50,
                'stock' => 120.00,
                'stock_minimo' => 40.00,
                'descripcion' => 'Grasa para rodamientos y puntos de fricción.',
                'type_insumo_id' => 4,
                'precio' => 4.50,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Tornillos y Tuercas (Kit Surtido)',
                'costo' => 18.00,
                'stock' => 50.00,
                'stock_minimo' => 15.00,
                'descripcion' => 'Kit surtido de tornillería métrica común.',
                'type_insumo_id' => 4,
                'precio' => 30.00,
                'created_at' => $now,
                'updated_at' => $now
            ],

            // --- Frenos (ID 5) ---
            [
                'nombre' => 'Pastillas de Freno Cerámicas Delanteras',
                'costo' => 25.00,
                'stock' => 50.00,
                'stock_minimo' => 10.00,
                'descripcion' => 'Juego de pastillas de freno de material cerámico.',
                'type_insumo_id' => 5,
                'precio' => 40.00,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Disco de Freno Ventilado',
                'costo' => 45.00,
                'stock' => 30.00,
                'stock_minimo' => 8.00,
                'descripcion' => 'Disco de freno ventilado de alta resistencia.',
                'type_insumo_id' => 5,
                'precio' => 75.00,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
