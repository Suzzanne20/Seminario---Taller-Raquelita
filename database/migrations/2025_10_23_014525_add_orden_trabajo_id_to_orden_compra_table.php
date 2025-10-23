<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orden_compra', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_trabajo_id')->nullable()->after('proveedor_id');

            // Relación opcional pero recomendada
            $table->foreign('orden_trabajo_id')
                ->references('id')
                ->on('orden_trabajo')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('orden_compra', function (Blueprint $table) {
            $table->dropForeign(['orden_trabajo_id']);
            $table->dropColumn('orden_trabajo_id');
        });
    }
};
