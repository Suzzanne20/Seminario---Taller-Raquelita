<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//Tabla pivote entre Insumo y Ordenes de Trabajo UwU
return new class extends Migration {
    public function up(): void {
        Schema::create('insumo_ot', function (Blueprint $table) {
            $table->unsignedBigInteger('insumo_id');
            $table->unsignedBigInteger('orden_trabajo_id');
            $table->decimal('cantidad', 10, 2);
            $table->primary(['insumo_id','orden_trabajo_id']);

            $table->foreign('insumo_id')->references('id')->on('insumo');
            $table->foreign('orden_trabajo_id')->references('id')->on('orden_trabajo');
        });
    }
    public function down(): void {
        Schema::dropIfExists('insumo_ot');
    }
};

