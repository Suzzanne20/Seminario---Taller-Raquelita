<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insumo_ot', function (Blueprint $table) {
            // quita la FK actual
            $table->dropForeign(['orden_trabajo_id']);
            // vuelve a crearla con CASCADE
            $table->foreign('orden_trabajo_id')
                ->references('id')->on('orden_trabajo')
                ->onDelete('cascade');
        });

        Schema::table('asignacion_orden', function (Blueprint $table) {
            $table->dropForeign(['orden_trabajo_id']);
            $table->foreign('orden_trabajo_id')
                ->references('id')->on('orden_trabajo')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('insumo_ot', function (Blueprint $table) {
            $table->dropForeign(['orden_trabajo_id']);
            $table->foreign('orden_trabajo_id')
                ->references('id')->on('orden_trabajo');
        });

        Schema::table('asignacion_orden', function (Blueprint $table) {
            $table->dropForeign(['orden_trabajo_id']);
            $table->foreign('orden_trabajo_id')
                ->references('id')->on('orden_trabajo');
        });
    }
};
