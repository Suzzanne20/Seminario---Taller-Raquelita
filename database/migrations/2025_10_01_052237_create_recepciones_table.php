<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recepcion', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_creacion');
            $table->char('vehiculo_placa', 7);
            $table->string('observaciones', 255)->nullable();
            $table->unsignedBigInteger('type_vehiculo_id');
            $table->unsignedBigInteger('id_tecnico'); 
            $table->timestamps();

            $table->foreign('vehiculo_placa')->references('placa')->on('vehiculo');
            $table->foreign('type_vehiculo_id')->references('id')->on('type_vehiculo');
            $table->foreign('id_tecnico')->references('id')->on('users');
        });
    }
    public function down(): void {
        Schema::dropIfExists('recepcion');
    }
};
