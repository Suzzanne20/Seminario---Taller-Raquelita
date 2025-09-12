<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) ORDEN_TRABAJO.id_creador -> users.id
        if (Schema::hasTable('orden_trabajo') && Schema::hasColumn('orden_trabajo', 'id_creador')) {
            Schema::table('orden_trabajo', function (Blueprint $table) {

                $table->dropForeign('fk_orden_trabajo_usuario1');
            });

            Schema::table('orden_trabajo', function (Blueprint $table) {
                $table->unsignedBigInteger('id_creador')->change();
            });
            // Nueva FK a users.id
            Schema::table('orden_trabajo', function (Blueprint $table) {
                $table->foreign('id_creador')->references('id')->on('users')
                    ->cascadeOnUpdate()->restrictOnDelete();
            });
        }

        // 2) ASIGNACION_ORDEN.usuario_id -> users.id
        if (Schema::hasTable('asignacion_orden') && Schema::hasColumn('asignacion_orden', 'usuario_id')) {
            Schema::table('asignacion_orden', function (Blueprint $table) {
                $table->dropForeign('fk_orden_trabajo_has_usuario_usuario1');
            });
            Schema::table('asignacion_orden', function (Blueprint $table) {
                $table->unsignedBigInteger('usuario_id')->change();
            });
            Schema::table('asignacion_orden', function (Blueprint $table) {
                $table->foreign('usuario_id')->references('id')->on('users')
                    ->cascadeOnUpdate()->restrictOnDelete();
            });
        }

        // 3) ESPECIALIDAD_USUARIO.usuario_id -> users.id
        if (Schema::hasTable('especialidad_usuario') && Schema::hasColumn('especialidad_usuario', 'usuario_id')) {
            Schema::table('especialidad_usuario', function (Blueprint $table) {
                $table->dropForeign('fk_especialidad_has_usuario_usuario1');
            });
            Schema::table('especialidad_usuario', function (Blueprint $table) {
                $table->unsignedBigInteger('usuario_id')->change();
            });
            Schema::table('especialidad_usuario', function (Blueprint $table) {
                $table->foreign('usuario_id')->references('id')->on('users')
                    ->cascadeOnUpdate()->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orden_trabajo') && Schema::hasColumn('orden_trabajo', 'id_creador')) {
            Schema::table('orden_trabajo', function (Blueprint $table) {
                $table->dropForeign(['id_creador']);
            });
        }
        if (Schema::hasTable('asignacion_orden') && Schema::hasColumn('asignacion_orden', 'usuario_id')) {
            Schema::table('asignacion_orden', function (Blueprint $table) {
                $table->dropForeign(['usuario_id']);
            });
        }
        if (Schema::hasTable('especialidad_usuario') && Schema::hasColumn('especialidad_usuario', 'usuario_id')) {
            Schema::table('especialidad_usuario', function (Blueprint $table) {
                $table->dropForeign(['usuario_id']);
            });
        }
    }
};
