<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orden_trabajo', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_creacion')->useCurrent();
            $table->string('descripcion', 100)->nullable();
            $table->integer('kilometraje')->nullable();
            $table->integer('proximo_servicio')->nullable();
            $table->decimal('costo_mo', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->unsignedBigInteger('id_creador');      
            $table->char('vehiculo_placa', 7);
            $table->unsignedBigInteger('type_service_id');
            $table->unsignedBigInteger('estado_id');           

            $table->timestamps();

            $table->foreign('id_creador')->references('id')->on('users');
            $table->foreign('vehiculo_placa')->references('placa')->on('vehiculo');
            $table->foreign('type_service_id')->references('id')->on('type_service');
            $table->foreign('estado_id')->references('id')->on('estado');
        });
    }
    public function down(): void {
        Schema::dropIfExists('orden_trabajo');
    }
};

