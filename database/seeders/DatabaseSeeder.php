<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminUserSeeder::class,
            DemoUsersSeeder::class,
        ]);

        // Seeders de datos de prueba para Inventario y Compras
        $this->call([
            TypeInsumoSeeder::class, // Necesario antes de InsumoSeeder
            ProveedorSeeder::class,
            InsumoSeeder::class,     // Depende de TypeInsumoSeeder
        ]);

        $this->call(CatalogosSeeder::class);

    }
}
