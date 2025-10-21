<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orden_compra', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_orden');
            $table->date('fecha_entrega_esperada')->nullable();
            $table->unsignedBigInteger('proveedor_id');
            $table->enum('estado', ['pendiente', 'aprobada', 'recibida', 'cancelada', 'finalizado'])->default('pendiente');
            $table->decimal('total', 10, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('proveedor_id')->references('id')->on('proveedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compra');
    }
};
