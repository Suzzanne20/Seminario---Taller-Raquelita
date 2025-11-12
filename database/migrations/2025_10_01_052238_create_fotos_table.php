<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('foto', function (Blueprint $table) {
            $table->id();
            // Se crea como BLOB genérico primero
            $table->binary('path_foto'); // BLOB temporal
            $table->string('descripcion', 255)->nullable();
            $table->unsignedBigInteger('recepcion_id');
            $table->timestamps();

            $table->foreign('recepcion_id')->references('id')->on('recepcion');
        });

        // Si el driver es MySQL/MariaDB, convierte a MEDIUMBLOB
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `foto` MODIFY `path_foto` MEDIUMBLOB');
        }
    }

    public function down(): void {
        Schema::dropIfExists('foto');
    }
};
