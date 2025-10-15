<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cotizacion_insumo', function (Blueprint $table) {
            $table->decimal('precio_unitario', 10, 2)->default(0)->after('insumo_id');
        });
    }

    public function down()
    {
        Schema::table('cotizacion_insumo', function (Blueprint $table) {
            $table->dropColumn('precio_unitario');
        });
    }

};
