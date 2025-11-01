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

        DB::table('estado')->upsert([
            ['id'=>1,'nombre'=>'Nueva'],
            ['id'=>2,'nombre'=>'Asignada'],
            ['id'=>3,'nombre'=>'En Proceso'],
            ['id'=>4,'nombre'=>'Pendiente'],
            ['id'=>5,'nombre'=>'Finalizada'],
            ['id'=>6,'nombre'=>'Aprobada'],
            ['id'=>7,'nombre'=>'Rechazada'],
        ], ['id'], ['nombre']);

        DB::table('type_vehiculo')->insertOrIgnore([
            ['id'=>1,'descripcion'=>'Sedan'],
            ['id'=>2,'descripcion'=>'Camioneta'],
            ['id'=>3,'descripcion'=>'Pick up'],
        ]);

        DB::table('type_insumo')->insertOrIgnore([
            ['id'=>1,'nombre'=>'Aceite'],
            ['id'=>2,'nombre'=>'Filtro de Aceite'],
            ['id'=>3,'nombre'=>'Depurador de Aire'],
            ['id'=>4,'nombre'=>'Accesorios'],
            ['id'=>5,'nombre'=>'Fricciones'],
        ]);

        DB::table('type_service')->insertOrIgnore([
            ['id'=>1,'descripcion'=>'Preventivo'],
            ['id'=>2,'descripcion'=>'Correctivo'],
            ['id'=>3,'descripcion'=>'Frenos'],
        ]);

    }
}
