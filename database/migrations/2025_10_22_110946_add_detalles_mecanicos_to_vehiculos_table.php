<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetallesMecanicosToVehiculosTable extends Migration
{
    public function up()
    {
        Schema::table('vehiculo', function (Blueprint $table) {
            // Cantidades y detalles como texto libre (Opción B)
            $table->string('cantidad_aceite_motor')->nullable();
            $table->string('marca_aceite')->nullable();
            $table->string('tipo_aceite')->nullable();
            $table->string('filtro_aceite')->nullable();
            $table->string('filtro_aire')->nullable();

            $table->string('cantidad_aceite_cc')->nullable();
            $table->string('marca_cc')->nullable();
            $table->string('tipo_aceite_cc')->nullable();
            $table->string('filtro_aceite_cc')->nullable();
            $table->string('filtro_de_enfriador')->nullable();
            $table->string('tipo_caja')->nullable();

            $table->string('cantidad_aceite_diferencial')->nullable();
            $table->string('marca_aceite_d')->nullable();
            $table->string('tipo_aceite_d')->nullable();

            $table->string('cantidad_aceite_transfer')->nullable();
            $table->string('marca_aceite_t')->nullable();
            $table->string('tipo_aceite_t')->nullable();

            $table->string('filtro_cabina')->nullable();
            $table->string('filtro_diesel')->nullable();
            $table->string('contra_filtro_diesel')->nullable();
            $table->string('candelas')->nullable();
            $table->string('pastillas_delanteras')->nullable();
            $table->string('pastillas_traseras')->nullable();
            $table->string('fajas')->nullable();
            $table->string('aceite_hidraulico')->nullable();
        });
    }

    public function down()
    {
        Schema::table('vehiculo', function (Blueprint $table) {
            $table->dropColumn([
                'cantidad_aceite_motor','marca_aceite','tipo_aceite','filtro_aceite','filtro_aire',
                'cantidad_aceite_cc','marca_cc','tipo_aceite_cc','filtro_aceite_cc','filtro_de_enfriador','tipo_caja',
                'cantidad_aceite_diferencial','marca_aceite_d','tipo_aceite_d',
                'cantidad_aceite_transfer','marca_aceite_t','tipo_aceite_t',
                'filtro_cabina','filtro_diesel','contra_filtro_diesel',
                'candelas','pastillas_delanteras','pastillas_traseras','fajas','aceite_hidraulico'
            ]);
        });
    }
}
