<?php

namespace Tests\Feature;

use App\Models\OrdenTrabajo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrdenTrabajoPlacaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('vehiculo', function (Blueprint $table) {
            $table->string('placa')->primary();
        });
        Schema::create('orden_trabajo', function (Blueprint $table) {
            $table->id();
            $table->string('vehiculo_placa');
        });
    }

    public function test_imported_plates_load_their_vehicle_and_render_without_changing_stored_data(): void
    {
        DB::table('vehiculo')->insert([
            ['placa' => 'P181KGY'],
            ['placa' => 'P309JKC'],
        ]);
        DB::table('orden_trabajo')->insert([
            ['id' => 1, 'vehiculo_placa' => 'P181kgy'],
            ['id' => 2, 'vehiculo_placa' => 'p309JKC'],
            ['id' => 3, 'vehiculo_placa' => 'P181KGY'],
        ]);

        $ordenes = OrdenTrabajo::with('vehiculo')->orderBy('id')->get();
        foreach ($ordenes as $orden) {
            $this->assertNotNull($orden->vehiculo);
            $this->assertSame($orden->vehiculo_placa, $orden->vehiculo->placa);
            $orden->vehiculo->setRelation('marca', null);
            $orden->vehiculo->setRelation('clientes', collect());
            $orden->setRelation('insumos', collect());
            $orden->setRelation('servicio', null);
            $orden->setRelation('estado', null);
        }

        $html = view('ordenes.partials.table', compact('ordenes'))->render();
        $this->assertStringContainsString('P181KGY', $html);
        $this->assertStringContainsString('P309JKC', $html);
        $this->assertSame('P181kgy', DB::table('orden_trabajo')->where('id', 1)->value('vehiculo_placa'));
        $this->assertSame('p309JKC', DB::table('orden_trabajo')->where('id', 2)->value('vehiculo_placa'));
    }

    public function test_table_renders_an_order_even_when_its_vehicle_is_missing(): void
    {
        $orden = new OrdenTrabajo(['vehiculo_placa' => 'P181kgy']);
        $orden->id = 1;
        foreach (['vehiculo', 'servicio', 'estado'] as $relation) {
            $orden->setRelation($relation, null);
        }
        $orden->setRelation('insumos', collect());

        $html = view('ordenes.partials.table', ['ordenes' => collect([$orden])])->render();
        $this->assertStringContainsString('P181KGY', $html);
    }
}
