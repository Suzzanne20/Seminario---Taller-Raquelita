<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::create('vehiculo', function (Blueprint $table) {
            $table->char('placa', 7)->primary();
            $table->smallInteger('modelo');
            $table->string('linea', 45);
            $table->string('motor', 45);
            $table->decimal('cilindraje', 10, 2)->nullable();

            // Se corrige la relación los vehiculos pertenecen a una marca
            $table->foreignId('marca_id')
                  ->constrained('marca')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();     // para evitar borrar marcas con vehículos

            $table->timestamps();
        });

        // CHECK de formato: 1 letra + 3 dígitos + 3 letras (ej: P123ABC)
        DB::statement("
            ALTER TABLE vehiculo
            ADD CONSTRAINT chk_placa_formato
            CHECK (placa = UPPER(placa) AND placa REGEXP '^[A-Z][0-9]{3}[A-Z]{3}$')
        ");
    }
    public function down(): void {
        Schema::dropIfExists('vehiculo');
    }
};

