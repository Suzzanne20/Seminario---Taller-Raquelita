<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('proveedor')->insert([
            [
                'nombre' => 'LubriMax Distribuidor',
                'nit' => '900123456-1',
                'telefono' => '555-3001',
                'email' => 'ventas@lubrimax.com',
                'direccion' => 'Calle Los Fluidos #15, Bodega C',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Repuestos Rápido',
                'nit' => '800654321-0',
                'telefono' => '555-3002',
                'email' => 'contacto@rapido.net',
                'direccion' => 'Avenida Principal #45, Galpón Sur',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'nombre' => 'Frenos Seguros S.A.',
                'nit' => '700987654-2',
                'telefono' => '555-3003',
                'email' => 'pedidos@frenosseguros.com',
                'direccion' => 'Vía 3, Parque Industrial',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);
    }
}
