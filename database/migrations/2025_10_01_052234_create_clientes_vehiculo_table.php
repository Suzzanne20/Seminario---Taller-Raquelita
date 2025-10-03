<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//Tabla pivote para Cliente y Vehiculo
return new class extends Migration {
    public function up(): void {
        Schema::create('cliente_vehiculo', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id');
            $table->char('vehiculo_placa', 7);
            $table->primary(['cliente_id','vehiculo_placa']);

            $table->foreign('cliente_id')->references('id')->on('cliente');
            $table->foreign('vehiculo_placa')->references('placa')->on('vehiculo');
        });
    }
    public function down(): void {
        Schema::dropIfExists('cliente_vehiculo');
    }
};
