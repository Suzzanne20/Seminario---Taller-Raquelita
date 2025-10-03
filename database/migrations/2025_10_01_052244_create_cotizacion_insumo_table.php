<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cotizacion_insumo', function (Blueprint $table) {
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('insumo_id');
            $table->decimal('cantidad', 10, 2);
            $table->primary(['cotizacion_id','insumo_id']);

            $table->foreign('cotizacion_id')->references('id')->on('cotizaciones');
            $table->foreign('insumo_id')->references('id')->on('insumo');
        });
    }
    public function down(): void {
        Schema::dropIfExists('cotizacion_insumo');
    }
};

