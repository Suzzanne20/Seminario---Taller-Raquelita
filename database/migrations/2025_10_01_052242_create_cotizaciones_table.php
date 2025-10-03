<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_creacion');
            $table->string('descripcion', 255);
            $table->decimal('costo_mo', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->unsignedBigInteger('type_service_id');
            $table->unsignedBigInteger('empleado_id')->nullable();   
            $table->unsignedBigInteger('cotizacion_id')->nullable(); 
            $table->unsignedBigInteger('estado_id')->default(1);
            $table->timestamps();

            $table->foreign('type_service_id')->references('id')->on('type_service');
            $table->foreign('empleado_id')->references('id')->on('users');
            $table->foreign('estado_id')->references('id')->on('estado');
        });
    }
    public function down(): void {
        Schema::dropIfExists('cotizaciones');
    }
};
