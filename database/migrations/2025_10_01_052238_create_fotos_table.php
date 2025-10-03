<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('foto', function (Blueprint $table) {
            $table->id();
            $table->string('path_foto', 255);
            $table->string('descripcion', 255)->nullable();
            $table->unsignedBigInteger('recepcion_id');
            $table->timestamps();

            $table->foreign('recepcion_id')->references('id')->on('recepcion');
        });
    }
    public function down(): void {
        Schema::dropIfExists('foto');
    }
};
