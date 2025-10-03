<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('asignacion_orden', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_trabajo_id');
            $table->unsignedBigInteger('usuario_id'); 
            $table->primary(['orden_trabajo_id','usuario_id']);

            $table->foreign('orden_trabajo_id')->references('id')->on('orden_trabajo')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('asignacion_orden');
    }
};

