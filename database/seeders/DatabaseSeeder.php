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
            TypeInsumoSeeder::class, // Catalogos previos antes de tablas pivote
            ProveedorSeeder::class,
            InsumoSeeder::class,     // Depende de TypeInsumoSeeder
        ]);

        // NUEVOS SEEDERS PARA VEHÍCULOS (añadidos al final)
        $this->call([
            MarcaSeeder::class,      // Debe ejecutarse primero
            VehiculoSeeder::class,   // Depende de MarcaSeeder
        ]);

    }
}