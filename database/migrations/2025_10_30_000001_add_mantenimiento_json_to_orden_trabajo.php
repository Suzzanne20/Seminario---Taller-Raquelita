<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orden_trabajo', function (Blueprint $t) {
            // JSON flexible para tildes del checklist
            $t->json('mantenimiento_json')->nullable()->after('descripcion');
        });
    }
    public function down(): void {
        Schema::table('orden_trabajo', function (Blueprint $t) {
            $t->dropColumn('mantenimiento_json');
        });
    }
};