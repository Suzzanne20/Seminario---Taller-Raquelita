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
        Schema::create('orden_compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_compra_id');
            $table->unsignedBigInteger('insumo_id');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            $table->foreign('orden_compra_id')->references('id')->on('orden_compra')->onDelete('cascade');
            $table->foreign('insumo_id')->references('id')->on('insumo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_compra_detalle');
    }
};
