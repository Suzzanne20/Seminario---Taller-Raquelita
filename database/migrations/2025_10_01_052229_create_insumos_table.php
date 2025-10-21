<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('insumo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->decimal('costo', 10, 2)->nullable();
            $table->integer('stock')->nulleable();
            $table->integer('stock_minimo');
            $table->string('descripcion', 200);
            $table->unsignedBigInteger('type_insumo_id');
            $table->decimal('precio', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('type_insumo_id')->references('id')->on('type_insumo');
        });
    }
    public function down(): void {
        Schema::dropIfExists('insumo');
    }
};
